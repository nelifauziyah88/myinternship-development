<?php
    
// Include Limbrary
require('fpdf.php');
include 'config.php';

class PDF extends FPDF
{
    // Page footer
    function Footer()
    {
        // Position at 1.5 cm from bottom
        $this->SetY(-25);
        // Arial italic 8
        $this->SetFont('Arial','B',8);
        
        // Page number
        $this->setFillColor(0, 0, 0);
        $this->Cell(170,0, '' , 0 , 0 , 'C' , 1);
        $this->setFillColor(0, 128, 0); $this->SetTextColor(255, 255, 255);
        $this->Cell(20 , 10 , $this->PageNo() , 0 , 0 , 'C' , 1);
        // $this->Cell(20,10,'Page '.$this->PageNo().'/{nb}',1,0,'L');
    }


    function WordWrap(&$text, $maxwidth)
{
    $text = trim($text);
    if ($text==='')
        return 0;
    $space = $this->GetStringWidth(' ');
    $lines = explode("\n", $text);
    $text = '';
    $count = 0;

    foreach ($lines as $line)
    {
        $words = preg_split('/ +/', $line);
        $width = 0;

        foreach ($words as $word)
        {
            $wordwidth = $this->GetStringWidth($word);
            if ($wordwidth > $maxwidth)
            {
                // Word is too long, we cut it
                for($i=0; $i<strlen($word); $i++)
                {
                    $wordwidth = $this->GetStringWidth(substr($word, $i, 1));
                    if($width + $wordwidth <= $maxwidth)
                    {
                        $width += $wordwidth;
                        $text .= substr($word, $i, 1);
                    }
                    else
                    {
                        $width = $wordwidth;
                        $text = rtrim($text)."\n".substr($word, $i, 1);
                        $count++;
                    }
                }
            }
            elseif($width + $wordwidth <= $maxwidth)
            {
                $width += $wordwidth + $space;
                $text .= $word.' ';
            }
            else
            {
                $width = $wordwidth + $space;
                $text = rtrim($text)."\n".$word.' ';
                $count++;
            }
        }
        $text = rtrim($text)."\n";
        $count++;
    }
    $text = rtrim($text);
    return $count;
}




}

// Mintak ID Permohonan

// Data Dari Asesi
    $id_permohonan = 'P0001';

    $id_asesi = $conn->query("SELECT `id_asesi` FROM `permohonan` WHERE `id_permohonan` = '$id_permohonan'");
    $data       = mysqli_fetch_assoc($id_asesi); 
    $id_asesi = $data['id_asesi'];


    $email_asesi = $conn->query("SELECT `email` FROM `asesi` WHERE `id_asesi` = '$id_asesi'");
    $data       = mysqli_fetch_assoc($email_asesi); 
    $email_asesi = $data['email'];


    $asesi = $conn->query("SELECT `nama` , `no_hp` FROM `akun` WHERE `email` = '$email_asesi'");
    $data       = mysqli_fetch_assoc($asesi); 
    $nama_asesi = $data['nama']; $no_hp = $data['no_hp'];


    $data_asesi = $conn->query("SELECT * FROM `asesi` WHERE `id_asesi` = '$id_asesi'");
    $data       = mysqli_fetch_assoc($data_asesi);


    // Data Pribadi
    $no_nik = $data['no_nik']; $tmpt_lahir = $data['tmpt_lahir']; 

    $tgl = new DateTime( $data['tgl_lahir'] );

    $tgl_lahir = $tgl->format('d-m-Y');




    $jenkel = $data['jenkel']; $kebangsaan = $data['kebangsaan']; $alamat_rmh = $data['alamat_rmh'];
    $kodepos = $data['kodepos']; $notelp_rmh = $data['notelp_rmh']; $pendidikan = $data['pendidikan'];
    $telppribadi_perusahaan = $data['telppribadi_perusahaan'];

    // Data Perusahaan
    $nama_perusahaan = $data['nama_perusahaan']; $jabatan = $data['jabatan']; $email_perusahaan = $data['email_perusahaan'];
    $telp_perusahaan = $data['telp_perusahaan']; $fax_perusahaan = $data['fax_perusahaan']; $alamat_perusahaan = $data['alamat_perusahaan'];
    $kodepos_perusahaan = $data['kodepos_perusahaan'];

// End



    // Membuat objek $pdf

    $pdf = new FPDF('P','mm','A4');
    // ("Orientasi Kertas" , "Satuan Pengukuran (mm / inc)" , "Ukuran Kertas")

    $pdf = new PDF();
    $pdf->AliasNbPages();

    // Deklarasi Tambah Halaman
    $pdf->AddPage();

    $border = 0; // Debug, border = 1 , no debug = 0


    /* Buat Cell **/
        // Set Ukuran Font
        $pdf->SetFont('Arial','B',16);

        // Membuat Cel dengan ukuran 40 x 10
        $pdf->Cell(190,10,'FR.APL.01. FORMULIR PERMOHONAN SERTIFIKASI KOMPETENSI' , $border , 1 , 'L');

        $pdf->Ln(3);

        // Max Lebar Cell = 190

        // Cell( "Lebar" , "Tinggi" "Isi Teks" , border (O = N , 1 = Y) , Baris Baru ( 0 = Stack new cell , 1 = Baris Baru new cell ) , "Text Allign");


    $pdf->SetFont('Arial', 'B' ,11);

    // Bagian 1
    $pdf->Cell(190,5,'Bagian 1 : Rincian Data Pemohon Sertifikasi' , $border , 1 , 'L');

    $pdf->SetFont('Arial', '' ,10);
    $pdf->Cell(190,5,'Pada bagian ini, cantumkan data pribadi, data pendidikan formal serta data pekerjaan anda pada saat ini.' , $border , 1 , 'L');


    $pdf->Ln(5); // Line Break


    // Bagian Data Pribadi 
    $pdf->Cell(5,5,'a.' , $border , 0 , 'C');

    $pdf->Cell(155,5,'Data Pribadi' , $border , 1 , 'L');


    // Nama
    $pdf->Cell(5,5,'' , $border , 0 , 'L');

    $pdf->Cell(50,5,'Nama' , $border , 0 , 'L');

    $pdf->Cell(5,5,':' , $border , 0 , 'L');

    $pdf->Cell(130,5, $nama_asesi , $border , 1 , 'L'); // Nama Asesi


    // No KTP
    $pdf->Cell(5,5,'' , $border , 0 , 'L');

    $pdf->Cell(50,5,'No. KTP/ NIK/PASSPORT' , $border , 0 , 'L');

    $pdf->Cell(5,5,':' , $border , 0 , 'L');

    $pdf->Cell(130,5, $no_nik , $border , 1 , 'L'); // No NIK


    // TTL
    $pdf->Cell(5,5,'' , $border , 0 , 'L');

    $pdf->Cell(50,5,'Tempat/ Tanggal Lahir' , $border , 0 , 'L');

    $pdf->Cell(5,5,':' , $border , 0 , 'L');

    $pdf->Cell(130,5, $tmpt_lahir. '/' .$tgl_lahir , $border , 1 , 'L');


    // Jenkel
    $pdf->Cell(5,5,'' , $border , 0 , 'L');

    $pdf->Cell(50,5,'Jenis Kelamin' , $border , 0 , 'L');

    $pdf->Cell(5,5,':' , $border , 0 , 'L');

    $pdf->Cell(45,5,'Laki - Laki' , $border , 0 , 'C');

    $pdf->Cell(10,5,'/' , $border , 0 , 'C');

    $pdf->Cell(40,5,'Perempuan' , $border , 0 , 'C');

    $pdf->Cell(15,5,'*' , $border , 1 , 'L');



    // Kebangsaan
    $pdf->Cell(5,5,'' , $border , 0 , 'L');

    $pdf->Cell(50,5,'Kebangsaan' , $border , 0 , 'L');

    $pdf->Cell(5,5,':' , $border , 0 , 'L');

    $pdf->Cell(130,5, $kebangsaan , $border , 1 , 'L');



    // Alamat - Jalan
    $pdf->Cell(5,5,'' , $border , 0 , 'L');

    $pdf->Cell(50,5,'Alamat Rumah' , $border , 0 , 'L');

    $pdf->Cell(5,5,':' , $border , 0 , 'L');

    $pdf->Cell(130,5, $alamat_rmh , $border , 1 , 'L');

    // Kalau alamat nya kepanjangan , buat baris baru

    // Alamat - Jalan
    $pdf->Cell(5,5,'' , $border , 0 , 'L');

    $pdf->Cell(50,5,'' , $border , 0 , 'L');

    $pdf->Cell(5,5,'' , $border , 0 , 'L');

    $pdf->Cell(130,5,'' , $border , 1 , 'L');



    // Alamat - Kode Pos
    $pdf->Cell(5,5,'' , $border , 0 , 'L');

    $pdf->Cell(50,5,'' , $border , 0 , 'L');

    $pdf->Cell(5,5,'' , $border , 0 , 'L');

    $pdf->Cell(20,5,'Kode Pos' , $border , 0 , 'L');

    $pdf->Cell(5,5,':' , $border , 0 , 'L');

    $pdf->Cell(105,5, $kodepos , $border , 1 , 'L');


    // Phone / E - Mail

    // 50
    $pdf->Cell(5,5,'' , $border , 0 , 'L');

    $pdf->Cell(50,5,'Phone /E-mail' , $border , 0 , 'L');

    $pdf->Cell(5,5,':' , $border , 0 , 'L');

    // 110
    $pdf->Cell(20,5, 'Rumah' , $border , 0 , 'L');

    $pdf->Cell(5,5, ': ' , $border , 0 , 'L');

    $pdf->Cell(30,5, $notelp_rmh , $border , 0 , 'L'); // No Rumah Pribadi

    $pdf->Cell(20,5, 'Kantor' , $border , 0 , 'L');

    $pdf->Cell(5,5, ': ' , $border , 0 , 'L');

    $pdf->Cell(50,5, $telppribadi_perusahaan , $border , 1 , 'L'); // No Kantor Pribadi


    // 50
    $pdf->Cell(5,5,'' , $border , 0 , 'L');

    $pdf->Cell(50,5,'' , $border , 0 , 'L');

    $pdf->Cell(5,5,'' , $border , 0 , 'L');

    // 110
    $pdf->Cell(20,5, 'HP' , $border , 0 , 'L');

    $pdf->Cell(5,5, ': ' , $border , 0 , 'L');

    $pdf->SetFont('Arial', '' ,9);

    $pdf->Cell(30,5, $no_hp , $border , 0 , 'L'); // No HP Pribadi

    $pdf->SetFont('Arial', '' ,11);

    $pdf->Cell(20,5, 'E-Mail ' , $border , 0 , 'L');

    $pdf->Cell(5,5, ': ' , $border , 0 , 'L');

    $pdf->SetFont('Arial', '' ,9);

    $pdf->Cell(50,5, $email_asesi , $border , 1 , 'L');



    $pdf->Ln(5); // Line Break
    $pdf->SetFont('Arial', '' ,11);


    // Bagian Data Pekerjaan Sekarang 
    $pdf->Cell(5,5,'b.' , $border , 0 , 'C');

    $pdf->Cell(155,5,'Data Pekerjaan Sekarang' , $border , 1 , 'L');


    // Perusahaan/Lembaga
    $pdf->Cell(5,5,'' , $border , 0 , 'L');

    $pdf->Cell(50,5,'Perusahaan/Lembaga' , $border , 0 , 'L');

    $pdf->Cell(5,5,':' , $border , 0 , 'L');

    $pdf->Cell(130,5, $nama_perusahaan , $border , 1 , 'L'); // Nama Perusahaan


    // Jabatan
    $pdf->Cell(5,5,'' , $border , 0 , 'L');

    $pdf->Cell(50,5,'Jabatan' , $border , 0 , 'L');

    $pdf->Cell(5,5,':' , $border , 0 , 'L');

    $pdf->Cell(130,5, $jabatan , $border , 1 , 'L'); // Jabatan


    // Alamat Kantor - Jalan
    $pdf->Cell(5,5,'' , $border , 0 , 'L');

    $pdf->Cell(50,5,'Alamat Kantor' , $border , 0 , 'L');

    $pdf->Cell(5,5,':' , $border , 0 , 'L');

    $pdf->Cell(130,5, $alamat_perusahaan , $border , 1 , 'L'); // Alamat Kantor

    // Kalau alamat nya kepanjangan , buat baris baru

    // Alamat - Jalan
    $pdf->Cell(5,5,'' , $border , 0 , 'L');

    $pdf->Cell(50,5,'' , $border , 0 , 'L');

    $pdf->Cell(5,5,'' , $border , 0 , 'L');

    $pdf->Cell(130,5,'' , $border , 1 , 'L');



    // Alamat - Kode Pos
    $pdf->Cell(5,5,'' , $border , 0 , 'L');

    $pdf->Cell(50,5,'' , $border , 0 , 'L');

    $pdf->Cell(5,5,'' , $border , 0 , 'L');

    $pdf->Cell(20,5,'Kode Pos' , $border , 0 , 'L');

    $pdf->Cell(5,5,':' , $border , 0 , 'L');

    $pdf->Cell(105,5, $kodepos_perusahaan , $border , 1 , 'L');


    // Phone / E - Mail

    // 50
    $pdf->Cell(5,5,'' , $border , 0 , 'L');

    $pdf->Cell(50,5,'No. Telp/Fax/E-mail' , $border , 0 , 'L');

    $pdf->Cell(5,5,':' , $border , 0 , 'L');

    // 110
    $pdf->Cell(20,5, 'Telp' , $border , 0 , 'L');

    $pdf->Cell(5,5, ': ' , $border , 0 , 'L');

    $pdf->Cell(40,5, $telp_perusahaan , $border , 0 , 'L'); // No Telp Perusahaan

    $pdf->Cell(20,5, 'Fax' , $border , 0 , 'L');

    $pdf->Cell(5,5, ': ' , $border , 0 , 'L');

    $pdf->Cell(40,5, $fax_perusahaan , $border , 1 , 'L'); // Fax Perusahaan


    // 50
    $pdf->Cell(5,5,'' , $border , 0 , 'L');

    $pdf->Cell(50,5,'' , $border , 0 , 'L');

    $pdf->Cell(5,5,'' , $border , 0 , 'L');

    // 110
    $pdf->Cell(20,5, 'E-mail' , $border , 0 , 'L');

    $pdf->Cell(5,5, ': ' , $border , 0 , 'L');

    $pdf->Cell(105,5, $email_perusahaan , $border , 1 , 'L'); // E Mail Perusahaan


    $pdf->Ln(5); // Line Break
    $pdf->SetFont('Arial', 'B' ,11);

    // Bagian 1
    $pdf->Cell(190,5,'Bagian 2 : Data Sertifikasi' , $border , 1 , 'L');

    $pdf->SetFont('Arial', '' ,10);

    $pdf->Cell(190,5, 'Tuliskan Judul dan Nomor Skema Sertifikasi, Daftar Unit Kompetensi sesuai kemasan pada skema sertifikasi yang' , $border , 1 , 'L');

    $pdf->Cell(190,5, 'Anda ajukan untuk mendapatkan pengakuan sesuai dengan latarbelakang pendidikan, pelatihan serta pengalaman' , $border , 1 , 'L');
    $pdf->Cell(190,5, 'kerja yang Anda miliki.' , $border , 1 , 'L');


    $pdf->Ln(5); // Line Break
    $pdf->SetFont('Arial', '' ,11);


    // Nama Skema
    $pdf->Cell(5,5,'' , $border , 0 , 'L');

    $pdf->Cell(50,5,'Judul Skema' , $border , 0 , 'L');

    $pdf->Cell(5,5,':' , $border , 0 , 'L');

    $pdf->Cell(130,5,'Junior Web Programming' , $border , 1 , 'L');


    // Nomor Skema
    $pdf->Cell(5,5,'' , $border , 0 , 'L');

    $pdf->Cell(50,5,'Nomor Skema' , $border , 0 , 'L');

    $pdf->Cell(5,5,':' , $border , 0 , 'L');

    $pdf->Cell(130,5,'SKM001/2020' , $border , 1 , 'L');


    // Tujuan Asesmen
    $pdf->Cell(5,5,'' , $border , 0 , 'L');

    $pdf->Cell(50,5,'Tujuan Asesmen' , $border , 0 , 'L');

    $pdf->Cell(5,5,':' , $border , 0 , 'L');

    $pdf->Cell(130,5,'Sertifikasi' , $border , 1 , 'L');



    $pdf->Ln(5);
    $pdf->SetFont('Arial','B',11);
    // Tabel Unit Skema
    // Header
    $pdf->setFillColor(251, 212, 180); 
    $pdf->Cell(10,12,'No.' , 1 , 0 , 'C' , 1);

    $pdf->Cell(50,12,'Kode Unit' , 1 , 0 , 'C' , 1);

    $pdf->Cell(90,12,'Judul Unit' , 1 , 0 , 'C' , 1);

    $pdf->Cell(40,12,'Jenis Standar' , 1 , 1 , 'C' , 1);


    $pdf->SetFont('Arial','',11);

    $pdf->Cell(10,12,'1.' , 1 , 0 , 'C' , 0);

    $pdf->Cell(50,12,'Kode Unit' , 1 , 0 , 'C' , 0);

    $pdf->Cell(90,12,'Judul Unit' , 1 , 0 , 'C' , 0);

    $pdf->Cell(40,12,'Jenis Standar' , 1 , 1 , 'C' , 0);



    $pdf->Cell(10,12,'2.' , 1 , 0 , 'C' , 0);

    $pdf->Cell(50,12,'Kode Unit' , 1 , 0 , 'C' , 0);

    $pdf->Cell(90,12,'Judul Unit' , 1 , 0 , 'C' , 0);

    $pdf->Cell(40,12,'Jenis Standar' , 1 , 1 , 'C' , 0);


    $pdf->Cell(10,12,'3.' , 1 , 0 , 'C' , 0);

    $pdf->Cell(50,12,'Kode Unit' , 1 , 0 , 'C' , 0);

    $pdf->Cell(90,12,'Judul Unit' , 1 , 0 , 'C' , 0);

    $pdf->Cell(40,12,'Jenis Standar' , 1 , 1 , 'C' , 0);


    $pdf->Cell(10,12,'4.' , 1 , 0 , 'C' , 0);

    $pdf->Cell(50,12,'Kode Unit' , 1 , 0 , 'C' , 0);

    $pdf->Cell(90,12,'Judul Unit' , 1 , 0 , 'C' , 0);

    $pdf->Cell(40,12,'Jenis Standar' , 1 , 1 , 'C' , 0);


    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B' ,11);

    // Bagian 1
    $pdf->Cell(190,5,'Bagian 3 : Bukti Kelengkapan Pemohon' , $border , 1 , 'L');

    $pdf->SetFont('Arial', 'B' ,10);
    
    // Bagian Data Pribadi 
    $pdf->Cell(5,5,'a.' , $border , 0 , 'C');

    $pdf->Cell(185,5,'Bukti kelengkapan persyaratan dasar pemohon :' , $border , 1 , 'L');

    $pdf->Ln(10); // Line Break
    $pdf->SetFont('Arial', 'B' ,11);


    // Tabel Unit Skema
    // Header
    $pdf->setFillColor(251, 212, 180); 
    $pdf->Cell(10,20,'No.' , 1 , 0 , 'C' , 1);

    $pdf->Cell(90,20,'Persyaratan Dasar' , 1 , 0 , 'C' , 1); 
    $x = $pdf->GetX();
    $pdf->Cell(50,10,'Ada' , 1 , 0 , 'C' , 1);
    $pdf->Cell(40,10,'Tidak Ada' , 1 , 1 , 'C' , 1);
    $pdf->SetX($x);
    $pdf->Cell(25,10,'Memenuhi' , 1 , 0 , 'C' , 1);
    $pdf->Cell(25,10,'Tidak' , 1 , 0 , 'C' , 1);
    $pdf->Cell(40,10,'Tidak Ada' , 1 , 1 , 'C' , 1);     

    $pdf->SetFont('Arial', '' ,11);

    $pdf->Cell(10,20,'1.' , 1 , 0 , 'C' , 0);
    $pdf->Cell(90,20,'Ini syarat Dasar' , 1 , 0 , 'C' , 0);
    $pdf->Cell(25,20,'Judul Unit' , 1 , 0 , 'C' , 0);
    $pdf->Cell(25,20,'Judul Unit' , 1 , 0 , 'C' , 0);
    $pdf->Cell(40,20,'Jenis Standar' , 1 , 1 , 'C' , 0);


    $pdf->Cell(10,20,'2.' , 1 , 0 , 'C' , 0);
    $pdf->Cell(90,20,'Ini syarat Dasar' , 1 , 0 , 'C' , 0);
    $pdf->Cell(25,20,'Judul Unit' , 1 , 0 , 'C' , 0);
    $pdf->Cell(25,20,'Judul Unit' , 1 , 0 , 'C' , 0);
    $pdf->Cell(40,20,'Jenis Standar' , 1 , 1 , 'C' , 0);


    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B' ,11);

    // Bagian Data Pribadi 
    $pdf->Cell(5,5,'b.' , $border , 0 , 'C');

    $pdf->Cell(185,5,'Bukti kompetensi yang relevan :' , $border , 1 , 'L');

    $pdf->Ln(5); // Line Break
    $pdf->SetFont('Arial', 'B' ,11);

    // Tabel Unit Skema
    // Header
    $pdf->setFillColor(198, 217, 241); 
    $pdf->Cell(10,20,'No.' , 1 , 0 , 'C' , 1);

    $pdf->Cell(140,20,'Rincian Bukti Pendidikan/Pelatihan, PengalamanKerja, Pengalaman Hidup' , 1 , 0 , 'C' , 1); 
    $x = $pdf->GetX();
    $pdf->Cell(40,10,'Lampiran Bukti*' , 1 , 1 , 'C' , 1);
    $pdf->SetX($x);
    $pdf->Cell(20,10,'Ada' , 1 , 0 , 'C' , 1);
    $pdf->Cell(20,10,'Tidak Ada' , 1 , 1 , 'C' , 1);

    $pdf->SetFont('Arial', '' ,11);
    $pdf->Cell(10,20,'1.' , 1 , 0 , 'C' , 0);
    $pdf->Cell(140,20,'Ini syarat Dasar' , 1 , 0 , 'C' , 0);
    $pdf->Cell(20,20,'v' , 1 , 0 , 'C' , 0);
    $pdf->Cell(20,20,'' , 1 , 1 , 'C' , 0);



    $pdf->Cell(10,20,'2.' , 1 , 0 , 'C' , 0);
    $pdf->Cell(140,20,'Ini syarat Dasar' , 1 , 0 , 'C' , 0);
    $pdf->Cell(20,20,'v' , 1 , 0 , 'C' , 0);
    $pdf->Cell(20,20,'' , 1 , 1 , 'C' , 0);


    $pdf->Ln(10); $pdf->SetFont('Arial', '' ,11);


    // $pdf->Cell(95,40, ' ' , 1, 0 , '' );$pdf->Cell(95,8, 'Tanda Tangan' , 1, 1  );
    // $y = $pdf->GetX();    
    // $pdf->SetX($y);
    // $pdf->Cell(95,8, 'Tanda Tangan' , 1, 1  );

    $pdf->setFillColor(251, 212, 180); 
    $pdf->Cell(95,7, 'Rekomendasi (oleh LSP ):' , 'L,T,R' , 0 , 'L'  , 0 );

    $pdf->Cell(95,7, 'Pemohon :' , 1, 1 , 'L' , 1  );

    $pdf->Cell(95,7, 'Berdasarkan persyaratan dasar pemohon, kandidat ' , 'L,R' , 0 , 'L'  , 0 );


    $pdf->Cell(30,7, 'Nama' , 1, 0 , 'L' , 1  );
    $pdf->Cell(65,7, '' , 1, 1 , 'L' ,   );

    $pdf->Cell(95,7, 'dapat:' , 'L,R' , 0 , 'L'  , 0 );

    $pdf->Cell(30,7, 'Tanggal' , 1, 0 , 'L' , 1  );
    $pdf->Cell(65,7, '' , 1, 1 , 'L' ,   );

    $pdf->Cell(17,7, 'Diterima' , 'L' , 0 , 'L'  , 0 );
    $pdf->Cell(3,7, '/' , '' , 0 , 'C'  , 0 );
    $pdf->Cell(25,7, 'tidak diterima' , '' , 0 , 'L'  , 0 );
    $pdf->Cell(50,7, 'sebagai asesi.' , 'R' , 0 , 'L'  , 0 );



    $pdf->Cell(30,15, 'Tanda Tangan' , 1, 0 , 'L' , 1  );
    $pdf->Cell(65,15, '' , 1, 0 , 'L' ,   );
    $pdf->Cell(0,7, '' , 0 , 1 , 'L'  , 0 ); //Hidden

    $pdf->Cell(95,8, '' , 'R,L,B' , 0 , 'L'  , 0 );
    
    $pdf->Cell(95,8, '' , 0 , 1 , 'L'  , 0 ); // Hidden

    $pdf->Cell(95,7, '' , 'L,T,R' , 0 , 'L'  , 0 );

    $pdf->Cell(95,7, 'Administrasi :' , 1, 1 , 'L' , 1  );

    $pdf->Cell(95,7, 'Catatan :' , 'L,R' , 0 , 'L'  , 0 );


    $pdf->Cell(30,7, 'Nama' , 1, 0 , 'L' , 1  );
    $pdf->Cell(65,7, '' , 1, 1 , 'L' ,   );

    $pdf->Cell(95,7, '' , 'L,R' , 0 , 'L'  , 0 );

    $pdf->Cell(30,7, 'Tanggal' , 1, 0 , 'L' , 1  );
    $pdf->Cell(65,7, '' , 1, 1 , 'L' ,   );


    $pdf->Cell(95,7, '' , 'R,L' , 0 , 'L'  , 0 );



    $pdf->Cell(30,15, 'Tanda Tangan' , 1, 0 , 'L' , 1  );
    $pdf->Cell(65,15, '' , 1, 0 , 'L' ,   );
    $pdf->Cell(0,7, '' , 0 , 1 , 'L'  , 0 ); //Hidden

    $pdf->Cell(95,8, '' , 'R,L,B' , 0 , 'L'  , 0 );
    
    $pdf->Cell(95,8, '' , 0 , 1 , 'L'  , 0 ); // Hidden


    // Footer
    $pdf->AliasNbPages();




// Backup Tabel konfirmasi Lama
    //     // Tabel Konfirmasi
    //     $y = $pdf->GetX();

    //     $pdf->Cell(95,39, 'Rekomendasi (oleh LSP ):' , 1, 0 , 'L' , 0  );

    //     $y = $pdf->GetX();
    //     $pdf->setFillColor(251, 212, 180); 
    //     $pdf->Cell(95,8, 'Pemohon :' , 1, 1 , 'L' , 1  );

    //     $pdf->SetX($y); 

    //     $y = $pdf->GetX();
    //     $pdf->Cell(30,8, 'Nama' , 1, 0 , 'L' , 1  );
    //     $pdf->Cell(65,8, '' , 1, 1 , 'L' ,   );
    //     $pdf->SetX($y); 


    //     $y = $pdf->GetX();
    //     $pdf->Cell(30,8, 'Tanggal' , 1, 0 , 'L' , 1  );
    //     $pdf->Cell(65,8, '' , 1, 1 , 'L' ,   );
    //     $pdf->SetX($y);

    //     $y = $pdf->GetX();
    //     $pdf->Cell(30,15, 'Tanda Tangan' , 1, 0 , 'L' , 1  );
    //     $pdf->Cell(65,15, '' , 1, 1 , 'L' ,   );
    //     $pdf->SetX($y);



    // $pdf->SetX($y);

    // $y = $pdf->GetY();
    // $pdf->SetY($y);
    
    // // $pdf->SetFont('Arial','',12);
    // // $texta = 'Inilah yang akan terjadi apabila makan bertemu dengan minuman dan jatuh cinta, mereka akan menghasilkan buah hati yakni penyakit' ;
    // // $text=str_repeat('this is a word wrap test ',20);
    // // $nb=$pdf->WordWrap($text,120);


    // $y = $pdf->GetX();

    //     $pdf->Cell(95,39, 'Catatan : ' , 1, 0 , 'L' , 0  );



    //     $y = $pdf->GetX();
    //     $pdf->setFillColor(251, 212, 180); 
    //     $pdf->Cell(95,8, 'Administrasi :' , 1, 1 , 'L' , 1  );

    //     $pdf->SetX($y); 

    //     $y = $pdf->GetX();
    //     $pdf->Cell(30,8, 'Nama' , 1, 0 , 'L' , 1  );
    //     $pdf->Cell(65,8, '' , 1, 1 , 'L' ,   );
    //     $pdf->SetX($y);

    //     $y = $pdf->GetX();
    //     $pdf->Cell(30,8, 'Tanggal' , 1, 0 , 'L' , 1  );
    //     $pdf->Cell(65,8, '' , 1, 1 , 'L' ,   );
    //     $pdf->SetX($y);

    //     $y = $pdf->GetX();
    //     $pdf->Cell(30,15, 'Tanda Tangan' , 1, 0 , 'L' , 1  );
    //     $pdf->Cell(65,15, '' , 1, 1 , 'L' ,   );
    //     $pdf->SetX($y);

    // $pdf->SetX($y);



    // // Sebelah Kanan






    // $pdf->Ln(10);

    // $pdf->Cell(40,18,'Words Here', 1,0, 'C');
    // $x = $pdf->GetX();
    // $pdf->Cell(40,9,'Ada', 1,0);
    // $pdf->Cell(40,9,'Tidak Ada', 1,1);
    // $pdf->SetX($x);
    // $pdf->Cell(20,9,'Ada P', 1,0);
    // $pdf->Cell(20,9,'Ada TP', 1,0);
    // $pdf->Cell(40,9,'Tidak Ada', 1,1);
    // $pdf->SetX($x);



    // Tampilkan Hasil
    $pdf->Output();

    $dir = "./tes/";

    $filename = 'tes.pdf';
    $pdf->Output($dir.$filename,  'F'); // save into some other location
?>