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
    <title>Approval Status</title>
    <meta content='width=device-width, initial-scale=1.0, shrink-to-fit=no' name='viewport' />
    <!-- Icon -->
    <link rel="icon" href="./assets/img/iconM.png" type="image/x-icon" />
    <link href="./assets/img/iconM.png" rel="apple-touch-icon" type="image/x-icon">

    <link rel='stylesheet' href='./core/component/sweetalert2.min.css'>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Tambahkan di <head> -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <!-- DataTables Bootstrap Integration (optional, biar rapih) -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

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
            white-space: normal;
            word-wrap: break-word;
            min-width: 140px;
            max-width: 140px;
            /* max-width:150px; */
        }

        .wrap2 {
            white-space: normal;
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
            color: white;
            border-radius: 10px;
        }

        .sidebar a.active i {
            color: white;
        }

        button:hover {
            background: #dee2e6;
            transform: translateY(-2px);
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
                            <h1 class="text-white pb-2 fw-bold">Approval Status</h1>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="page-inner mt--5">
                <div style="background: #fff; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.1); overflow: hidden;">

                    <!-- Table -->
                    <div class="table-responsive" style="padding: 25px;">
                        <table class="table table-bordered table-hover" style="width: 100%; text-align: center;" id="approvalTable">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">No</th>
                                    <th style="width: 120px; cursor: pointer;" onclick="sortTable()">
                                        Date
                                        <i id="sortIcon" class="fas fa-sort"></i>
                                    </th>
                                    <th style="width: 150px;">Approval by Internship Coordinator</th>
                                    <th style="width: 150px;">Approval by CDC Administrator</th>
                                    <th style="width: 150px;">Status</th>
                                    <th style="width: 150px;">Download Letter</th>
                                    <th style="width: 180px;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                <tr data-id="1">

                                    <td class="align-middle text-center">

                                    </td>
                                    <td class="align-middle text-center">

                                    </td>
                                    <td class="align-middle text-center">

                                    </td>
                                    <td class="align-middle text-center">

                                    </td>
                                    <td>

                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>

            <!-- Tambahkan di sebelum </body> -->
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

            <!-- DataTables JS -->
            <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
            <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

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

                $(document).ready(function() {
                    $('#companySelect').select2({
                        placeholder: "Choose Company",
                        allowClear: true,
                        width: '70%'
                    });
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

                // Script dropdown
                const actionBtn = document.getElementById('actionBtn');
                const actionMenu = document.getElementById('actionMenu');

                actionBtn.addEventListener('click', () => {
                    actionMenu.style.display = actionMenu.style.display === 'block' ? 'none' : 'block';
                });

                window.addEventListener('click', (e) => {
                    if (!actionBtn.contains(e.target)) {
                        actionMenu.style.display = 'none';
                    }
                });

                document.querySelectorAll('th.sortable').forEach((th, index) => {
                    th.addEventListener('click', () => {
                        th.classList.toggle('asc');
                        th.classList.toggle('desc');
                        // Di sini bisa tambahkan logika sorting tabel
                    });
                });
            </script>
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

    <script>
        (async function() {
            // ambil NIM dari session PHP
            const nim = <?= json_encode($student['nim']) ?>;

            // base API untuk student
            const API_BASE = 'http://localhost:8000/api/student';

            const tableBody = document.getElementById('tableBody');
            if (!tableBody) {
                console.error('Element #tableBody not found.');
                return;
            }

            function formatDate(dtStr) {
                if (!dtStr) return '-';
                const d = new Date(dtStr);
                const pad = n => String(n).padStart(2, '0');
                return `${pad(d.getDate())}/${pad(d.getMonth() + 1)}/${d.getFullYear()}`;
            }

            async function loadApproval() {
                try {
                    const res = await fetch(`${API_BASE}/approval-status/${nim}`);
                    if (!res.ok) throw new Error(`HTTP ${res.status}`);
                    const json = await res.json();

                    // Jika backend belum disetup untuk return JSON success:true, buat fallback
                    if (!json || (!json.success && !Array.isArray(json.data))) {
                        throw new Error('Invalid JSON structure');
                    }

                    const rows = json.data || [];
                    tableBody.innerHTML = '';

                    if (!rows.length) {
                        tableBody.innerHTML = `<tr><td colspan="7" class="text-center py-3">You haven’t submitted any internship letter yet.</td></tr>`;
                        return;
                    }

                    rows.forEach((r, idx) => {
                        const id = r.id_letter;
                        const dateCell = `<td class="align-middle text-center">${formatDate(r.created_at)}</td>`;

                        // koor approval badge
                        let koorBadge = '';
                        if (r.koor_approval === 'WAITING') koorBadge = `<span class="badge badge-warning px-3 py-2">Waiting</span>`;
                        else if (r.koor_approval === 'ACCEPTED') koorBadge = `<span class="badge badge-success px-3 py-2">Approved</span><br><small>${formatDate(r.updated_at)}</small>`;
                        else if (r.koor_approval === 'REJECTED') koorBadge = `<span class="badge badge-danger px-3 py-2">Rejected</span><br><small>${formatDate(r.updated_at)}</small>`;
                        else koorBadge = '-';

                        // cdc approval badge
                        let cdcBadge = '';
                        if (r.cdc_approval === 'WAITING') cdcBadge = `<span class="badge badge-warning px-3 py-2">Waiting</span>`;
                        else if (r.cdc_approval === 'ACCEPTED') cdcBadge = `<span class="badge badge-success px-3 py-2">Approved</span><br><small>${formatDate(r.updated_at)}</small>`;
                        else if (r.cdc_approval === 'REJECTED') cdcBadge = `<span class="badge badge-danger px-3 py-2">Rejected</span><br><small>${formatDate(r.updated_at)}</small>`;
                        else cdcBadge = '-';

                        // final status
                        let statusBadge = '';
                        if (r.status === 'WAITING') statusBadge = `<span class="badge badge-warning px-3 py-2">Waiting</span>`;
                        else if (r.status === 'ACCEPTED') statusBadge = `<span class="badge badge-success px-3 py-2">Approved</span>`;
                        else if (r.status === 'REJECTED') statusBadge = `<span class="badge badge-danger px-3 py-2">Rejected</span>`;
                        else statusBadge = '-';

                        // download button (disabled until ACCEPTED)
                        const downloadDisabled = (r.status !== 'ACCEPTED') ? 'disabled' : '';
                        const downloadBtn = `<button class="btn btn-sm btn-download" data-id="${id}" data-lang="${r.language || 'ID'}" ${downloadDisabled}
                    style="background:#E9ECEF;color:#212529;min-width:130px;border:none;box-shadow:0 3px 6px rgba(0,0,0,0.15);">
                    <i class="fa fa-download"></i> Download
                </button>`;

                        // action button (disabled until ACCEPTED)
                        const actionDisabled = (r.status !== 'ACCEPTED') ? 'disabled' : '';
                        const dropdownIcon = (r.status === 'ACCEPTED') ? '<i class="fa fa-caret-down ml-1"></i>' : '';
                        const actionBtn = `
                    <div class="action-wrapper">
                        <button class="btn btn-secondary btn-sm btn-action" data-id="${id}" ${actionDisabled}>
                            Action ${dropdownIcon}
                        </button>
                    </div>`;

                        const tr = document.createElement('tr');
                        tr.setAttribute('data-id', id);
                        tr.innerHTML = `
                    <td class="align-middle text-center">${idx + 1}</td>
                    ${dateCell}
                    <td class="align-middle text-center">${koorBadge}</td>
                    <td class="align-middle text-center">${cdcBadge}</td>
                    <td class="align-middle text-center">${statusBadge}</td>
                    <td class="align-middle text-center">${downloadBtn}</td>
                    <td class="align-middle text-center">${actionBtn}</td>
                `;
                        tableBody.appendChild(tr);
                    });

                    attachListeners();
                } catch (err) {
                    console.error('Error loading data:', err);
                    tableBody.innerHTML = `<tr><td colspan="7" class="text-center py-3 text-danger">Error loading data</td></tr>`;
                }
            }

            function attachListeners() {
                document.querySelectorAll('.btn-download').forEach(btn => {
                    btn.addEventListener('click', () => {
                        if (btn.disabled) return;
                        const id = btn.dataset.id;
                        const lang = btn.dataset.lang || 'ID';
                        window.location.href = `${API_BASE}/letter/${id}/download?lang=${lang}`;
                    });
                });

                // action button handler
                document.querySelectorAll('.btn-action').forEach(btn => {
                    btn.addEventListener('click', async () => {
                        if (btn.disabled) return;
                        const id = btn.dataset.id;
                        const choose = prompt('Type ACCEPTED for "Accepted by company" or REJECTED for "Rejected by company"')?.toUpperCase();
                        if (!['ACCEPTED', 'REJECTED'].includes(choose)) {
                            alert('Invalid choice or cancelled.');
                            return;
                        }

                        try {
                            const res = await fetch(`${API_BASE}/acceptance/${id}`, {
                                method: 'PUT',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    acceptance_status: choose
                                })
                            });
                            const json = await res.json();
                            if (!json.success) throw new Error(json.error || 'Failed to save');
                            alert('Action saved successfully.');
                            await loadApproval();
                        } catch (err) {
                            console.error(err);
                            alert('Failed to save action: ' + (err.message || 'Unknown error'));
                        }
                    });
                });
            }

            // initial load
            await loadApproval();

        })();
    </script>

</body>

</html>