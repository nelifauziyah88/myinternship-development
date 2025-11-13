<?php
session_start();

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
            /* Ukuran badge */
            font-size: 10px;
            /* Ukuran angka */
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
            /* warna biru */
            color: white !important;
            border-radius: 10px;
        }

        .sidebar a.active i {
            color: white;
        }

        .swal2-container {
            z-index: 50;
        }

        .required {
            color: #d9534f;
            /* merah */
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
                                            <div class="avatar-lg"><img src="" alt="image profile" class="avatar-img rounded"></div>
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
                                        <!-- <a class="dropdown-item" href="#">Inbox</a> -->
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
                            <a href="form_submission_copy.php">
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
                            <h1 class="text-white pb-2 fw-bold">Form Submission</h1>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
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

    <script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>

    <!-- Tambahkan di sebelum </body> -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!--   Core JS Files   -->
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

            Swal.fire({
                title: 'Logout from your account ?',
                text: 'Are you sure you want to end the current session?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, I\'m sure!",
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

            icon_spinner.className = '';
            icon_spinner.className = spinner;

            setTimeout(function() {
                icon_spinner.className = '';
                icon_spinner.className = icon_old;
            }, 2000);
        }

        $(document).ready(function() {
            $('#companySelect').select2({
                placeholder: "Choose Company",
                allowClear: true,
                width: '100%' 
            });
        });

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

    <script>
        (function() {
            const nim = <?= json_encode($student['nim']) ?>;

            // base API
            const API_BASE = 'http://localhost:8000/api/student';

            let __formBlockerElement = null;

            function createFormBlocker() {
                const form = document.getElementById('submissionForm');
                if (!form) return;

                if (__formBlockerElement) return;

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
                    pointerEvents: 'auto', 
                });

                blocker.style.cursor = 'default';

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
                            if (popup) {
                                popup.style.zIndex = 30000; 
                            }
                        }

                        createFormBlocker();
                    },
                });
            }

            function closeActiveSubmissionAlertAndUnblock() {
                if (Swal && Swal.close) Swal.close();

                removeFormBlocker();
            }

            async function checkActive() {
                try {
                    const res = await fetch(`${API_BASE}/check-submission/${encodeURIComponent(nim)}`);
                    const j = await res.json();
                    if (!j.last) {
                        removeFormBlocker();
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
                            timer: 1500,
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
                            timer: 1500,
                        });
                        return;
                    }

                    if (acceptanceStatus === '-') {
                        showActiveSubmissionAlertAndBlockForm();
                        return;
                    }

                } catch (err) {
                    console.error('checkActive error', err);
                }
            }

            $(document).ready(function() {
                checkActive();
            });

            async function loadStudentProfile() {
                try {
                    const res = await fetch(`${API_BASE}/form-submission/${encodeURIComponent(nim)}`);
                    if (!res.ok) return;
                    const j = await res.json();

                    const s = j.student || {};
                    if (document.getElementById('nimField')) document.getElementById('nimField').value = s.nim || '';
                    if (document.getElementById('nameField')) document.getElementById('nameField').value = s.name || '';
                    if (document.getElementById('programField')) document.getElementById('programField').value = s.program_study || '';
                    if (document.getElementById('departmentField')) document.getElementById('departmentField').value = j.department || '';
                    if (document.getElementById('coordinatorField')) document.getElementById('coordinatorField').value = j.coordinator || '';

                    if (s.email && document.getElementById('emailField')) document.getElementById('emailField').value = s.email;
                } catch (err) {
                    console.error('loadStudentProfile error', err);
                }
            }

            async function loadCompanies() {
                try {
                    const res = await fetch(`${API_BASE}/company`);
                    const list = await res.json();
                    const sel = document.getElementById('companySelect');
                    if (!sel) return;
                    sel.innerHTML = '<option value="" selected>Choose Company</option>';
                    list.forEach(c => {
                        const opt = document.createElement('option');
                        opt.value = c.id;
                        opt.textContent = c.name;
                        sel.appendChild(opt);
                    });

                    if (window.jQuery && jQuery().select2) {
                        $('#companySelect').select2({
                            width: '100%'
                        });
                    }
                } catch (err) {
                    console.error('loadCompanies error', err);
                }
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
                        console.error(err);
                    }
                });

            }

            document.addEventListener('change', async function(e) {
                if (!e.target) return;
                if (e.target.id === 'companySelect') {
                    const id = e.target.value;
                    if (!id) {
                        const addrEl = document.getElementById('companyAddress');
                        if (addrEl) addrEl.value = '';
                        return;
                    }
                    try {
                        const res = await fetch(`${API_BASE}/company/${encodeURIComponent(id)}`);
                        if (!res.ok) return;
                        const c = await res.json();
                        const addrEl = document.getElementById('companyAddress');
                        if (addrEl) {
                            addrEl.value = c.address || '';
                            addrEl.disabled = true;
                        }
                    } catch (err) {
                        console.error('company detail error', err);
                    }
                }
            });

            const chk = document.getElementById('companyExist');
            if (chk) {
                chk.addEventListener('change', function() {
                    const checked = this.checked;
                    const newFields = document.getElementById('newCompanyFields');
                    const newName = document.getElementById('newCompanyName');
                    const newContact = document.getElementById('newCompanyContact');
                    const sel = document.getElementById('companySelect');
                    const addrEl = document.getElementById('companyAddress');

                    if (newFields) {
                        newFields.style.display = checked ? '' : 'none';
                    }

                    if (sel) {
                        sel.disabled = checked;
                        if (checked) {
                            sel.value = '';
                            if (window.jQuery && jQuery().select2) {
                                $('#companySelect').val(null).trigger('change');
                            }
                            if (addrEl) {
                                addrEl.value = '';
                                addrEl.readOnly = false;
                            }
                        } else {
                            sel.disabled = false;
                            const selVal = sel.value || '';
                            if (selVal) {
                                fetch(`${API_BASE}/company/${encodeURIComponent(selVal)}`)
                                    .then(r => r.json())
                                    .then(c => {
                                        if (addrEl) {
                                            addrEl.value = c.address || '';
                                            addrEl.readOnly = true;
                                        }
                                    })
                                    .catch(err => console.error(err));
                            } else {
                                if (addrEl) {
                                    addrEl.value = '';
                                    addrEl.readOnly = true;
                                }
                            }
                        }
                    }

                    if (checked) {
                        if (newName) {
                            newName.value = '';
                            newName.focus();
                        }
                        if (newContact) newContact.value = '';
                        if (addrEl) {
                            addrEl.value = '';
                            addrEl.readOnly = false;
                        }
                    }

                    const newCompanyNameStar = document.getElementById('newCompanyNameStar');
                    const newCompanyContactStar = document.getElementById('newCompanyContactStar');
                    const companyAddressStar = document.getElementById('companyAddressStar');
                    const companySelectStar = document.getElementById('companySelectStar');

                    if (checked) {
                        if (newCompanyNameStar) newCompanyNameStar.style.display = 'inline-block';
                        if (newCompanyContactStar) newCompanyContactStar.style.display = 'inline-block';
                        if (companyAddressStar) companyAddressStar.style.display = 'inline-block';
                        if (companySelectStar) companySelectStar.style.display = 'none';
                    } else {
                        if (newCompanyNameStar) newCompanyNameStar.style.display = 'none';
                        if (newCompanyContactStar) newCompanyContactStar.style.display = 'none';
                        if (companyAddressStar) companyAddressStar.style.display = 'none';
                        if (companySelectStar) companySelectStar.style.display = 'inline-block';
                    }
                });
            }

            // intercept form submit
            const form = document.getElementById('submissionForm');
            if (form) {
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();

                    // prepare payload
                    const checked = document.getElementById('companyExist') && document.getElementById('companyExist').checked;

                    const payload = {
                        nim: nim,
                        company_id: checked ? null : (document.getElementById('companySelect') ? document.getElementById('companySelect').value : null),
                        company_name: checked ? (document.getElementById('newCompanyName') ? document.getElementById('newCompanyName').value : null) : null,
                        company_contact: checked ? (document.getElementById('newCompanyContact') ? document.getElementById('newCompanyContact').value : null) : null,
                        company_address: document.getElementById('companyAddress') ? document.getElementById('companyAddress').value : null,
                        start_date: document.getElementById('startDate') ? document.getElementById('startDate').value : null,
                        end_date: document.getElementById('endDate') ? document.getElementById('endDate').value : null,
                        semester: document.getElementById('semesterField') ? document.getElementById('semesterField').value : null,
                        class: (document.querySelector('input[name="class"]:checked') ? document.querySelector('input[name="class"]:checked').value : null),
                        email: document.getElementById('emailField') ? document.getElementById('emailField').value : null,
                        phone: document.getElementById('phoneField') ? document.getElementById('phoneField').value : null
                    };

                    // enhanced client-side validation for required fields
                    // required: class, semester, start_date, end_date, email, phone, language
                    const missing = [];
                    if (!payload.class) missing.push('Class');
                    if (!payload.semester) missing.push('Semester');
                    if (!payload.start_date) missing.push('Start Date');
                    if (!payload.end_date) missing.push('End Date');
                    if (!payload.email) missing.push('Email');
                    if (!payload.phone) missing.push('Active WhatsApp Number');

                    // language radio
                    const langVal = (document.querySelector('input[name="language"]:checked') ? document.querySelector('input[name="language"]:checked').value : null);
                    if (!langVal) missing.push('Language for Letter');
                    payload.language = langVal;

                    // company validation
                    if (!payload.company_id && !payload.company_name) {
                        missing.push('Company (choose or enter new company)');
                    }
                    if (!payload.company_address) missing.push('Company Address');

                    if (missing.length) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Missing required fields',
                            html: `<div>Please fill: <ul style="text-align:left;">${missing.map(m => `<li>${m}</li>`).join('')}</ul></div>`
                        });
                        return;
                    }

                    try {
                        const res = await fetch(`${API_BASE}/form-submission`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        });
                        const j = await res.json();
                        if (res.ok) {
                            Swal.fire('Success', 'Submission successfully created', 'success').then(() => {

                                window.location.href = 'approval_status.php';
                            });
                        } else {
                            Swal.fire('Error', j.error || 'Submission failed', 'error');
                        }
                    } catch (err) {
                        console.error('submit error', err);
                        Swal.fire('Error', 'Server error while submitting', 'error');
                    }
                });
            }

            (async () => {
                await checkActive();
                await loadStudentProfile();
                await loadCompanies();

                const newFields = document.getElementById('newCompanyFields');
                if (newFields) newFields.style.display = 'none';
            })();

            (function initRequiredStars() {
                const newCompanyNameStar = document.getElementById('newCompanyNameStar');
                const newCompanyContactStar = document.getElementById('newCompanyContactStar');
                const companyAddressStar = document.getElementById('companyAddressStar');
                const companySelectStar = document.getElementById('companySelectStar');

                if (newCompanyNameStar) newCompanyNameStar.style.display = 'none';
                if (newCompanyContactStar) newCompanyContactStar.style.display = 'none';
                if (companyAddressStar) companyAddressStar.style.display = 'none';
                // companySelectStar: visible by default only if you want the dropdown to be flagged required when not checked
                if (companySelectStar) companySelectStar.style.display = 'inline-block';
            })();

            const startDateInput = document.getElementById('startDate');
            const endDateInput = document.getElementById('endDate');

            function formatDate(date) {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }

            startDateInput.addEventListener('change', () => {
                const startValue = startDateInput.value;
                if (startValue) {
                    const startDate = new Date(startValue);

                    const minEndDate = new Date(startDate);
                    minEndDate.setDate(startDate.getDate() + 1);
                    endDateInput.min = formatDate(minEndDate);

                    if (endDateInput.value && new Date(endDateInput.value) < minEndDate) {
                        endDateInput.value = '';
                    }
                }
            });

            endDateInput.addEventListener('change', () => {
                const endValue = endDateInput.value;
                if (endValue) {
                    const endDate = new Date(endValue);

                    const maxStartDate = new Date(endDate);
                    maxStartDate.setDate(endDate.getDate() - 1);
                    startDateInput.max = formatDate(maxStartDate);

                    if (startDateInput.value && new Date(startDateInput.value) > maxStartDate) {
                        startDateInput.value = '';
                    }
                }
            });
        })();
    </script>

</body>

</html>