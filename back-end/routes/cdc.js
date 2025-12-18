const express = require("express");
const router = express.Router();
const bcrypt = require("bcryptjs");
const db = require("../db");
const { checkAndSetPublishedDate } = require("./student");

// Login
router.post("/login_cdc", async (req, res) => {
  try {
    let { username, password } = req.body || {};
    if (!username || !password) {
      return res.status(400).json({
        success: false,
        message: "Username and password are required",
      });
    }

    username = String(username).trim();
    password = String(password).trim();

    const [rows] = await db.query(
      "SELECT * FROM upkpk WHERE LOWER(username) = LOWER(?)",
      [username]
    );

    if (!rows || rows.length === 0) {
      return res
        .status(401)
        .json({ success: false, message: "Account not found" });
    }

    const user = rows[0];
    if (user.status && user.status.toLowerCase() !== "active") {
      return res
        .status(403)
        .json({ success: false, message: "Account inactive" });
    }

    const stored = (user.password || "").trim();
    let passwordMatch = false;

    if (
      stored.startsWith("$2a$") ||
      stored.startsWith("$2b$") ||
      stored.startsWith("$2y$")
    ) {
      passwordMatch = await bcrypt.compare(password, stored);
    } else {
      passwordMatch = stored === password;
    }

    if (!passwordMatch) {
      return res
        .status(401)
        .json({ success: false, message: "Incorrect password" });
    }

    return res.status(200).json({
      success: true,
      message: "Login succesfull",
      user: {
        id: user.id_upkpk,
        username: user.username,
        name: user.name,
        role: "cdc",
        profile_picture: user.profile_picture || null,
        id_kampus: user.id_kampus || null,
      },
    });
  } catch (err) {
    console.error("[LOGIN CDC] error:", err);
    return res
      .status(500)
      .json({ success: false, message: "An error occurred on the server" });
  }
});

// Detail submission
router.get("/cdc/submissions", async (req, res) => {
  try {
    const [rows] = await db.query(`
      SELECT 
        il.id_letter,
        il.nim,
        s.name AS student_name,
        sp.study_program AS program_study,
        il.company_name,
        il.start_date,
        il.end_date,
        il.status,
        il.koor_approval,
        il.cdc_approval,
        il.company_reply_letter,
        il.acceptance_status,
        il.created_at,
        il.updated_at
      FROM internship_letter il
      JOIN student_internship s ON s.nim = il.nim
      JOIN program_study sp ON sp.kode_prodi = s.program_study
      ORDER BY il.created_at DESC
    `);

    res.json({ success: true, data: rows });
  } catch (err) {
    console.error("[CDC] Error fetching submissions:", err);
    res.status(500).json({ success: false, message: "Server error" });
  }
});

// CDC approval
router.post("/cdc/approval", async (req, res) => {
  try {
    const { id_letter, status, user_id, user_name, comment, letter_number } = req.body;
    const s = status.toUpperCase();

    if (!["ACCEPTED", "REJECTED"].includes(s)) {
      return res.status(400).json({ success: false, message: "Status invalid." });
    }

    const [rows] = await db.query(
      `SELECT koor_approval, cdc_approval
       FROM internship_letter
       WHERE id_letter = ?
       LIMIT 1`,
      [id_letter]
    );

    if (!rows.length) {
      return res.status(404).json({ success: false, message: "Submission not found." });
    }

    const row = rows[0];

    if (row.koor_approval !== "ACCEPTED") {
      return res.status(403).json({ success: false, message: "Koor belum approve." });
    }

    if (row.cdc_approval !== "WAITING") {
      return res.status(400).json({
        success: false,
        message: "Submission tidak dalam status WAITING."
      });
    }

    if (s === "ACCEPTED") {
      if (!letter_number) {
        return res.status(400).json({ success: false, message: "Nomor surat wajib." });
      }

      const [dupRows] = await db.query(
        `SELECT id_letter FROM internship_letter
         WHERE letter_number = ? AND id_letter != ?`,
        [letter_number, id_letter]
      );

      if (dupRows.length) {
        return res.status(400).json({
          success: false,
          message: `Letter number "${letter_number}" sudah digunakan.`
        });
      }

      await db.query(
        `UPDATE internship_letter
         SET cdc_approval = 'ACCEPTED',
             status = 'ACCEPTED',
             letter_number = ?,
             updated_at = NOW()
         WHERE id_letter = ?`,
        [letter_number, id_letter]
      );

      await checkAndSetPublishedDate(id_letter);
    }

    if (s === "REJECTED") {
      await db.query(
        `UPDATE internship_letter
         SET cdc_approval = 'REJECTED',
             status = 'REJECTED',
             updated_at = NOW()
         WHERE id_letter = ?`,
        [id_letter]
      );
    }

    await db.query(
      `INSERT INTO internship_letter_history
       (id_letter, approved_by, user_id, user_name, status_approval, timestamp, comment)
       VALUES (?, 'CDC ADMINISTRATOR', ?, ?, ?, NOW(), ?)`,
      [id_letter, user_id, user_name, s, comment]
    );

    res.json({ success: true, message: `Submission ${s.toLowerCase()} oleh CDC.` });
  } catch (err) {
    console.error("CDC approval error:", err);
    res.status(500).json({ success: false, message: "Server error" });
  }
});

// Get latest rejected reason by CDC for a letter
router.get("/cdc/reason/:id", async (req, res) => {
  try {
    const id = req.params.id;
    const [rows] = await db.query(
      `SELECT comment, timestamp, approved_by, user_name, user_id
       FROM internship_letter_history
       WHERE id_letter = ? AND status_approval = 'REJECTED' AND approved_by = 'CDC ADMINISTRATOR'
       ORDER BY timestamp DESC LIMIT 1`,
      [id]
    );
    if (!rows.length)
      return res
        .status(404)
        .json({ success: false, message: "Reason not found." });
    res.json({
      success: true,
      comment: rows[0].comment,
      meta: { user_name: rows[0].user_name, timestamp: rows[0].timestamp },
    });
  } catch (err) {
    console.error("[CDC] reason fetch error:", err);
    res.status(500).json({ success: false, message: "Server error" });
  }
});

// Edit reason
router.post("/cdc/history/:id/edit", async (req, res) => {
  try {
    const id = req.params.id;
    const { comment } = req.body;
    if (!comment || !comment.trim())
      return res
        .status(400)
        .json({ success: false, message: "Comment is required." });

    // Find the latest history row to update
    const [rows] = await db.query(
      `SELECT id_history FROM internship_letter_history
       WHERE id_letter = ? AND status_approval = 'REJECTED' AND approved_by = 'CDC ADMINISTRATOR'
       ORDER BY timestamp DESC LIMIT 1`,
      [id]
    );
    if (!rows.length)
      return res
        .status(404)
        .json({ success: false, message: "History record not found." });

    const id_history = rows[0].id_history;
    await db.query(
      `UPDATE internship_letter_history
       SET comment = ?, timestamp = NOW()
       WHERE id_history = ?`,
      [comment, id_history]
    );

    res.json({ success: true, message: "Reason updated." });
  } catch (err) {
    console.error("[CDC] history edit error:", err);
    res.status(500).json({ success: false, message: "Server error" });
  }
});

// List submissions dengan filter
router.get("/cdc/submissions-filtered", async (req, res) => {
  try {
    const {
      study_program,
      department,
      student_name,
      coordinator,
      cdc,
      company,
      id_kampus,
    } = req.query;

    let query = `
      SELECT 
        il.id_letter,
        il.nim,
        s.name AS student_name,
        s.program_study AS student_kode_prodi,
        il.company_name,
        il.start_date,
        il.end_date,
        il.status,
        il.koor_approval,
        il.cdc_approval,
        il.company_reply_letter,
        il.acceptance_status,
        il.created_at,
        il.updated_at,
        ps.kode_prodi,
        ps.prodi AS program_name,
        ps.study_program,
        ps.jurusan,
        ps.major
      FROM internship_letter il
      JOIN student_internship s ON s.nim = il.nim
      LEFT JOIN program_study ps ON ps.kode_prodi = s.program_study AND ps.id_kampus = ?
      WHERE 1=1
    `;

    const params = [id_kampus];

    // Filter by study program
    if (study_program) {
      query += ` AND s.program_study = ?`;
      params.push(study_program);
    }

    // Filter by department
    if (department) {
      query += ` AND ps.major = ?`;
      params.push(department);
    }

    // Filter by student name
    if (student_name) {
      query += ` AND s.name LIKE ?`;
      params.push(`%${student_name}%`);
    }

    // Filter by coordinator approval
    if (coordinator) {
      const coordStatus = coordinator.toUpperCase();
      if (coordStatus === "APPROVED") {
        query += ` AND il.koor_approval = 'ACCEPTED'`;
      } else if (coordStatus === "WAITING") {
        query += ` AND il.koor_approval = 'WAITING'`;
      } else if (coordStatus === "REJECTED") {
        query += ` AND il.koor_approval = 'REJECTED'`;
      }
    }

    // Filter by CDC approval
    if (cdc) {
      const cdcStatus = cdc.toUpperCase();
      if (cdcStatus === "APPROVED") {
        query += ` AND il.cdc_approval = 'ACCEPTED'`;
      } else if (cdcStatus === "WAITING") {
        query += ` AND il.cdc_approval = 'WAITING'`;
      } else if (cdcStatus === "REJECTED") {
        query += ` AND il.cdc_approval = 'REJECTED'`;
      }
    }

    // Filter by company result
    if (company) {
      const companyStatus = company.toUpperCase();
      if (companyStatus === "ACCEPTED") {
        query += ` AND il.acceptance_status = 'ACCEPTED'`;
      } else if (companyStatus === "REJECTED") {
        query += ` AND il.acceptance_status = 'REJECTED'`;
      }
    }

    query += ` ORDER BY il.created_at DESC`;

    const [rows] = await db.query(query, params);
    res.json({ success: true, data: rows });
  } catch (err) {
    console.error("[CDC] Error fetching submissions:", err);
    res.status(500).json({ success: false, message: "Server error" });
  }
});

// Get study programs by kampus
router.get("/cdc/study-programs/:id_kampus", async (req, res) => {
  try {
    const { id_kampus } = req.params;

    const [rows] = await db.query(
      `
      SELECT DISTINCT 
        ps.kode_prodi,
        ps.study_program AS program_name,
        ps.study_program,
        ps.jurusan,
        ps.major
      FROM program_study ps
      WHERE ps.id_kampus = ?
      ORDER BY ps.study_program ASC
    `,
      [id_kampus]
    );

    res.json({ success: true, data: rows });
  } catch (err) {
    console.error("[CDC] Error fetching study programs:", err);
    res.status(500).json({ success: false, message: "Server error" });
  }
});

// Get departments by kampus
router.get("/cdc/departments/:id_kampus", async (req, res) => {
  try {
    const { id_kampus } = req.params;

    const [rows] = await db.query(
      `
      SELECT DISTINCT major AS department
      FROM program_study
      WHERE id_kampus = ?
      ORDER BY major ASC
      `,
      [id_kampus]
    );

    res.json({ success: true, data: rows });
  } catch (err) {
    console.error("[CDC] Error fetching departments:", err);
    res.status(500).json({ success: false, message: "Server error" });
  }
});

// Edit submission
router.put("/cdc/submissions/edit/:id_letter", async (req, res) => {
  const { id_letter } = req.params;
  const { company_name, company_address, company_phone, company_email } =
    req.body;

  // Validasi input
  if (!company_name || !company_address) {
    return res.status(400).json({
      success: false,
      message: "Company name and address are required.",
    });
  }

  if (!company_phone && !company_email) {
    return res.status(400).json({
      success: false,
      message: "At least one contact (phone or email) is required.",
    });
  }

  try {
    // Cek apakah submission exists dan ambil id_company
    const [checkRows] = await db.query(
      `SELECT id_letter, id_company FROM internship_letter WHERE id_letter = ?`,
      [id_letter]
    );

    if (!checkRows.length) {
      return res.status(404).json({
        success: false,
        message: "Submission not found",
      });
    }

    const id_company = checkRows[0].id_company;

    // Menggabungkan phone dan email menjadi satu string untuk company_contact
    let contactParts = [];
    if (company_phone) contactParts.push(company_phone);
    if (company_email) contactParts.push(company_email);
    const company_contact = contactParts.join(" ");

    // Update data di tabel company
    if (id_company) {
      await db.query(
        `UPDATE company
         SET name = ?,
             address = ?,
             phone = ?,
             email = ?
         WHERE id_company = ?`,
        [
          company_name,
          company_address,
          company_phone || "-",
          company_email || "-",
          id_company,
        ]
      );
      console.log(`[CDC] Updated company data for id_company: ${id_company}`);
    }

    // Update data di tabel internship_letter
    await db.query(
      `UPDATE internship_letter
       SET company_name = ?,
           company_address = ?,
           company_contact = ?,
           updated_at = NOW()
       WHERE id_letter = ?`,
      [company_name, company_address, company_contact, id_letter]
    );

    console.log(
      `[CDC] Updated internship_letter data for id_letter: ${id_letter}`
    );

    res.json({
      success: true,
      message: "Company information has been updated successfully.",
    });
  } catch (err) {
    console.error("[CDC] Error updating submission:", err);
    res.status(500).json({
      success: false,
      message: "Server error: " + err.message,
    });
  }
});

// Get company reply file info
router.get("/cdc/company-reply/:id_letter", async (req, res) => {
  try {
    const { id_letter } = req.params;

    const [rows] = await db.query(
      `SELECT 
         il.id_letter,
         il.acceptance_status,
         il.company_reply_letter,
         il.published_date,
         il.company_name,
         il.updated_at,
         il.nim,
         s.name AS student_name
       FROM internship_letter il
       JOIN student_internship s ON s.nim = il.nim
       WHERE il.id_letter = ?`,
      [id_letter]
    );

    if (!rows.length) {
      return res.status(404).json({
        success: false,
        message: "Submission not found",
      });
    }

    const data = rows[0];

    // Hitung apakah sudah lebih dari 14 hari
    let isOverdue = false;
    let reason = null;

    if (data.published_date) {
      const publishedDate = new Date(data.published_date);
      const currentDate = new Date();
      const diffTime = currentDate - publishedDate;
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

      if (diffDays > 14 && !data.company_reply_letter) {
        isOverdue = true;
        reason =
          "Students have not received a response to their internship application within 14 days after completing the internship interview.";
      }
    }

    res.json({
      success: true,
      data: {
        ...data,
        isOverdue,
        reason,
      },
    });
  } catch (err) {
    console.error("[LECTURER] Error fetching company reply:", err);
    res.status(500).json({
      success: false,
      message: "Server error",
    });
  }
});

// Get endpoint untuk dashboard statistics
router.get("/dashboard/statistics", async (req, res) => {
  try {
    const { department, year } = req.query;
    const useYearFilter = year && year !== 'all';
    const currentYear = useYearFilter ? year : null;
    const id_kampus = 1;

    const allProgramsQuery = `
          SELECT 
            CONCAT(ps.jenjang, ' ', ps.study_program) AS program_full_name,
            ps.major AS department,
            ps.kode_prodi
          FROM program_study ps
          WHERE ps.id_kampus = ?
            ${department ? "AND ps.major = ?" : ""}
          ORDER BY ps.major, program_full_name
        `;

    const allProgramsParams = department
      ? [id_kampus, department]
      : [id_kampus];

    const [allPrograms] = await db.query(allProgramsQuery, allProgramsParams);

    // Response time (koor & cdc)
    const responseTimeQuery = `
          SELECT 
            CONCAT(ps.jenjang, ' ', ps.study_program) AS program_full_name,
            ps.major AS department,
            AVG(DATEDIFF(ilh_koor.timestamp, il.created_at)) AS avg_response_time_koor,
            AVG(DATEDIFF(ilh_cdc.timestamp, ilh_koor.timestamp)) AS avg_response_time_cdc,
            AVG(DATEDIFF(ilh_cdc.timestamp, il.created_at)) AS avg_total_response_time,
            COUNT(*) AS data_count
          FROM internship_letter il
          INNER JOIN student_internship si ON il.nim = si.nim
          INNER JOIN program_study ps 
            ON si.program_study = ps.kode_prodi 
           AND si.id_kampus = ps.id_kampus
          LEFT JOIN (
            SELECT id_letter, MIN(timestamp) AS timestamp
            FROM internship_letter_history
            WHERE LOWER(approved_by) = 'internship coordinator'
              AND LOWER(status_approval) = 'accepted'
            GROUP BY id_letter
          ) ilh_koor ON il.id_letter = ilh_koor.id_letter
          LEFT JOIN (
            SELECT id_letter, MIN(timestamp) AS timestamp
            FROM internship_letter_history
            WHERE LOWER(approved_by) = 'cdc administrator'
              AND LOWER(status_approval) = 'accepted'
            GROUP BY id_letter
          ) ilh_cdc ON il.id_letter = ilh_cdc.id_letter
          WHERE ps.id_kampus = ?
          ${useYearFilter ? "AND YEAR(il.created_at) = ?" : ""}
          ${department ? "AND ps.major = ?" : ""}
            AND ilh_koor.timestamp IS NOT NULL
            AND ilh_cdc.timestamp IS NOT NULL
          GROUP BY program_full_name, ps.major
        `;

    const responseTimeParams = [id_kampus];
    if (useYearFilter) responseTimeParams.push(currentYear);
    if (department) responseTimeParams.push(department);

    const [responseTimeData] = await db.query(
      responseTimeQuery,
      responseTimeParams
    );

    // Acceptance rate
    const acceptanceRateQuery = `
          SELECT 
            CONCAT(ps.jenjang, ' ', ps.study_program) AS program_full_name,
            ps.major AS department,
            COUNT(CASE WHEN il.acceptance_status = 'ACCEPTED' THEN 1 END) AS accepted_count,
            COUNT(CASE WHEN il.acceptance_status = 'REJECTED' THEN 1 END) AS rejected_count,
            COUNT(*) AS total_count,
            ROUND((COUNT(CASE WHEN il.acceptance_status = 'ACCEPTED' THEN 1 END) / COUNT(*)) * 100, 2) AS acceptance_rate,
            ROUND((COUNT(CASE WHEN il.acceptance_status = 'REJECTED' THEN 1 END) / COUNT(*)) * 100, 2) AS rejection_rate
          FROM internship_letter il
          INNER JOIN student_internship si ON il.nim = si.nim
          INNER JOIN program_study ps 
            ON si.program_study = ps.kode_prodi 
           AND si.id_kampus = ps.id_kampus
          WHERE ps.id_kampus = ?
          ${useYearFilter ? "AND YEAR(il.created_at) = ?" : ""}
          ${department ? "AND ps.major = ?" : ""}
            AND il.acceptance_status IN ('ACCEPTED', 'REJECTED')
          GROUP BY program_full_name, ps.major
        `;

    const acceptanceRateParams = [id_kampus];
    if (useYearFilter) acceptanceRateParams.push(currentYear);
    if (department) acceptanceRateParams.push(department);

    const [acceptanceRateData] = await db.query(
      acceptanceRateQuery,
      acceptanceRateParams
    );

    // Merge data, all programs with actual data
    const responseTimeMap = new Map();
    responseTimeData.forEach((r) => {
      const key = `${r.department}|||${r.program_full_name}`;
      responseTimeMap.set(key, {
        avgResponseTimeKoor: Number(r.avg_response_time_koor || 0).toFixed(2),
        avgResponseTimeCdc: Number(r.avg_response_time_cdc || 0).toFixed(2),
        avgTotalResponseTime: Number(r.avg_total_response_time || 0).toFixed(2),
        dataCount: r.data_count || 0,
      });
    });

    const acceptanceRateMap = new Map();
    acceptanceRateData.forEach((r) => {
      const key = `${r.department}|||${r.program_full_name}`;
      acceptanceRateMap.set(key, {
        acceptedCount: r.accepted_count || 0,
        rejectedCount: r.rejected_count || 0,
        totalCount: r.total_count || 0,
        acceptanceRate: Number(r.acceptance_rate || 0),
        rejectionRate: Number(r.rejection_rate || 0),
      });
    });

    const responseTimeResult = [];
    const acceptanceRateResult = [];

    allPrograms.forEach((program) => {
      const key = `${program.department}|||${program.program_full_name}`;

      // Response time data
      const responseData = responseTimeMap.get(key);
      responseTimeResult.push({
        program: program.program_full_name,
        department: program.department,
        avgResponseTimeKoor: responseData
          ? responseData.avgResponseTimeKoor
          : "0.00",
        avgResponseTimeCdc: responseData
          ? responseData.avgResponseTimeCdc
          : "0.00",
        avgTotalResponseTime: responseData
          ? responseData.avgTotalResponseTime
          : "0.00",
        hasData: !!responseData,
        dataCount: responseData ? responseData.dataCount : 0,
      });

      // Acceptance rate data
      const acceptanceData = acceptanceRateMap.get(key);
      acceptanceRateResult.push({
        program: program.program_full_name,
        department: program.department,
        acceptedCount: acceptanceData ? acceptanceData.acceptedCount : 0,
        rejectedCount: acceptanceData ? acceptanceData.rejectedCount : 0,
        totalCount: acceptanceData ? acceptanceData.totalCount : 0,
        acceptanceRate: acceptanceData ? acceptanceData.acceptanceRate : 0,
        rejectionRate: acceptanceData ? acceptanceData.rejectionRate : 0,
        hasData: !!acceptanceData,
      });
    });

    // Get unique departments list
    const [departments] = await db.query(
      `
          SELECT DISTINCT major AS department
          FROM program_study
          WHERE id_kampus = ?
          ORDER BY major
        `,
      [id_kampus]
    );

    // Final response
    res.json({
      success: true,
      data: {
        year: currentYear || "All Years",
        department: department || "All Departments",
        departments: departments.map((d) => d.department),
        responseTime: responseTimeResult,
        acceptanceRate: acceptanceRateResult,
      },
    });
  } catch (err) {
    console.error("Dashboard statistics error:", err);
    res.status(500).json({
      success: false,
      message: "Server error",
      error: err.message,
    });
  }
});

// Dashboard summary
router.get("/dashboard/summary", async (req, res) => {
  try {
    const { year } = req.query;
    const currentYear = year || new Date().getFullYear();
    const id_kampus = 1;

    // Total submissions
    const [totalSubmissions] = await db.query(
      `SELECT COUNT(*) AS total
       FROM internship_letter il
       INNER JOIN student_internship si ON il.nim = si.nim
       WHERE YEAR(il.created_at) = ?
         AND si.id_kampus = ?`,
      [currentYear, id_kampus]
    );

    // Status breakdown
    const [statusBreakdown] = await db.query(
      `SELECT il.status, COUNT(*) AS count
       FROM internship_letter il
       INNER JOIN student_internship si ON il.nim = si.nim
       WHERE YEAR(il.created_at) = ?
         AND si.id_kampus = ?
       GROUP BY il.status`,
      [currentYear, id_kampus]
    );

    // Average response time
    const [avgResponseTime] = await db.query(
      `
      SELECT AVG(DATEDIFF(ilh_cdc.timestamp, il.created_at)) AS avg_days
      FROM internship_letter il
      INNER JOIN student_internship si ON il.nim = si.nim
      LEFT JOIN (
        SELECT id_letter, MIN(timestamp) AS timestamp
        FROM internship_letter_history
        WHERE LOWER(approved_by) = 'internship coordinator'
          AND LOWER(status_approval) = 'accepted'
        GROUP BY id_letter
      ) ilh_koor ON il.id_letter = ilh_koor.id_letter
      LEFT JOIN (
        SELECT id_letter, MIN(timestamp) AS timestamp
        FROM internship_letter_history
        WHERE LOWER(approved_by) = 'cdc administrator'
          AND LOWER(status_approval) = 'accepted'
        GROUP BY id_letter
      ) ilh_cdc ON il.id_letter = ilh_cdc.id_letter
      WHERE YEAR(il.created_at) = ?
        AND si.id_kampus = ?
        AND ilh_koor.timestamp IS NOT NULL
        AND ilh_cdc.timestamp IS NOT NULL
    `,
      [currentYear, id_kampus]
    );

    const [companyAcceptance] = await db.query(
      `SELECT il.acceptance_status, COUNT(*) AS count
       FROM internship_letter il
       INNER JOIN student_internship si ON il.nim = si.nim
       WHERE YEAR(il.created_at) = ?
         AND si.id_kampus = ?
         AND il.status = 'ACCEPTED'
       GROUP BY il.acceptance_status`,
      [currentYear, id_kampus]
    );

    // Final response
    res.json({
      success: true,
      data: {
        year: currentYear,
        totalSubmissions: totalSubmissions[0].total,
        statusBreakdown,
        averageResponseTime: Number(avgResponseTime[0].avg_days || 0).toFixed(
          2
        ),
        companyAcceptance,
      },
    });
  } catch (err) {
    console.error("Dashboard summary error:", err);
    res.status(500).json({
      success: false,
      message: "Server error",
      error: err.message,
    });
  }
});

// Export
router.get("/cdc/export-internship", async (req, res) => {
  try {
    const { start_date, end_date, study_program, department } = req.query;
    const id_kampus = 1;

    if (!start_date || !end_date) {
      return res
        .status(400)
        .json({
          success: false,
          message: "start_date and end_date are required (YYYY-MM-DD)",
        });
    }

    const s = new Date(start_date);
    const e = new Date(end_date);
    if (isNaN(s) || isNaN(e) || s > e) {
      return res
        .status(400)
        .json({ success: false, message: "Invalid date range" });
    }

    let query = `
      SELECT 
        i.nim,
        si.name AS student_name,
        CONCAT(ps.jenjang, ' ', ps.study_program) AS program_study,
        ps.major AS department,
        COALESCE(NULLIF(MAX(il.class), ''), '-') AS class,
        COALESCE(NULLIF(MAX(il.semester), ''), '-') AS semester,
        l.name AS internship_coordinator,
        COALESCE(NULLIF(MAX(c.name), ''), NULLIF(MAX(il.company_name), ''), '-') AS company_name,
        COALESCE(NULLIF(MAX(c.phone), ''), NULLIF(MAX(il.company_contact), ''), '-') AS company_contact,
        COALESCE(NULLIF(MAX(c.address), ''), NULLIF(MAX(il.company_address), ''), '-') AS company_address,
        i.start_date,
        i.end_date,
        COALESCE(NULLIF(si.email, ''), '-') AS email,
        COALESCE(NULLIF(si.no_whatsapp, ''), '-') AS whatsapp_number
      FROM internship i
      INNER JOIN student_internship si ON i.nim = si.nim
      LEFT JOIN company c ON i.id_company = c.id_company
      LEFT JOIN program_study ps 
        ON si.program_study = ps.kode_prodi 
        AND si.id_kampus = ps.id_kampus
      LEFT JOIN lecturer l 
        ON l.prodi_koor = si.program_study
        AND l.is_koor = 1
      LEFT JOIN internship_letter il ON i.nim = il.nim
        AND il.acceptance_status = 'ACCEPTED'
      WHERE i.status = 'ONGOING'
        AND i.start_date >= ?   -- start_date filter
        AND i.end_date <= ?     -- end_date filter
        AND si.id_kampus = ?
    `;

    const params = [start_date, end_date, id_kampus];

    if (department && department.trim() !== "") {
      query += ` AND ps.major = ?`;
      params.push(department);
    }

    if (study_program && study_program.trim() !== "") {
      query += ` AND si.program_study = ?`;
      params.push(study_program);
    }

    query += `
      GROUP BY 
        i.nim, si.name, ps.jenjang, ps.study_program, ps.major, 
        l.name, i.start_date, i.end_date, si.email, si.no_whatsapp
      ORDER BY i.start_date DESC, si.name ASC
    `;

    const [rows] = await db.query(query, params);

    if (!rows.length) {
      return res
        .status(404)
        .json({
          success: false,
          message: `No internship data found in given date range`,
        });
    }

    res.json({
      success: true,
      study_program: study_program || "All Programs",
      count: rows.length,
      data: rows,
    });
  } catch (err) {
    console.error("[CDC EXPORT] Error:", err);
    res
      .status(500)
      .json({ success: false, message: "Server error", error: err.message });
  }
});

module.exports = router;
