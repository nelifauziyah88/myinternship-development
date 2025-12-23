const express = require("express");
const router = express.Router();
const bcrypt = require("bcryptjs");
const puppeteer = require("puppeteer");
const db = require("../db");

// Login
router.post("/login_student", async (req, res) => {
  try {
    const { username, password } = req.body;

    // Validasi input wajib diisi
    if (!username || !password) {
      return res.status(400).json({
        success: false,
        message: "Username and Password are required",
      });
    }

    // Memeriksa apakah student ada di database
    const [rows] = await db.query(
      "SELECT * FROM student_internship WHERE username = ? OR email = ? OR nim = ?",
      [username, username, username]
    );

    if (rows.length === 0) {
      return res
        .status(401)
        .json({ success: false, message: "Account not found" });
    }

    const user = rows[0];

    // Memeriksa password (berdasarkan hash)
    const validPassword = await bcrypt.compare(password, user.password);
    if (!validPassword) {
      return res
        .status(401)
        .json({ success: false, message: "Incorrect password" });
    }

    res.status(200).json({
      success: true,
      message: "Login successfull",
      user: {
        nim: user.nim,
        name: user.name,
        email: user.email,
        program_study: user.program_study,
        id_kampus: user.id_kampus,
      },
    });
  } catch (error) {
    console.error("Error login student:", error);
    res.status(500).json({
      success: false,
      message: "An error occured. Please try again !",
    });
  }
});

// Api cek submission
router.get("/check-submission/:nim", async (req, res) => {
  const { nim } = req.params;
  try {
    const [rows] = await db.query(
      `SELECT id_letter, status, koor_approval, cdc_approval, acceptance_status
     FROM internship_letter
     WHERE nim = ?
     ORDER BY created_at DESC
     LIMIT 1`,
      [nim]
    );

    if (!rows.length) return res.json({ hasActive: false });

    const r = rows[0];
    const isActive =
      (r.status && r.status.toUpperCase() === "WAITING") ||
      (r.koor_approval && r.koor_approval.toUpperCase() === "WAITING") ||
      (r.cdc_approval && r.cdc_approval.toUpperCase() === "WAITING");

    res.json({
      hasActive: !!isActive,
      last: {
        ...r,
        acceptance_status: r.acceptance_status || "-",
      },
    });
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: "Server error" });
  }
});

// Api autofill form submission
router.get("/form-submission/:nim", async (req, res) => {
  const { nim } = req.params;
  try {
    // ambil student
    const [stuRows] = await db.query(
      `SELECT * FROM student_internship WHERE nim = ? LIMIT 1`,
      [nim]
    );
    if (!stuRows.length)
      return res.status(404).json({ error: "Student not found" });
    const student = stuRows[0];

    // ambil program_study
    const [psRows] = await db.query(
      `SELECT * FROM program_study WHERE kode_prodi = ? AND id_kampus = ? LIMIT 1`,
      [student.program_study, student.id_kampus]
    );
    const program = psRows.length ? psRows[0] : null;
    const department = program ? program.major || null : null;
    const program_study = program ? program.study_program || null : null;

    // ambil lecturer
    let coordinator = null;
    if (program) {
      const [lecRows] = await db.query(
        `SELECT name FROM lecturer WHERE prodi_koor = ? AND id_kampus = ? AND is_koor = 1 LIMIT 1`,
        [program.kode_prodi, program.id_kampus]
      );
      if (lecRows.length) coordinator = lecRows[0].name;
    }

    res.json({
      student: {
        nim: student.nim,
        name: student.name,
        id_kampus: student.id_kampus,
        email: student.email || null,
      },
      department,
      program_study,
      coordinator,
    });
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: "Server error" });
  }
});

// Api untuk list company
router.get("/company", async (req, res) => {
  const q = req.query.q ? `%${req.query.q}%` : null;
  try {
    let rows;
    if (q) {
      [rows] = await db.query(
        `SELECT id_company AS id, name FROM company WHERE name LIKE ? LIMIT 100`,
        [q]
      );
    } else {
      [rows] = await db.query(
        `SELECT id_company AS id, name FROM company LIMIT 200`
      );
    }
    res.json(rows);
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: "Server error" });
  }
});

// Api ambil data company berdasarkan id
router.get("/company/:id", async (req, res) => {
  const id = req.params.id;
  try {
    const [rows] = await db.query(
      `SELECT id_company, name, address, phone, email 
       FROM company 
       WHERE id_company = ? 
       LIMIT 1`,
      [id]
    );

    if (!rows.length)
      return res.status(404).json({ error: "Company not found" });

    res.json(rows[0]);
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: "Server error" });
  }
});

// Api submit form submission
router.post("/form-submission", async (req, res) => {
  try {
    const body = req.body;
    const nim = body.nim;
    const force = body.force || false;

    if (!nim) return res.status(400).json({ error: "nim required" });

    // Cek submission aktif
    const [lastRows] = await db.query(
      `SELECT id_letter, status, koor_approval, cdc_approval 
       FROM internship_letter 
       WHERE nim = ? 
       ORDER BY created_at DESC 
       LIMIT 1`,
      [nim]
    );

    if (lastRows.length) {
      const r = lastRows[0];
      const isActive =
        (r.status && r.status.toUpperCase() === "WAITING") ||
        (r.koor_approval && r.koor_approval.toUpperCase() === "WAITING") ||
        (r.cdc_approval && r.cdc_approval.toUpperCase() === "WAITING");

      if (isActive) {
        return res
          .status(400)
          .json({ error: "You already have an active submission" });
      }
    }

    // Handle company
    let id_company = body.company_id || null;
    let company_name = body.company_name || null;
    let company_contact = body.company_contact || null;
    let company_address = body.company_address || null;
    let company_not_exist = 1;

    function normalizeCompanyName(name) {
      return name
        .toLowerCase()
        .replace(/\./g, "")
        .replace(/\b(pt|cv|tbk|ltd|co|corp|inc|coorporation)\b/g, "")
        .replace(/[^a-z0-9]/g, "")
        .trim();
    }

    function levenshtein(a, b) {
      if (!a || !b) return 999;
      const matrix = Array(a.length + 1)
        .fill(null)
        .map(() => Array(b.length + 1).fill(null));
      for (let i = 0; i <= a.length; i++) matrix[i][0] = i;
      for (let j = 0; j <= b.length; j++) matrix[0][j] = j;
      for (let i = 1; i <= a.length; i++) {
        for (let j = 1; j <= b.length; j++) {
          const cost = a[i - 1] === b[j - 1] ? 0 : 1;
          matrix[i][j] = Math.min(
            matrix[i - 1][j] + 1,
            matrix[i][j - 1] + 1,
            matrix[i - 1][j - 1] + cost
          );
        }
      }
      return matrix[a.length][b.length];
    }

    function diceCoefficient(a, b) {
      if (!a || !b) return 0;
      const bg = (str) =>
        [...str].map((_, i) => str.slice(i, i + 2)).filter((x) => x.length === 2);
      const aBigrams = bg(a);
      const bBigrams = bg(b);
      let intersection = 0;
      const bClone = [...bBigrams];
      for (let x of aBigrams) {
        const idx = bClone.indexOf(x);
        if (idx !== -1) {
          intersection++;
          bClone.splice(idx, 1);
        }
      }
      return (2 * intersection) / (aBigrams.length + bBigrams.length);
    }

    // Cek duplikat atau similiar
    if (!id_company) {
      if (!company_name || !company_contact || !company_address) {
        return res.status(400).json({
          error: "new company name/contact/address required",
        });
      }

      const normalizedInput = normalizeCompanyName(company_name);

      const [existingCompanies] = await db.query(
        `SELECT id_company, name FROM company`
      );

      for (let c of existingCompanies) {
        const normalizedDB = normalizeCompanyName(c.name);

        // Exact match
        if (normalizedInput === normalizedDB && !force) {
          return res.status(400).json({
            error: `Company "${c.name}" already exists in dropdown.`,
            code: "COMPANY_DUPLICATE",
          });
        }

        // Fuzzy match
        const lev = levenshtein(normalizedInput, normalizedDB);
        const sim = diceCoefficient(normalizedInput, normalizedDB);

        if ((lev <= 4 || sim >= 0.55) && !force) {
          return res.status(200).json({
            success: false,
            code: "COMPANY_SIMILAR",
            similar_company: c.name
          });
        }
      }

      // Insert company baru
      const [stuRow] = await db.query(
        `SELECT id_kampus FROM student_internship WHERE nim = ? LIMIT 1`,
        [nim]
      );
      const id_kampus = stuRow.length ? stuRow[0].id_kampus : 1;

      const [companyResult] = await db.query(
        `INSERT INTO company 
        (name, type, phone, email, website, facebook, twitter, instagram, linkedin, logo,
         address, province, city, description, status, access_type, id_kampus)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'not verified', 1, ?)`,
        [
          company_name,
          "General",
          company_contact || "",
          "",
          "",
          "",
          "",
          "",
          "",
          "",
          company_address || "",
          "",
          "",
          "",
          id_kampus,
        ]
      );

      id_company = companyResult.insertId;
      company_not_exist = 1;

    } else {
      // existing company
      const [cRow] = await db.query(
        `SELECT name, address, phone, email FROM company WHERE id_company = ? LIMIT 1`,
        [id_company]
      );
      if (!cRow.length)
        return res.status(400).json({ error: "Invalid company_id" });

      company_name = cRow[0].name;
      company_address = cRow[0].address;
      company_contact = cRow[0].phone || cRow[0].email || company_contact || null;
      company_not_exist = 0;
    }

    // Insert submission
    const [result] = await db.query(
      `INSERT INTO internship_letter 
      (nim, id_company, start_date, end_date, status, semester, class, 
       koor_approval, cdc_approval, company_name, company_contact, company_address, 
       language, company_not_exist)
       VALUES (?, ?, ?, ?, 'WAITING', ?, ?, 'WAITING', 'WAITING', ?, ?, ?, ?, ?)`,
      [
        nim,
        id_company,
        body.start_date,
        body.end_date,
        body.semester,
        body.class,
        company_name,
        company_contact,
        company_address,
        body.language,
        company_not_exist,
      ]
    );

    // Update student
    const updateFields = [];
    const updateValues = [];
    if (body.email) {
      updateFields.push("email = ?");
      updateValues.push(body.email);
    }
    if (body.no_whatsapp || body.phone) {
      updateFields.push("no_whatsapp = ?");
      updateValues.push(body.no_whatsapp || body.phone);
    }
    if (updateFields.length > 0) {
      updateValues.push(nim);
      await db.query(`UPDATE student_internship SET ${updateFields.join(", ")} WHERE nim = ?`, updateValues);
    }

    res.json({ success: true, id: result.insertId });

  } catch (err) {
    console.error("form-submission error:", err);
    res.status(500).json({ error: err.message });
  }
});

// APi untuk list internship letter
router.get("/approval-status/:nim", async (req, res) => {
  try {
    const nim = req.params.nim;

    const sql = `
  SELECT 
    il.id_letter,
    il.nim,
    il.start_date,
    il.end_date,
    il.koor_approval,
    il.cdc_approval,
    il.status,
    il.acceptance_status,
    il.published_date,
    il.created_at,
    il.updated_at,
    il.language,

    (
      SELECT comment 
      FROM internship_letter_history h 
      WHERE h.id_letter = il.id_letter 
        AND LOWER(h.approved_by) = 'internship coordinator'
        AND LOWER(h.status_approval) = 'rejected'
      ORDER BY h.id_history DESC
      LIMIT 1
    ) AS koor_reason,

    (
      SELECT comment 
      FROM internship_letter_history h 
      WHERE h.id_letter = il.id_letter 
        AND LOWER(h.approved_by) = 'cdc administrator'
        AND LOWER(h.status_approval) = 'rejected'
      ORDER BY h.id_history DESC
      LIMIT 1
    ) AS cdc_reason

  FROM internship_letter il
  WHERE il.nim = ?
  ORDER BY il.created_at DESC
`;

    const [rows] = await db.query(sql, [nim]);
    return res.json({ success: true, data: rows });
  } catch (err) {
    console.error("GET /approval-status error:", err);
    return res.status(500).json({ success: false, error: "Server error" });
  }
});

// Ambil detail surat, data student dan data company berdasarkan id 
router.get("/internship_letter/:id", async (req, res) => {
  const { id } = req.params;
  const lang = (req.query.lang || "ID").toUpperCase();
  try {
    const [letterRows] = await db.query(
      `SELECT * FROM internship_letter WHERE id_letter = ? LIMIT 1`,
      [id]
    );
    if (!letterRows.length)
      return res.status(404).json({ error: "Letter not found" });
    const letter = letterRows[0];

    const [stuRows] = await db.query(
      `
  SELECT s.nim, s.name, s.email, s.id_kampus,
         p.jenjang, p.prodi, p.study_program, p.kode_prodi
  FROM student_internship s
  LEFT JOIN program_study p ON s.program_study = p.kode_prodi
  WHERE s.nim = ? LIMIT 1
`,
      [letter.nim]
    );
    const student = stuRows.length ? stuRows[0] : null;

    let company = null;
    if (letter.id_company) {
      const [compRows] = await db.query(
        `SELECT id_company, name, address, phone, email FROM company WHERE id_company = ? LIMIT 1`,
        [letter.id_company]
      );
      company = compRows.length ? compRows[0] : null;
    }

    return res.json({
      ...letter,
      student,
      company,
      language: lang,
      formatted: {
        indo: {
          start_date: formatDateIndo(letter.start_date),
          end_date: formatDateIndo(letter.end_date),
          published_date: formatDateIndo(letter.published_date),
        },
        eng: {
          start_date: formatDateEng(letter.start_date),
          end_date: formatDateEng(letter.end_date),
          published_date: formatDateEng(letter.published_date),
        },
      },
    });
  } catch (err) {
    console.error("GET /internship_letter/:id error:", err);
    return res.status(500).json({ error: "Server error" });
  }
});

// Helper: format tanggal ke "13 Agustus 2025"
function formatDateIndo(isoDateStr) {
  if (!isoDateStr) return "";
  const monthNames = [
    "Januari",
    "Februari",
    "Maret",
    "April",
    "Mei",
    "Juni",
    "Juli",
    "Agustus",
    "September",
    "Oktober",
    "November",
    "Desember",
  ];
  const d = new Date(isoDateStr);
  const dd = d.getDate();
  const mm = monthNames[d.getMonth()];
  const yyyy = d.getFullYear();
  return `${dd} ${mm} ${yyyy}`;
}

// Helper: format tanggal ke "October 30, 2025"
function formatDateEng(isoDateStr) {
  if (!isoDateStr) return "";
  const monthNames = [
    "January",
    "February",
    "March",
    "April",
    "May",
    "June",
    "July",
    "August",
    "September",
    "October",
    "November",
    "December",
  ];
  const d = new Date(isoDateStr);
  const dd = d.getDate();
  const mm = monthNames[d.getMonth()];
  const yyyy = d.getFullYear();
  return `${mm} ${dd}, ${yyyy}`;
}

// Helper function: Update published_date jika kedua approval sudah APPROVED
async function checkAndSetPublishedDate(id_letter) {
  try {
    const [rows] = await db.query(
      `SELECT koor_approval, cdc_approval, published_date 
       FROM internship_letter 
       WHERE id_letter = ? LIMIT 1`,
      [id_letter]
    );

    if (!rows.length) return;

    const letter = rows[0];

    // Memeriksa apakah kedua approval sudah ACCEPTED dan published_date masih NULL
    if (
      letter.koor_approval === "ACCEPTED" &&
      letter.cdc_approval === "ACCEPTED" &&
      !letter.published_date
    ) {
      const publishedDate = new Date();
      await db.query(
        `UPDATE internship_letter 
         SET published_date = ?, status = 'ACCEPTED' 
         WHERE id_letter = ?`,
        [publishedDate, id_letter]
      );
      console.log(`Published date set for letter ${id_letter}`);
    }
  } catch (err) {
    console.error("Error in checkAndSetPublishedDate:", err);
  }
}

// Generate & download PDF letter by id_letter
router.get("/letter/:id/download", async (req, res) => {
  const id = req.params.id;
  try {
    const [rows] = await db.query(
      `SELECT * FROM internship_letter WHERE id_letter = ? LIMIT 1`,
      [id]
    );
    if (!rows.length)
      return res.status(404).json({ error: "Letter not found" });
    const letter = rows[0];

    const lang = (req.query.lang || letter.language || "ID").toUpperCase();

    const [stuRows] = await db.query(
      `
      SELECT s.nim, s.name, s.email,
             p.jenjang, p.prodi, p.study_program
      FROM student_internship s
      LEFT JOIN program_study p ON s.program_study = p.kode_prodi
      WHERE s.nim = ? LIMIT 1
    `,
      [letter.nim]
    );
    const student = stuRows.length ? stuRows[0] : null;

    let company = null;
    if (letter.id_company) {
      const [cRows] = await db.query(
        `SELECT id_company, name, address FROM company WHERE id_company = ? LIMIT 1`,
        [letter.id_company]
      );
      if (cRows.length) company = cRows[0];
    }

    const now = new Date();
    const year = now.getFullYear();
    const month = now.getMonth();

    let letterNumber = letter.letter_number;
    if (!letterNumber) {
      const [countRows] = await db.query(
        `SELECT COUNT(*) AS cnt FROM internship_letter WHERE letter_number IS NOT NULL AND YEAR(created_at) = ?`,
        [year]
      );
      const next = (countRows[0]?.cnt ?? 0) + 1;
      await db.query(
        `UPDATE internship_letter SET letter_number = ? WHERE id_letter = ?`,
        [next, id]
      );
      letterNumber = next;
    }

    // Template URL
    const templateFile =
      lang === "ENG" ? "internship_letter_eng.php" : "internship_letter_id.php";
    const templateURL = `http://localhost/myinternship/front-end/${templateFile}?id_letter=${id}&lang=${lang}`;

    // Tambahkan update published_date:
    const browser = await puppeteer.launch({
      args: ["--no-sandbox", "--disable-setuid-sandbox"],
    });
    const page = await browser.newPage();
    await page.goto(templateURL, { waitUntil: "networkidle0", timeout: 0 });
    const pdfBuffer = await page.pdf({
      format: "A4",
      printBackground: true,
      margin: { top: "10mm", bottom: "10mm", left: "12mm", right: "12mm" },
    });
    await browser.close();

    const filename = `internship_letter_${letter.nim || letter.id_letter
      }_${year}.pdf`;
    res.setHeader("Content-Type", "application/pdf");
    res.setHeader("Content-Disposition", `attachment; filename="${filename}"`);
    res.send(pdfBuffer);
  } catch (err) {
    console.error("download letter error:", err);
    res.status(500).json({ error: "Server error when generating PDF" });
  }
});

router.post("/rejected-by-company/:id", async (req, res) => {
  try {
    const { acceptance_status, company_reply_letter } = req.body;
    const id = req.params.id;

    if (acceptance_status !== "REJECTED") {
      return res.status(400).json({
        success: false,
        type: "warning",
        message: "Invalid acceptance status",
      });
    }

    const [rows] = await db.query(
      `SELECT published_date FROM internship_letter WHERE id_letter = ? LIMIT 1`,
      [id]
    );

    if (!rows.length) {
      return res.status(404).json({
        success: false,
        type: "warning",
        message: "Letter not found",
      });
    }

    const publishedDate = rows[0].published_date;
    const filePath = company_reply_letter || "-";

    if (filePath !== "-") {
      await db.query(
        `UPDATE internship_letter
         SET acceptance_status = 'REJECTED',
             company_reply_letter = ?
         WHERE id_letter = ?`,
        [filePath, id]
      );

      return res.json({
        success: true,
        message: "Company rejection recorded.",
      });
    }

    if (!publishedDate) {
      return res.status(403).json({
        success: false,
        type: "warning",
        message:
          "You must wait 14 days for the company to respond to your internship claim.",
      });
    }

    const now = new Date();
    const pubDate = new Date(publishedDate);
    const diffDays = Math.floor((now - pubDate) / (1000 * 60 * 60 * 24));

    if (diffDays < 14) {
      return res.status(403).json({
        success: false,
        type: "warning",
        message:
          "System has detected that the letter is less than 14 days old.",
      });
    }

    await db.query(
      `UPDATE internship_letter
       SET acceptance_status = 'REJECTED',
           company_reply_letter = '-'
       WHERE id_letter = ?`,
      [id]
    );

    res.json({
      success: true,
      message: "Company rejection recorded.",
    });
  } catch (error) {
    console.error("Error in /rejected-by-company:", error);
    res.status(500).json({
      success: false,
      type: "warning",
      message: "Server error",
    });
  }
});

// Change Internship Period
router.post("/change-internship-period/:id", async (req, res) => {
  try {
    const id_letter = req.params.id;
    const { start_date, end_date } = req.body;

    // Validasi input
    if (!start_date || !end_date) {
      return res.status(400).json({
        success: false,
        message: "Start date and end date are required",
      });
    }

    // Ambil status surat
    const [rows] = await db.query(
      `SELECT status FROM internship_letter WHERE id_letter = ? LIMIT 1`,
      [id_letter]
    );

    if (!rows.length) {
      return res.status(404).json({
        success: false,
        message: "Internship letter not found",
      });
    }

    const status = rows[0].status;

    if (status !== "ACCEPTED" && status !== "COMPLETE") {
      return res.status(403).json({
        success: false,
        message: "Internship period can only be changed after approval",
      });
    }

    // Update tanggal & reset CDC
    await db.query(
      `UPDATE internship_letter
       SET start_date = ?,
           end_date = ?,
           cdc_approval = 'WAITING',
           status = 'WAITING',
           updated_at = NOW()
       WHERE id_letter = ?`,
      [start_date, end_date, id_letter]
    );

    return res.json({
      success: true,
      message: "Internship period updated. Waiting for CDC approval.",
    });

  } catch (err) {
    console.error("Change internship period error:", err);
    return res.status(500).json({
      success: false,
      message: "Server error",
    });
  }
});

// Helper untuk format ke YYYY-MM-DD tanpa timezone offset
function formatDate(dateValue) {
  if (!dateValue) return null;
  const d = new Date(dateValue);
  const year = d.getFullYear();
  const month = String(d.getMonth() + 1).padStart(2, "0");
  const day = String(d.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
}

// Api untuk autofill data form accepted_by_company
router.get("/accepted-by-company/autofill/:nim", async (req, res) => {
  const nim = req.params.nim;
  try {
    const [rows] = await db.query(
      `
      SELECT 
        s.nim,
        s.name,
        s.email,
        s.no_whatsapp,
        s.program_study,
        s.id_kampus,
        ps.jenjang,
        ps.study_program,
        ps.major AS department,
        l.class,
        l.semester,
        c.name AS company_name,
        c.address AS company_address,
        c.status AS status,
        LOWER(c.city) AS city,
        LOWER(c.province) AS province,
        LOWER(c.country) AS country,
        l.company_not_exist,
        l.id_company,
        l.start_date,
        l.end_date,
        uc.user_email AS hrd_email,
        uc.user_fullname AS hrd_name,
        uc.user_phone AS hrd_whatsapp,
        lec.name AS coordinator_name
      FROM student_internship s
      LEFT JOIN internship_letter l ON s.nim = l.nim
      LEFT JOIN program_study ps ON s.program_study = ps.kode_prodi AND s.id_kampus = ps.id_kampus
      LEFT JOIN company c ON l.id_company = c.id_company
      LEFT JOIN user_company uc ON c.id_company = uc.id_company AND uc.user_type = 'HRD'
      LEFT JOIN lecturer lec ON s.program_study = lec.prodi_koor AND s.id_kampus = lec.id_kampus AND lec.is_koor = 1
      WHERE s.nim = ?
      ORDER BY l.id_letter DESC LIMIT 1
    `,
      [nim]
    );

    if (rows.length === 0)
      return res.json({ success: false, message: "Data not found" });

    const d = rows[0];

    // Format tanggal fix timezone
    d.start_date = formatDate(d.start_date);
    d.end_date = formatDate(d.end_date);

    d.study_program_display = `${d.jenjang || ""} - ${d.study_program || ""}`;

    res.json({ success: true, data: d });
  } catch (err) {
    console.error("Autofill error:", err);
    res.status(500).json({ success: false, message: "Server error" });
  }
});

// Api untuk claim internship
router.post("/accepted-by-company/submit/:id_letter", async (req, res) => {
  try {
    const fields = req.body || {};
    const param_id_letter = req.params?.id_letter || null;

    const {
      nim,
      city,
      province,
      country,
      hrd_email,
      hrd_name,
      hrd_whatsapp,
      placement_department,
      start_date,
      end_date,
      info_source,
      email,
      whatsapp,
      company_not_exist,
      company_reply_letter,
    } = fields;

    const conn = await db.getConnection();
    await conn.beginTransaction();

    try {
      // Ambil surat terbaru
      const [letterRows] = await conn.query(
        `SELECT * FROM internship_letter 
         WHERE nim = ? ORDER BY id_letter DESC LIMIT 1`,
        [nim]
      );

      let letter = letterRows[0] || null;
      let id_company = letter?.id_company || null;
      let id_user_company = null;

      // Fallback jika id_letter param diberikan
      if (!letter && param_id_letter) {
        const [alt] = await conn.query(
          "SELECT * FROM internship_letter WHERE id_letter=? LIMIT 1",
          [param_id_letter]
        );
        if (alt.length) {
          letter = alt[0];
          id_company = alt[0].id_company;
        }
      }

      // Ambil id kampus
      const [studRows] = await conn.query(
        "SELECT id_kampus FROM student_internship WHERE nim = ?",
        [nim]
      );
      const id_kampus = studRows.length ? studRows[0].id_kampus : 1;

      // Determine company_not_exist flag
      const companyNotExistFlag =
        String(company_not_exist) === "1" || (letter && Number(letter.company_not_exist) === 1)
          ? 1
          : 0;

      // Company_not_exist === '1' = Insert company baru
      if (companyNotExistFlag === 1 && !id_company) {
        const companyName = letter?.company_name || "-";
        const companyAddress = letter?.company_address || "-";
        const phone = "-";
        const email_c = "-";

        const [result] = await conn.query(
          `INSERT INTO company 
           (name, type, type_other, phone, email, website, facebook, twitter, instagram, linkedin, logo,
            address, province, city, country, description, status, access_type, id_kampus)
           VALUES (?, '-', NULL, ?, ?, '-', '-', '-', '-', '-', '-',
           ?, ?, ?, ?, '-', 'not verified', '1', ?)`,
          [companyName, phone, email_c, companyAddress, province || null, city || null, country || null, id_kampus]
        );

        id_company = result.insertId;

        // Insert HRD
        const [u] = await conn.query(
          `INSERT INTO user_company 
           (id_company, user_fullname, user_email, user_phone, user_type, username, password)
           VALUES (?, ?, ?, ?, 'HRD', 'TEMP', '-')`,
          [id_company, hrd_name || "-", hrd_email || "-", hrd_whatsapp || "-"]
        );

        id_user_company = u.insertId;

        let padded =
          id_user_company < 10
            ? `00${id_user_company}`
            : id_user_company < 100
              ? `0${id_user_company}`
              : `${id_user_company}`;

        await conn.query(
          "UPDATE user_company SET username=? WHERE id_user_company=?",
          [`HRD.${padded}`, id_user_company]
        );
      }

      // 4. Company existing = cek status verified / not verified
      if (id_company) {
        const [cRows] = await conn.query(
          "SELECT status FROM company WHERE id_company = ? LIMIT 1",
          [id_company]
        );

        const companyStatus = cRows.length ? String(cRows[0].status).toLowerCase() : "verified";
        const isVerified = companyStatus === "verified";

        // Determine if fields can be updated (HRD + City/Province/Country)
        const canUpdateFields = companyNotExistFlag === 1 || !isVerified;

        if (canUpdateFields) {
          // Update company basic fields → hanya city/province/country
          await conn.query(
            `UPDATE company SET 
               city = COALESCE(?, city),
               province = COALESCE(?, province),
               country = COALESCE(?, country),
               id_kampus = COALESCE(?, id_kampus)
             WHERE id_company = ?`,
            [city || null, province || null, country || null, id_kampus, id_company]
          );

          // Update/Insert HRD
          const [existingHRD] = await conn.query(
            "SELECT id_user_company FROM user_company WHERE id_company=? AND user_type='HRD' LIMIT 1",
            [id_company]
          );

          if (existingHRD.length) {
            id_user_company = existingHRD[0].id_user_company;

            await conn.query(
              `UPDATE user_company SET
                 user_fullname = COALESCE(?, user_fullname),
                 user_email   = COALESCE(?, user_email),
                 user_phone   = COALESCE(?, user_phone)
               WHERE id_user_company = ?`,
              [hrd_name || null, hrd_email || null, hrd_whatsapp || null, id_user_company]
            );
          } else {
            const [u] = await conn.query(
              `INSERT INTO user_company
               (id_company, user_fullname, user_email, user_phone, user_type, username, password)
               VALUES (?, ?, ?, ?, 'HRD', 'TEMP', '-')`,
              [id_company, hrd_name || "-", hrd_email || "-", hrd_whatsapp || "-"]
            );

            id_user_company = u.insertId;

            let padded =
              id_user_company < 10
                ? `00${id_user_company}`
                : id_user_company < 100
                  ? `0${id_user_company}`
                  : `${id_user_company}`;

            await conn.query(
              "UPDATE user_company SET username = ? WHERE id_user_company = ?",
              [`HRD.${padded}`, id_user_company]
            );
          }
        }
      }

      // Auto verify company (setelah mahasiswa claim internship)
      await conn.query(
        `UPDATE company SET status='verified'
         WHERE id_company=? AND status='not verified'`,
        [id_company]
      );

      // Update data kontak mahasiswa
      if (email || whatsapp) {
        await conn.query(
          `UPDATE student_internship 
           SET email = COALESCE(?, email),
               no_whatsapp = COALESCE(?, no_whatsapp)
           WHERE nim=?`,
          [email || null, whatsapp || null, nim]
        );
      }

      // Insert data internship
      const sDate = start_date ? new Date(start_date) : null;
      const eDate = end_date ? new Date(end_date) : null;

      if (!sDate || !eDate || isNaN(sDate.getTime()) || isNaN(eDate.getTime())) {
        throw new Error("Invalid start_date or end_date");
      }

      const ms = Math.abs(eDate - sDate);
      const months = Math.max(1, Math.floor(ms / (1000 * 60 * 60 * 24 * 30)));
      const internship_period = `${months} month(s)`;

      const [internshipResult] = await conn.query(
        `INSERT INTO internship 
          (nim, id_company, start_date, end_date, id_user_company, status, internship_position, internship_period, timestamp_register)
          VALUES (?, ?, ?, ?, ?, 'ongoing', ?, ?, NOW())`,
        [nim, id_company, start_date, end_date, id_user_company, placement_department || null, internship_period]
      );
      const id_internship = internshipResult.insertId;

      // Insert internship acceptance
      const [letterRows3] = await conn.query(
        "SELECT id_letter FROM internship_letter WHERE nim = ? ORDER BY id_letter DESC LIMIT 1",
        [nim]
      );
      const id_letter_for_accept = letterRows3.length ? letterRows3[0].id_letter : param_id_letter;

      await conn.query(
        `INSERT INTO internship_letter_acceptance (id_letter, id_internship, source_internship_info, created_at)
         VALUES (?, ?, ?, NOW())`,
        [id_letter_for_accept, id_internship, info_source || null]
      );

      // Update internship_letter.acceptance_status & company_reply_letter
      if (company_reply_letter) {
        await conn.query(
          "UPDATE internship_letter SET company_reply_letter = ?, acceptance_status = 'ACCEPTED' WHERE nim = ? AND id_letter = ?",
          [company_reply_letter, nim, id_letter_for_accept]
        );
      } else {
        await conn.query(
          "UPDATE internship_letter SET acceptance_status = 'ACCEPTED' WHERE nim = ? AND id_letter = ?",
          [nim, id_letter_for_accept]
        );
      }

      await conn.commit();
      res.json({
        success: true,
        message: "Internship claim submitted successfully",
        data: {
          id_company,
          id_user_company,
          id_internship,
        },
      });
    } catch (err) {
      await conn.rollback();
      console.error("Transaction error (accepted-by-company):", err);
      return res.status(500).json({ success: false, error: err.message || "Database transaction failed" });
    } finally {
      conn.release();
    }
  } catch (err) {
    console.error("Upload error (accepted-by-company):", err);
    return res.status(500).json({ success: false, error: "Server error" });
  }
});

// Dashboard statistics
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

    const [responseTimeData] = await db.query(responseTimeQuery, responseTimeParams);

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

    const [acceptanceRateData] = await db.query(acceptanceRateQuery, acceptanceRateParams);

    // Merge data, all programs with actual data 
    const responseTimeMap = new Map();
    responseTimeData.forEach(r => {
      const key = `${r.department}|||${r.program_full_name}`;
      responseTimeMap.set(key, {
        avgResponseTimeKoor: Number(r.avg_response_time_koor || 0).toFixed(2),
        avgResponseTimeCdc: Number(r.avg_response_time_cdc || 0).toFixed(2),
        avgTotalResponseTime: Number(r.avg_total_response_time || 0).toFixed(2),
        dataCount: r.data_count || 0
      });
    });

    const acceptanceRateMap = new Map();
    acceptanceRateData.forEach(r => {
      const key = `${r.department}|||${r.program_full_name}`;
      acceptanceRateMap.set(key, {
        acceptedCount: r.accepted_count || 0,
        rejectedCount: r.rejected_count || 0,
        totalCount: r.total_count || 0,
        acceptanceRate: Number(r.acceptance_rate || 0),
        rejectionRate: Number(r.rejection_rate || 0)
      });
    });

    const responseTimeResult = [];
    const acceptanceRateResult = [];

    allPrograms.forEach(program => {
      const key = `${program.department}|||${program.program_full_name}`;

      // Response time data
      const responseData = responseTimeMap.get(key);
      responseTimeResult.push({
        program: program.program_full_name,
        department: program.department,
        avgResponseTimeKoor: responseData ? responseData.avgResponseTimeKoor : "0.00",
        avgResponseTimeCdc: responseData ? responseData.avgResponseTimeCdc : "0.00",
        avgTotalResponseTime: responseData ? responseData.avgTotalResponseTime : "0.00",
        hasData: !!responseData,
        dataCount: responseData ? responseData.dataCount : 0
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
        hasData: !!acceptanceData
      });
    });

    // Get unique departments list
    const [departments] = await db.query(`
          SELECT DISTINCT major AS department
          FROM program_study
          WHERE id_kampus = ?
          ORDER BY major
        `, [id_kampus]);

    // Final response
    res.json({
      success: true,
      data: {
        year: currentYear || "All Years",
        department: department || "All Departments",
        departments: departments.map(d => d.department),
        responseTime: responseTimeResult,
        acceptanceRate: acceptanceRateResult
      }
    });

  } catch (err) {
    console.error("Dashboard statistics error:", err);
    res.status(500).json({
      success: false,
      message: "Server error",
      error: err.message
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
    const [avgResponseTime] = await db.query(`
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
    `, [currentYear, id_kampus]);

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
        averageResponseTime: Number(avgResponseTime[0].avg_days || 0).toFixed(2),
        companyAcceptance
      }
    });

  } catch (err) {
    console.error("Dashboard summary error:", err);
    res.status(500).json({
      success: false,
      message: "Server error",
      error: err.message
    });
  }
});

module.exports = router;
module.exports.checkAndSetPublishedDate = checkAndSetPublishedDate;