<?php
require_once __DIR__ . '/../config/db.php';

// Load PHPMailer
require_once __DIR__ . '/../library/mailer/src/PHPMailer.php';
require_once __DIR__ . '/../library/mailer/src/SMTP.php';
require_once __DIR__ . '/../library/mailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Konfigurasi email
$email_pengirim = 'punyakalian';
$apppass        = 'punyakalian';

// Fungsi send email, PHPMailer + Gmail SMTP
function sendEmail($to, $subject, $body, $email_pengirim, $apppass) {
    $mail = new PHPMailer(true);

    try {
        // Konfigurasi SMTP Gmail
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $email_pengirim;
        $mail->Password   = $apppass;
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Pengirim
        $mail->setFrom($email_pengirim, 'MyInternship Reminder');

        // Tujuan
        $mail->addAddress($to);

        // Konten email
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        // Kirim
        $mail->send();
        return true;

    } catch (Exception $e) {
        echo "Error sending to $to: {$mail->ErrorInfo}<br>";
        return false;
    }
}

// Ambil tanggal hari ini
$today = date('Y-m-d');

// Query mahasiswa yang butuh reminder
$sql = "
SELECT 
    internship.id_internship,
    internship.end_date,
    user_company.user_email AS supervisor_email
FROM internship
JOIN internship_supervisor 
    ON internship_supervisor.id_internship = internship.id_internship
JOIN user_company
    ON user_company.id_user_company = internship_supervisor.id_user_company
WHERE internship.end_date = CURDATE()
AND internship.reminder_sent_at IS NULL
";

$result = $conn->query($sql);

if ($result === false) {
    echo "SQL Error: " . $conn->error;
    exit;
}

// Loop data & kirim email
if ($result->num_rows === 0) {
    echo "No students have an end date today.";
} else {
    while ($row = $result->fetch_assoc()) {

        $id_internship = $row['id_internship'];
        $email_supervisor = $row['supervisor_email'];

        // SUBJECT EMAIL
        $subject = "Reminder Internsip Feedback - MyInternship";

        // BODY EMAIL
        $body = "
            <p>Yth. Supervisor,</p>
            <p>Mahasiswa dengan ID <strong>{$id_internship}</strong> telah selesai melaksanakan magang pada tanggal <strong>{$today}</strong>.</p>
            <p>Mohon untuk segera mengisi feedback/penilaian mahasiswa tersebut melalui sistem MyInternship.</p>
            <p>Terima kasih.</p>
            <br>
            <p>Automated Email - MyInternship</p>
        ";

        echo "<br>Sending reminder to: {$email_supervisor} ... ";

        // KIRIM EMAIL
        $sent = sendEmail($email_supervisor, $subject, $body, $email_pengirim, $apppass);

        if ($sent) {
            // UPDATE reminder_sent_at jika email sukses
            $upd = "
                UPDATE internship 
                SET reminder_sent_at = NOW() 
                WHERE id_internship = {$id_internship}
            ";
            $conn->query($upd);

            echo "<span style='color:green;'>SUCCESS</span>";
        } else {
            echo "<span style='color:red;'>FAILED</span>";
        }
    }
}

echo "<br><br>Reminder cron executed at: " . date('Y-m-d H:i:s');
