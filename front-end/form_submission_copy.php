<?php
session_start();

// Jika tidak ada session student → redirect ke role_login.php
if (!isset($_SESSION['student']) || empty($_SESSION['student']['nim'])) {
    header('Location: role_login.php');
    exit;
}

$student = $_SESSION['student'];
$user = $student;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Form Submission</title>
    <meta content='width=device-width, initial-scale=1.0, shrink-to-fit=no' name='viewport' />

    <link rel="icon" href="./assets/img/iconM.png" type="image/x-icon" />
    <link href="./assets/img/iconM.png" rel="apple-touch-icon" type="image/x-icon">

    <link rel='stylesheet' href='./core/component/sweetalert2.min.css'>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="./assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="./assets/css/atlantis.css">

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

    <script src="./library/ckeditor/ckeditor.js"></script>

    <script src='./core/component/jquery.min.js'></script>
    <script>
        $(function() {});
    </script>
    <script defer src='./core/component/sweetalert2.min.js'></script>
    <script defer src='./core/component/soloalert.js'></script>

    <style type="text/css">
        /* Notification Badge */
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

        /* Calendar & UI Helpers */
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

        /* Scrollbar */
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

        /* Text Wrapping */
        .wrap {
            white-space: normal !important;
            word-wrap: break-word;
            min-width: 140px;
            max-width: 140px;
        }

        .wrap2 {
            white-space: normal !important;
            word-wrap: break-word;
            min-width: 170px;
            max-width: 170px;
        }

        /* Layout */
        .main-panel {
            padding-top: 50px;
        }

        /* Sidebar Active State */
        .sidebar a.active {
            background-color: #007bff;
            color: white !important;
            border-radius: 10px;
        }

        .sidebar a.active i {
            color: white;
        }

        /* SweetAlert */
        .swal2-container {
            z-index: 50;
        }

        /* Required Field Indicator */
        .required {
            color: #d9534f;
            margin-left: 6px;
            font-weight: 700;
        }

        .form-group label {
            display: inline-block;
            margin-bottom: 6px;
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
                            <span class="fw-mediumbold">Calendar</span>
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
                <a href="index.php" class="logo">
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
                                            <h3 id="dateModal">Wed, 08 Oct 2025</h3>
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
                                        <div class="card-body text-center text-accent-1">
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
                            <ul class='dropdown-menu messages-notif-box animated fadeIn' aria-labelledby='notifDropdown'>
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
                                        <a class="dropdown-item" href="index.php">My Dashboard</a>
                                        <a class="dropdown-item" href="index.php?page=industry_profile">My Profile</a>
                                        <a class="dropdown-item" href="index.php?page=my_company">My Company</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="index.php?page=home">Home</a>
                                        <a class="dropdown-item" href="index.php?page=announcements">Announcements</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#" onclick="logout_confirm()">Logout</a>
                                    </li>
                                </div>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>
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
                                    <span class="user-level">Student at <br> Politeknik Negeri Batam</span>
                                </span>
                            </a>
                            <div class="clearfix"></div>
                        </div>
                    </div>

                    <ul class="nav nav-primary">
                        <li class="nav-item">
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

                        <li class="nav-item active">
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
                        <li class="nav-item">
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

        <!-- MAIN PANEL -->
        <div class="main-panel">
            <!-- Header -->
            <div class="panel-header bg-primary-gradient">
                <div class="page-inner py-5">
                    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row">
                        <div>
                            <h1 class="text-white pb-2 fw-bold">Form Submission</h1>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FORM CONTENT -->
            <div class="page-inner mt--5">
                <div class="form-container" style="background: #fff; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.1); padding: 40px;">
                    <form id="submissionForm">
                        
                        <!-- ========== STUDENT INFORMATION ========== -->
                        <h5 style="margin-bottom: 20px; font-weight: 600; color: #333;">Student Information</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label style="font-weight: 500;">NIM</label>
                                    <input type="text" class="form-control" id="nimField" readonly
                                        style="background:#e9ecef;border:1px solid #ddd;border-radius:6px;height:38px;">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label style="font-weight: 500;">Full Name</label>
                                    <input type="text" class="form-control" id="nameField" readonly
                                        style="background:#e9ecef;border:1px solid #ddd;border-radius:6px;height:38px;">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label style="font-weight: 500;">Study Program</label>
                                    <input type="text" class="form-control" id="programField" readonly
                                        style="background:#e9ecef;border:1px solid #ddd;border-radius:6px;height:38px;">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label style="font-weight: 500;">Department</label>
                                    <input type="text" class="form-control" id="departmentField" readonly
                                        style="background:#e9ecef;border:1px solid #ddd;border-radius:6px;height:38px;">
                                </div>
                            </div>
                        </div>

                        <!-- ========== CONTACT INFORMATION ========== -->
                        <h5 style="margin-top: 30px; margin-bottom: 20px; font-weight: 600; color: #333;">Contact Information</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label style="font-weight: 500;">Email <span class="required">*</span></label>
                                    <input type="email" class="form-control" id="emailField"
                                        style="background:#f5f5f5;border:1px solid #ddd;border-radius:6px;height:38px;">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label style="font-weight: 500;">Active WhatsApp Number <span class="required">*</span></label>
                                    <input type="text" class="form-control" id="phoneField" placeholder="e.g., 081234567890"
                                        style="background:#f5f5f5;border:1px solid #ddd;border-radius:6px;height:38px;">
                                </div>
                            </div>
                        </div>

                        <!-- ========== CLASS & SEMESTER ========== -->
                        <h5 style="margin-top: 30px; margin-bottom: 20px; font-weight: 600; color: #333;">Class & Semester</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label style="font-weight: 500;">Class <span class="required">*</span></label><br>
                                    <label style="margin-right: 20px;">
                                        <input type="radio" name="class" id="classRegular" value="REGULAR CLASS"> Regular Class
                                    </label>
                                    <label>
                                        <input type="radio" name="class" id="classEvening" value="EVENING CLASS"> Evening Class
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label style="font-weight: 500;">Semester <span class="required">*</span></label>
                                    <select class="form-control" id="semesterField"
                                        style="background:#f5f5f5;border:1px solid #ddd;border-radius:6px;height:38px;">
                                        <option value="">- Select Semester -</option>
                                        <option value="5">5</option>
                                        <option value="6">6</option>
                                        <option value="7">7</option>
                                        <option value="8">8</option>
                                        <option value="9">9</option>
                                        <option value="10">10</option>
                                        <option value="11">11</option>
                                        <option value="12">12</option>
                                        <option value="13">13</option>
                                        <option value="14">14</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label style="font-weight: 500;">Internship Coordinator</label>
                                    <input type="text" class="form-control" id="coordinatorField" readonly
                                        style="background:#e9ecef;border:1px solid #ddd;border-radius:6px;height:38px;">
                                </div>
                            </div>
                        </div>

                        <!-- ========== COMPANY INFORMATION ========== -->
                        <h5 style="margin-top: 30px; margin-bottom: 20px; font-weight: 600; color: #333;">Company Information</h5>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label style="font-weight: 500;">
                                        Company Name <span id="companySelectStar" class="required">*</span>
                                    </label>
                                    <select id="companySelect" class="form-control" style="width: 100% !important; background:#f5f5f5;">
                                        <option value="">- Select Company -</option>
                                    </select>
                                    <div style="margin-top: 10px;">
                                        <input type="checkbox" id="companyExist">
                                        <label for="companyExist" style="font-weight: 400; margin-left: 5px;">Company not listed?</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- New Company Fields (Hidden by default) -->
                        <div id="newCompanyFields" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label style="font-weight: 500;">
                                            New Company Name <span id="newCompanyNameStar" class="required">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="newCompanyName" placeholder="Enter company name"
                                            style="background:#f5f5f5;border:1px solid #ddd;border-radius:6px;height:38px;">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label style="font-weight: 500;">
                                            Company Contact <span id="newCompanyContactStar" class="required">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="newCompanyContact" placeholder="Enter contact person or phone number"
                                            style="background:#f5f5f5;border:1px solid #ddd;border-radius:6px;height:38px;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label style="font-weight: 500;">
                                        Company Address <span id="companyAddressStar" class="required">*</span>
                                    </label>
                                    <textarea class="form-control" id="companyAddress" rows="1" readonly placeholder="Company address will appear here"
                                        style="background:#f5f5f5;border:1px solid #ddd;border-radius:6px;resize:vertical;"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- ========== INTERNSHIP DURATION ========== -->
                        <h5 style="margin-top: 30px; margin-bottom: 20px; font-weight: 600; color: #333;">Internship Duration</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label style="font-weight: 500;">Start Date <span class="required">*</span></label>
                                    <input type="date" class="form-control" id="startDate"
                                        style="background:#f5f5f5;border:1px solid #ddd;border-radius:6px;height:38px;">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label style="font-weight: 500;">End Date <span class="required">*</span></label>
                                    <input type="date" class="form-control" id="endDate"
                                        style="background:#f5f5f5;border:1px solid #ddd;border-radius:6px;height:38px;">
                                </div>
                            </div>
                        </div>

                        <!-- ========== LETTER LANGUAGE ========== -->
                        <h5 style="margin-top: 30px; margin-bottom: 20px; font-weight: 600; color: #333;">Letter Language</h5>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label style="font-weight: 500;">Select the language for the letter <span class="required">*</span></label><br>
                                    <label style="margin-right: 30px;">
                                        <input type="radio" name="language" id="LANG_ID" value="ID"> Bahasa Indonesia
                                    </label>
                                    <label>
                                        <input type="radio" name="language" id="LANG_ENG" value="ENG"> English
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="row" style="margin-top: 30px;">
                            <div class="col-md-12">
                                <div style="text-align: right;">
                                    <button type="submit" class="btn btn-primary" 
                                        style="background:#5A9BF6;color:#fff;border:none;border-radius:6px;padding:10px 30px;font-weight:500;font-size:15px;">
                                        Submit Request
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- FOOTER -->
            <footer class="footer">
                <div class="container-fluid">
                    <div class="copyright ml-auto">
                        2025, made with <i class="fa fa-heart heart text-danger"></i> by PBLIFPagi3A-3
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- JAVASCRIPT LIBRARIES -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="./assets/js/core/popper.min.js"></script>
    <script src="./assets/js/core/bootstrap.min.js"></script>
    <script src="./assets/js/plugin/jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>
    <script src="./assets/js/plugin/jquery-ui-touch-punch/jquery.ui.touch-punch.min.js"></script>
    <script src="./assets/js/plugin/moment/moment.min.js"></script>
    <script src="./assets/js/plugin/bootstrap-toggle/bootstrap-toggle.min.js"></script>
    <script src="./assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
    <script src="./assets/js/plugin/fullcalendar/fullcalendar.min.js"></script>
    <script src="./assets/js/plugin/atlantis.min.js"></script>
    <script src="./assets/js/plugin/chart.js/chart.min.js"></script>

    <!-- ============================================ -->
    <!-- CLOCK & CALENDAR FUNCTIONS -->
    <!-- ============================================ -->
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

            $calendar = $('#calendar');
            $calendar.fullCalendar({
                fixedWeekCount: false
            });
        }

        function clock_run() {
            'use strict';
            let d = new Date();
            let en_day = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            let en_month = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            
            function updateDateTime() {
                let d = new Date();
                let day = en_day[d.getDay()];
                let date = d.getDate();
                let month = en_month[d.getMonth()];
                let year = d.getFullYear();
                let curr_date = day + ', ' + date + ' ' + month + ' ' + year;

                $("#date").text(curr_date);
                $("#dateModal").text(curr_date);

                let hours = d.getHours();
                let minutes = d.getMinutes();
                let seconds = d.getSeconds();
                let time = ((hours < 10 ? "0" : "") + hours) + ' : ' + 
                          ((minutes < 10 ? "0" : "") + minutes) + ' : ' + 
                          ((seconds < 10 ? "0" : "") + seconds);

                $("#clock").text(time);
                $("#clock2").text(time);
            }

            updateDateTime();
            setInterval(updateDateTime, 1000);
        }
    </script>

    <!-- ============================================ -->
    <!-- UTILITY FUNCTIONS -->
    <!-- ============================================ -->
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

            Swal.fire({
                title: 'Logout from your account?',
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

        function konfirmasi(notif, lokasi) {
            var x = confirm(notif);
            if (x === true) {
                window.location.href = lokasi;
            }
        }

        function spinner() {
            var icon_spinner = event.target.querySelector('i');
            var icon_old = icon_spinner.className;
            var spinner = "fas fa-spinner fa-spin mr-1";

            icon_spinner.className = spinner;

            setTimeout(function() {
                icon_spinner.className = icon_old;
            }, 2000);
        }
    </script>

    <!-- ============================================ -->
    <!-- SELECT2 INITIALIZATION -->
    <!-- ============================================ -->
    <script>
        $(document).ready(function() {
            $('#companySelect').select2({
                placeholder: "- Select Company -",
                allowClear: true,
                width: '100%'
            });
        });
    </script>

    <!-- ============================================ -->
    <!-- SIDEBAR ACTIVE MENU HIGHLIGHT -->
    <!-- ============================================ -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
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
        });
    </script>

    <!-- ============================================ -->
    <!-- FORM SUBMISSION LOGIC -->
    <!-- ============================================ -->
    <script>
        (function() {
            // ========================================
            // CONFIGURATION & VARIABLES
            // ========================================
            const nim = <?= json_encode($student['nim']) ?>;
            const API_BASE = 'http://localhost:8000/api/student';
            let __formBlockerElement = null;

            // ========================================
            // FORM BLOCKER FUNCTIONS
            // ========================================
            function createFormBlocker() {
                const form = document.getElementById('submissionForm');
                if (!form || __formBlockerElement) return;

                const rect = form.getBoundingClientRect();
                const blocker = document.createElement('div');
                blocker.id = 'form-blocker';
                
                Object.assign(blocker.style, {
                    position: 'fixed',
                    top: `${rect.top}px`,
                    left: `${rect.left}px`,
                    width: `${rect.width}px`,
                    height: `${rect.height}px`,
                    background: 'rgba(255,255,255,0.0)',
                    zIndex: 20000,
                    pointerEvents: 'auto',
                    cursor: 'default'
                });

                document.body.appendChild(blocker);
                __formBlockerElement = blocker;

                window.addEventListener('resize', updateFormBlockerPosition);
                window.addEventListener('scroll', updateFormBlockerPosition, true);
            }

            function updateFormBlockerPosition() {
                const blocker = __formBlockerElement;
                const form = document.getElementById('submissionForm');
                if (!blocker || !form) return;
                
                const rect = form.getBoundingClientRect();
                blocker.style.top = `${rect.top}px`;
                blocker.style.left = `${rect.left}px`;
                blocker.style.width = `${rect.width}px`;
                blocker.style.height = `${rect.height}px`;
            }

            function removeFormBlocker() {
                if (!__formBlockerElement) return;
                __formBlockerElement.remove();
                __formBlockerElement = null;
                window.removeEventListener('resize', updateFormBlockerPosition);
                window.removeEventListener('scroll', updateFormBlockerPosition, true);
            }

            // ========================================
            // ALERT FUNCTIONS
            // ========================================
            function showActiveSubmissionAlertAndBlockForm() {
                Swal.fire({
                    title: 'You have an active internship submission',
                    html: '<p>You cannot submit a new application until your current submission is processed.</p>',
                    icon: 'info',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        const cont = document.querySelector('.swal2-container');
                        if (cont) {
                            cont.style.pointerEvents = 'none';
                            const popup = cont.querySelector('.swal2-popup');
                            if (popup) popup.style.zIndex = 30000;
                        }
                        createFormBlocker();
                    }
                });
            }

            function closeActiveSubmissionAlertAndUnblock() {
                if (Swal && Swal.close) Swal.close();
                removeFormBlocker();
            }

            // ========================================
            // CHECK ACTIVE SUBMISSION
            // ========================================
            async function checkActive() {
                try {
                    const res = await fetch(`${API_BASE}/check-submission/${encodeURIComponent(nim)}`);
                    const j = await res.json();
                    
                    if (!j.last) {
                        closeActiveSubmissionAlertAndUnblock();
                        return;
                    }

                    const acceptanceStatus = j.last?.acceptance_status || '-';
                    const finalStatus = j.last?.status ? j.last.status.toUpperCase() : '-';

                    if (finalStatus === 'WAITING') {
                        showActiveSubmissionAlertAndBlockForm();
                        return;
                    }

                    if (finalStatus === 'REJECTED') {
                        removeFormBlocker();
                        Swal.fire({
                            html: `
                                <div style="font-size: 40px; margin-bottom: 10px;">😊</div>
                                <h3 style="font-weight: bold;">Don't be sad, you'll get accepted next time!</h3>
                            `,
                            showConfirmButton: false,
                            allowOutsideClick: true,
                            backdrop: true,
                            timer: 2000
                        });
                        return;
                    }

                    if (finalStatus === 'ACCEPTED' && acceptanceStatus === '-') {
                        showActiveSubmissionAlertAndBlockForm();
                        return;
                    }

                    if (finalStatus === 'ACCEPTED' && acceptanceStatus === 'ACCEPTED') {
                        Swal.fire({
                            html: `
                                <div style="font-size: 40px; margin-bottom: 10px;">😆</div>
                                <h3 style="font-weight: bold;">Congratulations on being accepted for your internship!</h3>
                            `,
                            showConfirmButton: false,
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            backdrop: true,
                            didOpen: () => {
                                const cont = document.querySelector('.swal2-container');
                                if (cont) {
                                    cont.style.pointerEvents = 'none';
                                    const popup = cont.querySelector('.swal2-popup');
                                    if (popup) popup.style.zIndex = 30000;
                                }
                            }
                        });
                        createFormBlocker();
                        return;
                    }

                    if (finalStatus === 'ACCEPTED' && acceptanceStatus === 'REJECTED') {
                        removeFormBlocker();
                        Swal.fire({
                            html: `
                                <div style="font-size: 40px; margin-bottom: 10px;">😊</div>
                                <h3 style="font-weight: bold;">Don't be sad, you'll get accepted next time!</h3>
                            `,
                            showConfirmButton: false,
                            allowOutsideClick: true,
                            backdrop: true,
                            timer: 2000
                        });
                        return;
                    }

                    if (acceptanceStatus === '-') {
                        showActiveSubmissionAlertAndBlockForm();
                    }

                } catch (err) {
                    console.error('checkActive error:', err);
                }
            }

            // ========================================
            // LOAD STUDENT PROFILE
            // ========================================
            async function loadStudentProfile() {
                try {
                    const res = await fetch(`${API_BASE}/form-submission/${encodeURIComponent(nim)}`);
                    if (!res.ok) return;
                    const j = await res.json();

                    const s = j.student || {};
                    const setFieldValue = (id, value) => {
                        const el = document.getElementById(id);
                        if (el) el.value = value || '';
                    };

                    setFieldValue('nimField', s.nim);
                    setFieldValue('nameField', s.name);
                    setFieldValue('programField', s.program_study);
                    setFieldValue('departmentField', j.department);
                    setFieldValue('coordinatorField', j.coordinator);
                    setFieldValue('emailField', s.email);
                } catch (err) {
                    console.error('loadStudentProfile error:', err);
                }
            }

            // ========================================
            // LOAD COMPANIES
            // ========================================
            async function loadCompanies() {
                try {
                    const res = await fetch(`${API_BASE}/company`);
                    const list = await res.json();
                    const sel = document.getElementById('companySelect');
                    if (!sel) return;
                    
                    sel.innerHTML = '<option value="">- Select Company -</option>';
                    list.forEach(c => {
                        const opt = document.createElement('option');
                        opt.value = c.id;
                        opt.textContent = c.name;
                        sel.appendChild(opt);
                    });

                    if (window.jQuery && $.fn.select2) {
                        $('#companySelect').select2({
                            placeholder: "- Select Company -",
                            allowClear: true,
                            width: '100%'
                        });
                    }
                } catch (err) {
                    console.error('loadCompanies error:', err);
                }
            }

            // ========================================
            // COMPANY SELECT HANDLER
            // ========================================
            $('#companySelect').on('select2:select', async function(e) {
                const id = e.params.data.id;
                const $addr = $('#companyAddress');

                if (!id) {
                    $addr.val('').prop('readonly', true);
                    return;
                }

                try {
                    const res = await fetch(`${API_BASE}/company/${id}`);
                    const c = await res.json();
                    $addr.val(c.address || '').prop('readonly', true);
                } catch (err) {
                    console.error('Company fetch error:', err);
                }
            });

            // ========================================
            // COMPANY EXIST CHECKBOX HANDLER
            // ========================================
            const chk = document.getElementById('companyExist');
            if (chk) {
                chk.addEventListener('change', function() {
                    const checked = this.checked;
                    const newFields = document.getElementById('newCompanyFields');
                    const sel = document.getElementById('companySelect');
                    const addrEl = document.getElementById('companyAddress');
                    const companySelectStar = document.getElementById('companySelectStar');

                    // Toggle new company fields
                    if (newFields) {
                        newFields.style.display = checked ? '' : 'none';
                    }

                    // Handle company select dropdown
                    if (sel) {
                        sel.disabled = checked;
                        if (checked) {
                            if (window.jQuery && $.fn.select2) {
                                $('#companySelect').val(null).trigger('change');
                            } else {
                                sel.value = '';
                            }
                        }
                    }

                    // Handle address field
                    if (addrEl) {
                        addrEl.value = '';
                        addrEl.readOnly = !checked;
                    }

                    // Toggle required star visibility
                    if (companySelectStar) {
                        companySelectStar.style.display = checked ? 'none' : 'inline-block';
                    }

                    // Clear new company fields when unchecked
                    if (!checked) {
                        const newName = document.getElementById('newCompanyName');
                        const newContact = document.getElementById('newCompanyContact');
                        if (newName) newName.value = '';
                        if (newContact) newContact.value = '';
                    }
                });
            }

            // ========================================
            // DATE VALIDATION
            // ========================================
            const startDateInput = document.getElementById('startDate');
            const endDateInput = document.getElementById('endDate');

            function formatDate(date) {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }

            if (startDateInput) {
                startDateInput.addEventListener('change', () => {
                    const startValue = startDateInput.value;
                    if (startValue && endDateInput) {
                        const startDate = new Date(startValue);
                        const minEndDate = new Date(startDate);
                        minEndDate.setDate(startDate.getDate() + 1);
                        endDateInput.min = formatDate(minEndDate);

                        if (endDateInput.value && new Date(endDateInput.value) < minEndDate) {
                            endDateInput.value = '';
                        }
                    }
                });
            }

            if (endDateInput) {
                endDateInput.addEventListener('change', () => {
                    const endValue = endDateInput.value;
                    if (endValue && startDateInput) {
                        const endDate = new Date(endValue);
                        const maxStartDate = new Date(endDate);
                        maxStartDate.setDate(endDate.getDate() - 1);
                        startDateInput.max = formatDate(maxStartDate);

                        if (startDateInput.value && new Date(startDateInput.value) > maxStartDate) {
                            startDateInput.value = '';
                        }
                    }
                });
            }

            // ========================================
            // FORM VALIDATION & SUBMISSION
            // ========================================
            const form = document.getElementById('submissionForm');
            if (form) {
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();

                    const isNewCompany = document.getElementById('companyExist')?.checked || false;

                    // Build payload
                    const payload = {
                        nim: nim,
                        company_id: !isNewCompany ? (document.getElementById('companySelect')?.value || null) : null,
                        company_name: isNewCompany ? (document.getElementById('newCompanyName')?.value || null) : null,
                        company_contact: isNewCompany ? (document.getElementById('newCompanyContact')?.value || null) : null,
                        company_address: document.getElementById('companyAddress')?.value || null,
                        start_date: document.getElementById('startDate')?.value || null,
                        end_date: document.getElementById('endDate')?.value || null,
                        semester: document.getElementById('semesterField')?.value || null,
                        class: document.querySelector('input[name="class"]:checked')?.value || null,
                        email: document.getElementById('emailField')?.value || null,
                        phone: document.getElementById('phoneField')?.value || null,
                        language: document.querySelector('input[name="language"]:checked')?.value || null
                    };

                    // Validation
                    const missing = [];
                    
                    if (!payload.class) missing.push('Class');
                    if (!payload.semester) missing.push('Semester');
                    if (!payload.start_date) missing.push('Start Date');
                    if (!payload.end_date) missing.push('End Date');
                    if (!payload.email) missing.push('Email');
                    if (!payload.phone) missing.push('Active WhatsApp Number');
                    if (!payload.language) missing.push('Language for Letter');
                    
                    if (isNewCompany) {
                        if (!payload.company_name) missing.push('New Company Name');
                        if (!payload.company_contact) missing.push('Company Contact');
                    } else {
                        if (!payload.company_id) missing.push('Company Selection');
                    }
                    
                    if (!payload.company_address) missing.push('Company Address');

                    if (missing.length > 0) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Missing Required Fields',
                            html: `<div style="text-align: left;">Please fill in:<ul>${missing.map(m => `<li>${m}</li>`).join('')}</ul></div>`
                        });
                        return;
                    }

                    // Email validation
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(payload.email)) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Invalid Email',
                            text: 'Please enter a valid email address.'
                        });
                        return;
                    }

                    // Phone validation (basic)
                    const phoneRegex = /^[0-9+\-\s()]+$/;
                    if (!phoneRegex.test(payload.phone)) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Invalid Phone Number',
                            text: 'Please enter a valid phone number.'
                        });
                        return;
                    }

                    // Submit form
                    try {
                        const res = await fetch(`${API_BASE}/form-submission`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(payload)
                        });
                        
                        const j = await res.json();
                        
                        if (res.ok) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: 'Submission successfully created',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href = 'approval_status.php';
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Submission Failed',
                                text: j.error || 'An error occurred while submitting the form.'
                            });
                        }
                    } catch (err) {
                        console.error('Submit error:', err);
                        Swal.fire({
                            icon: 'error',
                            title: 'Server Error',
                            text: 'Unable to connect to the server. Please try again later.'
                        });
                    }
                });
            }

            // ========================================
            // INITIALIZATION
            // ========================================
            $(document).ready(async function() {
                await checkActive();
                await loadStudentProfile();
                await loadCompanies();
            });

        })();
    </script>

</body>

</html>