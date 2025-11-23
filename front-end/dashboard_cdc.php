<?php
session_start();

if (!isset($_SESSION['cdc']) || empty($_SESSION['cdc']['username'])) {
    header('Location: role_login.php');
    exit;
}

$cdc = $_SESSION['cdc'];
$user = $cdc;

$id_kampus = $user['id_kampus'] ?? null;
$nama_kampus = "Tidak diketahui";

if ($id_kampus) {
    $api_url = "http://localhost:8000/api/kampus/" . urlencode($id_kampus);

    $context = stream_context_create([
        "http" => [
            "method" => "GET",
            "timeout" => 5,
            "ignore_errors" => true
        ]
    ]);

    $response = @file_get_contents($api_url, false, $context);

    if ($response !== false) {
        $data = json_decode($response, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            if (isset($data['nama_kampus'])) {
                $nama_kampus = $data['nama_kampus'];
            } elseif (isset($data['message'])) {
                $nama_kampus = "Tidak diketahui (" . $data['message'] . ")";
            } else {
                $nama_kampus = "Tidak diketahui (Format respons tidak sesuai)";
            }
        } else {
            $nama_kampus = "Tidak diketahui (JSON error: " . json_last_error_msg() . ")";
        }
    } else {
        $nama_kampus = "Tidak diketahui (API tidak dapat diakses)";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Meta for Compatibility -->
    <meta charset="utf-8">
    <title>Dashboard CDC</title>
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
    <script>
        $(function() {});
    </script>
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
                    <img src="./assets/img/my_internship_logo_grey5.png" alt="navbar brand" class="navbar-brand" style="width: 180px; height: auto;">
                </a>
                <button class="navbar-toggler sidenav-toggler ml-auto" type="button" data-toggle="collapse" data-target="collapse" aria-expanded="false" aria-label="Toggle navigation">
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
                                <a aria-label="Current Date and Calendar" class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" aria-expanded="false">
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
                                <a aria-label="Current Time" class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" aria-expanded="false">
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
                            <a class="nav-link" data-toggle="collapse" href="#search-nav" role="button" aria-expanded="false" aria-controls="search-nav">
                                <i class="fa fa-clock"></i>
                            </a>
                        </li>
                        <li class="nav-item dropdown hidden-caret">
                            <a class="nav-link" href="#" role="button" data-toggle="modal" data-target="#Modalkalender" aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-calendar"></i>
                            </a>
                        </li>

                        <!-- Notification -->
                        <li class="nav-item dropdown hidden-caret" id="notification">
                            <a class="nav-link dropdown-toggle" href="#" id="notifDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-bell"></i>
                                <span id="count_notification"></span>
                            </a>
                            <ul class='dropdown-menu messages-notif-box animated fadeIn' aria-labelledby='notifDropdown' id=''>
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
                            <a class="dropdown-toggle profile-pic" data-toggle="dropdown" href="#" aria-expanded="false">
                                <div class="avatar-sm">
                                    <img src="./assets/img/profilelecturer.jpg" alt="..." class="avatar-img rounded-circle">
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-user animated fadeIn">
                                <div class="dropdown-user-scroll scrollbar-outer">
                                    <li>
                                        <div class="user-box">
                                            <div class="avatar-lg"><img src="./assets/img/profilelecturer.jpg" alt="image profile" class="avatar-img rounded"></div>
                                            <div class="u-text">
                                                <h5><?php echo htmlspecialchars($user['name']); ?></h5>
                                                <p class="text-muted"> CDC at :<br><?= htmlspecialchars($nama_kampus) ?></p>
                                                <a href="index.php?page=industry_profile" class="btn btn-xs btn-secondary btn-sm">View Profile</a>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="index.php?page=industry_profile">My Profile</a>
                                        <a class="dropdown-item" href="index.php?page=my_company">My Internship</a>
                                        <!-- <a class="dropdown-item" href="#">Inbox</a> -->
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#" onclick="logout_confirm()">Logout</a>
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
                                    <span class="wrap2"><?php echo htmlspecialchars($user['name']); ?></span>
                                    <span class="user-level wrap2">CDC at : <br><?php echo htmlspecialchars($nama_kampus); ?></span>
                                </span>
                                </span>
                            </a>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                    <ul class="nav nav-primary">
                        <li class="nav-item active">
                            <a href="dashboard_cdc.php">
                                <i class="fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item ">
                            <a href="index.php?page=student_offer" class="collapsed" aria-expanded="false">
                                <i class="fas fa-clipboard-list"></i>
                                <p>Home</p>
                            </a>
                        </li>
                        <li class="nav-section">
                            <span class="sidebar-mini-icon">
                                <i class="fa fa-ellipsis-h"></i>
                            </span>
                            <h4 class="text-section">CDC Menu</h4>
                        </li>
                        <li class="nav-item">
                            <a href="approval_of_letter_cdc.php" class="collapsed" aria-expanded="false">
                                <i class="fas fa-briefcase"></i>
                                <p>Approval Of Letter</p>
                            </a>
                        </li>
                        <li class="nav-section">
                            <span class="sidebar-mini-icon">
                                <i class="fa fa-ellipsis-h"></i>
                            </span>
                            <h4 class="text-section">Account</h4>
                        </li>
                        <li class="nav-item">
                            <a href="https://wa.me/6281364440803" target="_blank" class="collapsed"
                                aria-expanded="false">
                                <i class="fas fa-question"></i>
                                <p>Helpdesk</p>
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
                <div class="filter-section mb-4 p-3 bg-white rounded shadow-sm d-flex justify-content-between align-items-center flex-wrap">
                    <div class="department-nav d-flex align-items-center gap-2">

                        <div class="current-dept font-weight-bold text-dark bg-light px-3 py-2 rounded" id="currentDept">All Departments</div>

                    </div>
                    <div>
                        <select class="year-filter border-primary rounded px-3 py-2 font-weight-bold text-primary" id="yearFilter" onchange="filterByYear()">
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
                            <h5 class="text-center font-weight-bold mb-3" id="pieTitle">Internship Acceptance Rate by Department</h5>

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

            <!-- Footer -->
            <footer class="footer">
                <div class="container-fluid">
                    <div class="copyright ml-auto">
                        2025, made with <i class="fa fa-heart heart text-danger"></i> by PBLIFPagi3A-3
                    </div>
                </div>
            </footer>

            <script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>

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
// ============================================
// LOGOUT & UTILITY FUNCTIONS
// ============================================
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
            $.ajax({
                url: "session_logout.php",
                type: "POST",
                data: { 'token': _token },
                success: function() {
                    setTimeout(function() {
                        localStorage.removeItem('first');
                        localStorage.removeItem('first_chime');
                        localStorage.removeItem('next_chime');
                        localStorage.removeItem('dashboard_dept_index');
                        localStorage.removeItem('dashboard_year');
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

function copyToClipboard(text) {
    var tempInput = document.createElement("input");
    document.body.appendChild(tempInput);
    tempInput.value = text;
    tempInput.select();
    document.execCommand("copy");
    document.body.removeChild(tempInput);
    alert("Text copied to clipboard: " + text);
}

// ============================================
// CLOCK & CALENDAR FUNCTIONS
// ============================================
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
    
    $("#date").text(curr_date);
    
    setInterval(function() {
        let d = new Date();
        let hours = d.getHours();
        let minutes = d.getMinutes();
        let seconds = d.getSeconds();
        let time = ((hours < 10 ? "0" : "") + hours) + ' : ' +
                   ((minutes < 10 ? "0" : "") + minutes) + ' : ' +
                   ((seconds < 10 ? "0" : "") + seconds);
        
        $("#clock").text(time);
        $("#clock2").text(time);
    }, 1000);
}

function show_calendar() {
    var $calendar = $('#calendar');
    $calendar.fullCalendar({
        fixedWeekCount: false
    });
}

// ============================================
// SIDEBAR NAVIGATION HIGHLIGHT
// ============================================
function initSidebarHighlight() {
    const navItems = document.querySelectorAll(".sidebar .nav-item");
    
    navItems.forEach(item => {
        item.addEventListener("click", function() {
            navItems.forEach(i => i.classList.remove("active"));
            this.classList.add("active");
        });
    });
    
    const currentPage = window.location.href;
    navItems.forEach(item => {
        const link = item.querySelector("a");
        if (link && currentPage.includes(link.getAttribute("href"))) {
            navItems.forEach(i => i.classList.remove("active"));
            item.classList.add("active");
        }
    });
}

// ============================================
// GLOBAL VARIABLES
// ============================================
let departmentsData = {};
let departments = [];
let currentDeptIndex = 0;
let responseChart = null;
let pieCharts = [];

// ============================================
// LOCAL STORAGE - PERSISTENT STATE
// ============================================
function saveDashboardState() {
    try {
        localStorage.setItem('dashboard_dept_index', currentDeptIndex.toString());
        const yearSelect = document.getElementById('yearFilter');
        if (yearSelect) {
            localStorage.setItem('dashboard_year', yearSelect.value);
        }
        console.log('State saved: Department Index ' + currentDeptIndex + ', Year ' + (yearSelect ? yearSelect.value : 'N/A'));
    } catch (e) {
        console.error('Error saving dashboard state:', e);
    }
}

function loadDashboardState() {
    try {
        const savedIndex = localStorage.getItem('dashboard_dept_index');
        const savedYear = localStorage.getItem('dashboard_year');
        
        const state = {
            deptIndex: savedIndex !== null ? parseInt(savedIndex) : 0,
            year: savedYear || new Date().getFullYear().toString()
        };
        
        console.log('State loaded: Department Index ' + state.deptIndex + ', Year ' + state.year);
        return state;
    } catch (e) {
        console.error('Error loading dashboard state:', e);
        return {
            deptIndex: 0,
            year: new Date().getFullYear().toString()
        };
    }
}

// ============================================
// FETCH DASHBOARD DATA FROM API
// ============================================
async function fetchDashboardData(year) {
    try {
        console.log('Fetching data for year: ' + year);
        
        const response = await fetch(`http://localhost:8000/api/student/dashboard/statistics?year=${year}`);
        const result = await response.json();

        if (result.success) {
            const data = result.data;
            departments = data.departments;
            
            if (departments.length === 0) {
                console.warn('No departments found');
                return false;
            }
            
            console.log('Found ' + departments.length + ' departments:', departments);
            
            departmentsData = {};
            
            // Group data by department
            departments.forEach(dept => {
                const deptResponseTime = data.responseTime.filter(r => r.department === dept);
                const deptAcceptanceRate = data.acceptanceRate.filter(r => r.department === dept);
                
                const programs = deptResponseTime.map(r => r.program);
                const responseTimes = deptResponseTime.map(r => parseFloat(r.avgResponseTimeKoor)); 
                const acceptanceRates = deptAcceptanceRate.map(r => [r.acceptanceRate, r.rejectionRate]);
                
                departmentsData[dept] = {
                    programs: programs,
                    responseTime: responseTimes,
                    acceptanceRates: acceptanceRates,
                    hasData: deptResponseTime.some(r => r.hasData) || deptAcceptanceRate.some(r => r.hasData)
                };
                
                console.log('  ' + dept + ': ' + programs.length + ' programs, Has Data: ' + departmentsData[dept].hasData);
            });
            
            return true;
        } else {
            console.error('API Error:', result.message);
            return false;
        }
    } catch (error) {
        console.error('Fetch Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Connection Error',
            text: 'Failed to load dashboard data. Please check your connection.',
            confirmButtonColor: '#dc3545'
        });
        return false;
    }
}

// ============================================
// BAR CHART - Response Time
// ============================================
function createResponseChart(labels, data) {
    const canvas = document.getElementById('responseChart');
    if (!canvas) {
        console.error('Canvas responseChart not found!');
        return;
    }

    // Destroy previous chart
    if (responseChart) {
        try {
            responseChart.destroy();
        } catch (e) {
            console.error('Error destroying chart:', e);
        }
        responseChart = null;
    }

    // Clear canvas
    canvas.removeAttribute('width');
    canvas.removeAttribute('height');
    canvas.removeAttribute('style');

    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // Calculate dimensions
    const barHeight = 80;
    const minHeight = 400;
    const chartHeight = Math.max(minHeight, labels.length * barHeight);
    const containerWidth = canvas.parentElement.offsetWidth;

    // Set canvas size
    canvas.width = containerWidth;
    canvas.height = chartHeight;
    canvas.style.width = '100%';
    canvas.style.height = chartHeight + 'px';

    // Create chart
    responseChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Average Response Time (Days)',
                data: data,
                backgroundColor: '#4e73df',
                borderRadius: 5,
                barThickness: 55
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: false,
            maintainAspectRatio: false,
            scales: {
                x: {
                    beginAtZero: true,
                    max: Math.max(7, Math.ceil(Math.max(...data) * 1.1)),
                    ticks: { stepSize: 1, font: { size: 11 } },
                    title: { display: true, text: 'Days', font: { size: 12, weight: 'bold' } },
                    grid: { color: '#e9ecef' }
                },
                y: {
                    ticks: { color: '#495057', font: { size: 11 }, autoSkip: false },
                    grid: { display: false }
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    padding: 10,
                    callbacks: {
                        label: (context) => `Response Time: ${context.parsed.x.toFixed(2)} days`
                    }
                }
            }
        }
    });
    
    console.log('Bar chart created: ' + labels.length + ' programs');
}

// ============================================
// PIE CHARTS - Acceptance Rate
// ============================================
function updatePieCharts(pieData) {
    const container = document.getElementById('pieContainer');
    if (!container) {
        console.error('Pie container not found!');
        return;
    }

    // Destroy old charts
    pieCharts.forEach((c, index) => {
        try {
            c.destroy();
        } catch (e) {
            console.error('Error destroying pie chart ' + index + ':', e);
        }
    });
    pieCharts = [];

    // Clear container
    container.innerHTML = '';

    // Create new pie charts
    pieData.forEach((item, i) => {
        const pieDiv = document.createElement('div');
        pieDiv.className = 'pie-item mb-3';

        const title = document.createElement('div');
        title.className = 'text-center font-weight-bold mb-2';
        title.style.fontSize = '13px';
        title.textContent = item.name;

        const canvas = document.createElement('canvas');
        canvas.id = `pie-${i}`;

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
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        padding: 10,
                        callbacks: {
                            label: (ctx) => `${ctx.label}: ${ctx.formattedValue}%`
                        }
                    }
                }
            }
        });

        pieCharts.push(chart);
    });
    
    console.log('Created ' + pieData.length + ' pie charts');
}

// ============================================
// NAVIGATION FUNCTIONS
// ============================================
function navigateDepartment(dir) {
    currentDeptIndex += dir;
    if (currentDeptIndex < 0) currentDeptIndex = 0;
    if (currentDeptIndex >= departments.length) currentDeptIndex = departments.length - 1;

    console.log('Navigating to department index: ' + currentDeptIndex);

    // SAVE state immediately
    saveDashboardState();

    updateDashboard();
    updateNavButtons();

    // Scroll to chart
    const chartRow = document.querySelector('#responseChart')?.closest('.row');
    if (chartRow) {
        chartRow.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function updateNavButtons() {
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');

    if (prevBtn) prevBtn.disabled = currentDeptIndex === 0;
    if (nextBtn) nextBtn.disabled = currentDeptIndex === departments.length - 1;
}

function updateDashboard() {
    if (departments.length === 0) {
        const currentDeptEl = document.getElementById('currentDept');
        if (currentDeptEl) currentDeptEl.textContent = 'No Data Available';
        
        showNoDataState();
        return;
    }
    
    const dept = departments[currentDeptIndex];
    const data = departmentsData[dept];

    console.log('Updating dashboard for: ' + dept + ' (index ' + currentDeptIndex + ')');

    // Update department title
    const currentDeptEl = document.getElementById('currentDept');
    if (currentDeptEl) {
        currentDeptEl.textContent = dept + ' Department';
    }

    // Check if department has programs
    if (!data || !data.programs || data.programs.length === 0) {
        console.log('No programs found for ' + dept);
        showNoDataState();
        return;
    }

    // Render charts
    console.log('Rendering charts for ' + dept + ': ' + data.programs.length + ' programs');
    createResponseChart(data.programs, data.responseTime);

    const pies = data.programs.map((p, i) => ({
        name: p,
        data: data.acceptanceRates[i] || [0, 0]
    }));
    updatePieCharts(pies);

    const pieTitle = document.getElementById('pieTitle');
    if (pieTitle) {
        pieTitle.textContent = 'Internship Acceptance Rate — ' + dept;
    }
}

function showNoDataState() {
    // Destroy bar chart
    if (responseChart) {
        try {
            responseChart.destroy();
        } catch (e) {}
        responseChart = null;
    }

    // Destroy pie charts
    pieCharts.forEach(c => {
        try {
            c.destroy();
        } catch (e) {}
    });
    pieCharts = [];

    // Clear bar chart canvas
    const responseCanvas = document.getElementById('responseChart');
    if (responseCanvas) {
        const ctx = responseCanvas.getContext('2d');
        responseCanvas.width = responseCanvas.parentElement.offsetWidth;
        responseCanvas.height = 400;
        responseCanvas.style.width = '100%';
        responseCanvas.style.height = '400px';
        ctx.clearRect(0, 0, responseCanvas.width, responseCanvas.height);
    }
    
    // Clear pie container
    const pieContainer = document.getElementById('pieContainer');
    if (pieContainer) {
        pieContainer.innerHTML = '';
    }
    
    console.log('Blank state rendered');
}

// ============================================
// FILTER BY YEAR - KEEP CURRENT DEPARTMENT
// ============================================
async function filterByYear() {
    const yearSelect = document.getElementById('yearFilter');
    if (!yearSelect) {
        console.error('Year filter not found!');
        return;
    }
    
    const selectedYear = yearSelect.value === 'all' ? new Date().getFullYear() : yearSelect.value;
    
    console.log('Filter changed to year: ' + selectedYear);
    console.log('Current department before filter: ' + (departments[currentDeptIndex] || 'N/A') + ' (index ' + currentDeptIndex + ')');
    
    // Show loading indicator
    const currentDeptEl = document.getElementById('currentDept');
    const originalText = currentDeptEl ? currentDeptEl.textContent : '';
    if (currentDeptEl) {
        currentDeptEl.textContent = 'Loading...';
    }
    
    // CRITICAL: Remember current department NAME (not just index)
    const currentDeptName = departments[currentDeptIndex];
    
    // Fetch new data
    const success = await fetchDashboardData(selectedYear);
    
    if (success && departments.length > 0) {
        // TRY to find the same department in new data
        const newIndex = departments.findIndex(dept => dept === currentDeptName);
        
        if (newIndex !== -1) {
            // Department exists in new year data - STAY at this department
            currentDeptIndex = newIndex;
            console.log('Department "' + currentDeptName + '" found at index ' + newIndex + ' - STAYING HERE');
        } else {
            // Department doesn't exist in new year - go to first department
            currentDeptIndex = 0;
            console.log('Department "' + currentDeptName + '" not found in year ' + selectedYear + ' - Reset to first department');
        }
        
        // SAVE state to localStorage
        saveDashboardState();
        
        // Update UI
        updateDashboard();
        updateNavButtons();
        
        console.log('Filter complete: Now at ' + departments[currentDeptIndex] + ' (index ' + currentDeptIndex + ')');
    } else {
        // No data found
        departments = [];
        departmentsData = {};
        currentDeptIndex = 0;
        
        if (currentDeptEl) {
            currentDeptEl.textContent = 'No Data Available';
        }
        
        showNoDataState();
        updateNavButtons();
        
        console.log('No data available for selected year');
    }
}

// ============================================
// NAVIGATION UI SETUP
// ============================================
function setupNavigationUI() {
    let currentDeptElement = document.getElementById('currentDept');
    if (!currentDeptElement) {
        console.warn('Element #currentDept not found');
        return;
    }

    let deptNavContainer = currentDeptElement.parentElement;
    deptNavContainer.className = 'department-nav d-flex align-items-center';
    deptNavContainer.style.cssText = 'gap: 10px;';

    const prevBtn = document.createElement('button');
    prevBtn.id = 'prevBtn';
    prevBtn.className = 'btn btn-outline-primary';
    prevBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
    prevBtn.title = 'Previous Department';
    prevBtn.style.cssText = 'width: 45px; height: 45px; padding: 0;';

    const nextBtn = document.createElement('button');
    nextBtn.id = 'nextBtn';
    nextBtn.className = 'btn btn-outline-primary';
    nextBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
    nextBtn.title = 'Next Department';
    nextBtn.style.cssText = 'width: 45px; height: 45px; padding: 0;';

    currentDeptElement.className = 'current-dept font-weight-bold text-dark bg-light px-4 py-2 rounded text-center';
    currentDeptElement.style.cssText = 'min-width: 350px; max-width: 450px; margin: 0;';

    deptNavContainer.insertBefore(prevBtn, currentDeptElement);
    deptNavContainer.appendChild(nextBtn);

    prevBtn.addEventListener('click', () => navigateDepartment(-1));
    nextBtn.addEventListener('click', () => navigateDepartment(1));
}

// ============================================
// BROWSER SECURITY
// ============================================
function setupBrowserSecurity() {
    window.addEventListener("pageshow", function(event) {
        if (event.persisted) window.location.reload();
    });
    
    window.history.pushState(null, "", window.location.href);
    window.onpopstate = function() {
        window.history.pushState(null, "", window.location.href);
    };
}

// ============================================
// INITIALIZATION
// ============================================
$(document).ready(function() {
    clock_run();
    show_calendar();
});

document.addEventListener('DOMContentLoaded', async function() {
    console.log('Dashboard initialization started');
    
    initSidebarHighlight();
    setupNavigationUI();
    setupBrowserSecurity();
    
    // Load saved state
    const savedState = loadDashboardState();
    
    // Set year filter to saved value
    const yearSelect = document.getElementById('yearFilter');
    if (yearSelect) {
        yearSelect.value = savedState.year;
        console.log('Year filter set to: ' + savedState.year);
    }
    
    // Show loading
    const currentDeptEl = document.getElementById('currentDept');
    if (currentDeptEl) {
        currentDeptEl.textContent = 'Loading...';
    }
    
    // Fetch data with saved year
    const success = await fetchDashboardData(savedState.year);
    
    if (success && departments.length > 0) {
        // Validate saved index
        if (savedState.deptIndex >= 0 && savedState.deptIndex < departments.length) {
            currentDeptIndex = savedState.deptIndex;
            console.log('Restored to department: ' + departments[currentDeptIndex] + ' (index ' + currentDeptIndex + ')');
        } else {
            currentDeptIndex = 0;
            console.log('Saved index ' + savedState.deptIndex + ' out of bounds - Reset to first department');
        }
        
        // Render dashboard
        updateDashboard();
        updateNavButtons();
        
        console.log('Dashboard initialized successfully');
    } else {
        // No data
        currentDeptIndex = 0;
        
        if (currentDeptEl) {
            currentDeptEl.textContent = 'No Data Available';
        }
        
        showNoDataState();
        updateNavButtons();
        
        console.log('Dashboard initialized with no data');
    }
});
</script>

</body>

</html>