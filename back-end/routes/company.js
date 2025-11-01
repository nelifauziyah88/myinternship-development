const express = require('express');
const router = express.Router();
const db = require('../db');
const bcrypt = require('bcryptjs');

// POST /api/login_company
router.post('/login_company', async (req, res) => {
  try {
    const { username, password } = req.body || {};
    if (!username || !password) {
      return res.status(400).json({ success: false, message: 'Username dan password wajib diisi.' });
    }

    // Cari company berdasarkan username
    const [rows] = await db.promise().query('SELECT * FROM company WHERE username = ?', [username]);

    if (rows.length === 0) {
      return res.status(401).json({ success: false, message: 'Username tidak ditemukan.' });
    }

    const company = rows[0];

    // Bandingkan password
    const isMatch = await bcrypt.compare(password, company.password);
    if (!isMatch) {
      return res.status(401).json({ success: false, message: 'Password salah.' });
    }

    // Jika berhasil login
    res.json({
      success: true,
      message: 'Login company berhasil!',
      data: {
        id: company.id_company,
        username: company.username,
        company_name: company.company_name,
        email: company.email
      }
    });

  } catch (error) {
    console.error('Error during company login:', error);
    res.status(500).json({ success: false, message: 'Terjadi kesalahan server.', error: error.message });
  }
});

module.exports = router;
