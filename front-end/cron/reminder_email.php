<?php
require_once __DIR__ . '/../config/db.php';

require_once __DIR__ . '/../library/mailer/src/PHPMailer.php';
require_once __DIR__ . '/../library/mailer/src/SMTP.php';
require_once __DIR__ . '/../library/mailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$email_pengirim = '...';
$apppass        = '***';

function sendEmail($to, $subject, $body, $email_pengirim, $apppass)
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $email_pengirim;
        $mail->Password   = $apppass;
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom($email_pengirim, 'MyInternship Reminder');
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        echo "Error sending to $to: {$mail->ErrorInfo}<br>";
        return false;
    }
}

$today = date('Y-m-d');

$sql = "
SELECT 
    internship.id_internship,
    internship.nim,
    internship.end_date,
    internship.id_user_company,
    user_company.user_email,
    user_company.user_fullname,
    user_company.username,
    user_company.user_type
FROM internship
LEFT JOIN user_company 
    ON user_company.id_user_company = internship.id_user_company
WHERE internship.end_date = CURDATE()
AND internship.reminder_sent_at IS NULL
";

$result = $conn->query($sql);

if ($result === false) {
    echo "SQL Error: " . $conn->error;
    exit;
}

if ($result->num_rows === 0) {
    echo "No students have an end date today.";
} else {

    while ($row = $result->fetch_assoc()) {

        $id_internship = $row['id_internship'];
        $nim = $row['nim'];
        $id_user_company = $row['id_user_company'];
        $user_type = $row['user_type']; 
        $email_supervisor = $row['user_email'];

        $supervisor_name = $row['user_fullname'] ?: $row['username'];

        if ($id_user_company === null) {
            echo "<br>SKIPPED: No id_user_company assigned for internship ID {$id_internship}";
            continue;
        }

        if ($user_type !== 'SPV') {
            echo "<br>SKIPPED: User type not SPV for internship ID {$id_internship}";
            continue;
        }

        if (!$email_supervisor) {
            echo "<br>SKIPPED: Supervisor email empty for internship ID {$id_internship}";
            continue;
        }

        $subject = "Reminder Internship Feedback - MyInternship";

        $body = "
            <p>Yth. {$supervisor_name},</p>
            <p>Mahasiswa dengan NIM <strong>{$nim}</strong> telah selesai melaksanakan magang pada tanggal <strong>{$today}</strong>.</p>
            <p>Mohon untuk segera mengisi feedback/penilaian mahasiswa tersebut melalui sistem MyInternship.</p>
            <p>Terima kasih.</p>
            <br>
            <p>Automated Email - MyInternship</p>
        ";

        echo "<br>Sending reminder to: {$email_supervisor} ... ";

        $sent = sendEmail($email_supervisor, $subject, $body, $email_pengirim, $apppass);

        if ($sent) {
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
