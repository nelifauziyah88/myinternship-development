const express = require("express");
const router = express.Router();
const db = require("../db");

// GET /api/kampus/:id
router.get("/:id", async (req, res) => {
  try {
    const { id } = req.params;
    console.log(` [GET KAMPUS] Fetching kampus with id: ${id}`);

    const [rows] = await db
      .query("SELECT nama_kampus FROM tb_kampus WHERE id_kampus = ? LIMIT 1", [
        id,
      ]);

    console.log(" [GET KAMPUS] Query result:", rows);

    if (!rows || rows.length === 0) {
      console.log(" [GET KAMPUS] Campus not found");
      return res.status(404).json({
        success: false,
        message: "Campus not found",
        nama_kampus: "Campus not found",
      });
    }

    const kampusName = rows[0].nama_kampus;
    console.log(` [GET KAMPUS] Found: ${kampusName}`);

    return res.status(200).json({
      success: true,
      nama_kampus: kampusName,
    });
  } catch (err) {
    console.error(" [GET KAMPUS] Error:", err);
    return res.status(500).json({
      success: false,
      message: "Error Server: " + err.message,
      nama_kampus: "Error Server",
    });
  }
});

module.exports = router;
