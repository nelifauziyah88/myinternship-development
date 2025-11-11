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

// Approve/Reject submission (Lecturer / Coordinator)
router.post("/lecturer/approval", async (req, res) => {
  try {
    const { id_letter, status, user_id, user_name, comment } = req.body;

    if (!id_letter || !status) {
      return res.status(400).json({ success: false, message: "id_letter and status are required." });
    }

    const s = status.toUpperCase();
    if (!["ACCEPTED", "REJECTED"].includes(s)) {
      return res.status(400).json({ success: false, message: "Status invalid." });
    }

    // fetch current letter
    const [rows] = await db.query(
      `SELECT id_letter, koor_approval
       FROM internship_letter
       WHERE id_letter = ? LIMIT 1`,
      [id_letter]
    );

    if (!rows.length) {
      return res.status(404).json({ success: false, message: "Submission not found." });
    }

    const row = rows[0];

    // For lecturer action, ensure current koor_approval is WAITING (so coordinator can act)
    if ((row.koor_approval || '').toUpperCase() !== "WAITING") {
      return res.status(400).json({ success: false, message: "The submission is not awaiting Coordinator approval." });
    }

    // Apply update to internship_letter (koor's action)
    if (s === "ACCEPTED") {
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

    // --- Insert into internship_letter_history ---
    let approved_by = "INTERNSHIP COORDINATOR";
    let user_id_val = "-";
    let user_name_val = "-";

    try {
      // try server-side lookup from session if present
      const actor = req.user || req.session?.user || null;
      if (actor) {
        // If you keep lecturer session with nim_nik_unit stored, try to look it up
        if (actor.role && actor.role.toLowerCase().includes("lecturer") || actor.is_koor) {
          const [lrows] = await db.query(
            `SELECT nim_nik_unit, name FROM lecturer WHERE nim_nik_unit = ? LIMIT 1`,
            [actor.nim_nik_unit || actor.id]
          );
          if (lrows && lrows.length) {
            user_id_val = lrows[0].nim_nik_unit || "-";
            user_name_val = lrows[0].name || "-";
            approved_by = "INTERNSHIP COORDINATOR";
          }
        } else {
          user_id_val = actor.id || "-";
          user_name_val = actor.name || "-";
        }
      }
    } catch (err) {
      console.error("[KOOR] user lookup error:", err);
    }

    // fallback to values sent by client if server-side lookup missing
    const final_user_id = (user_id_val && user_id_val !== "-") ? user_id_val : (user_id || "-");
    const final_user_name = (user_name_val && user_name_val !== "-") ? user_name_val : (user_name || "-");

    await db.query(
      `INSERT INTO internship_letter_history 
       (id_letter, approved_by, user_id, user_name, status_approval, timestamp, comment)
       VALUES (?, ?, ?, ?, ?, NOW(), ?)`,
      [id_letter, approved_by, final_user_id, final_user_name, s, comment || null]
    );

    return res.json({
      success: true,
      message: `Submission has been ${s.toLowerCase()} by Internship Coordinator.`,
    });
  } catch (err) {
    console.error("[LECTURER] approval error:", err);
    return res.status(500).json({
      success: false,
      message: "Server error: " + err.message,
    });
  }
});

// get latest rejected reason by CDC for a letter
router.get("/lecturer/reason/:id", async (req, res) => {
  try {
    const id = req.params.id;
    const [rows] = await db.query(
      `SELECT comment, timestamp, approved_by, user_name, user_id
       FROM internship_letter_history
       WHERE id_letter = ? AND status_approval = 'REJECTED' AND approved_by = 'INTERNSHIP COORDINATOR'
       ORDER BY timestamp DESC LIMIT 1`,
      [id]
    );
    if (!rows.length) return res.status(404).json({ success: false, message: "Reason not found." });
    res.json({ success: true, comment: rows[0].comment, meta: { user_name: rows[0].user_name, timestamp: rows[0].timestamp } });
  } catch (err) {
    console.error("[CDC] reason fetch error:", err);
    res.status(500).json({ success: false, message: "Server error" });
  }
});

// edit the latest CDC rejection reason for a letter
router.post("/lecturer/history/:id/edit", async (req, res) => {
  try {
    const id = req.params.id;
    const { comment } = req.body;
    if (!comment || !comment.trim()) return res.status(400).json({ success: false, message: "Comment is required." });

    // Find the latest history row to update
    const [rows] = await db.query(
      `SELECT id_history FROM internship_letter_history
       WHERE id_letter = ? AND status_approval = 'REJECTED' AND approved_by = 'INTERNSHIP COORDINATOR'
       ORDER BY timestamp DESC LIMIT 1`,
      [id]
    );
    if (!rows.length) return res.status(404).json({ success: false, message: "History record not found." });

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

module.exports = router;
