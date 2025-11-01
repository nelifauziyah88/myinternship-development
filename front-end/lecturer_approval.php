<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Meta for Compatibility -->
    <meta charset="utf-8">
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
            active: function () {
                sessionStorage.fonts = true;
            }
        });
    </script>

    <!-- CKEDITOR -->
    <script src="./library/ckeditor/ckeditor.js"></script>

    <script src='./core/component/jquery.min.js'></script>
    <script>
        $(function () { });
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

        html,
        body {
            overflow-x: hidden;
        }

        .fixed-header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            background: linear-gradient(45deg, #007bff, #6610f2);
            z-index: 999;
            /* agar di atas semua elemen lain */
            padding: 2rem 0;
            text-align: center;
        }

        .fixed-header h1 {
            color: white;
            font-weight: bold;
            margin: 0;
        }

        /* Memberi jarak konten di bawah supaya tidak tertutup header */
        .page-inner-content {
            margin-top: 160px;
            /* sesuaikan tinggi header */
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
                                    <img src="./assets/img/profilelecturer.jpg" alt="..."
                                        class="avatar-img rounded-circle">
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-user animated fadeIn">
                                <div class="dropdown-user-scroll scrollbar-outer">
                                    <li>
                                        <div class="user-box">
                                            <div class="avatar-lg"><img src="./assets/img/profilelecturer.jpg"
                                                    alt="image profile" class="avatar-img rounded"></div>
                                            <div class="u-text">
                                                <h5>Dummy Mahasiswa</h5>
                                                <p class="text-muted">Mahasiswa</p><a
                                                    href="index.php?page=student_identity"
                                                    class="btn btn-xs btn-secondary btn-sm">View Profile</a>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="index.php?page=student_identity">My Profile</a>
                                        <a class="dropdown-item" href="index.php?page=my_internship">My Internship</a>

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
                                    <span class="wrap2">Dummy Lecturer</span>
                                    <span class="user-level">NIK : 0123456789</span>
                                    <span class="user-level wrap2">Lecturer at Politeknik <br>
                                        Negeri Batam</span>
                                </span>
                            </a>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                    <ul class="nav nav-primary">
                        <li class="nav-item">
                            <a href="index.php" class="collapsed" aria-expanded="false">
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
                            <h4 class="text-section">Lecturer Menu</h4>
                        </li>
                        <li class="nav-item ">
                            <a data-toggle="collapse" href="#register_internship" class="collapsed"
                                aria-expanded="false">
                                <i class="fab fa-wpforms"></i>
                                <p>My Student</p>
                                <span class="caret"></span>
                            </a>
                            <div class="collapse" id="register_internship">
                                <ul class="nav nav-collapse open">
                                    <li class="">
                                        <a href="index.php?page=register_cooperation">
                                            <span class="sub-item">Register Cooperation Internship</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li class="nav-item active">
                            <a href="index.php?page=my_internship" class="collapsed" aria-expanded="false">
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
            <!-- Approval Status Header - FULL WIDTH -->
            <div class="panel-header bg-primary-gradient">
                <div class="page-inner py-5">
                    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row">
                        <div>
                            <h1 class="text-white pb-2 fw-bold">Approval Status</h1>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Container -->
            <div class="page-inner mt--5">
                <div class="row mt--2">
                    <!-- Filter Section -->
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="card-title">Filter</div>
                            </div>
                            <div class="card-body">
                                <form id="filterForm" method="GET" action="">
                                    <div class="row align-items-end">

                                        <!-- Filter By Student Name -->
                                        <div class="col-md mb-3">
                                            <label for="filter_student_name" class="form-label">Filter by Student
                                                Name</label>
                                            <input type="text" class="form-control" id="filter_student_name"
                                                name="student_name" placeholder="Enter Student Name"
                                                onkeyup="applyFilter()">
                                        </div>

                                        <!-- Filter By Approval Coordinator -->
                                        <div class="col-md mb-3">
                                            <label for="filter_coordinator" class="form-label">Filter by Approval
                                                Coordinator</label>
                                            <select class="form-control" id="filter_coordinator" name="coordinator"
                                                onchange="applyFilter()">
                                                <option value="">ALL</option>
                                                <option value="approve">Approved</option>
                                                <option value="waiting">Waiting</option>
                                                <option value="reject">Rejected</option>
                                            </select>
                                        </div>

                                        <!-- Filter By Approval CDC -->
                                        <div class="col-md mb-3">
                                            <label for="filter_cdc" class="form-label">Filter by Approval CDC</label>
                                            <select class="form-control" id="filter_cdc" name="cdc"
                                                onchange="applyFilter()">
                                                <option value="">ALL</option>
                                                <option value="approve">Approved</option>
                                                <option value="waiting">Waiting</option>
                                                <option value="reject">Rejected</option>
                                            </select>
                                        </div>

                                        <!-- Filter By Result Company -->
                                        <div class="col-md mb-3">
                                            <label for="filter_company" class="form-label">Filter by Result
                                                Company</label>
                                            <select class="form-control" id="filter_company" name="company"
                                                onchange="applyFilter()">
                                                <option value="">ALL</option>
                                                <option value="accept">Accepted</option>
                                                <option value="-">-</option>
                                                <option value="reject">Rejected</option>
                                            </select>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Approval Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover">
                                <thead class="thead-light text-center">
                                    <tr>
                                        <th>No</th>
                                        <th style="width: 120px; cursor: pointer;" onclick="sortTable()">
                                            Date
                                            <i id="sortIcon" class="fas fa-sort"></i>
                                        </th>
                                        <th>NIM</th>
                                        <th>Name</th>
                                        <th>Approval Coordinator</th>
                                        <th>Approval CDC</th>
                                        <th>Result</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody class="text-center align-middle">
                                    <!-- Data Dummy (10 mahasiswa) -->
                                    <script>
                                        const data = [
                                            { no: 1, date: "01/01/2025", nim: "3312401008", name: "Claurasia Valerie Birvindea", koor: "approve", cdc: "waiting", result: "-" },
                                            { no: 2, date: "02/01/2025", nim: "3312401022", name: "John Doe", koor: "reject", cdc: "-", result: "-" },
                                            { no: 3, date: "03/01/2025", nim: "3312401025", name: "Diana Prasetya", koor: "approve", cdc: "approve", result: "accepted" },
                                            { no: 4, date: "04/01/2025", nim: "3312401030", name: "Rafi Ramadhan", koor: "waiting", cdc: "-", result: "-" },
                                            { no: 5, date: "05/01/2025", nim: "3312401041", name: "Alif Nurhuda", koor: "approve", cdc: "approve", result: "accepted" },
                                            { no: 6, date: "06/01/2025", nim: "3312401050", name: "Aisyah Putri", koor: "reject", cdc: "-", result: "-" },
                                            { no: 7, date: "07/01/2025", nim: "Vincent Budianto", name: "Vincent Budianto", koor: "approve", cdc: "waiting", result: "-" },
                                            { no: 8, date: "08/01/2025", nim: "3312401072", name: "Clara Wulandari", koor: "approve", cdc: "approve", result: "accepted" },
                                            { no: 9, date: "09/01/2025", nim: "3312401080", name: "Johan Sihombing", koor: "waiting", cdc: "-", result: "-" },
                                            { no: 10, date: "10/01/2025", nim: "3312401088", name: "Sinta Prameswari", koor: "approve", cdc: "reject", result: "rejected" },
                                        ];

                                        data.forEach((d) => {
                                            const koorBadge =
                                                d.koor === "approve"
                                                    ? '<span class="badge badge-success">Approve</span>'
                                                    : d.koor === "reject"
                                                        ? '<span class="badge badge-danger">Reject</span>'
                                                        : '<span class="badge badge-warning text-white">Waiting</span>';

                                            const cdcBadge =
                                                d.koor === "reject"
                                                    ? '<span class="badge badge-secondary">-</span>'
                                                    : d.cdc === "approve"
                                                        ? '<span class="badge badge-success">Approve</span>'
                                                        : d.cdc === "reject"
                                                            ? '<span class="badge badge-danger">Reject</span>'
                                                            : d.cdc === "waiting"
                                                                ? '<span class="badge badge-warning text-white">Waiting</span>'
                                                                : '<span class="badge badge-secondary">-</span>';

                                            const resultBadge =
                                                d.result === "accepted"
                                                    ? '<span class="badge badge-success">Accepted</span>'
                                                    : d.result === "rejected"
                                                        ? '<span class="badge badge-danger">Rejected</span>'
                                                        : "<span>-</span>";

                                            document.write(`
                <tr>
                  <td>${d.no}</td>
                  <td>${d.date}</td>
                  <td>${d.nim}</td>
                  <td>${d.name}</td>
                  <td>${koorBadge}</td>
                  <td>${cdcBadge}</td>
                  <td>
                    ${resultBadge}<br>
                    <button class="btn btn-outline-secondary btn-xs mt-1">
                      <i class="fa fa-eye"></i> Detail Reply Company
                    </button>
                  </td>
                  <td>
                    <button class="btn btn-outline-primary btn-sm">
                      <i class="fa fa-file-alt"></i> Detail Submission
                    </button>
                  </td>
                </tr>
              `);
                                        });
                                    </script>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>


                <!-- Footer -->
                <footer class="footer">
                    <div class="container">
                        <div class="copyright ml-auto">
                            2022, made with <i class="fa fa-heart heart text-danger"></i> by <a
                                href="https://psteam.id">PSTEAM</a>
                        </div>
                    </div>
                </footer>
                <script>
                    function applyFilter() {
                        // Ambil nilai filter
                        const nameFilter = $("#filter_student_name").val().toLowerCase();
                        const coordinatorFilter = $("#filter_coordinator").val().toLowerCase();
                        const cdcFilter = $("#filter_cdc").val().toLowerCase();
                        const companyFilter = $("#filter_company").val().toLowerCase();

                        // Loop setiap baris di tabel
                        $("table tbody tr").each(function () {
                            const name = $(this).find("td:nth-child(4)").text().toLowerCase();

                            const coordinatorStatus = $(this)
                                .find("td:nth-child(5) .badge")
                                .text()
                                .toLowerCase();

                            const cdcStatus = $(this)
                                .find("td:nth-child(6) .badge")
                                .text()
                                .toLowerCase();

                            const companyStatus = $(this)
                                .find("td:nth-child(7)")
                                .text()
                                .toLowerCase();

                            let visible = true;

                            // Filter berdasarkan nama
                            if (nameFilter && !name.includes(nameFilter)) visible = false;

                            // Filter Approval Coordinator
                            if (coordinatorFilter && !coordinatorStatus.includes(coordinatorFilter))
                                visible = false;

                            // Filter Approval CDC
                            if (cdcFilter && !cdcStatus.includes(cdcFilter)) visible = false;

                            // Filter Result Company
                            if (companyFilter && !companyStatus.includes(companyFilter))
                                visible = false;

                            $(this).toggle(visible);
                        });
                    }
                </script>

                <script>
                    let ascending = true; // status awal: urut dari tanggal terlama ke terbaru

                    function sortTable() {
                        const table = document.querySelector("table");
                        const tbody = table.querySelector("tbody");
                        const rows = Array.from(tbody.querySelectorAll("tr"));

                        // Urutkan baris berdasarkan kolom ke-2 (Date)
                        rows.sort((a, b) => {
                            const dateA = a.children[1].innerText.split('/').reverse().join('-');
                            const dateB = b.children[1].innerText.split('/').reverse().join('-');
                            const timeA = new Date(dateA);
                            const timeB = new Date(dateB);
                            return ascending ? timeA - timeB : timeB - timeA;
                        });

                        // Hapus isi tbody dan masukkan ulang baris yang sudah diurut
                        tbody.innerHTML = "";
                        rows.forEach((row) => tbody.appendChild(row));

                        // Ganti ikon panah naik/turun
                        const icon = document.getElementById("sortIcon");
                        if (ascending) {
                            icon.className = "fas fa-sort-up text-primary"; // terlama ke terbaru
                        } else {
                            icon.className = "fas fa-sort-down text-primary"; // terbaru ke terlama
                        }

                        ascending = !ascending; // ubah arah sort untuk klik berikutnya
                    }
                </script>


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

                <script>
                    $(document).ready(function () {

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

                        setInterval(function () {
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

                            success: function (response, xhr, status, error) {
                                console.log('Getting form notification');

                                $('body').append(response);
                            },

                            error: function (xhr, status, error) {
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
                            /* Read more about isConfirmed, isDenied below */
                            if (result.isConfirmed) {
                                // AJAX
                                $.ajax({
                                    url: "index.php?request=logout",
                                    // type: "GET",
                                    type: "POST",
                                    data: {
                                        'token': _token
                                    },

                                    success: function () {
                                        setTimeout(function () {
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

                        setTimeout(function () {
                            icon_spinner.className = '';
                            icon_spinner.className = icon_old;
                        }, 2000);
                    }
                </script>
            </div>
</body>
</html>