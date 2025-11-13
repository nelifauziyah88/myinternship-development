const nodemailer = require("nodemailer");
const cron = require("node-cron");

// Konfigurasi transporter Mailtrap
const transporter = nodemailer.createTransport({
  host: "smtp.mailtrap.io",
  port: 2525,
  auth: {
    user: "0819e60d498e66", 
    pass: "6ef16c2b68e07b",       
  },
});

// Dummy data (simulasi hasil dari database)
const dummyData = [
  {
    student_name: "Neli Fauziyah",
    company_email: "company@example.com",
    end_date: new Date().toISOString().split("T")[0], // hari ini
  },
  {
    student_name: "Raisa Putri",
    company_email: "partner@example.com",
    end_date: new Date().toISOString().split("T")[0], // hari ini juga
  },
];

// 🔁 Cron job: jalan tiap 1 menit biar gampang dites
cron.schedule("00 10 * * *", async () => {
  console.log("⏰ Mengecek mahasiswa magang (dummy)...");

  for (const row of dummyData) {
    const mailOptions = {
      from: '"MyInternship" <no-reply@myinternship.test>',
      to: row.company_email,
      subject: "📅 Pengingat: Magang berakhir hari ini",
      text: `Halo,\n\nMahasiswa bernama ${row.student_name} telah menyelesaikan masa magangnya hari ini (${row.end_date}).\n\nTerima kasih atas kerja samanya.\n\nSalam,\nTim MyInternship`,
    };

    try {
      await transporter.sendMail(mailOptions);
      console.log(`✅ Email terkirim ke ${row.company_email}`);
    } catch (error) {
      console.error(`❌ Gagal kirim ke ${row.company_email}:`, error);
    }
  }
});
