const express = require("express");
const router = express.Router();
const bcrypt = require("bcryptjs");
const puppeteer = require("puppeteer");
const db = require("../db");
const fs = require("fs");
const path = require("path");
const multer = require("multer");

// Login
router.post("/login_student", async (req, res) => {
  try {
    const { username, password } = req.body;

    // Validasi input wajib diisi
    if (!username || !password) {
      return res.status(400).json({
        success: false,
        message: "Username and Password are required.",
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
        .json({ success: false, message: "Account not found." });
    }

    const user = rows[0];

    // Memeriksa password (berdasarkan hash)
    const validPassword = await bcrypt.compare(password, user.password);
    if (!validPassword) {
      return res
        .status(401)
        .json({ success: false, message: "Incorrect password." });
    }

    // Jika berhasil
    res.status(200).json({
      success: true,
      message: "Login successfull.",
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

/**
 * GET /api/student/check-submission/:nim
 * - Cek apakah student dengan NIM punya submission aktif
 * - "Aktif" bila salah satu kolom status/koor_approval/cdc_approval = 'WAITING'
 */
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

/**
 * GET /api/student/form-submission/:nim
 * - Ambil student profile + department + coordinator
 * - Alur join:
 *   student_internship (program_study, id_kampus)
 *   join program_study on program_study.kode_prodi = student_internship.program_study AND id_kampus match
 *   join lecturer on lecturer.prodi_koor = program_study.kode_prodi AND lecturer.id_kampus = program_study.id_kampus AND lecturer.is_koor = 1
 */
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

    // ambil program_study (cocokkan kode_prodi & id_kampus)
    const [psRows] = await db.query(
      `SELECT * FROM program_study WHERE kode_prodi = ? AND id_kampus = ? LIMIT 1`,
      [student.program_study, student.id_kampus]
    );
    const program = psRows.length ? psRows[0] : null;
    const department = program ? program.jurusan || null : null;

    // ambil lecturer koordinator
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
        program_study: student.program_study,
        id_kampus: student.id_kampus,
        email: student.email || null,
      },
      department,
      coordinator,
    });
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: "Server error" });
  }
});

/**
 * GET /api/student/company
 * - Return list company (id, name).
 * - Optional query q for search.
 */
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

/**
 * GET /api/student/company/:id
 * - Return details company
 */
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

/**
 * POST /api/student/form-submission
 * - Terima payload dan insert ke internship_letter
 * - Validasi:
 *    - nim required
 *    - bila company_id null -> require new_company_name, new_company_contact, company_address
 *    - bila company_id present -> get company address from DB and ignore client address for safety
 */
router.post("/form-submission", async (req, res) => {
  try {
    const body = req.body;
    const nim = body.nim;
    if (!nim) return res.status(400).json({ error: "nim required" });

    // check if there's an active submission
    const [lastRows] = await db.query(
      `SELECT id_letter, status, koor_approval, cdc_approval FROM internship_letter WHERE nim = ? ORDER BY created_at DESC LIMIT 1`,
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

    // determine company fields
    let id_company = body.company_id || null;
    let company_name = body.company_name || null;
    let company_contact = body.company_contact || null;
    let company_address = body.company_address || null;

    // Tambahkan variable untuk track company_not_exist
    let company_not_exist = 1;

    if (!id_company) {
      // new company -> require fields
      if (!company_name || !company_contact || !company_address) {
        return res
          .status(400)
          .json({ error: "new company name/contact/address required" });
      }

      // SESUDAH - Tambahkan semua kolom NOT NULL dengan nilai default:
      const [stuRow] = await db.query(
        `SELECT id_kampus FROM student_internship WHERE nim = ? LIMIT 1`,
        [nim]
      );
      const id_kampus = stuRow.length ? stuRow[0].id_kampus : 1;

      const [companyResult] = await db.query(
        `INSERT INTO company (
  name, type, phone, email, website, facebook, twitter, instagram, linkedin, logo,
  address, province, city, description, status, access_type, id_kampus
)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'not verified', 1, ?)
`,
        [
          company_name,
          "General", // type (default)
          body.company_contact || "", // phone (gunakan string kosong jika null)
          "", // email
          "", // website (default kosong)
          "", // facebook (default kosong)
          "", // twitter (default kosong)
          "", // instagram (default kosong)
          "", // linkedin (default kosong)
          "", // logo (default kosong)
          company_address || "", // address
          "", // province (default kosong)
          "", // city (default kosong)
          "", // description (default kosong)
          id_kampus, // id_kampus (sesuaikan dengan student.id_kampus atau default 1)
        ]
      );

      // Set id_company dengan id yang baru dibuat
      id_company = companyResult.insertId;

      // Set flag bahwa ini company baru (tidak exist di database sebelumnya)
      company_not_exist = 1; // 0 = company tidak exist sebelumnya
    } else {
      // existing company -> get address, phone, and email from DB
      const [cRow] = await db.query(
        `SELECT name, address, phone, email 
     FROM company 
     WHERE id_company = ? 
     LIMIT 1`,
        [id_company]
      );

      if (!cRow.length)
        return res.status(400).json({ error: "Invalid company_id" });

      company_name = cRow[0].name;
      company_address = cRow[0].address;

      // Format contact (email prioritas)
      const phone = cRow[0].phone || "";
      const email = cRow[0].email || "";
      if (phone && email) {
        company_contact = `${phone}\n${email}`;
      } else if (email) {
        company_contact = email;
      } else if (phone) {
        company_contact = phone;
      } else {
        company_contact = company_contact || null;
      }

      // Set flag bahwa company sudah exist di database
      company_not_exist = 0; // 1 = company exist di database
    }

    // other fields (guard defaults)
    const start_date = body.start_date || null;
    const end_date = body.end_date || null;
    const semester = body.semester || null;
    const class_type = body.class || null;
    const email = body.email || null;
    const phone = body.phone || null;
    const no_whatsapp = body.no_whatsapp || body.phone || null;
    const language = body.language || null;

    // server-side required validations
    if (!class_type) return res.status(400).json({ error: "class required" });
    if (!semester) return res.status(400).json({ error: "semester required" });
    if (!start_date)
      return res.status(400).json({ error: "start_date required" });
    if (!end_date) return res.status(400).json({ error: "end_date required" });
    if (!email) return res.status(400).json({ error: "email required" });
    if (!phone) return res.status(400).json({ error: "phone required" });
    if (!language) return res.status(400).json({ error: "language required" });

    // Generate letter_number otomatis
    const [lastLetter] = await db.query(
      "SELECT MAX(letter_number) AS lastNum FROM internship_letter"
    );
    const nextNumber = (lastLetter[0].lastNum || 0) + 1;

    // insert into internship_letter
    const [result] = await db.query(
      `INSERT INTO internship_letter 
    (nim, id_company, start_date, end_date, status, semester, class, koor_approval, cdc_approval, company_name, company_contact, company_address, language, letter_number, company_not_exist)
   VALUES (?, ?, ?, ?, 'WAITING', ?, ?, 'WAITING', 'WAITING', ?, ?, ?, ?, ?, ?)`,
      [
        nim,
        id_company,
        start_date,
        end_date,
        semester,
        class_type,
        company_name,
        company_contact,
        company_address,
        language,
        nextNumber,
        company_not_exist,
      ]
    );

    // INSERT KODE BARU DI SINI - Update email dan phone di student_internship
    if (email || no_whatsapp) {
      const updateFields = [];
      const updateValues = [];

      if (email) {
        updateFields.push("email = ?");
        updateValues.push(email);
      }

      if (no_whatsapp) {
        updateFields.push("no_whatsapp = ?");
        updateValues.push(no_whatsapp);
      }

      if (updateFields.length > 0) {
        updateValues.push(nim); // untuk WHERE clause
        await db.query(
          `UPDATE student_internship 
           SET ${updateFields.join(", ")} 
           WHERE nim = ?`,
          updateValues
        );
        console.log(`Updated student contact info for NIM: ${nim}`);
      }
    }

    res.json({ success: true, id: result.insertId });
  } catch (err) {
    console.error("form-submission error:", err);
    res.status(500).json({ error: err.message });
  }
});

/**
 * GET /api/student/approval-status/:nim
 * Ambil semua internship_letter milik nim, urut terbaru (created_at desc)
 */
router.get("/approval-status/:nim", async (req, res) => {
  try {
    const nim = req.params.nim;

    const sql = `
  SELECT 
    il.id_letter,
    il.nim,
    il.koor_approval,
    il.cdc_approval,
    il.status,
    il.acceptance_status,
    il.published_date,
    il.created_at,
    il.updated_at,
    il.language,

    -- Ambil alasan dari koor (REJECTED terakhir)
    (
      SELECT comment 
      FROM internship_letter_history h 
      WHERE h.id_letter = il.id_letter 
        AND LOWER(h.approved_by) = 'internship coordinator'
        AND LOWER(h.status_approval) = 'rejected'
      ORDER BY h.id_history DESC
      LIMIT 1
    ) AS koor_reason,

    -- Ambil alasan dari cdc (REJECTED terakhir)
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

/**
 * GET /api/internship_letter/:id
 * Ambil detail surat magang berdasarkan id_letter (termasuk data student & company)
 */
// Ambil detail surat + student + company
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
        },
        eng: {
          start_date: formatDateEng(letter.start_date),
          end_date: formatDateEng(letter.end_date),
        },
      },
    });
  } catch (err) {
    console.error("GET /internship_letter/:id error:", err);
    return res.status(500).json({ error: "Server error" });
  }
});

// helper: format tanggal ke "13 Agustus 2025"
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

// helper: format tanggal ke "October 30, 2025"
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

    // Cek apakah kedua approval sudah ACCEPTED dan published_date masih NULL
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

// Route: generate & download PDF letter by id_letter
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

    // JOIN ke tabel program_study biar lengkap
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
    const month = now.getMonth(); // 0-11
    const monthRoman = getMonthRoman(month);

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

    // Helper function untuk konversi bulan ke romawi
    function getMonthRoman(month) {
      const romans = [
        "I",
        "II",
        "III",
        "IV",
        "V",
        "VI",
        "VII",
        "VIII",
        "IX",
        "X",
        "XI",
        "XII",
      ];
      return romans[month];
    }

    // Template URL - kirim monthRoman sebagai query parameter
    const templateFile =
      lang === "ENG" ? "internship_letter_eng.php" : "internship_letter_id.php";
    const templateURL = `http://localhost/myinternship/front-end/${templateFile}?id_letter=${id}&lang=${lang}&month_roman=${monthRoman}`;

    // SESUDAH - Tambahkan update published_date:
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

/**
 * POST /api/student/rejected-by-company/:id_letter
 * - Mahasiswa menandai surat magang sebagai REJECTED oleh perusahaan.
 * - Bisa upload bukti (opsional).
 */

// Konfigurasi folder upload
// Konfigurasi folder upload
const uploadDir = path.join(__dirname, "../uploads/company_replies");
if (!fs.existsSync(uploadDir)) fs.mkdirSync(uploadDir, { recursive: true });

const storage = multer.diskStorage({
  destination: (req, file, cb) => cb(null, uploadDir),
  filename: (req, file, cb) => {
    const unique = Date.now() + "-" + Math.round(Math.random() * 1e9);
    cb(null, unique + path.extname(file.originalname));
  },
});
const upload = multer({ storage });

router.post('/rejected-by-company/:id', upload.single('company_reply_letter'), async (req, res) => {
  try {
    const { acceptance_status } = req.body;
    const id = req.params.id;

    console.log("=== DEBUG REJECTED-BY-COMPANY ===");
    console.log("ID:", id);
    console.log("Body:", req.body);
    console.log("File:", req.file);

    if (acceptance_status !== "REJECTED") {
      return res.status(400).json({ success: false, message: "Invalid acceptance status." });
    }

    let filePath = '-'; // default jika tidak upload
if (req.file) {
  filePath = `uploads/${req.file.filename}`;
}


    const [result] = await db.query(
      `UPDATE internship_letter 
       SET acceptance_status = ?, company_reply_letter = ? 
       WHERE id_letter = ?`,  
      [acceptance_status, filePath, id]
    );

    if (result.affectedRows === 0) {
      return res.status(404).json({ success: false, message: "Letter not found." });
    }

    res.json({ success: true, message: "Company rejection recorded." });

  } catch (error) {
    console.error("Error in /rejected-by-company:", error);
    res.status(500).json({ success: false, message: "Server error." });
  }
});

// helper untuk format ke YYYY-MM-DD tanpa timezone offset
function formatDate(dateValue) {
  if (!dateValue) return null;
  const d = new Date(dateValue);
  const year = d.getFullYear();
  const month = String(d.getMonth() + 1).padStart(2, "0");
  const day = String(d.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
}

// ===============================
// GET: Autofill data form accepted_by_company
// ===============================
router.get("/accepted-by-company/autofill/:nim", async (req, res) => {
  const nim = req.params.nim;
  try {
    const [rows] = await db.query(`
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
    `, [nim]);

    if (rows.length === 0) return res.json({ success: false, message: "Data not found" });

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

// ===============================
// POST: Submit accepted_by_company
// ===============================
router.post("/accepted-by-company/submit", async (req, res) => {
  try {
    const chunks = [];
    req.on("data", chunk => chunks.push(chunk));

    req.on("end", async () => {
      const raw = Buffer.concat(chunks);
      const contentType = req.headers["content-type"] || "";
      const boundaryMatch = contentType.match(/boundary=(.+)$/);
      if (!boundaryMatch) return res.status(400).json({ success: false, message: "Invalid form data" });

      const boundary = boundaryMatch[1];
      const parts = raw.toString().split("--" + boundary).filter(p => p.includes("Content-Disposition"));

      const fields = {};
      let uploadedFilePath = null;

      for (const part of parts) {
        const nameMatch = part.match(/name="([^"]+)"/);
        if (!nameMatch) continue;
        const fieldName = nameMatch[1];

        const filenameMatch = part.match(/filename="([^"]+)"/);
        if (filenameMatch) {
          // Handle file upload
          const filename = Date.now() + "_" + filenameMatch[1];
          const fileStart = part.indexOf("\r\n\r\n") + 4;
          const fileEnd = part.lastIndexOf("\r\n");
          const fileContent = part.substring(fileStart, fileEnd);

          const uploadDir = path.join(__dirname, "..", "uploads", "company_reply_letters");
          if (!fs.existsSync(uploadDir)) fs.mkdirSync(uploadDir, { recursive: true });

          const filePath = path.join(uploadDir, filename);
          fs.writeFileSync(filePath, fileContent, "binary");
          uploadedFilePath = "uploads/company_reply_letters/" + filename;
        } else {
          // Handle normal text field
          const valueStart = part.indexOf("\r\n\r\n") + 4;
          const valueEnd = part.lastIndexOf("\r\n");
          const value = part.substring(valueStart, valueEnd).trim();
          fields[fieldName] = value;
        }
      }

      const {
        nim,
        company_name,
        company_address,
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
        company_not_exist
      } = fields;

      const conn = await db.getConnection();
      await conn.beginTransaction();

      try {
        // 1. Ambil data internship_letter
        const [letterRows] = await conn.query(
          "SELECT * FROM internship_letter WHERE nim = ? ORDER BY id_letter DESC LIMIT 1",
          [nim]
        );
        const letter = letterRows[0] || null;
        let id_company = letter?.id_company || null;
        let id_user_company = null;

        // 2. Jika company_not_exist = 1 → insert ke company baru
if (company_not_exist === "1" || (letter && letter.company_not_exist === 1)) {

  // Ambil phone/email dari internship_letter.company_contact (jika ada)
  let phone = "-";
  let email_c = "-"; // pakai email_c biar tidak tabrakan dengan variabel email mahasiswa

  if (letter && letter.company_contact) {
    const contact = letter.company_contact.trim();

    // jika hanya angka atau +62
    if (/^[+0-9\s-]+$/.test(contact) && contact.replace(/\D/g, "").length >= 8) {
      phone = contact;
    }

    // jika mengandung @, anggap email
    else if (contact.includes("@")) {
      email_c = contact;
    }
  }

  // Ambil id_kampus mahasiswa dari tabel student_internship
  const [studRows] = await conn.query(
    "SELECT id_kampus FROM student_internship WHERE nim = ? LIMIT 1",
    [nim]
  );
  const id_kampus = studRows.length > 0 ? studRows[0].id_kampus : 1; // default 1 kalau tidak ditemukan

  // Insert ke tabel company lengkap
  const [result] = await conn.query(`
    INSERT INTO company 
    (name, type, type_other, phone, email, website, facebook, twitter, instagram, linkedin, logo, address, province, city, country, description, status, access_type, id_kampus)
    VALUES (?, '-', NULL, ?, ?, '-', '-', '-', '-', '-', '-', ?, ?, ?, ?, '-', 'verified', '1', ?)
  `, [
    company_name,
    phone,
    email_c,
    company_address,
    province,
    city,
    country,
    id_kampus
  ]);

  id_company = result.insertId;

          // 3. Insert user_company baru (HRD)
let existingPassword = "-";
const [oldHRD] = await conn.query(
  "SELECT password FROM user_company WHERE id_company = ? AND user_type = 'HRD' LIMIT 1",
  [id_company]
);
if (oldHRD.length > 0 && oldHRD[0].password) {
  existingPassword = oldHRD[0].password;
}

// Insert user_company baru dengan username dan password placeholder
const [userResult] = await conn.query(
  "INSERT INTO user_company (id_company, user_fullname, user_email, user_phone, user_type, username, password) VALUES (?, ?, ?, ?, 'HRD', 'TEMP', ?)",
  [id_company, hrd_name, hrd_email, hrd_whatsapp, existingPassword || "-"]
);
          id_user_company = userResult.insertId;

          // 4. Update username HRD jadi format HRD.00X
          let paddedId;
          if (id_user_company < 10) paddedId = `00${id_user_company}`;
          else if (id_user_company < 100) paddedId = `0${id_user_company}`;
          else paddedId = `${id_user_company}`;

          const generatedUsername = `HRD.${paddedId}`;
          await conn.query(
            "UPDATE user_company SET username = ? WHERE id_user_company = ?",
            [generatedUsername, id_user_company]
          );
        } else {
          // 5. Company sudah ada → ambil HRD lama dari user_company
          const [existingHRD] = await conn.query(
            "SELECT id_user_company FROM user_company WHERE id_company = ? AND user_type = 'HRD' LIMIT 1",
            [id_company]
          );

          if (existingHRD.length > 0) {
            id_user_company = existingHRD[0].id_user_company;
          } else {
            // fallback kalau HRD belum pernah terdaftar
            const [userResult] = await conn.query(
              "INSERT INTO user_company (id_company, user_fullname, user_email, user_phone, user_type) VALUES (?, ?, ?, ?, 'HRD')",
              [id_company, hrd_name, hrd_email, hrd_whatsapp]
            );
            id_user_company = userResult.insertId;

            // update username
            let paddedId;
            if (id_user_company < 10) paddedId = `00${id_user_company}`;
            else if (id_user_company < 100) paddedId = `0${id_user_company}`;
            else paddedId = `${id_user_company}`;
            const generatedUsername = `HRD.${paddedId}`;
            await conn.query(
              "UPDATE user_company SET username = ? WHERE id_user_company = ?",
              [generatedUsername, id_user_company]
            );
          }
        }

        // 6. Update student_internship
        await conn.query(
          "UPDATE student_internship SET email = ?, no_whatsapp = ? WHERE nim = ?",
          [email, whatsapp, nim]
        );

        // 7. Insert internship_letter_acceptance
        // === Ambil id_letter dari surat terakhir mahasiswa
        const [letterRows2] = await conn.query(
          "SELECT id_letter FROM internship_letter WHERE nim = ? ORDER BY id_letter DESC LIMIT 1",
          [nim]
        );
        const id_letter = letterRows2.length ? letterRows2[0].id_letter : null;

        // === Hitung periode magang (harus di atas insert internship)
        const ms = Math.abs(new Date(end_date) - new Date(start_date));
        const months = Math.max(1, Math.floor(ms / (1000 * 60 * 60 * 24 * 30)));
        const internship_period = `${months} month(s)`;

        // === Insert into internship
        const [internshipResult] = await conn.query(
          `INSERT INTO internship 
          (nim, id_company, start_date, end_date, id_user_company, status, internship_position, internship_period, timestamp_register)
          VALUES (?, ?, ?, ?, ?, 'ongoing', ?, ?, NOW())`,
          [nim, id_company, start_date, end_date, id_user_company, placement_department, internship_period]
        );
        const id_internship = internshipResult.insertId;

        // === Insert into internship_letter_acceptance
        await conn.query(
          `INSERT INTO internship_letter_acceptance (id_letter, id_internship, source_internship_info, created_at)
          VALUES (?, ?, ?, NOW())`,
          [id_letter, id_internship, info_source]
        );

        // Update internship_letter (company_reply_letter + acceptance_status)
        if (uploadedFilePath) {
          await conn.query(
            "UPDATE internship_letter SET company_reply_letter = ?, acceptance_status = 'ACCEPTED' WHERE nim = ?",
            [uploadedFilePath, nim]
          );
        } else {
          // Accpted without upload files
          await conn.query(
            "UPDATE internship_letter SET acceptance_status = 'ACCEPTED' WHERE nim = ?",
            [nim]
          );
        }

        await conn.commit();
        res.json({ success: true, message: "Internship claim submitted successfully!" });
      } catch (err) {
        await conn.rollback();
        console.error("Transaction error:", err);
        res.status(500).json({ success: false, error: "Database transaction failed" });
      } finally {
        conn.release();
      }
    });
  } catch (err) {
    console.error("Upload error:", err);
    res.status(500).json({ success: false, error: "File upload failed" });
  }
});

module.exports = router;
module.exports.checkAndSetPublishedDate = checkAndSetPublishedDate;
