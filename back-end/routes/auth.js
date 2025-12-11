const express = require('express');
const router = express.Router();
const bcrypt = require('bcryptjs');
const db = require('../db');

// GET /api/kampus, ambil semua kampus
router.get('/kampus', async (req, res) => {
  try {
    const [rows] = await db.query("SELECT id_kampus, nama_kampus FROM tb_kampus");
    res.json(rows);
  } catch (error) {
    console.error("Error fetching kampus:", error);
    res.status(500).json({ message: "Failed to fetch kampus data." });
  }
});

// GET /api/program_study/:id_kampus, ambil program studi berdasarkan kampus
router.get('/program_study/:id_kampus', async (req, res) => {
  try {
    const { id_kampus } = req.params;
    const [rows] = await db.query(
      "SELECT kode_prodi, jenjang, prodi, study_program FROM program_study WHERE id_kampus = ?",
      [id_kampus]
    );
    res.json(rows);
  } catch (error) {
    console.error("Error fetching program study:", error);
    res.status(500).json({ message: "Failed to fetch program study data." });
  }
});

// Api registrasi
router.post('/registrasi', async (req, res) => {
  try {
    const {
      nim,
      name,
      programStudy,
      email,
      otherEmail,
      phone,
      noWhatsapp,
      username,
      password,
      nikDospem,
      idKampus
    } = req.body;

    if (!nim || !name || !email || !username || !password) {
      return res.status(400).json({ message: 'Please fill in all required fields (NIM, name, email, username, password).' });
    }

    if (!programStudy || (typeof programStudy === 'string' && programStudy.toLowerCase().includes('select'))) {
      return res.status(400).json({ message: 'Study Program is required' });
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
      return res.status(400).json({ message: 'Invalid email format !' });
    }

    if (password.length < 8) {
      return res.status(400).json({ message: 'Password must be at least 8 characters long !' });
    }
    if (!(/[a-z]/.test(password) && /[A-Z]/.test(password))) {
      return res.status(400).json({ message: 'Passwords must contain both uppercase and lowercase letters !' });
    }
    if (!/\d/.test(password)) {
      return res.status(400).json({ message: 'Passwords must contain at least 1 number !' });
    }
    if (!/[!@#$%^&*(),.?":{}|<>_\-\[\]\\\/;'+=]/.test(password)) {
      return res.status(400).json({ message: 'Passwords must contain at least 1 special character !' });
    }

    const lowerPW = password.toLowerCase();
    if (username && lowerPW.includes(String(username).toLowerCase())) {
      return res.status(400).json({ message: 'Passwords must not contain usernames !' });
    }
    if (email && lowerPW.includes(String(email).toLowerCase())) {
      return res.status(400).json({ message: 'Passwords must not contain email addresses !' });
    }

    const hashedPassword = await bcrypt.hash(password, 10);

    await db.query(
      `INSERT INTO student_internship 
        (nim, name, program_study, email, other_email, phone, no_whatsapp, username, password, nik_dospem, id_kampus, account_status, local_account, password_reset)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 1, 0)`,
      [
        nim,
        name,
        programStudy,
        email,
        otherEmail || '',
        phone || '',
        noWhatsapp || '',
        username,
        hashedPassword,
        nikDospem || '',
        idKampus || 1
      ]
    );

    res.status(201).json({ message: 'Registration successful!' });
  } catch (error) {
    console.error('Error registration:', error);
    res.status(500).json({ message: 'An error occurred. Please try again!' });
  }
});

module.exports = router;
