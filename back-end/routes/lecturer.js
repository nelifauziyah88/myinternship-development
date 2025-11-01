const express = require('express');
const router = express.Router();
const bcrypt = require('bcryptjs');
const db = require('../db');

// Login
router.post("/login_lecturer", async (req, res) => {
  try {
    let { username, password } = req.body || {};
    if (!username || !password) {
      return res.status(400).json({ success: false, message: "Username and password are required." });
    }

    username = String(username).trim();
    password = String(password).trim();

    const [rows] = await db.query(
      "SELECT * FROM lecturer WHERE LOWER(nim_nik_unit) = LOWER(?) OR LOWER(email_polibatam) = LOWER(?)",
      [username, username]
    );

    if (!rows || rows.length === 0) {
      return res.status(401).json({ success: false, message: "Account not found." });
    }

    const user = rows[0];

    if (user.status && user.status.toLowerCase() !== "active") {
      return res.status(403).json({ success: false, message: "Account inactive." });
    }

    const stored = (user.password || "").trim();
    let passwordMatch = false;

    if (stored.startsWith("$2a$") || stored.startsWith("$2b$") || stored.startsWith("$2y$")) {
      passwordMatch = await bcrypt.compare(password, stored);
    } else {
      passwordMatch = stored === password;
    }

    if (!passwordMatch) {
      return res.status(401).json({ success: false, message: "Wrong password." });
    }

    return res.status(200).json({
      success: true,
      message: "Login successfull.",
      user: {
        nim_nik_unit: user.nim_nik_unit,
        name: user.name,
        email_polibatam: user.email_polibatam,
        role: "lecturer",
        is_koor: user.is_koor || 0,
        prodi_koor: user.prodi_koor || null,
        status: user.status,
        id_kampus: user.id_kampus || null,
      },
    });
  } catch (err) {
    console.error("[LOGIN LECTURER] error:", err);
    return res.status(500).json({ success: false, message: "An error occurred on the server." });
  }
});

// List submission mahasiswa
router.get("/lecturer/submissions/:nim_nik_unit", async (req, res) => {
  const { nim_nik_unit } = req.params;

  try {
    const [lecRows] = await db.query(
      `SELECT prodi_koor, id_kampus 
       FROM lecturer 
       WHERE nim_nik_unit = ? AND is_koor = 1 
       LIMIT 1`,
      [nim_nik_unit]
    );

    if (!lecRows.length) {
      return res.status(403).json({ success: false, message: "You are not the internship coordinator." });
    }

    const { prodi_koor, id_kampus } = lecRows[0];

    const [rows] = await db.query(
      `SELECT 
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
       WHERE s.program_study LIKE CONCAT('%', ?, '%') 
         AND s.id_kampus = ?
       ORDER BY il.created_at DESC`,
      [prodi_koor, id_kampus]
    );

    res.json({ success: true, data: rows });
  } catch (err) {
    console.error("[KOOR] Error fetching submissions:", err);
    res.status(500).json({ success: false, message: "Server error" });
  }
});

// Detail submission
router.get("/lecturer/submissions/detail/:id_letter", async (req, res) => {
  const { id_letter } = req.params;

  try {
    const [rows] = await db.query(
      `SELECT 
         il.id_letter,
         il.nim,
         s.name AS student_name,
         s.program_study,
         il.company_name,
         il.company_address,
         il.company_contact,
         il.start_date,
         il.end_date,
         il.status,
         il.koor_approval,
         il.cdc_approval,
         il.created_at,
         il.updated_at
       FROM internship_letter il
       JOIN student_internship s ON s.nim = il.nim
       WHERE il.id_letter = ?`,
      [id_letter]
    );

    if (!rows.length) {
      return res.status(404).json({ success: false, message: "Submission not found" });
    }

    res.json({ success: true, data: rows[0] });
  } catch (err) {
    console.error("[KOOR] Error fetching submission detail:", err);
    res.status(500).json({ success: false, message: "Server error" });
  }
});

// Approve/Reject submission
router.post("/lecturer/approval", async (req, res) => {
  const { id_letter, status } = req.body;
  if (!id_letter || !status) {
    return res.status(400).json({ success: false, message: "id_letter and status are required." });
  }

  const allowed = ["ACCEPTED", "REJECTED"];
  if (!allowed.includes(status.toUpperCase())) {
    return res.status(400).json({ success: false, message: "Status invalid." });
  }

  try {
    if (status.toUpperCase() === "ACCEPTED") {
      await db.query(
        `UPDATE internship_letter
         SET koor_approval = 'ACCEPTED',
             status = 'WAITING',
             cdc_approval = 'WAITING',
             updated_at = NOW()
         WHERE id_letter = ?`,
        [id_letter]
      );
    } else {
      await db.query(
        `UPDATE internship_letter
         SET koor_approval = 'REJECTED',
             status = 'REJECTED',
             cdc_approval = 'REJECTED',
             updated_at = NOW()
         WHERE id_letter = ?`,
        [id_letter]
      );
    }

    res.json({
      success: true,
      message: `Submission has been ${status.toLowerCase()} by coordinator.`,
    });
  } catch (err) {
    console.error("[KOOR] Error updating approval:", err);
    res.status(500).json({ success: false, message: "Server error" });
  }
});

module.exports = router;
