<?php
session_start();

if (!isset($_SESSION['student']) || empty($_SESSION['student']['nim'])) {
    header('Location: role_login.php');
    exit;
}

$student = $_SESSION['student'];
$user = $student;

$id_kampus = $user['id_kampus'] ?? null;
$nama_kampus = "Unknown";

if ($id_kampus) {
    // URL ke backend Express
    $api_url = "http://localhost:8000/api/kampus/" . urlencode($id_kampus);

    // Ambil data dari backend
    $response = @file_get_contents($api_url);

    if ($response !== false) {
        $data = json_decode($response, true);

        // Periksa apakah format API sesuai
        if (json_last_error() === JSON_ERROR_NONE && isset($data['nama_kampus'])) {
            $nama_kampus = $data['nama_kampus'];
        } else {
            $nama_kampus = "API data format is not compatible";
        }
    } else {
        $nama_kampus = "Unknown (API inaccessible)";
    }
} else {
    $nama_kampus = "Unknown (Campus ID not available)";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Meta for Compatibility -->
    <meta charset="utf-8">
    <title>Dashboard Student</title>
    <meta content='width=device-width, initial-scale=1.0, shrink-to-fit=no' name='viewport' />
    <!-- Icon -->
    <link rel="icon" href="./assets/img/iconM.png" type="image/x-icon" />
    <link href="./assets/img/iconM.png" rel="apple-touch-icon" type="image/x-icon">

    <link rel='stylesheet' href='./core/component/sweetalert2.min.css'>

    <!-- CSS Files -->
    <link rel="stylesheet" href="./assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="./assets/css/atlantis.css">

    <!-- Fonts and icons -->
    <script src="./assets/js/plugin/webfont/webfont.min.js"></script>
    <script>
        WebFont.load({
            google: {
                "families": ["Lato:300,400,700,900"]
            },
            custom: {
                "families": ["Flaticon", "Font Awesome 5 Solid", "Font Awesome 5 Regular", "Font Awesome 5 Brands", "simple-line-icons"],
                urls: ['./assets/css/fonts.min.css']
            },
            active: function() {
                sessionStorage.fonts = true;
            }
        });
    </script>

    <!-- CKEDITOR -->
    <script src="./library/ckeditor/ckeditor.js"></script>

    <script src='./core/component/jquery.min.js'></script>
    <script defer src='./core/component/sweetalert2.min.js'></script>
    <script defer src='./core/component/soloalert.js'></script>

    <style type="text/css">
        /* Posisi relatif untuk ikon agar badge bisa ditempatkan relatif terhadapnya */
        .notification-icon {
            position: relative;
            /* Sesuaikan ukuran ikon jika diperlukan */
        }

        /* Badge notifikasi kecil hijau */
        .custom-notification-badge {
            position: absolute;
            top: -8px;
            /* Sesuaikan posisi badge secara vertikal */
            right: -8px;
            /* Sesuaikan posisi badge secara horizontal */
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            /* Ukuran badge */
            font-size: 10px;
            /* Ukuran angka */
            line-height: 1;
            min-width: 16px;
            /* Pastikan ukuran minimal badge */
            text-align: center;
            /* Pusatkan angka di dalam badge */
        }

        .fc-sun {
            color: red;
            /* Mengubah warna font menjadi merah pada hari Minggu */
        }

        .disabled2 {
            pointer-events: none;
        }

        .not-avail {
            text-decoration: line-through;
            pointer-events: none;
            color: #808080;
        }

        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            -webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3);
            box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3);
            background: #fff;
        }

        ::-webkit-scrollbar-thumb {
            background: #6c757d;
        }

        .wrap {
            white-space: normal !important;
            word-wrap: break-word;
            min-width: 140px;
            max-width: 140px;
            /* max-width:150px; */
        }

        .wrap2 {
            white-space: normal !important;
            word-wrap: break-word;
            min-width: 170px;
            max-width: 170px;
            /* max-width:150px; */
        }

        .main-panel {
            padding-top: 50px;
        }

        .sidebar a.active {
            background-color: #007bff;
            /* warna biru */
            color: white !important;
            border-radius: 10px;
        }

        .sidebar a.active i {
            color: white;
        }

        /* ========== REFINED CSS - Ganti CSS Chart yang Lama ========== */

        /* Filter Section - Lebih rapi */
        .filter-section {
            background: white;
            padding: 20px 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .department-nav {
            gap: 12px;
        }

        .nav-btn {
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            border: none;
            display: none;
        }

        .nav-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(21, 114, 232, 0.3);
        }

        .nav-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
            transform: none;
        }

        .current-dept {
            min-width: 320px;
            text-align: center;
            font-size: 17px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        .year-filter {
            padding: 10px 18px;
            border: 2px solid #1572e8;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }

        .year-filter:hover {
            background: #f8f9fa;
            border-color: #0d5bb8;
        }

        .year-filter:focus {
            outline: none;
            box-shadow: 0 0 0 4px rgba(21, 114, 232, 0.15);
            border-color: #0d5bb8;
        }

        /* Chart Cards - Lebih konsisten */
        .chart-card {
            height: 600px;
            overflow-y: auto;
            overflow-x: hidden;
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            position: relative;
        }

        .chart-card h5 {
            margin-bottom: 20px;
            color: #2c3e50;
            font-size: 16px;
            font-weight: 700;
        }

        .chart-card canvas {
            width: 100% !important;
        }

        /* Pie Card - Better spacing */
        .pie-card {
            height: 600px;
            overflow-y: auto;
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
        }

        .pie-card h5 {
            margin-bottom: 20px;
            color: #2c3e50;
            font-size: 16px;
            font-weight: 700;
        }

        /* Pie Items - Lebih clean */
        .pie-item {
            margin-bottom: 18px;
            padding: 18px;
            background: #f8f9fa;
            border-radius: 10px;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .pie-item:hover {
            background: #f1f3f5;
            border-color: #dee2e6;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .pie-item canvas {
            width: 100% !important;
            height: auto !important;
            max-height: 180px !important;
            margin-bottom: 12px;
        }

        .pie-item .text-center {
            font-size: 13px;
            font-weight: 600;
            color: #495057;
            margin-top: 10px;
            line-height: 1.4;
        }

        /* Container Pie - Smooth scroll */
        #pieContainer {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding-right: 5px;
        }

        #pieContainer::-webkit-scrollbar {
            width: 6px;
        }

        #pieContainer::-webkit-scrollbar-track {
            background: #f1f3f5;
            border-radius: 10px;
        }

        #pieContainer::-webkit-scrollbar-thumb {
            background: #adb5bd;
            border-radius: 10px;
        }

        #pieContainer::-webkit-scrollbar-thumb:hover {
            background: #868e96;
        }

        /* Legend Box - Tetap di bawah */
        .legend-box {
            margin-top: 15px;
            padding: 12px 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
            flex-shrink: 0;
            gap: 25px;
        }

        .legend-box .d-flex {
            align-items: center;
            gap: 8px;
        }

        .legend-box span {
            font-size: 13px;
            font-weight: 600;
        }

        .legend-box .rounded {
            border: 2px solid white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        /* Scrollbar untuk Chart Card */
        .chart-card::-webkit-scrollbar {
            width: 6px;
        }

        .chart-card::-webkit-scrollbar-track {
            background: #f1f3f5;
            border-radius: 10px;
        }

        .chart-card::-webkit-scrollbar-thumb {
            background: #adb5bd;
            border-radius: 10px;
        }

        .chart-card::-webkit-scrollbar-thumb:hover {
            background: #868e96;
        }

        /* Responsive Design */
        @media (max-width: 991px) {

            .chart-card,
            .pie-card {
                height: 500px;
            }
        }

        @media (max-width: 768px) {
            .filter-section {
                flex-direction: column;
                gap: 15px;
                padding: 18px;
            }

            .department-nav {
                width: 100%;
                justify-content: center;
                flex-wrap: wrap;
            }

            .current-dept {
                min-width: auto;
                width: 100%;
                font-size: 15px;
                padding: 10px 15px;
            }

            .nav-btn {
                padding: 8px 20px;
                font-size: 13px;
            }

            .year-filter {
                width: 100%;
                padding: 10px 15px;
            }

            .chart-card,
            .pie-card {
                height: 450px;
                padding: 18px;
            }

            .chart-card h5,
            .pie-card h5 {
                font-size: 14px;
                margin-bottom: 15px;
            }

            .pie-item {
                padding: 15px;
            }

            .pie-item canvas {
                max-height: 160px !important;
            }
        }

        @media (max-width: 576px) {

            .chart-card,
            .pie-card {
                height: 400px;
            }

            .legend-box {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }
        }

        /* Pie Container - 2 Column Grid */
        #pieContainer {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding-right: 5px;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            align-content: start;
        }

        /* Pie Items - Optimized untuk 2 kolom */
        .pie-item {
            margin-bottom: 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .pie-item:hover {
            background: #f1f3f5;
            border-color: #dee2e6;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transform: translateY(-2px);
        }

        .pie-item canvas {
            width: 100% !important;
            height: auto !important;
            max-height: 160px !important;
            margin-bottom: 10px;
        }

        .pie-item .text-center {
            font-size: 12px;
            font-weight: 600;
            color: #495057;
            margin-top: 8px;
            line-height: 1.3;
            text-align: center;
        }

        /* Scrollbar styling */
        #pieContainer::-webkit-scrollbar {
            width: 6px;
        }

        #pieContainer::-webkit-scrollbar-track {
            background: #f1f3f5;
            border-radius: 10px;
        }

        #pieContainer::-webkit-scrollbar-thumb {
            background: #adb5bd;
            border-radius: 10px;
        }

        #pieContainer::-webkit-scrollbar-thumb:hover {
            background: #868e96;
        }

        /* Responsive - Balik ke 1 kolom di mobile */
        @media (max-width: 1200px) {
            #pieContainer {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }

            .pie-item canvas {
                max-height: 140px !important;
            }

            .pie-item .text-center {
                font-size: 11px;
            }
        }

        @media (max-width: 768px) {
            #pieContainer {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .pie-item {
                padding: 12px;
            }

            .pie-item canvas {
                max-height: 150px !important;
            }
        }

        @media (max-width: 576px) {
            .pie-item canvas {
                max-height: 140px !important;
            }
        }
    </style>
</head>

<body>

    <div class="wrapper">

        <div class="modal fade" id="Modalkalender" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header no-bd">
                        <h5 class="modal-title">
                            <span class="fw-mediumbold">
                                Calendar</span>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="calendar"></div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-primary btn-block" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="main-header">
            <!-- Logo Header -->
            <div class="logo-header" data-background-color="blue2">
                <a href="landing_page.php" class="logo">
                    <img src="./assets/img/my_internship_logo_grey5.png" alt="navbar brand" class="navbar-brand"
                        style="width: 180px; height: auto;">
                </a>
                <button class="navbar-toggler sidenav-toggler ml-auto" type="button" data-toggle="collapse"
                    data-target="collapse" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon">
                        <i class="icon-menu"></i>
                    </span>
                </button>
                <button class="topbar-toggler more"><i class="icon-options-vertical"></i></button>
                <div class="nav-toggle">
                    <button class="btn btn-toggle toggle-sidebar">
                        <i class="icon-menu"></i>
                    </button>
                </div>
            </div>
            <!-- End Logo Header -->

            <!-- Navbar Header -->
            <nav class="navbar navbar-header navbar-expand-lg" data-background-color="blue">
                <div class="container-fluid">
                    <div class="collapse" id="search-nav">
                        <ul class="navbar-nav navbar-left topbar-nav nav-search mr-md-3 align-items-center">

                            <!-- Tanggal -->
                            <li class="nav-item dropdown hidden-caret">
                                <a aria-label="Current Date and Calendar" class="nav-link dropdown-toggle"
                                    data-toggle="dropdown" href="#" aria-expanded="false">
                                    <span id="date">Wed, 08 Oct 2025</span>
                                </a>
                                <ul class="float-right dropdown-menu dropdown-calendar dropdown-user animated fadeIn">
                                    <div class="dropdown-user-scroll scrollbar-outer">
                                        <div class="card-body text-center text-accent-1">
                                            <h3>Wed, 08 Oct 2025M</h3>
                                        </div>
                                    </div>
                                </ul>
                            </li>

                            <!-- Jam -->
                            <li class="nav-item dropdown hidden-caret">
                                <a aria-label="Current Time" class="nav-link dropdown-toggle" data-toggle="dropdown"
                                    href="#" aria-expanded="false">
                                    <span id="clock">22 : 12 : 24</span>
                                </a>
                                <ul class="float-right dropdown-menu dropdown-calendar dropdown-user animated fadeIn">
                                    <div class="dropdown-user-scroll scrollbar-outer">
                                        <div class="card-body text-center text-accent-1 ">
                                            <h3>Jakarta, Indonesia</h3>
                                            <h1>
                                                <span id="clock2">22 : 12 : 24</span>
                                            </h1>
                                        </div>
                                    </div>
                                </ul>
                            </li>
                        </ul>
                    </div>
                    <ul class="navbar-nav topbar-nav ml-md-auto align-items-center">
                        <li class="nav-item toggle-nav-search hidden-caret">
                            <a class="nav-link" data-toggle="collapse" href="#search-nav" role="button"
                                aria-expanded="false" aria-controls="search-nav">
                                <i class="fa fa-clock"></i>
                            </a>
                        </li>
                        <li class="nav-item dropdown hidden-caret">
                            <a class="nav-link" href="#" role="button" data-toggle="modal" data-target="#Modalkalender"
                                aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-calendar"></i>
                            </a>
                        </li>

                        <!-- Notification -->
                        <li class="nav-item dropdown hidden-caret" id="notification">
                            <a class="nav-link dropdown-toggle" href="#" id="notifDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-bell"></i>
                                <span id="count_notification"></span>
                            </a>
                            <ul class='dropdown-menu messages-notif-box animated fadeIn' aria-labelledby='notifDropdown'
                                id=''>
                                <li>
                                    <div class='dropdown-title'>New Notification</div>
                                </li>
                                <li>
                                    <div class='dropdown-title'>You don't have new notification</div>
                                </li>
                            </ul>
                        </li>

                        <!-- Profil -->
                        <li class="nav-item dropdown hidden-caret">
                            <a class="dropdown-toggle profile-pic" data-toggle="dropdown" href="#"
                                aria-expanded="false">
                                <div class="avatar-sm">
                                    <img src="./assets/img/profilelecturer.jpg" alt="..." class="avatar-img rounded-circle">
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-user animated fadeIn">
                                <div class="dropdown-user-scroll scrollbar-outer">
                                    <li>
                                        <div class="user-box">
                                            <div class="avatar-lg"><img src="./assets/img/profilelecturer.jpg" alt="image profile"
                                                    class="avatar-img rounded"></div>
                                            <div class="u-text">
                                                <h5><?= htmlspecialchars($user['name']) ?></h5>
                                                <p class="text-muted">Mahasiswa</p>
                                                <a href="index.php?page=industry_profile"
                                                    class="btn btn-xs btn-secondary btn-sm">View Profile</a>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="index.php?page=industry_profile">My Profile</a>
                                        <a class="dropdown-item" href="index.php?page=my_company">My Internship</a>
                                        <!-- <a class="dropdown-item" href="#">Inbox</a> -->
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#"
                                            onclick="logout_confirm()">Logout</a>
                                    </li>
                                </div>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>
            <!-- End Navbar -->
        </div>

        <!-- Sidebar -->
        <div class="sidebar sidebar-style-2">
            <div class="sidebar-wrapper scrollbar scrollbar-inner">
                <div class="sidebar-content">
                    <div class="user">
                        <div class="avatar-sm float-left mr-2">
                            <img src="./assets/img/profilelecturer.jpg" alt="..." class="avatar-img rounded-circle">
                        </div>
                        <div class="info">
                            <a data-toggle="collapse" href="#collapseExample" aria-expanded="true">
                                <span>
                                    <span class="wrap2"><?= htmlspecialchars($user['name']) ?></span>
                                    <span class="user-level">NIM: <?= htmlspecialchars($user['nim']) ?></span>
                                    <span class="user-level">
                                        Student at <br> Politeknik Negeri Batam
                                    </span>
                                </span>
                            </a>
                            <div class="clearfix"></div>
                        </div>
                    </div>

                    <ul class="nav nav-primary">
                        <li class="nav-item active">
                            <a href="dashboard_student_final.php">
                                <i class="fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="home.php">
                                <i class="fas fa-home"></i>
                                <p>Home</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="student_identity.php">
                                <i class="fas fa-id-card"></i>
                                <p>Student Identity</p>
                            </a>
                        </li>

                        <!-- Student Section -->
                        <li class="nav-section">
                            <span class="sidebar-mini-icon">
                                <i class="fa fa-ellipsis-h"></i>
                            </span>
                            <h4 class="text-section">Student</h4>
                        </li>

                        <li class="nav-item">
                            <a href="company_list.php">
                                <i class="fas fa-building"></i>
                                <p>Company List</p>
                            </a>
                        </li>

                        <!-- Internship Approval Section -->
                        <li class="nav-section">
                            <span class="sidebar-mini-icon">
                                <i class="fa fa-ellipsis-h"></i>
                            </span>
                            <h4 class="text-section">Internship Approval</h4>
                        </li>

                        <li class="nav-item">
                            <a href="form_submission.php">
                                <i class="fas fa-file-alt"></i>
                                <p>Form Submission</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="approval_status.php">
                                <i class="fas fa-clipboard-check"></i>
                                <p>Approval Status</p>
                            </a>
                        </li>

                        <!-- Account Section -->
                        <li class="nav-section">
                            <span class="sidebar-mini-icon">
                                <i class="fa fa-ellipsis-h"></i>
                            </span>
                            <h4 class="text-section">Account</h4>
                        </li>
                        <li class="nav-item ">
                            <a href="#" class="collapsed" aria-expanded="false">
                                <i class="fas fa-user"></i>
                                <p>Profile</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" onclick="logout_confirm()" class="collapsed" aria-expanded="false">
                                <i class="fas fa-sign-out-alt"></i>
                                <p>Logout</p>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- End Sidebar -->

        <div class="main-panel">
            <!-- Header -->
            <div class="panel-header bg-primary-gradient">
                <div class="page-inner py-5">
                    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row">
                        <div>
                            <h1 class="text-white pb-2 fw-bold">Welcome To My Internship</h1>
                            <h5 class="text-white fw-light">
                                Managing Industrial Learning through Structured Internship
                            </h5>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ====== NEW CONTENT (Filter + Charts Section) ====== -->
            <div class="container-fluid mt-4">

                <!-- Filter Section -->
                <div
                    class="filter-section mb-4 p-3 bg-white rounded shadow-sm d-flex justify-content-between align-items-center flex-wrap">
                    <div class="department-nav d-flex align-items-center gap-2">

                        <div class="current-dept font-weight-bold text-dark bg-light px-3 py-2 rounded"
                            id="currentDept">All Departments</div>

                    </div>
                    <div>
                        <select class="year-filter border-primary rounded px-3 py-2 font-weight-bold text-primary"
                            id="yearFilter" onchange="filterByYear()">
                            <option value="all">All Years</option>
                            <option value="2025" selected>2025</option>
                            <option value="2024">2024</option>
                            <option value="2023">2023</option>
                        </select>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="row">
                    <!-- LEFT BAR CHART -->
                    <div class="col-md-7 mb-4">
                        <div class="chart-card">
                            <h5 class="font-weight-bold mb-3">Service Response Time Chart – Internship Coordinator</h5>
                            <div style="width: 100%; position: relative;">
                                <canvas id="responseChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT PIE CHARTS -->
                    <div class="col-md-5 mb-4">
                        <div class="pie-card">
                            <h5 class="text-center font-weight-bold mb-3" id="pieTitle">Internship Acceptance Rate by
                                Department</h5>

                            <div id="pieContainer"></div>

                            <div class="legend-box d-flex justify-content-center p-2 bg-light rounded">
                                <div class="d-flex align-items-center mr-3">
                                    <div class="bg-success rounded mr-2" style="width: 20px; height: 20px;"></div>
                                    <span class="font-weight-bold text-success">Accepted</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="bg-danger rounded mr-2" style="width: 20px; height: 20px;"></div>
                                    <span class="font-weight-bold text-danger">Rejected</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script src="https://code.jquery.com/jquery-3.7.0.min.js"
                integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>

            <!--   Core JS Files   -->
            <!-- <script src="./assets/js/core/jquery.3.2.1.min.js"></script> -->
            <script src="./assets/js/core/popper.min.js"></script>
            <script src="./assets/js/core/bootstrap.min.js"></script>

            <!-- jQuery UI -->
            <script src="./assets/js/plugin/jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>
            <script src="./assets/js/plugin/jquery-ui-touch-punch/jquery.ui.touch-punch.min.js"></script>

            <!-- Moment JS -->
            <script src="./assets/js/plugin/moment/moment.min.js"></script>

            <!-- Bootstrap Toggle -->
            <script src="./assets/js/plugin/bootstrap-toggle/bootstrap-toggle.min.js"></script>

            <!-- jQuery Scrollbar -->
            <script src="./assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>

            <!-- Fullcalendar -->
            <script src="./assets/js/plugin/fullcalendar/fullcalendar.min.js"></script>

            <!-- Atlantis JS -->
            <script src="./assets/js/atlantis.min.js"></script>

            <!-- Chart JS -->
            <script src="./assets/js/plugin/chart.js/chart.min.js"></script>

            <!-- JS Dependencies -->
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

            <script>
                function logout_confirm() {

                    let _token = $('meta[name="csrf-token"]').attr('content');

                    Swal.fire({
                        title: 'Logout from your account ?',
                        text: 'Are you sure you want to end the current session?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: "#DD6B55",
                        confirmButtonText: "Yes, I'm sure!",
                        cancelButtonText: "Cancel"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // AJAX logout ke PHP
                            $.ajax({
                                url: "session_logout.php",
                                type: "POST",
                                data: {
                                    'token': _token
                                },
                                success: function() {
                                    setTimeout(function() {
                                        localStorage.removeItem('first');
                                        localStorage.removeItem('first_chime');
                                        localStorage.removeItem('next_chime');
                                        window.location.href = 'role_login.php';
                                    }, 200);
                                },
                                error: function() {
                                    Swal.fire('Error', 'Logout failed, please try again.', 'error');
                                }
                            });
                        }
                    });
                }

                $(document).ready(function() {

                    clock_run();

                    show_calendar();
                });

                function show_calendar() {
                    var date = new Date();
                    var d = date.getDate();
                    var m = date.getMonth();
                    var y = date.getFullYear();
                    var className = Array('fc-primary', 'fc-danger', 'fc-black', 'fc-success', 'fc-info', 'fc-warning', 'fc-danger-solid', 'fc-warning-solid', 'fc-success-solid', 'fc-black-solid', 'fc-success-solid', 'fc-primary-solid');

                    $calendar = $('#calendar');
                    $calendar.fullCalendar({
                        fixedWeekCount: false, // Set false agar jumlah minggu yang ditampilkan menyesuaikan dengan bulan aktif
                    });
                }

                function clock_run() {

                    'use strict';
                    let d = new Date();
                    let en_day = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                    let en_month = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                    let day = en_day[d.getDay()];
                    let date = d.getDate();
                    let month = en_month[d.getMonth()];
                    let year = (d.getYear() + 1900);
                    let curr_date = day + ', ' + date + ' ' + month + ' ' + year;
                    localStorage.setItem('curr_date', curr_date);
                    let old_date = localStorage.getItem('curr_date');

                    if ($("#date").text() != curr_date) {
                        localStorage.setItem('curr_date', curr_date);
                        $("#date").text(curr_date);
                    }

                    setInterval(function() {
                        let d = new Date();
                        let day = en_day[d.getDay()];
                        let date = d.getDate();
                        let month = en_month[d.getMonth()];
                        let year = (d.getYear() + 1900);
                        let date_day = day + ', ' + date + ' ' + month + ' ' + year;

                        if (date_day != old_date) {
                            localStorage.setItem('curr_date', date_day);
                            $("#date").text(date_day);
                        }

                        let hours = d.getHours();
                        let minutes = d.getMinutes();
                        let seconds = d.getSeconds();
                        let time = ((hours < 10 ? "0" : "") + hours) + ' : ' + ((minutes < 10 ? "0" : "") + minutes) + ' : ' + ((seconds < 10 ? "0" : "") + seconds);

                        $("#clock").text(time);
                        $("#clock2").text(time);
                    }, 1000);
                }
            </script>

            <!-- Javascript Function -->
            <script type="text/javascript">
                function copyToClipboard(text) {
                    var tempInput = document.createElement("input");
                    document.body.appendChild(tempInput);
                    tempInput.value = text;
                    tempInput.select();

                    document.execCommand("copy");

                    document.body.removeChild(tempInput);

                    alert("Text copied to clipboard: " + text);

                }

                function getNotificationForm(formSelector) {
                    $.ajax({
                        url: 'index.php?request=validation_get',
                        type: 'GET',

                        success: function(response, xhr, status, error) {
                            console.log('Getting form notification');

                            $('body').append(response);
                        },

                        error: function(xhr, status, error) {
                            console.log('Failed Getting form notification');
                        }
                    });
                    return true;
                }

                function konfirmasi(notif, lokasi) {

                    var x = confirm(notif);
                    if (x === true) {
                        window.location.href = lokasi;
                    }
                }

                function spinner() {

                    // var icon_spinner = event.target.childNodes[0];
                    var icon_spinner = event.target.querySelector('i');
                    var icon_old = icon_spinner.className;
                    var spinner = "fas fa-spinner fa-spin mr-1";

                    // console.log(icon_spinner);
                    icon_spinner.className = '';
                    icon_spinner.className = spinner;

                    setTimeout(function() {
                        icon_spinner.className = '';
                        icon_spinner.className = icon_old;
                    }, 2000);
                }

                // === Department Data (from your first file) ===
                const allLabels = [
                    '— Business Management —', 'D4 Applied Business Administration', 'D3 Accounting', 'D4 Managerial Accounting', 'D2 Goods Distribution', 'D4 International Trade Logistics',
                    '— Mechanical Engineering —', 'D3 Mechanical Engineering', 'Professional Engineer Program', 'D3 Aircraft Maintenance Engineering', 'D4 Ship Construction Engineering', 'D4 Welding and Fabrication Engineering Technology',
                    '— Electrical Engineering —', 'D3 Manufacturing Electronics Engineering', 'D4 Electrical Engineering Technology', 'D3 Instrumentation Engineering', 'D4 Mechatronic Engineering', 'D2 Automation Engineering', 'D4 Robotics Engineering', 'D4 Energy Generation Engineering Technology',
                    '— Informatics Engineering —', 'D3 Informatics Engineering', 'D3 Geomatics Technology', 'D4 Animation', 'D4 Multimedia Engineering Technology', 'D4 Cyber Security Engineering', 'D4 Software Development Engineering'
                ];
                const allValues = [null, 7, 6, 5, 7, 6, null, 5, 6, 7, 4, 5, null, 6, 7, 5, 6, 7, 6, 5, null, 5, 7, 6, 7, 5, 6];

                const departmentsData = {
                    'Business Management': {
                        programs: ['D4 Applied Business Administration', 'D3 Accounting', 'D4 Managerial Accounting', 'D2 Goods Distribution', 'D4 International Trade Logistics'],
                        responseTime: [7, 6, 5, 7, 6],
                        acceptanceRates: [
                            [85, 15],
                            [78, 22],
                            [82, 18],
                            [75, 25],
                            [88, 12]
                        ]
                    },
                    'Mechanical Engineering': {
                        programs: ['D3 Mechanical Engineering', 'Professional Engineer Program', 'D3 Aircraft Maintenance Engineering', 'D4 Ship Construction Engineering', 'D4 Welding and Fabrication Engineering Technology'],
                        responseTime: [5, 6, 7, 4, 5],
                        acceptanceRates: [
                            [75, 25],
                            [80, 20],
                            [70, 30],
                            [85, 15],
                            [72, 28]
                        ]
                    },
                    'Electrical Engineering': {
                        programs: ['D3 Manufacturing Electronics Engineering', 'D4 Electrical Engineering Technology', 'D3 Instrumentation Engineering', 'D4 Mechatronic Engineering', 'D2 Automation Engineering', 'D4 Robotics Engineering', 'D4 Energy Generation Engineering Technology'],
                        responseTime: [6, 7, 5, 6, 7, 6, 5],
                        acceptanceRates: [
                            [65, 35],
                            [70, 30],
                            [68, 32],
                            [75, 25],
                            [62, 38],
                            [80, 20],
                            [73, 27]
                        ]
                    },
                    'Informatics Engineering': {
                        programs: ['D3 Informatics Engineering', 'D3 Geomatics Technology', 'D4 Animation', 'D4 Multimedia Engineering Technology', 'D4 Cyber Security Engineering', 'D4 Software Development Engineering'],
                        responseTime: [5, 7, 6, 7, 5, 6],
                        acceptanceRates: [
                            [90, 10],
                            [85, 15],
                            [88, 12],
                            [82, 18],
                            [92, 8],
                            [87, 13]
                        ]
                    }
                };

                const pieDataAllDepartments = [{
                        name: 'Business Management Department',
                        data: [82, 18]
                    },
                    {
                        name: 'Mechanical Engineering Department',
                        data: [76, 24]
                    },
                    {
                        name: 'Electrical Engineering Department',
                        data: [70, 30]
                    },
                    {
                        name: 'Informatics Engineering Department',
                        data: [87, 13]
                    }
                ];

                const departments = ['Business Management', 'Mechanical Engineering', 'Electrical Engineering', 'Informatics Engineering'];
                let currentDeptIndex = 0;
                let responseChart = null;
                let pieCharts = [];

                // ===== Chart Functions =====
                function createResponseChart(labels, data) {
                    const canvas = document.getElementById('responseChart');
                    const ctx = canvas.getContext('2d');

                    // Destroy chart lama
                    if (responseChart) {
                        responseChart.destroy();
                        responseChart = null;
                    }

                    // Reset canvas completely
                    canvas.removeAttribute('style');
                    canvas.removeAttribute('width');
                    canvas.removeAttribute('height');

                    // Calculate new height
                    const chartHeight = labels.length * 35;

                    // Set canvas dimensions
                    canvas.width = canvas.parentElement.offsetWidth;
                    canvas.height = chartHeight;
                    canvas.style.width = '100%';
                    canvas.style.height = chartHeight + 'px';

                    responseChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Average Response Time (Days)',
                                data: data,
                                backgroundColor: data.map(v => v === null ? 'rgba(0,0,0,0)' : '#4e73df'),
                                borderRadius: 5,
                                barThickness: 20
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: false,
                            maintainAspectRatio: false,
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    max: 7,
                                    ticks: {
                                        stepSize: 1,
                                        font: {
                                            size: 11
                                        }
                                    },
                                    title: {
                                        display: true,
                                        text: 'Days',
                                        font: {
                                            size: 12,
                                            weight: 'bold'
                                        }
                                    },
                                    grid: {
                                        color: '#e9ecef'
                                    }
                                },
                                y: {
                                    ticks: {
                                        callback: function(value, index) {
                                            const label = this.getLabelForValue(index);
                                            if (label.startsWith('—')) {
                                                return label;
                                            }
                                            return '   ' + label;
                                        },
                                        color: function(ctx) {
                                            const label = ctx.tick.label;
                                            return label.startsWith('—') ? '#2c3e50' : '#495057';
                                        },
                                        font: function(ctx) {
                                            const label = ctx.tick.label;
                                            return {
                                                size: 11,
                                                weight: label.startsWith('—') ? 'bold' : 'normal'
                                            };
                                        }
                                    },
                                    grid: {
                                        display: false
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0,0,0,0.8)',
                                    padding: 10,
                                    titleFont: {
                                        size: 12
                                    },
                                    bodyFont: {
                                        size: 11
                                    },
                                    callbacks: {
                                        title: (context) => {
                                            const label = context[0].label;
                                            return label.startsWith('—') ? '' : label;
                                        },
                                        label: (context) => {
                                            return context.raw === null ? '' : `Response Time: ${context.formattedValue} days`;
                                        }
                                    }
                                }
                            }
                        }
                    });
                }

                function updatePieCharts(pieData, isAll = false) {
                    // Destroy old charts
                    pieCharts.forEach(c => c.destroy());
                    pieCharts = [];

                    const container = document.getElementById('pieContainer');
                    container.innerHTML = '';

                    // Update title
                    document.getElementById('pieTitle').textContent = isAll ?
                        'Internship Acceptance Rate by Department' :
                        'Internship Acceptance Rate by Study Program';

                    // Create pie charts
                    pieData.forEach((item, i) => {
                        const pieDiv = document.createElement('div');
                        pieDiv.className = 'pie-item';

                        const canvas = document.createElement('canvas');
                        canvas.id = `pie-${i}`;

                        const title = document.createElement('div');
                        title.className = 'text-center font-weight-bold mt-2';
                        title.style.fontSize = '13px';
                        title.style.color = '#495057';
                        title.textContent = item.name;

                        pieDiv.appendChild(canvas);
                        pieDiv.appendChild(title);
                        container.appendChild(pieDiv);

                        const ctx = canvas.getContext('2d');
                        const chart = new Chart(ctx, {
                            type: 'pie',
                            data: {
                                labels: ['Accepted', 'Rejected'],
                                datasets: [{
                                    data: item.data,
                                    backgroundColor: ['#28a745', '#dc3545'],
                                    borderWidth: 2,
                                    borderColor: '#fff'
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    tooltip: {
                                        backgroundColor: 'rgba(0,0,0,0.8)',
                                        padding: 10,
                                        titleFont: {
                                            size: 12
                                        },
                                        bodyFont: {
                                            size: 11
                                        },
                                        callbacks: {
                                            label: (context) => {
                                                return `${context.label}: ${context.formattedValue}%`;
                                            }
                                        }
                                    }
                                }
                            }
                        });
                        pieCharts.push(chart);
                    });
                }

                function navigateDepartment(dir) {
                    currentDeptIndex += dir;
                    if (currentDeptIndex < 0) currentDeptIndex = 0;
                    if (currentDeptIndex >= departments.length) currentDeptIndex = departments.length - 1;
                    updateDashboard();
                    updateNavButtons();
                }

                function updateNavButtons() {
                    document.getElementById('prevBtn').disabled = currentDeptIndex === 0;
                    document.getElementById('nextBtn').disabled = currentDeptIndex === departments.length - 1;
                }

                function updateDashboard() {
                    const dept = departments[currentDeptIndex];
                    {
                        const data = departmentsData[dept];
                        document.getElementById('currentDept').textContent = dept + ' Department';
                        createResponseChart(data.programs, data.responseTime);
                        const pies = data.programs.map((p, i) => ({
                            name: p,
                            data: data.acceptanceRates[i]
                        }));
                        updatePieCharts(pies, false);
                    }
                    const data = departmentsData[dept];
                    document.getElementById('currentDept').textContent = dept + ' Department';
                    createResponseChart(data.programs, data.responseTime);
                    const pies = data.programs.map((p, i) => ({
                        name: p,
                        data: data.acceptanceRates[i]
                    }));
                    updatePieCharts(pies, false);
                }
                }

                function filterByYear() {
                    updateDashboard();
                }

                document.addEventListener('DOMContentLoaded', function() {
                    updateDashboard();
                    updateNavButtons();
                });

                // Highlight menu
                document.addEventListener("DOMContentLoaded", function() {
                    const navItems = document.querySelectorAll(".sidebar .nav-item");

                    navItems.forEach(item => {
                        item.addEventListener("click", function() {
                            // Hapus active dari semua nav-item
                            navItems.forEach(i => i.classList.remove("active"));

                            // Tambahkan active ke item yang diklik
                            this.classList.add("active");
                        });
                    });

                    // --- BONUS: agar aktif sesuai halaman yang dibuka ---
                    const currentPage = window.location.href;
                    navItems.forEach(item => {
                        const link = item.querySelector("a");
                        if (link && currentPage.includes(link.getAttribute("href"))) {
                            navItems.forEach(i => i.classList.remove("active"));
                            item.classList.add("active");
                        }
                    });
                });
            </script>
            <!-- Footer -->
            <footer class="footer">
                <div class="container-fluid">
                    <div class="copyright ml-auto">
                        2025, made with <i class="fa fa-heart heart text-danger"></i> by PBLIFPagi3A-3
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- START: Replaced chart logic — one department at a time with Next/Prev navigation -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ===== Data Tiap Jurusan =====
            const departmentsData = {
                'Informatics Engineering': {
                    programs: ['D3 Informatics Engineering', 'D3 Geomatics Technology', 'D4 Animation', 'D4 Multimedia Engineering Technology', 'D4 Cyber Security Engineering', 'D4 Software Development Engineering'],
                    responseTime: [5, 7, 6, 7, 5, 6],
                    acceptanceRates: [
                        [90, 10],
                        [85, 15],
                        [88, 12],
                        [82, 18],
                        [92, 8],
                        [87, 13]
                    ]
                },
                'Electrical Engineering': {
                    programs: ['D3 Manufacturing Electronics Engineering', 'D4 Electrical Engineering Technology', 'D3 Instrumentation Engineering', 'D4 Mechatronic Engineering', 'D2 Automation Engineering', 'D4 Robotics Engineering', 'D4 Energy Generation Engineering Technology'],
                    responseTime: [6, 7, 5, 6, 7, 6, 5],
                    acceptanceRates: [
                        [65, 35],
                        [70, 30],
                        [68, 32],
                        [75, 25],
                        [62, 38],
                        [80, 20],
                        [73, 27]
                    ]
                },
                'Mechanical Engineering': {
                    programs: ['D3 Mechanical Engineering', 'Professional Engineer Program', 'D3 Aircraft Maintenance Engineering', 'D4 Ship Construction Engineering', 'D4 Welding and Fabrication Engineering Technology'],
                    responseTime: [5, 6, 7, 4, 5],
                    acceptanceRates: [
                        [75, 25],
                        [80, 20],
                        [70, 30],
                        [85, 15],
                        [72, 28]
                    ]
                },
                'Business Management': {
                    programs: ['D4 Applied Business Administration', 'D3 Accounting', 'D4 Managerial Accounting', 'D2 Goods Distribution', 'D4 International Trade Logistics'],
                    responseTime: [7, 6, 5, 7, 6],
                    acceptanceRates: [
                        [85, 15],
                        [78, 22],
                        [82, 18],
                        [75, 25],
                        [88, 12]
                    ]
                }
            };

            // ordered departments as requested
            const departments = ['Informatics Engineering', 'Electrical Engineering', 'Mechanical Engineering', 'Business Management'];
            let currentDeptIndex = 0;
            let responseChart = null;
            let pieCharts = [];

            // helper: format date or labels - unused but kept for parity
            function formatDate(d) {
                return d;
            }

            // Create / recreate the horizontal bar chart (response time)
            function createResponseChart(labels, data) {
                const canvas = document.getElementById('responseChart');
                if (!canvas) return;
                const ctx = canvas.getContext('2d');

                // destroy if exists
                if (responseChart) {
                    try {
                        responseChart.destroy();
                    } catch (e) {}
                    responseChart = null;
                }

                // set canvas height based on number of labels
                canvas.style.height = (labels.length * 35) + 'px';
                canvas.height = labels.length * 35;

                responseChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Average Response Time (Days)',
                            data: data,
                            backgroundColor: '#4e73df',
                            borderRadius: 5,
                            barThickness: 18
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                beginAtZero: true,
                                max: 7,
                                ticks: {
                                    stepSize: 1
                                }
                            },
                            y: {
                                ticks: {
                                    color: '#333'
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(ctx) {
                                        return 'Response Time: ' + ctx.formattedValue + ' days';
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // create pie charts for programs of the current department
            function updatePieCharts(pieData) {
                const container = document.getElementById('pieContainer');
                if (!container) return;

                // destroy old charts
                if (pieCharts && pieCharts.length) {
                    pieCharts.forEach(c => {
                        try {
                            c.destroy();
                        } catch (e) {}
                    });
                    pieCharts = [];
                }
                container.innerHTML = '';

                pieData.forEach((item, i) => {
                    const pieDiv = document.createElement('div');
                    pieDiv.className = 'pie-item mb-3';
                    pieDiv.style.background = '#fff';
                    pieDiv.style.padding = '10px';
                    pieDiv.style.borderRadius = '8px';
                    pieDiv.style.boxShadow = '0 1px 4px rgba(0,0,0,0.04)';
                    pieDiv.style.minWidth = '180px';
                    pieDiv.style.flex = '1 1 45%'; // responsive two per row

                    const title = document.createElement('div');
                    title.className = 'text-center font-weight-bold mb-2';
                    title.style.fontSize = '13px';
                    title.textContent = item.name;

                    const canvas = document.createElement('canvas');
                    canvas.id = 'pie-' + i;
                    canvas.style.width = '100%';
                    canvas.style.height = '140px';

                    pieDiv.appendChild(title);
                    pieDiv.appendChild(canvas);
                    container.appendChild(pieDiv);

                    const ctx = canvas.getContext('2d');
                    const chart = new Chart(ctx, {
                        type: 'pie',
                        data: {
                            labels: ['Accepted', 'Rejected'],
                            datasets: [{
                                data: item.data,
                                backgroundColor: ['#28a745', '#dc3545'],
                                borderColor: '#fff',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(ctx) {
                                            return ctx.label + ': ' + ctx.formattedValue + '%';
                                        }
                                    }
                                }
                            }
                        }
                    });

                    pieCharts.push(chart);
                });
            }

            // main update: set title, create left chart and right pies
            function updateDashboard() {
                const dept = departments[currentDeptIndex];
                const data = departmentsData[dept];
                const currentDeptEl = document.getElementById('currentDept');
                if (currentDeptEl) currentDeptEl.textContent = dept + ' Department Dashboard';

                if (data) {
                    createResponseChart(data.programs, data.responseTime);

                    const pies = data.programs.map((p, i) => ({
                        name: p,
                        data: data.acceptanceRates[i]
                    }));
                    updatePieCharts(pies);

                    // update pie title
                    const pieTitle = document.getElementById('pieTitle');
                    if (pieTitle) pieTitle.textContent = 'Internship Acceptance Rate — ' + dept;
                }

                updateNavButtons();
            }

            function navigateDepartment(dir) {
                currentDeptIndex += dir;
                if (currentDeptIndex < 0) currentDeptIndex = 0;
                if (currentDeptIndex >= departments.length) currentDeptIndex = departments.length - 1;
                updateDashboard();
                // scroll into view the charts area for better UX
                const chartRow = document.querySelector('#responseChart')?.closest('.row') || document.querySelector('.row');
                if (chartRow) chartRow.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }

            // append navigation controls below the charts row (centered)
            function ensureNavControls() {
                // find a good anchor: the row that contains responseChart
                const canvas = document.getElementById('responseChart');
                let anchorRow = canvas ? canvas.closest('.row') : null;

                if (!anchorRow) {
                    // fallback: find the first .row on page that contains pieContainer or responseChart
                    anchorRow = document.querySelector('div.row');
                }

                if (!anchorRow) return;

                // check if nav already exists
                if (document.getElementById('deptNavWrap')) return;

                const navWrap = document.createElement('div');
                navWrap.id = 'deptNavWrap';
                navWrap.className = 'text-center mt-3';
                navWrap.style.width = '100%';

                const prevBtn = document.createElement('button');
                prevBtn.id = 'prevBtn';
                prevBtn.className = 'btn btn-outline-primary mr-2';
                prevBtn.textContent = '← Previous';

                const label = document.createElement('span');
                label.id = 'currentDept';
                label.className = 'font-weight-bold mx-3';
                label.textContent = '';

                const nextBtn = document.createElement('button');
                nextBtn.id = 'nextBtn';
                nextBtn.className = 'btn btn-outline-primary ml-2';
                nextBtn.textContent = 'Next →';

                navWrap.appendChild(prevBtn);
                navWrap.appendChild(label);
                navWrap.appendChild(nextBtn);

                // insert after the anchorRow
                anchorRow.parentNode.insertBefore(navWrap, anchorRow.nextSibling);

                prevBtn.addEventListener('click', () => navigateDepartment(-1));
                nextBtn.addEventListener('click', () => navigateDepartment(1));
            }

            // init
            ensureNavControls();
            updateDashboard();
        });
    </script>
    <!-- END: Replaced chart logic -->

    <!-- Tambahkan library SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Blokir browser cache saat user tekan tombol back
        window.addEventListener("pageshow", function(event) {
            if (event.persisted) {
                // Jika halaman dibuka dari cache browser (misal setelah logout)
                window.location.reload(); // paksa reload agar session check jalan lagi
            }
        });

        // Cegah user pakai tombol back untuk kembali ke dashboard
        window.history.pushState(null, "", window.location.href);
        window.onpopstate = function() {
            window.history.pushState(null, "", window.location.href);
        };
    </script>

</body>

</html>
