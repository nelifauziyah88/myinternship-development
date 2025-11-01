<?php
$id_letter = $_GET['id_letter'] ?? null;
if (!$id_letter) die("ID Letter not found !");

$lang = $_GET['lang'] ?? 'ID';
$apiUrl = "http://localhost:8000/api/student/internship_letter/$id_letter?lang=$lang";

$response = file_get_contents($apiUrl);
if ($response === FALSE) die("Failed to retrieve data from API.");
$data = json_decode($response, true);
if (!$data) die("Letter data not found.");

$letter = $data;
$student = $data['student'] ?? [];
$company = $data['company'] ?? [];

$fmt = new IntlDateFormatter('id_ID', IntlDateFormatter::LONG, IntlDateFormatter::NONE, 'Asia/Jakarta', IntlDateFormatter::GREGORIAN, 'd MMMM yyyy');

$start_date = $fmt->format(new DateTime($letter['start_date']));
$end_date   = $fmt->format(new DateTime($letter['end_date']));
$dateStr    = $fmt->format(new DateTime()); // tanggal hari ini
$periode    = $start_date . ' - ' . $end_date;


$year = date('Y');
$letterNumber = $letter['letter_number'] ?? '___';
$nomorFormatted = "{$letterNumber}/WDI/PL29/VIII/{$year}";

$company_name = $letter['company_name'] ?? ($company['name'] ?? '');
$company_addr = $letter['company_address'] ?? ($company['address'] ?? '');
$student_name = $student['name'] ?? '';
$student_nim  = $letter['nim'] ?? '';
$program_study = ($student['jenjang'] ?? '') . ' ' . ($student['prodi'] ?? ($student['program_study'] ?? ''));

// Logo path (pastikan sesuai struktur folder)
$logoPolibatam = "assets/img/polibatam.png";
$logoUKAS = "assets/img/ukas.png";
$stempel = "assets/img/ttd.png";
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Magang Industri</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        @page {
            size: A4;
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            background: #f5f5f5;
            padding: 10px;
        }

        /* SURAT PERMOHONAN STYLES */
        .container-permohonan {
            width: 210mm;
            min-height: 297mm;
            background: white;
            margin: 0 auto 20px;
            padding: 15mm 20mm;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .header-permohonan {
            display: flex;
            align-items: flex-start;
            border-bottom: 3px solid #000;
            padding-bottom: 6px;
            margin-bottom: 3px;
        }

        .logo-left {
            width: 65px;
            height: 65px;
            margin-right: 12px;
        }

        .logo-left img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .header-text-permohonan {
            flex: 1;
            text-align: center;
        }

        .header-text-permohonan h1 {
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 1px;
            letter-spacing: 0.3px;
        }

        .header-text-permohonan h2 {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 1px;
        }

        .header-text-permohonan p {
            font-size: 8pt;
            line-height: 1.2;
            margin-bottom: 0.5px;
        }

        .logo-right {
            width: 85px;
            display: flex;
            flex-direction: column;
            gap: 3px;
            padding-top: 5px;
        }

        .header-border {
            border-bottom: 1px solid #000;
            margin-bottom: 12px;
        }

        .date {
            text-align: right;
            font-size: 10pt;
            margin-bottom: 12px;
        }

        .letter-info {
            font-size: 10pt;
            margin-bottom: 12px;
            line-height: 1.4;
        }

        .letter-info table {
            border: none;
        }

        .letter-info td {
            padding: 1px 0;
            vertical-align: top;
        }

        .letter-info td:first-child {
            width: 70px;
        }

        .letter-info td:nth-child(2) {
            width: 15px;
        }

        .recipient {
            font-size: 10pt;
            margin-bottom: 12px;
            line-height: 1.4;
        }

        .content-permohonan {
            font-size: 10pt;
            text-align: justify;
            line-height: 1.5;
            margin-bottom: 12px;
        }

        .content-permohonan p {
            margin-bottom: 10px;
        }

        .student-table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0;
            font-size: 9pt;
        }

        .student-table th,
        .student-table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
        }

        .student-table th {
            background: #f0f0f0;
            font-weight: bold;
        }

        .student-table td:nth-child(2) {
            text-align: left;
        }

        .closing {
            font-size: 10pt;
            line-height: 1.5;
            margin-top: 12px;
        }

        .signature-section {
            margin-top: 15px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .tembusan-box {
            text-align: left;
            align-self: flex-end;
            margin-bottom: 10px;
        }

        .tembusan-title {
            font-size: 9pt;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .tembusan-content {
            font-size: 9pt;
            font-style: italic;
            line-height: 1.3;
        }

        .signature-box {
            text-align: center;
            margin-left: auto;
        }

        .signature-title {
            font-size: 10pt;
            margin-bottom: 3px;
        }

        .signature-subtitle {
            font-size: 9pt;
            font-style: italic;
            margin-bottom: 5px;
        }

        .signature-image {
            width: 100px;
            height: 80px;
            margin: 5px auto;
        }

        .signature-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .signature-name {
            font-size: 10pt;
            font-weight: bold;
            margin-top: 5px;
            text-decoration: underline;
        }

        .link {
            color: #0066cc;
            text-decoration: none;
        }

        .link:hover {
            text-decoration: underline;
        }

        /* SURAT BALASAN STYLES */
        .container-balasan {
            width: 210mm;
            height: 297mm;
            margin: 0 auto;
            background-color: white;
            padding: 15mm 20mm;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header-balasan {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            border-bottom: 3px solid #000;
            padding-bottom: 6px;
        }

        .logo-balasan {
            width: 65px;
            height: 65px;
            margin-right: 12px;
            flex-shrink: 0;
        }

        .logo-balasan img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .header-text-balasan {
            flex: 1;
        }

        .header-title {
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 2px;
            line-height: 1.3;
        }

        .header-date {
            font-size: 10pt;
            color: #333;
        }

        .location {
            text-align: right;
            margin: 12px 0;
            font-size: 10pt;
        }

        .letter-meta {
            margin-bottom: 12px;
            font-size: 10pt;
        }

        .letter-meta table {
            border-collapse: collapse;
        }

        .letter-meta td {
            padding: 1px 0;
            vertical-align: top;
        }

        .letter-meta td:first-child {
            width: 100px;
        }

        .letter-meta td:nth-child(2) {
            width: 20px;
        }

        .greeting {
            margin-bottom: 12px;
            font-size: 10pt;
        }

        .content-balasan {
            margin-bottom: 12px;
            font-size: 10pt;
            line-height: 1.5;
            text-align: justify;
        }

        .student-table-balasan {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0;
            font-size: 9pt;
        }

        .student-table-balasan th,
        .student-table-balasan td {
            border: 1.5px solid #333;
            padding: 5px;
            text-align: center;
            vertical-align: middle;
        }

        .student-table-balasan th {
            background-color: #e8e8e8;
            font-weight: bold;
            font-size: 9pt;
        }

        .student-table-balasan thead tr:first-child th {
            padding: 5px;
        }

        .student-table-balasan tbody tr {
            min-height: 35px;
        }

        .student-table-balasan tbody tr:hover {
            background-color: #f9f9f9;
        }

        .student-table-balasan td input[type="text"] {
            width: 100%;
            border: none;
            padding: 4px;
            font-family: 'Times New Roman', Times, serif;
            font-size: 9pt;
            background: transparent;
            text-align: center;
        }

        .student-table-balasan td input[type="text"]:focus {
            outline: 2px solid #4dd0e1;
            background: #f0faff;
            border-radius: 3px;
        }

        .student-table-balasan tbody tr:nth-child(n+4) td {
            padding: 3px 2px;
            font-size: 8pt;
            height: 22px;
        }

        .student-table-balasan tbody tr:nth-child(n+4) td input[type="text"] {
            padding: 2px;
            font-size: 8pt;
        }

        .checkbox-cell {
            width: 70px;
            padding: 10px !important;
        }

        .checkbox-container {
            display: inline-block;
            width: 22px;
            height: 22px;
            border: 2.5px solid #333;
            cursor: pointer;
            position: relative;
            transition: all 0.2s;
            background: white;
        }

        .note {
            font-size: 9pt;
            margin: 12px 0;
            font-style: italic;
            color: #555;
        }

        .closing-balasan {
            margin-top: 12px;
            font-size: 10pt;
        }

        .signature-balasan {
            margin-top: 40px;
        }

        .signature-circle {
            width: 70px;
            height: 70px;
            border: 2px solid #333;
            border-radius: 50%;
            margin: 0 auto 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8pt;
            text-align: center;
            line-height: 1.2;
            background: #fafafa;
        }

        .signature-text {
            text-align: left;
            font-size: 10pt;
        }

        .signature-text div {
            margin: 2px 0;
        }

        .footer-note {
            margin-top: 20px;
            font-size: 9pt;
            font-style: italic;
            color: #555;
        }

        .footer-note strong {
            font-weight: bold;
            color: #000;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .action-buttons {
                display: none;
            }

            .container-permohonan,
            .container-balasan {
                box-shadow: none;
                margin: 0;
                page-break-after: always;
            }

            .container-balasan {
                page-break-after: auto;
            }
        }
    </style>
</head>

<body>
    <!-- SURAT PERMOHONAN MAGANG -->
    <div class="container-permohonan" id="content-permohonan">
        <div class="header-permohonan">
            <div class="logo-left">
                <img src="assets/img/polibatam.png" alt="Logo Polibatam">
            </div>
            <div class="header-text-permohonan">
                <h1>KEMENTERIAN PENDIDIKAN TINGGI,</h1>
                <h1>SAINS, DAN TEKNOLOGI</h1>
                <h2>POLITEKNIK NEGERI BATAM</h2>
                <p>Jalan Ahmad Yani, Batam Centre, Batam 29461, Kepulauan Riau, Batam 29461</p>
                <p>Telepon +62 778 469626 - 469660, Faksimile +62 778 463620</p>
                <p>Laman: www.polibatam.ac.id, Surel: info@polibatam.ac.id</p>
            </div>
            <div class="logo-right">
                <img src="assets/img/ukas.png" alt="Logo UKAS">
            </div>
        </div>
        <div class="header-border"></div>

        <div class="date">
            Batam, <?= htmlspecialchars($dateStr) ?>
        </div>

        <div class="letter-info">
            <table>
                <tr>
                    <td>Nomor</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($nomorFormatted) ?></td>
                </tr>
                <tr>
                    <td>Lampiran</td>
                    <td>:</td>
                    <td>1 (satu) berkas</td>
                </tr>
                <tr>
                    <td>Hal</td>
                    <td>:</td>
                    <td><strong><u>Permohonan Magang Industri</u></strong></td>
                </tr>
            </table>
        </div>

        <div class="recipient">
            <strong>Yth. HR Manager</strong><br>
            <strong><?= htmlspecialchars($company_name) ?></strong><br>
            <strong>di <?= htmlspecialchars($company_addr) ?></strong>
        </div>

        <div class="content-permohonan">
            <p>Dengan hormat kami sampaikan apresiasi yang sebesar-besarnya kepada <em><?= htmlspecialchars($company_name) ?></em>
                atas kerja samanya selama ini dalam hal kegiatan magang industri mahasiswa. Pelaksanaan magang
                memberikan kontribusi yang sangat penting untuk meningkatkan keterampilan kerja, menumbuhkan
                pengetahuan tentang lingkungan, proses dan budaya kerja di perusahaan/instansi. Pada akhirnya pelaksanaan
                magang akan membentuk kedisiplinan dan perilaku baik bagi mahasiswa yang krusial dalam penyiapan
                generasi muda yang kompeten dan siap kerja.</p>

            <p>Surat ini sebagai permohonan kami agar mahasiswa berikut ini dapat diterima untuk melaksanakan magang
                industri di perusahaan/instansi yang Bapak/Ibu pimpin:</p>

            <table class="student-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>NIM</th>
                        <th>Program Studi</th>
                        <th>Periode Magang</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td><?= htmlspecialchars($student_name) ?></td>
                        <td><?= htmlspecialchars($student_nim) ?></td>
                        <td><?= htmlspecialchars($program_study) ?></td>
                        <td><?= htmlspecialchars($periode) ?></td>
                    </tr>
                </tbody>
            </table>

            <p>Kami juga lampirkan Kompetensi dan Posisi yang dapat diisi oleh mahasiswa tersebut di atas pada link berikut
                <a href="https://polibatam.id/KompetensiMagangPolibatam" class="link">https://polibatam.id/KompetensiMagangPolibatam</a>
                serta Format Balasan Surat Permohonan Magang apabila mahasiswa tersebut diterima di perusahaan Bapak/Ibu.
            </p>

            <p>Konfirmasi penerimaan magang melalui surat balasan mohon dapat dikirim kembali melalui email
                <a href="mailto:cdc@polibatam.ac.id" class="link">cdc@polibatam.ac.id</a>
                paling lambat 10 hari kerja Politeknik Negeri Batam. Apabila tidak ada konfirmasi terkait hal tersebut
                hingga batas waktu, kami menganggap Perusahaan Bapak/Ibu belum bisa menerima mahasiswa magang tersebut saat ini.
            </p>

            <p>Jika perusahaan Bapak/Ibu memerlukan posisi magang selain yang kami usulkan, Bapak/Ibu mohon dapat
                mengisi formulir yang dapat diakses pada link berikut:
                <a href="https://polibatam.id/FormKebutuhanMagang" class="link">https://polibatam.id/FormKebutuhanMagang</a>.
            </p>
        </div>

        <div class="closing">
            <p>Demikian permohonan ini disampaikan, atas kerjasamanya diucapkan terima kasih.</p>
        </div>

        <div class="signature-section">
            <div class="tembusan-box">
                <div class="tembusan-title">Tembusan:</div>
                <div class="tembusan-content">Direktur Politeknik Negeri Batam</div>
            </div>

            <div class="signature-box">
                <div class="signature-title">Wakil Direktur III</div>
                <div class="signature-subtitle">Bidang Kemahasiswaan, Alumni dan Kerjasama,</div>
                <div class="signature-image">
                    <img src="assets/img/ttd.png" alt="Stempel">
                </div>
                <div class="signature-name">Dr. Muhammad Zarqani, S.Si., M.Sc.</div>
            </div>
        </div>
    </div>

    <!-- SURAT BALASAN MAGANG -->
    <div class="container-balasan" id="content-balasan">
        <div class="header-balasan">
            <div class="logo-balasan">
                <img src="assets/img/polibatam.png" alt="Logo Politeknik Negeri Batam">
            </div>
            <div class="header-text-balasan">
                <div class="header-title">No.FO.8.4.1.1-V0 Format Surat Balasan Permohonan Magang</div>
                <div class="header-date">23 Maret 2020</div>
            </div>
        </div>

        <div class="location">
            Batam, ..............................
        </div>

        <div class="letter-meta">
            <table>
                <tr>
                    <td>Nomor</td>
                    <td>:</td>
                    <td></td>
                </tr>
                <tr>
                    <td>Lampiran</td>
                    <td>:</td>
                    <td></td>
                </tr>
                <tr>
                    <td>Hal</td>
                    <td>:</td>
                    <td>Balasan Surat Permohonan Magang Industri</td>
                </tr>
            </table>
        </div>

        <div class="letter-meta" style="margin-top: 30px;">
            <div>Kepada Yth. Bapak/Ibu</div>
            <div>Kepala GDC Politeknik Negeri Batam</div>
        </div>

        <div class="greeting">
            Dengan Hormat,
        </div>

        <div class="content-balasan">
            Berdasarkan Surat Permohonan Magang Industri Nomor ................... yang telah diajukan kepada kami,
            bersama ini kami sampaikan bahwa mahasiswa berikut :
        </div>

        <table class="student-table-balasan">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 40px;">No.</th>
                    <th rowspan="2" style="width: 180px;">Nama</th>
                    <th rowspan="2" style="width: 100px;">NIM</th>
                    <th rowspan="2" style="width: 100px;">Program<br>Studi</th>
                    <th rowspan="2" style="width: 110px;">*Periode Magang</th>
                    <th colspan="2">*Keterangan</th>
                    <th rowspan="2" style="width: 130px;">Bagian<br>Penempatan</th>
                </tr>
                <tr>
                    <th class="checkbox-cell">Diterima</th>
                    <th class="checkbox-cell">Tidak<br>Diterima</th>
                </tr>
            </thead>
            <tbody id="studentTable">
                <tr>
                    <td>1.</td>
                    <td><input type="text"></td>
                    <td><input type="text"></td>
                    <td><input type="text"></td>
                    <td><input type="text"></td>
                    <td class="checkbox-cell">
                        <div class="checkbox-container" onclick="toggleCheckbox(this)"></div>
                    </td>
                    <td class="checkbox-cell">
                        <div class="checkbox-container" onclick="toggleCheckbox(this)"></div>
                    </td>
                    <td><input type="text"></td>
                </tr>
                <tr>
                    <td>2.</td>
                    <td><input type="text"></td>
                    <td><input type="text"></td>
                    <td><input type="text"></td>
                    <td><input type="text"></td>
                    <td class="checkbox-cell">
                        <div class="checkbox-container" onclick="toggleCheckbox(this)"></div>
                    </td>
                    <td class="checkbox-cell">
                        <div class="checkbox-container" onclick="toggleCheckbox(this)"></div>
                    </td>
                    <td><input type="text"></td>
                </tr>
                <tr>
                    <td>3.</td>
                    <td><input type="text"></td>
                    <td><input type="text"></td>
                    <td><input type="text"></td>
                    <td><input type="text"></td>
                    <td class="checkbox-cell">
                        <div class="checkbox-container"></div>
                    </td>
                    <td class="checkbox-cell">
                        <div class="checkbox-container"></div>
                    </td>
                    <td><input type="text"></td>
                </tr>
                <tr>
                    <td></td>
                    <td><input type="text"></td>
                    <td><input type="text"></td>
                    <td><input type="text"></td>
                    <td><input type="text"></td>
                    <td><input type="text"></td>
                    <td><input type="text"></td>
                    <td><input type="text"></td>
                </tr>
                <tr>
                    <td></td>
                    <td><input type="text"></td>
                    <td><input type="text"></td>
                    <td><input type="text"></td>
                    <td><input type="text"></td>
                    <td><input type="text"></td>
                    <td><input type="text"></td>
                    <td><input type="text"></td>
                </tr>
                <tr>
                    <td></td>
                    <td><input type="text"></td>
                    <td><input type="text"></td>
                    <td><input type="text"></td>
                    <td><input type="text"></td>
                    <td><input type="text"></td>
                    <td><input type="text"></td>
                    <td><input type="text"></td>
                </tr>
                <tr>
                    <td></td>
                    <td><input type="text"></td>
                    <td><input type="text"></td>
                    <td><input type="text"></td>
                    <td><input type="text"></td>
                    <td><input type="text"></td>
                    <td><input type="text"></td>
                    <td><input type="text"></td>
                </tr>
                <tr>
                    <td></td>
                    <td><input type="text"></td>
                    <td><input type="text"></td>
                    <td><input type="text"></td>
                    <td><input type="text"></td>
                    <td><input type="text"></td>
                    <td><input type="text"></td>
                    <td><input type="text"></td>
                </tr>
            </tbody>
        </table>

        <div class="note">
            *Bagian ini diisi oleh perusahaan
        </div>

        <div class="content-balasan">
            Demikian surat kami sampaikan, atas perhatian dan kerjasamanya kami ucapkan terima kasih.
        </div>

        <div class="closing-balasan">
            Hormat Kami,

            <div class="signature-balasan">
                <div class="signature-circle">
                    Cap<br>& Tanda<br>Tangan
                </div>
                <div class="signature-text">
                    <div>Nama</div>
                    <div>Jabatan</div>
                    <div>Nama Perusahaan</div>
                </div>
            </div>
        </div>

        <div class="footer-note">
            <strong>Note:</strong> Kop surat boleh diganti dengan Kop surat perusahaan
        </div>
    </div>

</body>

</html>
