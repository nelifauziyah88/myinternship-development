<?php
session_start();

if (!isset($_SESSION['student']) || empty($_SESSION['student']['nim'])) {
    header('Location: role_login.php');
    exit;
}

$student = $_SESSION['student'];
$user = $student;

$id_letter = $_GET['id'] ?? null;
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
    <title>Internship Acceptance Confirmation Form</title>
    <meta content='width=device-width, initial-scale=1.0, shrink-to-fit=no' name='viewport' />
    <!-- Icon -->
    <link rel="icon" href="./assets/img/iconM.png" type="image/x-icon" />
    <link href="./assets/img/iconM.png" rel="apple-touch-icon" type="image/x-icon">

    <link rel='stylesheet' href='./core/component/sweetalert2.min.css'>

    <!-- Tambahkan di <head> -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

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
        .notification-icon {
            position: relative;
        }

        .custom-notification-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 10px;
            line-height: 1;
            min-width: 16px;
            text-align: center;
        }

        .fc-sun {
            color: red;
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
            color: white !important;
            border-radius: 10px;
        }

        .sidebar a.active i {
            color: white;
        }

        .form-container {
            background: #fff;
            border-radius: 6px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
            padding: 40px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            font-weight: 500;
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            color: #333;
        }

        .form-control {
            background: #e9ecef;
            border: none;
            border-radius: 6px;
            height: 38px;
            padding: 8px 12px;
            font-size: 14px;
        }

        .form-control:focus {
            background: #e9ecef;
            outline: none;
            box-shadow: 0 0 0 2px rgba(30, 115, 232, 0.2);
        }

        .btn-submit {
            background-color: #2563eb;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-submit:hover {
            background-color: #1d4ed8;
        }

        .btn-back {
            background-color: #f3f4f6;
            color: #374151;
            border: 1.5px solid #d1d5db;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-back:hover {
            background-color: #e5e7eb;
        }

        .dropzone {
            width: 70%;
            padding: 40px;
            border: 2px dashed #4a9fb8;
            border-radius: 8px;
            background-color: #e8f4f8;
            text-align: center;
            cursor: pointer;
            transition: 0.2s;
        }

        .dropzone.dragover {
            background-color: #d4eef3;
            border-color: #3fa2b5;
        }

        .dz-icon {
            font-size: 48px;
            margin-bottom: 15px;
            color: #4a9fb8;
        }

        .dz-title {
            font-size: 20px;
            color: #4a9fb8;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .dz-subtitle {
            font-size: 14px;
            color: #7a8a92;
            font-weight: normal;
        }

        .file-list {
            width: 70%;
            margin-top: 15px;
            padding: 15px;
            border-radius: 10px;
            background: #f7f9fa;
            border: 1px solid #d8e2e6;
        }

        .file-item {
            padding: 10px;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .progress-bar {
            width: 100%;
            height: 6px;
            border-radius: 5px;
            background-color: #d7dfe4;
            margin-top: 5px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            background-color: #1f6feb;
            width: 0%;
            transition: width 0.3s;
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
                    <img src="assets/img/logo_header.png" alt="navbar brand" class="navbar-brand" style="width: 180px; height: auto;">
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
                                    <img src="assets/img/profile.png" alt="..." class="avatar-img rounded-circle">
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-user animated fadeIn">
                                <div class="dropdown-user-scroll scrollbar-outer">
                                    <li>
                                        <div class="user-box">
                                            <div class="avatar-lg"><img src="assets/img/profile.png" alt="image profile" class="avatar-img rounded"></div>
                                            <div class="u-text">
                                                <h5><?= htmlspecialchars($user['name']) ?></h5>
                                                <p class="text-muted">Mahasiswa</p>
                                                <a href="index.php?page=industry_profile" class="btn btn-xs btn-secondary btn-sm">View Profile</a>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#">My Profile</a>
                                        <a class="dropdown-item" href="#">My Internship</a>
                                        <!-- <a class="dropdown-item" href="#">Inbox</a> -->
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="landing_page.php" onclick="logout_confirm()">Logout</a>
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
                            <img src="assets/img/profile.png" alt="..." class="avatar-img rounded-circle">
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
                        <li class="nav-item">
                            <a href="dashboard_student.php">
                                <i class="fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="landing_page.php">
                                <i class="fas fa-home"></i>
                                <p>Home</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="#">
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
                            <a href="#">
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

                        <li class="nav-item active">
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
                            <a href="landing_page.php" onclick="logout_confirm()" class="collapsed" aria-expanded="false">
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
                            <h1 class="text-white pb-2 fw-bold">Internship Acceptance Confirmation Form</h1>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="page-inner mt--5">
                <div class="row mt--2">
                    <div class="col-md-12">
                        <div class="form-container">
                            <form id="acceptedForm" method="POST" enctype="multipart/form-data">
                                <!-- hidden helper fields (important) -->
                                 <input type="hidden" name="id_letter" value="<?= $id_letter ?>">
                                <input type="hidden" name="company_not_exist" value="0"> <!-- akan di-update oleh JS -->
                                <input type="hidden" name="id_company" value=""> <!-- id_company jika company sudah ada -->
                                <input type="hidden" name="company_name_hidden" value="">
                                <input type="hidden" name="company_address_hidden" value="">
                                <input type="hidden" name="city_hidden" value="">
                                <input type="hidden" name="province_hidden" value="">
                                <input type="hidden" name="country_hidden" value="">
                                <input type="hidden" name="hrd_email_hidden" value="">
                                <input type="hidden" name="hrd_name_hidden" value="">
                                <input type="hidden" name="hrd_whatsapp_hidden" value="">

                                <!-- Auto fill readonly -->
                                <div class="form-group">
                                    <label>NIM<span style="color:red;">*</span></label>
                                    <input type="text" name="nim" class="form-control" readonly style="width:70%;">
                                </div>

                                <div class="form-group">
                                    <label>Name<span style="color:red;">*</span></label>
                                    <input type="text" name="student_name" class="form-control" readonly style="width:70%;">
                                </div>

                                <div class="form-group">
                                    <label>Study Program<span style="color:red;">*</span></label>
                                    <input type="text" name="study_program" class="form-control" readonly style="width:70%;">
                                </div>

                                <div class="form-group">
                                    <label>Department<span style="color:red;">*</span></label>
                                    <input type="text" name="department" class="form-control" readonly style="width:70%;">
                                </div>

                                <div class="form-group">
                                    <label style="margin-bottom: 10px;">Class<span style="color:red;">*</span></label>
                                    <div class="radio-group">
                                        <!-- value sama persis dengan DB -->
                                        <label><input type="radio" name="class" value="REGULAR CLASS"> Regular class</label>
                                        <label><input type="radio" name="class" value="EVENING CLASS"> Evening class</label>
                                    </div>
                                </div>

                                <div class="form-group" style="display: flex; align-items: center; gap: 10px;">
                                    <label style="margin: 0;">Semester<span style="color:red;">*</span></label>
                                    <input name="semester" class="form-control" style="width:80px; text-align-last:center;" readonly>
                                    </input>
                                </div>

                                <div class="form-group">
                                    <label>Internship Coordinator<span style="color:red;">*</span></label>
                                    <input type="text" name="internship_coordinator" class="form-control" readonly style="width:70%;">
                                </div>

                                <div class="form-group">
                                    <label>Company Name <span style="color:red;">*</span></label>
                                    <input type="text" name="company_name" class="form-control" placeholder="Enter company name" style="width:70%; background:#e9ecef;">
                                </div>

                                <div class="form-group">
                                    <label>Company Address <span style="color:red;">*</span></label>
                                    <input type="text" name="company_address" class="form-control" placeholder="Enter company address" style="width:70%;">
                                </div>

                                <div class="form-group">
                                    <label style="margin-bottom: 10px;">City <span style="color:red;">*</span></label>
                                    <div class="radio-options">
                                        <label><input type="radio" name="city" value="batam"> Batam</label>
                                        <label><input type="radio" name="city" value="tanjung_pinang"> Tanjung Pinang</label>
                                        <label><input type="radio" name="city" value="tanjung_balai"> Tanjung Balai Karimun</label>
                                        <label><input type="radio" name="city" value="other"> Other</label>
                                    </div>
                                    <input type="text" name="city_other" class="form-control"
                                        placeholder="Please type another option here"
                                        style="width:70%; margin-top: 10px; display:none;">
                                </div>

                                <div class="form-group">
                                    <label style="margin-bottom: 10px;">Province <span style="color:red;">*</span></label>
                                    <div class="radio-options">
                                        <label><input type="radio" name="province" value="riau_islands"> Riau Islands</label>
                                        <label><input type="radio" name="province" value="other"> Other</label>
                                    </div>
                                    <input type="text" name="province_other" class="form-control"
                                        placeholder="Please type another option here"
                                        style="width:70%; margin-top: 10px; display:none;">
                                </div>

                                <div class="form-group">
                                    <label style="margin-bottom: 10px;">Country <span style="color:red;">*</span></label>
                                    <div class="radio-options">
                                        <label><input type="radio" name="country" value="indonesia"> Indonesia</label>
                                        <label><input type="radio" name="country" value="overseas"> Overseas</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>HRD Email <span style="color:red;">*</span></label>
                                    <input type="email" name="hrd_email" class="form-control" placeholder="Enter HRD email" style="width:70%;">
                                </div>

                                <div class="form-group">
                                    <label>HRD Name <span style="color:red;">*</span></label>
                                    <input type="text" name="hrd_name" class="form-control" placeholder="Enter HRD name" style="width:70%;">
                                </div>

                                <div class="form-group">
                                    <label>Active HR Department WhatsApp Number <span style="color:red;">*</span></label>
                                    <input type="text" name="hrd_whatsapp" class="form-control" placeholder="Enter HR WhatsApp number" style="width:70%;">
                                </div>

                                <div class="form-group">
                                    <label>Placement Department/Division <span style="color:red;">*</span></label>
                                    <input type="text" name="placement_department" class="form-control" placeholder="Enter department/division" style="width:70%;">
                                </div>

                                <div class="form-group" style="display:flex; gap:15px;">
                                    <div style="flex:1; max-width: calc(35% - 7.5px);">
                                        <label>Start Date <span style="color:red;">*</span></label>
                                        <input type="date" name="start_date" class="form-control">
                                    </div>
                                    <div style="flex:1; max-width: calc(35% - 7.5px);">
                                        <label>End Date <span style="color:red;">*</span></label>
                                        <input type="date" name="end_date" class="form-control">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label style="margin-bottom: 12px;">How did you get the internship information? <span style="color:red;">*</span></label>
                                    <div class="radio-options">
                                        <label><input type="radio" name="info_source" value="self-observation"> Self-observation</label>
                                        <label><input type="radio" name="info_source" value="cdc"> From CDC Polibatam</label>
                                        <label><input type="radio" name="info_source" value="coordinator"> From Coordinator</label>
                                        <label><input type="radio" name="info_source" value="myinternship"> From MyInternship</label>
                                        <label><input type="radio" name="info_source" value="workplace"> Interning at the Workplace</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Email <span style="color:red;">*</span></label>
                                    <input type="email" name="email" class="form-control" placeholder="Enter your email" style="width:70%;">
                                </div>

                                <div class="form-group">
                                    <label>Active WhatsApp Number <span style="color:red;">*</span></label>
                                    <input type="text" name="whatsapp" class="form-control" placeholder="Enter WhatsApp number" style="width:70%;">
                                </div>

                                <div class="form-group" style="margin-bottom: 30px;">
                                    <label style="margin-bottom: 10px;">
                                        Internship Response Letter / Proof of Acceptance <span style="color:red;">*</span>
                                    </label>

                                    <!-- DROPZONE -->
                                    <div id="dropzone" class="dropzone">
                                        <div class="dz-content">
                                            <div class="dz-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                            <div class="dz-title">Browse Files</div>
                                            <div class="dz-subtitle">Click or drag a PDF file here</div>
                                        </div>
                                    </div>

                                    <!-- HIDDEN FILE INPUT -->
                                    <input type="file" id="fileInput" name="attachment" accept="application/pdf" style="display:none;">

                                    <!-- FILE LIST PREVIEW -->
                                    <div id="fileList" class="file-list" style="display:none;"></div>
                                </div>

                                <div style="display:flex; justify-content: space-between; margin-top: 30px;">
                                    <button type="button" class="btn-back" onclick="window.history.back()">
                                        <i class="fas fa-arrow-left" style="margin-right:6px;"></i> Back
                                    </button>
                                    <button type="submit" class="btn-submit">
                                        Claim Internship <i class="fas fa-arrow-right" style="margin-left:6px;"></i>
                                    </button>
                                </div>
                            </form>

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
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

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

            <script>
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
                        fixedWeekCount: false,
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

                function logout_confirm() {

                    let _token = $('meta[name="csrf-token"]').attr('content');

                    swal.fire({
                        title: 'Logout Confirmation',
                        text: 'Are you sure you want end current session ?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: "#DD6B55",
                        confirmButtonText: "Yes, I'm sure !",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // AJAX
                            $.ajax({
                                url: "index.php?request=logout",
                                type: "POST",
                                data: {
                                    'token': _token
                                },

                                success: function() {
                                    setTimeout(function() {
                                        localStorage.setItem('first', null);
                                        localStorage.setItem('first_chime', null);
                                        localStorage.setItem('next_chime', null);
                                        window.location.href = 'index.php';
                                    }, 200);
                                },
                            });
                        }
                    })
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
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const nim = <?= json_encode($student['nim']) ?>;
            const API_BASE = "http://localhost:8000/api/student";

            const form = document.getElementById("acceptedForm");

            const safeSet = (selector, value) => {
                const el = form.querySelector(selector);
                if (el) el.value = value;
            };

            const setRadioChecked = (name, value) => {
                const el = form.querySelector(`input[name="${name}"][value="${value}"]`);
                if (el) el.checked = true;
            };

            const setRadioDisabled = (name, disabled) => {
                form.querySelectorAll(`input[name="${name}"]`).forEach(i => i.disabled = disabled);
            };

            const normalizeValue = (str) => {
                if (!str) return "";
                const val = str.trim().toLowerCase();
                if (val.includes("batam")) return "batam";
                if (val.includes("tanjung pinang")) return "tanjung_pinang";
                if (val.includes("tanjung balai")) return "tanjung_balai";
                if (val.includes("riau")) return "riau_islands";
                if (val.includes("indonesia")) return "indonesia";
                if (val.includes("overseas")) return "overseas";
                return val;
            };

            const updateHiddenMirrors = (d) => {
                if (!d) return;
                form.querySelector("input[name='company_name_hidden']").value = d.company_name || "";
                form.querySelector("input[name='company_address_hidden']").value = d.company_address || "";
                form.querySelector("input[name='city_hidden']").value = d.city || "";
                form.querySelector("input[name='province_hidden']").value = d.province || "";
                form.querySelector("input[name='country_hidden']").value = d.country || "";
                form.querySelector("input[name='hrd_email_hidden']").value = d.hrd_email || "";
                form.querySelector("input[name='hrd_name_hidden']").value = d.hrd_name || "";
                form.querySelector("input[name='hrd_whatsapp_hidden']").value = d.hrd_whatsapp || "";
                form.querySelector("input[name='id_company']").value = d.id_company || "";
                form.querySelector("input[name='company_not_exist']").value = d.company_not_exist ? String(d.company_not_exist) : "0";
            };

            // === SHOW/HIDE city_other based on radio ===
            form.querySelectorAll("input[name='city']").forEach(r => {
                r.addEventListener("change", () => {
                    const isOther = r.value === "other";
                    const cityOther = form.querySelector("input[name='city_other']");
                    cityOther.style.display = isOther ? "block" : "none";
                    if (!isOther) cityOther.value = "";
                });
            });

            // === SHOW/HIDE province_other based on radio ===
            form.querySelectorAll("input[name='province']").forEach(r => {
                r.addEventListener("change", () => {
                    const isOther = r.value === "other";
                    const provOther = form.querySelector("input[name='province_other']");
                    provOther.style.display = isOther ? "block" : "none";
                    if (!isOther) provOther.value = "";
                });
            });


            // === AUTO-FILL ===
            fetch(`${API_BASE}/accepted-by-company/autofill/${nim}`)
                .then(res => res.json())
                .then(data => {
                    if (!data.success) return console.error("Autofill failed:", data.message);

                    const d = data.data;

                    // basic info
                    safeSet("input[name='nim']", d.nim);
                    safeSet("input[name='student_name']", d.name);
                    safeSet("input[name='study_program']", d.study_program_display);
                    safeSet("input[name='department']", d.department);
                    safeSet("input[name='semester']", d.semester);
                    safeSet("input[name='internship_coordinator']", d.coordinator_name);

                    // CLASS (radio)
                    if (d.class) {
                        setRadioChecked("class", d.class);
                        setRadioDisabled("class", true);
                    }

                    // COMPANY
                    safeSet("input[name='company_name']", d.company_name);
                    safeSet("input[name='company_address']", d.company_address);

                    // === CITY / PROVINCE / COUNTRY ===
                    const cityVal = normalizeValue(d.city);
                    const provVal = normalizeValue(d.province);
                    const countryVal = normalizeValue(d.country);
                    if (cityVal) setRadioChecked("city", cityVal);
                    if (provVal) setRadioChecked("province", provVal);
                    if (countryVal) setRadioChecked("country", countryVal);

                    // Show text input jika value existing adalah "other"
                    const cityOther = form.querySelector("input[name='city_other']");
                    if (cityVal === "other") {
                        cityOther.style.display = "block";
                    } else {
                        cityOther.style.display = "none";
                    }

                    const provOther = form.querySelector("input[name='province_other']");
                    if (provVal === "other") {
                        provOther.style.display = "block";
                    } else {
                        provOther.style.display = "none";
                    }

                    // === HRD ===
                    safeSet("input[name='hrd_email']", d.hrd_email);
                    safeSet("input[name='hrd_name']", d.hrd_name);
                    safeSet("input[name='hrd_whatsapp']", d.hrd_whatsapp);

                    // === Email & WhatsApp (Mahasiswa) ===
                    safeSet("input[name='email']", d.email);
                    safeSet("input[name='whatsapp']", d.no_whatsapp);

                    // === Start / End Date ===
                    const formatDate = (val) => {
                        if (!val) return "";
                        const date = new Date(val);
                        // deteksi apakah val sudah dalam bentuk string 'YYYY-MM-DD'
                        if (/^\d{4}-\d{2}-\d{2}$/.test(val)) return val;
                        return date.toISOString().split("T")[0];
                    };

                    safeSet("input[name='start_date']", formatDate(d.start_date));
                    safeSet("input[name='end_date']", formatDate(d.end_date));

                    updateHiddenMirrors(d);

                    // === Company existence behavior ===
                    const compNotExist = Number(d.company_not_exist) === 1 ? 1 : 0;
                    if (compNotExist === 0) {
                        // readonly mode
                        ["company_name", "company_address", "hrd_email", "hrd_name", "hrd_whatsapp"].forEach(name => {
                            const el = form.querySelector(`input[name='${name}']`);
                            if (el) el.readOnly = true;
                        });
                        setRadioDisabled("city", true);
                        setRadioDisabled("province", true);
                        setRadioDisabled("country", true);

                        // City and Province field is disable
                        const provinceOther = form.querySelector("input[name='province_other']");
                        if (provinceOther) {
                            provinceOther.readOnly = true;
                            provinceOther.style.backgroundColor = "#f5f5f5";
                            provinceOther.style.display = "none";
                        }

                        const cityOther = form.querySelector("input[name='city_other']");
                        if (cityOther) {
                            cityOther.readOnly = true;
                            cityOther.style.backgroundColor = "#f5f5f5";
                            cityOther.style.display = "none";
                        }

                    } else {
                        // editable mode
                        ["company_name", "company_address", "hrd_email", "hrd_name", "hrd_whatsapp"].forEach(name => {
                            const el = form.querySelector(`input[name='${name}']`);
                            if (el) el.readOnly = false;
                        });
                        setRadioDisabled("city", false);
                        setRadioDisabled("province", false);
                        setRadioDisabled("country", false);

                        // Open field if student input new company
                        const provinceOther = form.querySelector("input[name='province_other']");
                        if (provinceOther) {
                            provinceOther.readOnly = false;
                            provinceOther.style.backgroundColor = "";
                        }

                        const cityOther = form.querySelector("input[name='city_other']");
                        if (cityOther) {
                            cityOther.readOnly = false;
                            cityOther.style.backgroundColor = "";
                        }
                    }

                    // === VALIDASI START-END DATE ===
                    const startInput = form.querySelector("input[name='start_date']");
                    const endInput = form.querySelector("input[name='end_date']");

                    const adjustDateLimits = () => {
                        if (startInput.value) {
                            const minEnd = new Date(startInput.value);
                            minEnd.setDate(minEnd.getDate() + 1);
                            endInput.min = minEnd.toISOString().split("T")[0];
                        }
                        if (endInput.value) {
                            const maxStart = new Date(endInput.value);
                            maxStart.setDate(maxStart.getDate() - 1);
                            startInput.max = maxStart.toISOString().split("T")[0];
                        }
                    };

                    startInput.addEventListener("change", adjustDateLimits);
                    endInput.addEventListener("change", adjustDateLimits);
                    adjustDateLimits();
                })
                .catch(err => console.error("Autofill error:", err));

            const dropzone = document.getElementById("dropzone");
            const fileInput = document.getElementById("fileInput");
            const fileList = document.getElementById("fileList");

            // ========== CLICK ⇒ OPEN FILE EXPLORER ==========
            dropzone.addEventListener("click", () => fileInput.click());

            // ========== CHANGE (file chosen manually) ==========
            fileInput.addEventListener("change", (e) => {
                handleFile(e.target.files[0]);
            });

            // ========== DRAG OVER ==========
            dropzone.addEventListener("dragover", (e) => {
                e.preventDefault();
                dropzone.classList.add("dragover");
            });

            // ========== DRAG LEAVE ==========
            dropzone.addEventListener("dragleave", () => {
                dropzone.classList.remove("dragover");
            });

            // ========== DROP FILE ==========
            dropzone.addEventListener("drop", (e) => {
                e.preventDefault();
                dropzone.classList.remove("dragover");

                const file = e.dataTransfer.files[0];
                handleFile(file);
            });

            // ========== HANDLE THE FILE ==========
            function handleFile(file) {
                if (!file) return;

                if (file.type !== "application/pdf") {
                    alert("Only PDF files are allowed.");
                    return;
                }

                const dt = new DataTransfer();
                dt.items.add(file);
                fileInput.files = dt.files;

                dropzone.style.display = "none";
                fileList.style.display = "block";

                fileList.innerHTML = `
        <div class="file-item">
            ${file.name}
            <span class="delete-file" style="color:red; cursor:pointer; margin-left:auto; font-weight:bold;">✕</span>
        </div>
        <div class="progress-bar">
            <div class="progress-bar-fill" id="uploadProgress"></div>
        </div>
    `;

                simulateProgress();

                // DELETE BUTTON
                document.querySelector(".delete-file").addEventListener("click", () => {
                    fileInput.value = "";
                    fileList.style.display = "none";
                    dropzone.style.display = "block";
                });
            }


            // ========== Fake progress bar (optional) ==========
            function simulateProgress() {
                const bar = document.getElementById("uploadProgress");
                let width = 0;

                const timer = setInterval(() => {
                    width += 10;
                    bar.style.width = width + "%";

                    if (width >= 100) {
                        clearInterval(timer);
                        bar.parentElement.style.display = "none";
                    }
                }, 80);
            }

            // === SUBMISSION ===
            const id_letter = form.querySelector("[name='id_letter']").value;

            form.addEventListener("submit", async (e) => {
                e.preventDefault();

                // === DATE VALIDATION ===
                const startVal = new Date(form.querySelector("input[name='start_date']").value);
                const endVal = new Date(form.querySelector("input[name='end_date']").value);
                if (startVal >= endVal) {
                    return Swal.fire({
                        icon: "error",
                        title: "Invalid Dates",
                        text: "End date must be after start date."
                    });
                }

                // === REQUIRED ===
                const compNotExist = Number(form.querySelector("input[name='company_not_exist']").value);

                const isEmpty = (selector) => {
                    const el = form.querySelector(selector);
                    return !el || el.value.trim() === "";
                };

                const getChecked = (name) => form.querySelector(`input[name='${name}']:checked`);

                const errors = [];

                // Placement department (always required)
                if (isEmpty("input[name='placement_department']")) errors.push("Placement Department/Division is required.");

                // Start-End-Date (required)
                if (isEmpty("input[name='start_date']")) errors.push("Start date is required.");
                if (isEmpty("input[name='end_date']")) errors.push("End date is required.");

                // Info source (radio)
                if (!getChecked("info_source")) errors.push("Information source is required.");

                // Email & WhatsApp
                if (isEmpty("input[name='email']")) errors.push("Student email is required.");
                if (isEmpty("input[name='whatsapp']")) errors.push("Student WhatsApp number is required.");

                // File attachment
                const fileInput = form.querySelector("input[name='attachment']");
                if (!fileInput.files || fileInput.files.length === 0) errors.push("Please upload the internship response letter.");

                // If company_not_exist = 1, check all manual field
                if (compNotExist === 1) {
                    if (isEmpty("input[name='company_name']")) errors.push("Company name is required.");
                    if (isEmpty("input[name='company_address']")) errors.push("Company address is required.");
                    if (!getChecked("city")) errors.push("City is required.");
                    if (getChecked("city")?.value === "other" && isEmpty("input[name='city_other']"))
                        errors.push("City (other) must be filled.");
                    if (!getChecked("province")) errors.push("Province is required.");
                    if (getChecked("province")?.value === "other" && isEmpty("input[name='province_other']"))
                        errors.push("Province (other) must be filled.");
                    if (!getChecked("country")) errors.push("Country is required.");
                    if (isEmpty("input[name='hrd_email']")) errors.push("HRD email is required.");
                    if (isEmpty("input[name='hrd_name']")) errors.push("HRD name is required.");
                    if (isEmpty("input[name='hrd_whatsapp']")) errors.push("HRD WhatsApp number is required.");
                } else {
                    // company_not_exist = 0 → check empty autofill
                    const autoFields = [{
                            name: "company_name",
                            label: "Company name"
                        },
                        {
                            name: "company_address",
                            label: "Company address"
                        },
                        {
                            name: "hrd_email",
                            label: "HRD email"
                        },
                        {
                            name: "hrd_name",
                            label: "HRD name"
                        },
                        {
                            name: "hrd_whatsapp",
                            label: "HRD WhatsApp"
                        }
                    ];
                    autoFields.forEach(f => {
                        if (isEmpty(`input[name='${f.name}']`))
                            errors.push(`${f.label} (autofill) is missing. Please contact administrator.`);
                    });
                }

                // Stop submit when error occured
                if (errors.length > 0) {
                    return Swal.fire({
                        icon: "error",
                        title: "Incomplete Form",
                        html: `<ul style="text-align:left;">${errors.map(e => `<li>${e}</li>`).join("")}</ul>`
                    });
                }

                // === Submit date if valid ===
                const formData = new FormData(form);
                formData.append("nim", nim);

                try {
                    // 1. Upload file ke PHP dulu
                    const uploadRes = await fetch("upload_company_reply.php", {
                        method: "POST",
                        body: formData, // FormData original berisi file
                    });

                    const uploadData = await uploadRes.json();

                    if (!uploadData.success) {
                        return Swal.fire({
                            icon: "error",
                            title: "Upload Failed",
                            text: uploadData.message || "Unable to upload file."
                        });
                    }

                    // 2. Setelah file berhasil diupload → kirim DATA (tanpa file)
                    const payload = {
                        nim,
                        company_name: form.querySelector("[name='company_name']").value,
                        company_address: form.querySelector("[name='company_address']").value,
                        city: form.querySelector("[name='city']").value,
                        province: form.querySelector("[name='province']").value,
                        country: form.querySelector("[name='country']").value,
                        hrd_email: form.querySelector("[name='hrd_email']").value,
                        hrd_name: form.querySelector("[name='hrd_name']").value,
                        hrd_whatsapp: form.querySelector("[name='hrd_whatsapp']").value,
                        placement_department: form.querySelector("[name='placement_department']").value,
                        start_date: form.querySelector("[name='start_date']").value,
                        end_date: form.querySelector("[name='end_date']").value,
                        info_source: form.querySelector("[name='info_source']").value,
                        email: form.querySelector("[name='email']").value,
                        whatsapp: form.querySelector("[name='whatsapp']").value,
                        company_not_exist: form.querySelector("[name='company_not_exist']").value,

                        // PENTING: path file dari PHP
                        company_reply_letter: uploadData.path
                    };

                    // 3. Kirim ke Node
                    console.log("DEBUG: id_letter =", id_letter);
                    const res = await fetch(`${API_BASE}/accepted-by-company/submit/${id_letter}`, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify(payload),
                    });

                    const data = await res.json();

                    if (data.success) {
                        Swal.fire({
                            icon: "success",
                            title: "Success",
                            text: data.message || "Internship claim submitted successfully!",
                        }).then(() => (window.location.href = "approval_status.php"));
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: data.error || data.message || "Something went wrong"
                        });
                    }
                } catch (err) {
                    console.error("Submission error:", err);
                    Swal.fire({
                        icon: "error",
                        title: "Upload Failed",
                        text: "Unable to send data to the server."
                    });
                }
            });
        });
    </script>

</body>

</html>