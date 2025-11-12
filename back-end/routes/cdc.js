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
        message: "Username and password are required.",
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
        .json({ success: false, message: "Account not found." });
    }

    const user = rows[0];
    if (user.status && user.status.toLowerCase() !== "active") {
      return res
        .status(403)
        .json({ success: false, message: "Account inactive." });
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
        .json({ success: false, message: "Wrong password." });
    }

    return res.status(200).json({
      success: true,
      message: "Login succesfull.",
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
      .json({ success: false, message: "An error occurred on the server." });
  }
});

// List semua submission
router.get("/cdc/submissions", async (req, res) => {
  try {
    const [rows] = await db.query(`
      SELECT 
        il.id_letter,
        il.nim,
        s.name AS student_name,
        il.company_name,
        il.start_date,
        il.end_date,
        il.status,
        il.koor_approval,
        il.cdc_approval,
        il.created_at,
        il.updated_at
      FROM internship_letter il
      JOIN student_internship s ON s.nim = il.nim
      ORDER BY il.created_at DESC
    `);
    res.json({ success: true, data: rows });
  } catch (err) {
    console.error("[CDC] Error fetching submissions:", err);
    res.status(500).json({ success: false, message: "Server error" });
  }
});

// Approve/Reject submission
router.post("/cdc/approval", async (req, res) => {
  try {
    const { id_letter, status, user_id, user_name, comment } = req.body;
    if (!id_letter || !status) {
      return res.status(400).json({
        success: false,
        message: "id_letter and status are required.",
      });
    }

    const s = status.toUpperCase();
    if (!["ACCEPTED", "REJECTED"].includes(s)) {
      return res
        .status(400)
        .json({ success: false, message: "Status invalid." });
    }

    const [rows] = await db.query(
      `SELECT id_letter, koor_approval, cdc_approval 
       FROM internship_letter 
       WHERE id_letter = ? LIMIT 1`,
      [id_letter]
    );

    if (!rows.length)
      return res
        .status(404)
        .json({ success: false, message: "Submission not found." });

    const row = rows[0];

    if (row.koor_approval.toUpperCase() !== "ACCEPTED") {
      return res.status(403).json({
        success: false,
        message: "The Coordinator has not yet allowed the CDC to take action.",
      });
    }
    if (row.cdc_approval.toUpperCase() !== "WAITING") {
      return res.status(400).json({
        success: false,
        message: "The submission is not awaiting CDC approval",
      });
    }

    // Determine update SQL for internship_letter
    if (s === "ACCEPTED") {
      await db.query(
        `UPDATE internship_letter
         SET cdc_approval = 'ACCEPTED',
             status = CASE WHEN koor_approval = 'ACCEPTED' THEN 'ACCEPTED' ELSE status END,
             updated_at = NOW()
         WHERE id_letter = ?`,
        [id_letter]
      );
    } else {
      await db.query(
        `UPDATE internship_letter
         SET cdc_approval = 'REJECTED',
             status = 'REJECTED',
             updated_at = NOW()
         WHERE id_letter = ?`,
        [id_letter]
      );
    }

    // --- Insert into internship_letter_history ---
    let approved_by = "CDC ADMINISTRATOR";
    let user_id_val = "-";
    let user_name_val = "-";

    try {
      const actor = req.user || req.session?.user || null;
      if (actor) {
        if (actor.role && actor.role.toLowerCase().includes("cdc")) {
          const [urows] = await db.query(
            `SELECT id_upkpk, name FROM upkpk WHERE id_upkpk = ? LIMIT 1`,
            [actor.id_upkpk || actor.id]
          );
          if (urows && urows.length) {
            user_id_val = urows[0].id_upkpk || "-";
            user_name_val = urows[0].name || "-";
            approved_by = "CDC ADMINISTRATOR";
          }
        } else {
          user_id_val = actor.id || "-";
          user_name_val = actor.name || "-";
        }
      }
    } catch (err) {
      console.error("[CDC] user lookup error:", err);
    }

    // Insert history row
    await db.query(
      `INSERT INTO internship_letter_history 
   (id_letter, approved_by, user_id, user_name, status_approval, timestamp, comment)
   VALUES (?, 'CDC ADMINISTRATOR', ?, ?, ?, NOW(), ?)`,
      [id_letter, user_id || "-", user_name || "-", s, comment]
    );

    // Cek dan set published_date jika kedua approval sudah ACCEPTED
    await checkAndSetPublishedDate(id_letter);

    res.json({
      success: true,
      message: `Submission has been ${s.toLowerCase()} by CDC.`,
    });
  } catch (err) {
    console.error("[CDC] approval error:", err);
    return res.status(500).json({
      success: false,
      message: "Server error: " + err.message,
    });
  }
});

// get latest rejected reason by CDC for a letter
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

// edit the latest CDC rejection reason for a letter
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

// List submissions dengan filter (versi baru dengan filter)
router.get("/cdc/submissions-filtered", async (req, res) => {
  try {
    const {
      study_program,
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
        il.created_at,
        il.updated_at,
        ps.kode_prodi,
        ps.prodi AS program_name,
        ps.study_program,
        ps.jurusan
      FROM internship_letter il
      JOIN student_internship s ON s.nim = il.nim
      LEFT JOIN program_study ps ON ps.kode_prodi = s.program_study AND ps.id_kampus = ?
      WHERE 1=1
    `;

    const params = [id_kampus]; // id_kampus untuk JOIN program_study

    // Filter by study program (kode_prodi dari student_internship)
    if (study_program) {
      query += ` AND s.program_study = ?`;
      params.push(study_program);
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
      if (cdcStatus === "APPROVE") {
        query += ` AND il.cdc_approval = 'ACCEPTED'`;
      } else if (cdcStatus === "WAITING") {
        query += ` AND il.cdc_approval = 'WAITING'`;
      } else if (cdcStatus === "REJECT") {
        query += ` AND il.cdc_approval = 'REJECTED'`;
      }
    }

    // Filter by company result
    if (company) {
      const companyStatus = company.toUpperCase();
      if (companyStatus === "ACCEPTED") {
        query += ` AND il.status = 'ACCEPTED'`;
      } else if (companyStatus === "WAITING") {
        query += ` AND il.status = 'WAITING'`;
      } else if (companyStatus === "REJECTED") {
        query += ` AND il.status = 'REJECTED'`;
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
        ps.prodi AS program_name,
        ps.study_program,
        ps.jurusan,
        ps.major
      FROM program_study ps
      WHERE ps.id_kampus = ?
      ORDER BY ps.prodi ASC
    `,
      [id_kampus]
    );

    res.json({ success: true, data: rows });
  } catch (err) {
    console.error("[CDC] Error fetching study programs:", err);
    res.status(500).json({ success: false, message: "Server error" });
  }
});

// Edit submission (hanya data perusahaan)
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
    // Cek apakah submission exists
    const [checkRows] = await db.query(
      `SELECT id_letter FROM internship_letter WHERE id_letter = ?`,
      [id_letter]
    );

    if (!checkRows.length) {
      return res.status(404).json({
        success: false,
        message: "Submission not found",
      });
    }

    // Gabungkan phone dan email menjadi satu string untuk company_contact
    let contactParts = [];
    if (company_phone) contactParts.push(company_phone);
    if (company_email) contactParts.push(company_email);
    const company_contact = contactParts.join(" ");

    // Update data perusahaan
    await db.query(
      `UPDATE internship_letter
       SET company_name = ?,
           company_address = ?,
           company_contact = ?,
           updated_at = NOW()
       WHERE id_letter = ?`,
      [company_name, company_address, company_contact, id_letter]
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

module.exports = router;
