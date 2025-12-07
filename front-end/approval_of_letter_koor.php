<?php
session_start();

if (!isset($_SESSION['lecturer']) || empty($_SESSION['lecturer']['nim_nik_unit'])) {
    header('Location: role_login.php');
    exit;
}

$lecturer = $_SESSION['lecturer'];
$nim_nik_unit = $lecturer['nim_nik_unit'];
$user = $lecturer;

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
    <title>Approval Coordinator</title>
    <meta content='width=device-width, initial-scale=1.0, shrink-to-fit=no' name='viewport' />
    <!-- Icon -->
    <link rel="icon" href="./assets/img/iconM.png" type="image/x-icon" />
    <link href="./assets/img/iconM.png" rel="apple-touch-icon" type="image/x-icon">

    <link rel='stylesheet' href='./core/component/sweetalert2.min.css'>
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- CSS Files -->
    <link rel="stylesheet" href="./assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="./assets/css/atlantis.css">

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

        .btn-xs {
            padding: 4px 8px;
            /* lebih kecil dari btn-sm */
            font-size: 0.75rem;
            /* teks sedikit lebih kecil */
            line-height: 1.2;
            /* supaya height-nya rendah */
            border-radius: 4px;
            /* tetap sedikit membulat */
        }

        .badge.waiting {
            background-color: #ffc107;
            color: #fff;
            border-radius: 8px;
            padding: 5px 10px;
            font-weight: 500;
        }

        .badge.approved {
            background-color: #28a745;
            color: #fff;
            border-radius: 8px;
            padding: 5px 10px;
            font-weight: 500;
        }

        .badge.rejected {
            background-color: #dc3545;
            color: #fff;
            border-radius: 8px;
            padding: 5px 10px;
            font-weight: 500;
        }

        /* Export Button Styling */
        .btn-danger,
        .btn-success {
            transition: all 0.3s ease;
        }

        .btn-danger:hover {
            background-color: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .btn-success:hover {
            background-color: #218838;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        /* Prevent focus on background elements when SweetAlert is open */
        .swal2-shown .wrapper,
        .swal2-shown body> :not(.swal2-container) {
            pointer-events: none;
            user-select: none;
        }

        .swal2-container {
            pointer-events: none;
        }

        .swal2-popup {
            pointer-events: all;
        }

        /* Ensure buttons in background are not focusable */
        .swal2-shown button:not(.swal2-popup button),
        .swal2-shown input:not(.swal2-popup input),
        .swal2-shown select:not(.swal2-popup select),
        .swal2-shown textarea:not(.swal2-popup textarea),
        .swal2-shown a:not(.swal2-popup a) {
            pointer-events: none;
            opacity: 0.5;
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
                <a href="#" class="logo">
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
                                                <h5><?= htmlspecialchars($user['name']) ?></h5>
                                                <p class="text-muted">Lecturer at :
                                                    <br><?= htmlspecialchars($nama_kampus) ?>
                                                </p>
                                                <a href="index.php?page=industry_profile"
                                                    class="btn btn-xs btn-secondary btn-sm">View Profile</a>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="cdc_dashboard.php">My Dashboard</a>
                                        <a class="dropdown-item" href="#">My Profile</a>
                                        <a class="dropdown-item" href="#">My Company</a>
                                        <!-- <a class="dropdown-item" href="#">Inbox</a> -->
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#">Home</a>
                                        <a class="dropdown-item" href="#">Announcements</a>
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
                                    <span class="user-level">NIK:
                                        <?php echo htmlspecialchars($user['nim_nik_unit']); ?></span>
                                    <span class="user-level wrap2">Lecturer
                                        at:<?php echo htmlspecialchars($nama_kampus); ?><br>
                                    </span>
                                </span>
                            </a>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                    <ul class="nav nav-primary">
                        <li class="nav-item">
                            <a href="dashboard_lecturer.php" class="collapsed" aria-expanded="false">
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
                            <a href="approval_of_letter_koor.php" class="collapsed" aria-expanded="false">
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
                                                <option value="approved">Approved</option>
                                                <option value="waiting">Waiting</option>
                                                <option value="rejected">Rejected</option>
                                            </select>
                                        </div>

                                        <!-- Filter By Approval CDC -->
                                        <div class="col-md mb-3">
                                            <label for="filter_cdc" class="form-label">Filter by Approval CDC</label>
                                            <select class="form-control" id="filter_cdc" name="cdc"
                                                onchange="applyFilter()">
                                                <option value="">ALL</option>
                                                <option value="approved">Approved</option>
                                                <option value="waiting">Waiting</option>
                                                <option value="rejected">Rejected</option>
                                            </select>
                                        </div>

                                        <!-- Filter By Result Company -->
                                        <div class="col-md mb-3">
                                            <label for="filter_company" class="form-label">Filter by Result
                                                Company</label>
                                            <select class="form-control" id="filter_company" name="company"
                                                onchange="applyFilter()">
                                                <option value="">ALL</option>
                                                <option value="accepted">Accepted</option>
                                                <option value="rejected">Rejected</option>
                                            </select>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <!-- Button Export Excel -->
                        <div class="col-md-2 mb-3" style="float: right;">
                            <button class="btn btn-success btn-block" onclick="exportToExcel()">
                                <i class="fas fa-file-excel"></i> Export to Excel
                            </button>
                        </div>
                    </div>

                    <!-- Table Section -->
                    <div class="col-md-12">
                        <div class="card full-height">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" id="approvalTable">
                                        <thead>
                                            <tr class="text-center">
                                                <th style="width: 50px;">No</th>
                                                <th style="width: 120px; cursor: pointer;" onclick="sortTable()">
                                                    Date
                                                    <i id="sortIcon" class="fas fa-sort"></i>
                                                </th>
                                                <th style="width: 120px;">NIM</th>
                                                <th style="width: 270px;">Name</th>
                                                <th style="width: 150px;">Approval Coordinator</th>
                                                <th style="width: 150px;">Approval CDC</th>
                                                <th style="width: 150px;">Result</th>
                                                <th style="width: 180px;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tableBody">
                                        </tbody>
                                    </table>
                                </div>
                                <!-- Pagination -->
                                <div class="mt-3">
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination justify-content-center">
                                            <li class="page-item disabled">
                                                <a class="page-link" href="#" tabindex="-1">Previous</a>
                                            </li>
                                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                            <li class="page-item">
                                                <a class="page-link" href="#">Next</a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <footer class="footer">
                <div class="container">
                    <nav class="pull-left">
                    </nav>
                    <div class="copyright ml-auto">
                        © 2025, made with <i class="fa fa-heart heart text-danger"></i> by <a
                            href="https://github.com/nelifauziyah88/myinternship-development">PBLIFPagi3A-3</a>
                    </div>
                </div>
            </footer>
        </div>

        <script>
            // ========================================
            // KONFIGURASI AWAL
            // ========================================
            // Mendapatkan ID dosen dari PHP
            const lecturerId = "<?php echo $nim_nik_unit; ?>";
            // Mendapatkan nama user saat ini dari PHP
            const currentUserName = <?= json_encode($user['name'] ?? "-") ?>;
            // Base URL untuk API endpoint
            const apiBase = "http://localhost:8000/api";

            // ========================================
            // EVENT LISTENER - LOAD SUBMISSIONS SAAT HALAMAN SIAP
            // ========================================
            document.addEventListener("DOMContentLoaded", loadSubmissions);

            // ========================================
            // FUNGSI UTAMA - LOAD DATA SUBMISSIONS
            // ========================================
            async function loadSubmissions() {
                // Dapatkan element tbody
                const body = document.getElementById("tableBody");
                // Tampilkan loading state
                body.innerHTML = "<tr><td colspan='8'>Loading...</td></tr>";

                try {
                    // Fetch data submissions dari API
                    const res = await fetch(`${apiBase}/lecturer/submissions/${lecturerId}`);
                    const json = await res.json();

                    // Validasi response - cek jika tidak ada data
                    if (!json.success || !json.data || json.data.length === 0) {
                        body.innerHTML = "<tr><td colspan='8' class='text-center text-muted'>No data found.</td></tr>";
                        return;
                    }

                    // Reset table body jika ada data
                    body.innerHTML = "";

                    // ========================================
                    // LOOP DATA - RENDER SETIAP SUBMISSION
                    // ========================================
                    json.data.forEach((item, index) => {
                        // Format tanggal submission
                        const date = new Date(item.created_at).toLocaleDateString("en-GB");

                        // Helper function untuk format waktu
                        const formatTime = (t) => {
                            if (!t) return "-";
                            const d = new Date(t);
                            return d.toLocaleDateString("en-GB");
                        };

                        // ========================================
                        // RENDER KOLOM COORDINATOR APPROVAL
                        // ========================================
                        let coordinatorHtml = "";

                        // Status: WAITING - tampilkan dropdown approve/reject
                        if (item.koor_approval === "WAITING") {
                            coordinatorHtml = `
          <div class="dropdown text-center">
            <button class="btn btn-warning btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
              Waiting
            </button>
            <div class="dropdown-menu">
              <a class="dropdown-item text-success" href="#" onclick="updateApproval(${item.id_letter}, 'ACCEPTED', this)">
                <i class="fas fa-check text-success"></i> Approve
              </a>
              <a class="dropdown-item text-danger" href="#" onclick="promptReject(${item.id_letter}, 'REJECTED', this)">
                <i class="fas fa-times text-danger"></i> Reject
              </a>
            </div>
          </div>`;
                        }
                        // Status: ACCEPTED - tampilkan badge approved dengan timestamp
                        else if (item.koor_approval === "ACCEPTED") {
                            coordinatorHtml = `
          <div class="text-center">
            <span class="badge approved">Approved</span>
            <div class="text-muted" style="font-size:12px;margin-top:2px;">${formatTime(item.updated_at)}</div>
          </div>`;
                        }
                        // Status: REJECTED - tampilkan badge rejected dengan tombol show reason
                        else if (item.koor_approval === "REJECTED") {
                            coordinatorHtml = `<div class="text-center">
         <span class='badge rejected'>Rejected</span>
         <div class="text-muted" style="font-size:12px;margin-top:2px;">${formatTime(item.updated_at)}</div>
         <button class="btn btn-sm btn-light mt-1" onclick="viewReason(${item.id_letter})" title="Show reason">
           <i class="fas fa-comment"></i> Show reason
         </button>
       </div>`;
                        }

                        // ========================================
                        // RENDER KOLOM CDC APPROVAL
                        // ========================================
                        let cdcHtml = "";

                        // Jika coordinator belum approve, CDC tidak bisa approve (tampilkan -)
                        if (item.koor_approval === "WAITING") {
                            cdcHtml = `-`;
                        }
                        // Jika coordinator sudah approve, CDC masih waiting
                        else if (item.cdc_approval === "WAITING" && item.koor_approval === "ACCEPTED") {
                            cdcHtml = `<span class="badge waiting">Waiting</span>`;
                        }
                        // Status CDC: ACCEPTED
                        else if (item.cdc_approval === "ACCEPTED") {
                            cdcHtml = `
          <div class="text-center">
            <span class="badge approved">Approved</span>
            <div class="text-muted" style="font-size:12px;margin-top:2px;">${formatTime(item.updated_at)}</div>
          </div>`;
                        }
                        // Status CDC: REJECTED
                        else if (item.cdc_approval === "REJECTED") {
                            cdcHtml = `
          <div class="text-center">
            <span class="badge rejected">Rejected</span>
            <div class="text-muted" style="font-size:12px;margin-top:2px;">${formatTime(item.updated_at)}</div>
          </div>`;
                        }
                        // Default: tampilkan -
                        else {
                            cdcHtml = `-`;
                        }

                        // ========================================
                        // KOLOM HASIL (PLACEHOLDER)
                        // ========================================
                        const result = "-";

                        // ========================================
                        // RENDER TABLE ROW
                        // ========================================
                        body.innerHTML += `
        <tr>
          <td class="text-center">${index + 1}</td>
          <td class="text-center">${date}</td>
          <td class="text-center">${item.nim}</td>
          <td>${item.student_name}</td>
          <td class="approval-cell text-center">${coordinatorHtml}</td>
          <td class="text-center">${cdcHtml}</td>
          <td class="text-center">${result}</td>
          <td>
            <button class="btn btn-info btn-sm" onclick="viewDetail(${item.id_letter})">
              <i class="fa fa-eye"></i> Detail Submission
            </button>
          </td>
        </tr>`;
                    });
                } catch (err) {
                    // Handle error saat fetch data
                    console.error(err);
                    body.innerHTML = "<tr><td colspan='8' class='text-danger'>Error loading data.</td></tr>";
                }
            }

            // ========================================
            // FUNGSI UPDATE APPROVAL STATUS
            // ========================================
            async function updateApproval(id, status, el) {
                // Tampilkan konfirmasi SweetAlert
                const confirm = await Swal.fire({
                    title: "Confirm?",
                    text: `You are about to mark this submission as ${status}`,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes, confirm"
                });

                // Jika user cancel, hentikan proses
                if (!confirm.isConfirmed) return;

                try {
                    // Kirim request update approval ke API
                    const res = await fetch(`${apiBase}/lecturer/approval`, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            id_letter: id,
                            status
                        })
                    });

                    const json = await res.json();

                    // Handle response dari API
                    if (json.success) {
                        // Jika sukses, tampilkan notifikasi dan reload data
                        Swal.fire("Success!", json.message, "success");
                        loadSubmissions();
                    } else {
                        // Jika gagal, tampilkan pesan error
                        Swal.fire("Error", json.message, "error");
                    }
                } catch (err) {
                    // Handle error saat fetch
                    Swal.fire("Error", err.message, "error");
                }
            }

            // ========================================
            // FUNGSI NAVIGATE KE DETAIL SUBMISSION
            // ========================================
            function viewDetail(id) {
                // Redirect ke halaman detail dengan parameter id
                window.location.href = `detail_submissions_koor.php?id=${id}`;
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

        <!-- Sweet Alert -->
        <script src="./assets/js/core/bootstrap.min.js"></script>
        <script src="./assets/js/atlantis.min.js"></script>
        <script src="https://kit.fontawesome.com/a076d05399.js"></script>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
        <script
            src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/xlsx-js-style/dist/xlsx.min.js"></script>

        <script>
            // ============================================================
            // JQUERY INITIALIZATION - Clock & Calendar
            // ============================================================
            $(document).ready(function () {
                clock_run();
                show_calendar();
            });

            /**
             * Initialize and display calendar widget
             */
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

            /**
             * Run real-time clock display
             * Updates date and time every second
             */
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

                // Update date if changed
                if ($("#date").text() != curr_date) {
                    localStorage.setItem('curr_date', curr_date);
                    $("#date").text(curr_date);
                }

                // Update clock every second
                setInterval(function () {
                    let d = new Date();
                    let day = en_day[d.getDay()];
                    let date = d.getDate();
                    let month = en_month[d.getMonth()];
                    let year = (d.getYear() + 1900);
                    let date_day = day + ', ' + date + ' ' + month + ' ' + year;

                    // Update date if it changed
                    if (date_day != old_date) {
                        localStorage.setItem('curr_date', date_day);
                        $("#date").text(date_day);
                    }

                    // Format and update time
                    let hours = d.getHours();
                    let minutes = d.getMinutes();
                    let seconds = d.getSeconds();
                    let time = ((hours < 10 ? "0" : "") + hours) + ' : ' + ((minutes < 10 ? "0" : "") + minutes) + ' : ' + ((seconds < 10 ? "0" : "") + seconds);

                    $("#clock").text(time);
                    $("#clock2").text(time);
                }, 1000);
            }
        </script>

        <script type="text/javascript">
            // ============================================================
            // UTILITY FUNCTIONS
            // ============================================================
            /**
             * Copy text to clipboard
             * @param {string} text - Text to copy
             */
            function copyToClipboard(text) {
                var tempInput = document.createElement("input");
                document.body.appendChild(tempInput);
                tempInput.value = text;
                tempInput.select();

                document.execCommand("copy");

                document.body.removeChild(tempInput);

                alert("Text copied to clipboard: " + text);
            }

            /**
             * Get notification form via AJAX
             * @param {string} formSelector - Form selector (not used in current implementation)
             */
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

            /**
             * Logout confirmation and session cleanup
             */
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
                            success: function () {
                                setTimeout(function () {
                                    // Clear localStorage
                                    localStorage.removeItem('first');
                                    localStorage.removeItem('first_chime');
                                    localStorage.removeItem('next_chime');
                                    window.location.href = 'role_login.php';
                                }, 200);
                            },
                            error: function () {
                                Swal.fire('Error', 'Logout failed, please try again.', 'error');
                            }
                        });
                    }
                });
            }

            /**
             * Show native browser confirmation dialog
             * @param {string} notif - Notification message
             * @param {string} lokasi - URL to redirect if confirmed
             */
            function konfirmasi(notif, lokasi) {
                var x = confirm(notif);
                if (x === true) {
                    window.location.href = lokasi;
                }
            }

            /**
             * Show spinner animation on button icon
             */
            function spinner() {
                var icon_spinner = event.target.querySelector('i');
                var icon_old = icon_spinner.className;
                var spinner = "fas fa-spinner fa-spin mr-1";

                icon_spinner.className = '';
                icon_spinner.className = spinner;

                setTimeout(function () {
                    icon_spinner.className = '';
                    icon_spinner.className = icon_old;
                }, 2000);
            }

            // ============================================================
            // GLOBAL VARIABLES FOR FILTERING & SORTING
            // ============================================================
            let allSubmissions = []; // Store all submission data
            let sortAscending = true; // Track sort direction

            // ============================================================
            // DATA LOADING & RENDERING
            // ============================================================

            // Initialize on page load
            document.addEventListener("DOMContentLoaded", () => {
                loadSubmissions();
                loadYears();
            });

            function loadYears() {
                const yearSelect = document.getElementById("filter_year");
                const currentYear = new Date().getFullYear();

                yearSelect.innerHTML = `<option value="">ALL</option>`; // Default option

                for (let y = currentYear; y >= 2021; y--) {
                    yearSelect.innerHTML += `<option value="${y}">${y}</option>`;
                }
            }

            /**
             * Load all submissions data from API
             * Stores data globally for filtering and sorting
             */
            async function loadSubmissions() {
                const body = document.getElementById("tableBody");
                body.innerHTML = "<tr><td colspan='8'>Loading...</td></tr>";

                try {
                    const res = await fetch(`${apiBase}/lecturer/submissions/${lecturerId}`);
                    const json = await res.json();

                    // Handle empty or failed response
                    if (!json.success || !json.data || json.data.length === 0) {
                        body.innerHTML = "<tr><td colspan='8' class='text-center text-muted'>No data found.</td></tr>";
                        allSubmissions = [];
                        return;
                    }

                    // Store data globally for filtering/sorting
                    allSubmissions = json.data;

                    // Render table with all data
                    renderTable(allSubmissions);

                } catch (err) {
                    console.error(err);
                    body.innerHTML = "<tr><td colspan='8' class='text-danger'>Error loading data.</td></tr>";
                    allSubmissions = [];
                }
            }

            /**
             * Render table rows from data array
             * @param {Array} data - Array of submission objects
             */
            function renderTable(data) {
                const body = document.getElementById("tableBody");

                // Handle empty data
                if (!data || data.length === 0) {
                    body.innerHTML = "<tr><td colspan='8' class='text-center text-muted'>No data found.</td></tr>";
                    return;
                }

                body.innerHTML = "";

                // Helper function to format timestamp
                const formatTime = (t) => {
                    if (!t) return "-";
                    const d = new Date(t);
                    return d.toLocaleDateString("en-GB");
                };

                // Loop through each submission
                data.forEach((item, index) => {
                    const date = new Date(item.created_at).toLocaleDateString("en-GB");

                    // === BUILD COORDINATOR APPROVAL COLUMN ===
                    let coordinatorHtml = "";
                    if (item.koor_approval === "WAITING") {
                        // Show action dropdown
                        coordinatorHtml = `
                <div class="dropdown text-center">
                    <button class="btn btn-warning btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                        Waiting
                    </button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item text-success" href="#" onclick="updateApproval(${item.id_letter}, 'ACCEPTED', this)">
                            <i class="fas fa-check text-success"></i> Approve
                        </a>
                        <a class="dropdown-item text-danger" href="#" onclick="promptReject(${item.id_letter}, 'REJECTED', this)">
                            <i class="fas fa-times text-danger"></i> Reject
                        </a>
                    </div>
                </div>`;
                    } else if (item.koor_approval === "ACCEPTED") {
                        // Show approved status
                        coordinatorHtml = `
                <div class="text-center">
                    <span class="badge approved">Approved</span>
                    <div class="text-muted" style="font-size:12px;margin-top:2px;">${formatTime(item.updated_at)}</div>
                </div>`;
                    } else if (item.koor_approval === "REJECTED") {
                        // Show rejected status with reason button
                        coordinatorHtml = `
                <div class="text-center">
                    <span class='badge rejected'>Rejected</span>
                    <div class="text-muted" style="font-size:12px;margin-top:2px;">${formatTime(item.updated_at)}</div>
                    <button class="btn btn-sm btn-light mt-1" onclick="viewReason(${item.id_letter})" title="Show reason">
                        <i class="fas fa-comment"></i> Show reason
                    </button>
                </div>`;
                    }

                    // === BUILD CDC APPROVAL COLUMN ===
                    let cdcHtml = "";
                    if (item.koor_approval === "WAITING") {
                        // CDC can't process if coordinator hasn't approved
                        cdcHtml = `-`;
                    } else if (item.cdc_approval === "WAITING" && item.koor_approval === "ACCEPTED") {
                        cdcHtml = `<span class="badge waiting">Waiting</span>`;
                    } else if (item.cdc_approval === "ACCEPTED") {
                        cdcHtml = `
                <div class="text-center">
                    <span class="badge approved">Approved</span>
                    <div class="text-muted" style="font-size:12px;margin-top:2px;">${formatTime(item.updated_at)}</div>
                </div>`;
                    } else if (item.cdc_approval === "REJECTED") {
                        cdcHtml = `
                <div class="text-center">
                    <span class="badge rejected">Rejected</span>
                    <div class="text-muted" style="font-size:12px;margin-top:2px;">-</div>
                </div>`;
                    } else {
                        cdcHtml = `-`;
                    }

                    // === BUILD RESULT BADGE ===
                    const resultHtml = buildResultBadge(item);

                    // === BUILD TABLE ROW ===
                    body.innerHTML += `
            <tr>
                <td class="text-center">${index + 1}</td>
                <td class="text-center">${date}</td>
                <td class="text-center">${item.nim}</td>
                <td>${item.student_name}</td>
                <td class="approval-cell text-center">${coordinatorHtml}</td>
                <td class="text-center">${cdcHtml}</td>
                <td class="text-center">${resultHtml}</td>
                <td>
                    <button class="btn btn-info btn-sm" onclick="viewDetail(${item.id_letter})">
                        <i class="fa fa-eye"></i> Detail Submission
                    </button>
                </td>
            </tr>`;
                });
            }

            // ============================================================
            // HELPER - BUILD RESULT BADGE
            // ============================================================

            /**
             * Build badge untuk result company dengan button view reply
             * 
             * @param {Object} item - Data submission lengkap
             * @returns {string} HTML string untuk badge result
             */
            function buildResultBadge(item) {
                const acceptance = item.acceptance_status;

                // Jika belum ada acceptance status dari company
                if (!acceptance || acceptance === '-') {
                    return `<span>-</span>`;
                }

                // Jika ACCEPTED
                if (acceptance === 'ACCEPTED') {
                    return `
            <div class="text-center">
                <span class="badge approved">Accepted</span>
                <button class="btn btn-sm btn-info mt-1" onclick="viewCompanyReply(${item.id_letter})">
                    <i class="fas fa-file-alt"></i> View Reply
                </button>
            </div>
        `;
                }

                // Jika REJECTED
                if (acceptance === 'REJECTED') {
                    return `
            <div class="text-center">
                <span class="badge rejected">Rejected</span>
                <button class="btn btn-sm btn-info mt-1" onclick="viewCompanyReply(${item.id_letter})">
                    <i class="fas fa-file-alt"></i> View Reply
                </button>
            </div>
        `;
                }

                return `<span>-</span>`;
            }

            // ============================================================
            // FILTERING & SORTING FUNCTIONS
            // ============================================================

            /**
             * Apply filters to submission data
             * Called when any filter input changes
             */
            function applyFilter() {
    // Ambil value dari filter
    const studentName = document.getElementById("filter_student_name")?.value.toLowerCase().trim();
    const coordinatorStatus = document.getElementById("filter_coordinator")?.value.toLowerCase();
    const cdcStatus = document.getElementById("filter_cdc")?.value.toLowerCase();
    const companyResult = document.getElementById("filter_company")?.value.toLowerCase();

    if (!allSubmissions || !allSubmissions.length) return;

    const filteredData = allSubmissions.filter(item => {
        // Filter by Student Name
        const matchName = !studentName || (item.student_name && item.student_name.toLowerCase().includes(studentName));

        // Filter by Coordinator Approval
        let matchCoordinator = true;
        if (coordinatorStatus) {
            const val = item.koor_approval?.toUpperCase() || '';
            if (coordinatorStatus === "approved") matchCoordinator = val === "ACCEPTED";
            else if (coordinatorStatus === "waiting") matchCoordinator = val === "WAITING";
            else if (coordinatorStatus === "rejected") matchCoordinator = val === "REJECTED";
        }

        // Filter by CDC Approval
        let matchCDC = true;
        if (cdcStatus) {
            const val = item.cdc_approval?.toUpperCase() || '';
            if (cdcStatus === "approved") matchCDC = val === "ACCEPTED";
            else if (cdcStatus === "waiting") matchCDC = val === "WAITING";
            else if (cdcStatus === "rejected") matchCDC = val === "REJECTED";
        }

        // Filter by Company Result
        let matchCompany = true;
        if (companyResult) {
            const val = item.acceptance_status?.toUpperCase() || '';
            if (companyResult === "accepted") matchCompany = val === "ACCEPTED";
            else if (companyResult === "rejected") matchCompany = val === "REJECTED";
        }

        return matchName && matchCoordinator && matchCDC && matchCompany;
    });

    // Render ulang tabel
    renderTable(filteredData);
}

            /**
             * Sort table by date
             * Toggle between ascending and descending
             */
            function sortTable() {
                sortAscending = !sortAscending;

                const sortIcon = document.getElementById("sortIcon");
                if (sortAscending) {
                    sortIcon.className = "fas fa-sort-up";
                    allSubmissions.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
                } else {
                    sortIcon.className = "fas fa-sort-down";
                    allSubmissions.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
                }

                // Re-apply filters after sorting
                applyFilter();
            }

            // ============================================================
            // APPROVAL ACTIONS
            // ============================================================

            /**
             * Update approval status (Approve action)
             * @param {number} id - Letter ID
             * @param {string} status - New status
             * @param {HTMLElement} el - Clicked element
             */
            async function updateApproval(id, status, el) {
                const confirm = await Swal.fire({
                    title: "Confirm?",
                    text: `You are about to mark this submission as ${status}`,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes, confirm"
                });

                if (!confirm.isConfirmed) return;

                try {
                    const res = await fetch(`${apiBase}/lecturer/approval`, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            id_letter: id,
                            status,
                            user_id: lecturerId,
                            user_name: currentUserName,
                            comment: null
                        })
                    });
                    const json = await res.json();

                    if (json.success) {
                        Swal.fire("Success!", json.message, "success");
                        loadSubmissions();
                    } else {
                        Swal.fire("Error", json.message, "error");
                    }
                } catch (err) {
                    Swal.fire("Error", err.message, "error");
                }
            }

            /**
             * Prompt for rejection reason
             * @param {number} id - Letter ID
             * @param {HTMLElement} el - Clicked element
             */
            async function promptReject(id, el) {
                const {
                    value: reason,
                    isConfirmed
                } = await Swal.fire({
                    title: "Why are you rejecting?",
                    text: "Please provide your reason for rejecting this submission.",
                    input: "textarea",
                    inputPlaceholder: "Write the reason here...",
                    inputAttributes: {
                        'aria-label': 'Reason for rejection'
                    },
                    showCancelButton: true,
                    cancelButtonText: "Cancel",
                    confirmButtonText: "Submit",
                    preConfirm: (value) => {
                        if (!value || !value.trim()) {
                            Swal.showValidationMessage("Reason is required.");
                            return false;
                        }
                        return value.trim();
                    }
                });

                if (!isConfirmed) return;

                // Show confirmation before submitting
                await confirmThenApprove(id, "REJECTED", reason);
            }

            /**
             * Confirm approval action then execute
             * @param {number} id - Letter ID
             * @param {string} status - Approval status
             * @param {string|null} comment - Optional comment
             */
            async function confirmThenApprove(id, status, comment = null) {
                const confirm = await Swal.fire({
                    title: "Confirm?",
                    text: `You are about to mark this submission as ${status}`,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes, confirm"
                });

                if (!confirm.isConfirmed) return;

                await performApprovalRequest(id, status, comment);
            }

            /**
             * Perform approval request to API
             * @param {number} id - Letter ID
             * @param {string} status - Approval status
             * @param {string|null} comment - Optional comment
             */
            async function performApprovalRequest(id, status, comment = null) {
                try {
                    const res = await fetch(`${apiBase}/lecturer/approval`, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            id_letter: id,
                            status,
                            user_id: lecturerId,
                            user_name: currentUserName,
                            comment
                        })
                    });
                    const json = await res.json();

                    if (json.success) {
                        Swal.fire("Success!", json.message, "success");
                        loadSubmissions();
                    } else {
                        Swal.fire("Error", json.message, "error");
                    }
                } catch (err) {
                    Swal.fire("Error", err.message, "error");
                }
            }

            // ============================================================
            // REJECTION REASON MANAGEMENT
            // ============================================================

            /**
             * View rejection reason with edit option
             * @param {number} id_letter - Letter ID
             */
            async function viewReason(id_letter) {
                try {
                    const res = await fetch(`${apiBase}/lecturer/reason/${id_letter}`);
                    if (!res.ok) {
                        const j = await res.json().catch(() => ({
                            message: 'Unknown error'
                        }));
                        return Swal.fire("Error", j.message || "Reason not found", "error");
                    }
                    const json = await res.json();
                    const reason = json.comment || "-";

                    const result = await Swal.fire({
                        title: "Rejection reason",
                        html: `<div style="text-align:left; white-space:pre-wrap;">${escapeHtml(reason)}</div>`,
                        showDenyButton: true,
                        denyButtonText: "Edit",
                        confirmButtonText: "Close"
                    });

                    if (result.isDenied) {
                        editReason(id_letter, reason);
                    }
                } catch (err) {
                    Swal.fire("Error", err.message, "error");
                }
            }

            /**
             * Edit rejection reason
             * @param {number} id_letter - Letter ID
             * @param {string} current - Current reason
             */
            async function editReason(id_letter, current) {
                const {
                    value: newReason,
                    isConfirmed
                } = await Swal.fire({
                    title: "Edit rejection reason",
                    input: "textarea",
                    inputValue: current || "",
                    inputPlaceholder: "Write the reason here...",
                    showCancelButton: true,
                    cancelButtonText: "Cancel",
                    confirmButtonText: "Save",
                    preConfirm: (value) => {
                        if (!value || !value.trim()) {
                            Swal.showValidationMessage("Reason is required.");
                            return false;
                        }
                        return value.trim();
                    }
                });

                if (!isConfirmed) return;

                try {
                    const res = await fetch(`${apiBase}/lecturer/history/${id_letter}/edit`, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            comment: newReason
                        })
                    });
                    const json = await res.json();
                    if (json.success) {
                        Swal.fire("Success", json.message, "success");
                        loadSubmissions();
                    } else {
                        Swal.fire("Error", json.message, "error");
                    }
                } catch (err) {
                    Swal.fire("Error", err.message, "error");
                }
            }

            /**
             * Escape HTML to prevent XSS
             * @param {string} str - String to escape
             * @returns {string} Escaped string
             */
            function escapeHtml(str) {
                if (!str) return "";
                return String(str)
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }

            /**
             * Navigate to detail page
             * @param {number} id - Letter ID
             */
            function viewDetail(id) {
                window.location.href = `detail_submissions_koor.php?id=${id}`;
            }

            // ============================================================
            // FUNGSI VIEW COMPANY REPLY - ENHANCED VERSION
            // ============================================================

            /**
             * View company reply - tampilkan file dengan informasi lengkap
             * 
             * @param {number} id_letter - ID letter submission
             */
            async function viewCompanyReply(id_letter) {
                try {
                    // Fetch company reply data dari API
                    const res = await fetch(`${apiBase}/lecturer/company-reply/${id_letter}`);

                    if (!res.ok) {
                        const json = await res.json().catch(() => ({
                            message: 'Unknown error'
                        }));
                        return Swal.fire("Error", json.message || "Failed to load company reply", "error");
                    }

                    const json = await res.json();
                    const data = json.data;

                    // Case 1: Ada file upload
                    if (data.company_reply_letter && data.company_reply_letter !== '-') {
                        const fileName = data.company_reply_letter;
                        const fileUrl = `./${data.company_reply_letter}`;

                        // Tentukan apakah PDF atau gambar
                        const isPDF = fileName.toLowerCase().endsWith('.pdf');

                        // Badge styling berdasarkan status
                        let statusBadge = '';
                        if (data.acceptance_status === 'ACCEPTED') {
                            statusBadge = '<span class="badge approved">Accepted</span>';
                        } else if (data.acceptance_status === 'REJECTED') {
                            statusBadge = '<span class="badge rejected">Rejected</span>';
                        } else {
                            statusBadge = '<span class="badge waiting">Waiting</span>';
                        }

                        // Format reply date dari updated_at (ketika mahasiswa upload file)
                        let replyDateFormatted = '-';
                        if (data.updated_at) {
                            const date = new Date(data.updated_at);
                            replyDateFormatted = date.toLocaleDateString('en-GB', {
                                day: '2-digit',
                                month: 'short',
                                year: 'numeric'
                            });
                        }

                        // Informasi mahasiswa dan perusahaan
                        const infoSection = `
                <div style="background: #ffffff; padding: 25px; border-radius: 10px; margin-bottom: 20px; border: 2px solid #e3e6f0; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e3e6f0;">
                        <h4 style="margin: 0; color: #5a5c69; font-weight: 600;">
                            <i class="fas fa-file-contract" style="color: #4e73df;"></i> Company Reply Letter
                        </h4>
                        ${statusBadge}
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                        <div>
                            <p style="margin: 0 0 8px 0; color: #858796; font-weight: 500; font-size: 13px;">
                                <i class="fas fa-user-graduate" style="color: #4e73df; width: 18px;"></i> Student Name:
                            </p>
                            <p style="margin: 0 0 0 26px; font-size: 15px; color: #5a5c69; font-weight: 500;">${data.student_name || '-'}</p>
                        </div>
                        <div>
                            <p style="margin: 0 0 8px 0; color: #858796; font-weight: 500; font-size: 13px;">
                                <i class="fas fa-id-card" style="color: #4e73df; width: 18px;"></i> NIM:
                            </p>
                            <p style="margin: 0 0 0 26px; font-size: 15px; color: #5a5c69; font-weight: 500;">${data.nim || '-'}</p>
                        </div>
                        <div>
                            <p style="margin: 0 0 8px 0; color: #858796; font-weight: 500; font-size: 13px;">
                                <i class="fas fa-building" style="color: #4e73df; width: 18px;"></i> Company Name:
                            </p>
                            <p style="margin: 0 0 0 26px; font-size: 15px; color: #5a5c69; font-weight: 500;">${data.company_name || '-'}</p>
                        </div>
                        <div>
                            <p style="margin: 0 0 8px 0; color: #858796; font-weight: 500; font-size: 13px;">
                                <i class="fas fa-calendar-alt" style="color: #4e73df; width: 18px;"></i> Upload Date:
                            </p>
                            <p style="margin: 0 0 0 26px; font-size: 15px; color: #5a5c69; font-weight: 500;">${replyDateFormatted}</p>
                        </div>
                    </div>
                </div>
            `;

                        let modalContent = '';

                        if (isPDF) {
                            // PDF Preview dengan embed
                            modalContent = `
                    ${infoSection}
                    <div style="border: 2px solid #e0e0e0; border-radius: 8px; overflow: hidden; margin-bottom: 20px;">
                        <div style="background: #f5f5f5; padding: 10px; border-bottom: 2px solid #e0e0e0;">
                            <i class="fas fa-file-pdf text-danger"></i> 
                            <strong>${fileName.split('/').pop()}</strong>
                        </div>
                        <div style="width: 100%; height: 500px;">
                            <embed src="${fileUrl}" type="application/pdf" width="100%" height="100%" />
                        </div>
                    </div>
                    <div class="text-center" style="display: flex; gap: 10px; justify-content: center;">
                        <button onclick="window.open('${fileUrl}', '_blank')" class="btn btn-info">
                            <i class="fas fa-external-link-alt"></i> Open in New Tab
                        </button>
                        <a href="${fileUrl}" download="${fileName.split('/').pop()}" class="btn btn-primary">
                            <i class="fas fa-download"></i> Download File
                        </a>
                    </div>
                `;
                        } else {
                            // Image Preview
                            modalContent = `
                    ${infoSection}
                    <div style="border: 2px solid #e0e0e0; border-radius: 8px; overflow: hidden; margin-bottom: 20px;">
                        <div style="background: #f5f5f5; padding: 10px; border-bottom: 2px solid #e0e0e0;">
                            <i class="fas fa-image text-primary"></i> 
                            <strong>${fileName.split('/').pop()}</strong>
                        </div>
                        <div style="width: 100%; padding: 20px; background: #fafafa;">
                            <img src="${fileUrl}" alt="Company Reply" style="max-width: 100%; height: auto; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);" />
                        </div>
                    </div>
                    <div class="text-center" style="display: flex; gap: 10px; justify-content: center;">
                        <button onclick="window.open('${fileUrl}', '_blank')" class="btn btn-info">
                            <i class="fas fa-external-link-alt"></i> Open in New Tab
                        </button>
                        <a href="${fileUrl}" download="${fileName.split('/').pop()}" class="btn btn-primary">
                            <i class="fas fa-download"></i> Download File
                        </a>
                    </div>
                `;
                        }

                        // Tampilkan modal dengan file
                        Swal.fire({
                            title: '', // Kosongkan karena sudah ada info di dalam content
                            html: modalContent,
                            width: '900px',
                            showConfirmButton: true,
                            confirmButtonText: 'Close',
                            customClass: {
                                popup: 'animated fadeIn'
                            }
                        });
                    }
                    // Case 2: REJECTED tanpa file (overdue)
                    else if (data.acceptance_status === 'REJECTED' && data.isOverdue) {
                        Swal.fire({
                            title: "Company Reply - REJECTED",
                            html: `
                    <div style="background: #fff3cd; padding: 20px; border-radius: 8px; border-left: 4px solid #ffc107; margin: 15px 0;">
                        <div style="text-align: left; margin-bottom: 15px;">
                            <p style="margin: 5px 0;"><strong><i class="fas fa-user-graduate"></i> Student:</strong> ${data.student_name || '-'}</p>
                            <p style="margin: 5px 0;"><strong><i class="fas fa-id-card"></i> NIM:</strong> ${data.nim || '-'}</p>
                            <p style="margin: 5px 0;"><strong><i class="fas fa-building"></i> Company:</strong> ${data.company_name || '-'}</p>
                        </div>
                        <hr style="border-color: #ffc107;">
                        <div style="text-align: left;">
                            <strong><i class="fas fa-exclamation-triangle text-warning"></i> Reason:</strong>
                            <p style="margin-top: 10px; line-height: 1.6; color: #856404;">${data.reason}</p>
                        </div>
                    </div>
                `,
                            icon: 'warning',
                            confirmButtonText: 'Close'
                        });
                    }
                    // Case 3: Tidak ada data sama sekali
                    else {
                        Swal.fire({
                            title: "No Reply File Yet",
                            html: `
                    <div style="text-align: left; padding: 20px; background: #f8f9fa; border-radius: 8px; margin: 15px 0;">
                        <p style="margin: 5px 0;"><strong><i class="fas fa-user-graduate"></i> Student:</strong> ${data.student_name || '-'}</p>
                        <p style="margin: 5px 0;"><strong><i class="fas fa-id-card"></i> NIM:</strong> ${data.nim || '-'}</p>
                        <p style="margin: 5px 0;"><strong><i class="fas fa-building"></i> Company:</strong> ${data.company_name || '-'}</p>
                        <hr>
                        <p style="color: #6c757d; margin-top: 15px;">
                            <i class="fas fa-info-circle"></i> The student has not received a reply letter within 14 days after the internship interview.
                        </p>
                    </div>
                `,
                            icon: 'info',
                            confirmButtonText: 'Close'
                        });
                    }

                } catch (err) {
                    console.error("Error viewing company reply:", err);
                    Swal.fire("Error", "Failed to load company reply: " + err.message, "error");
                }
            }

            // ===============================
            // FORMAT DATE
            // ===============================
            function formatDate(dateString) {
                if (!dateString) return "-";
                const date = new Date(dateString);
                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const year = date.getFullYear();
                return `${day}/${month}/${year}`;
            }

            
            // FULL REVISI SCRIPT KOOR DENGAN WRAPTEXT COMPANY ADDRESS

async function exportToExcel() {
    const { value: formValues } = await Swal.fire({
        title: 'Select Date Range',
        html:
            '<label for="swal-start" style="display:block;text-align:left;margin-bottom:6px">Start date</label>' +
            '<input id="swal-start" type="date" class="swal2-input" style="margin:0 auto;">' +
            '<label for="swal-end" style="display:block;text-align:left;margin-top:8px;margin-bottom:6px">End date</label>' +
            '<input id="swal-end" type="date" class="swal2-input" style="margin:0 auto;">',
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Export',
        didOpen: () => {
            const startInput = document.getElementById("swal-start");
            const endInput = document.getElementById("swal-end");

            startInput.addEventListener("change", () => {
                endInput.min = startInput.value;
                endInput.value = "";
            });
        },
        preConfirm: () => {
            const start = document.getElementById('swal-start').value;
            const end = document.getElementById('swal-end').value;

            if (!start) return Swal.showValidationMessage('Start date is required');
            if (!end) return Swal.showValidationMessage('End date is required');
            if (new Date(start) > new Date(end)) return Swal.showValidationMessage('Start date must be before or equal to End date');

            return { start, end };
        }
    });

    if (!formValues) return;

    const { start, end } = formValues;

    Swal.fire({
        title: 'Fetching data...',
        text: 'Please wait...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    const data = await fetchInternshipData(start, end);

    if (!data || data.length === 0) {
        Swal.close();
        Swal.fire({ icon: "info", title: "No Data Found", text: "No internship records found." });
        return;
    }

    try {
        const wb = XLSX.utils.book_new();

        const excelData = [
            ['INTERNSHIP DATA REPORT'],
            [`Period: ${formatDate(start)} - ${formatDate(end)}`],
            [`Total Students: ${data.length}`],
            [],
            ['No', 'NIM', 'Name', 'Study Program', 'Class', 'Semester', 'Company Name', 'Company Contact', 'Company Address', 'Start Date', 'End Date', 'Email', 'WhatsApp'],
            ...data.map((item, index) => [
                index + 1,
                item.nim || '-',
                item.student_name || '-',
                item.program_study || '-',
                item.class || '-',
                item.semester || '-',
                item.company_name || '-',
                item.company_contact || '-',
                item.company_address || '-',
                formatDate(item.start_date),
                formatDate(item.end_date),
                item.email || '-',
                item.whatsapp_number || '-'
            ])
        ];

        const ws = XLSX.utils.aoa_to_sheet(excelData);

        ws['!cols'] = [
            { wch: 5 }, { wch: 15 }, { wch: 25 }, { wch: 35 }, { wch: 20 }, { wch: 10 },
            { wch: 40 }, { wch: 18 }, { wch: 60 }, { wch: 14 }, { wch: 14 },
            { wch: 30 }, { wch: 20 }
        ];

        ws['!merges'] = [
            { s: { r: 0, c: 0 }, e: { r: 0, c: 12 } },
            { s: { r: 1, c: 0 }, e: { r: 1, c: 12 } },
            { s: { r: 2, c: 0 }, e: { r: 2, c: 12 } }
        ];

        const border = {
            top: { style: 'thin' }, bottom: { style: 'thin' },
            left: { style: 'thin' }, right: { style: 'thin' }
        };

        const headerStyle = {
            font: { name: 'Times New Roman', sz: 11, bold: true, color: { rgb: 'FFFFFF' } },
            fill: { fgColor: { rgb: '4472C4' } },
            alignment: { horizontal: 'center', vertical: 'center', wrapText: true },
            border
        };

        ws['A1'].s = { font: { name: 'Times New Roman', sz: 16, bold: true }, alignment: { horizontal: 'center' } };
        ws['A2'].s = { alignment: { horizontal: 'center' } };
        ws['A3'].s = { alignment: { horizontal: 'center' } };

        const headerCols = ['A','B','C','D','E','F','G','H','I','J','K','L','M'];
        headerCols.forEach(col => { if (ws[col + '5']) ws[col + '5'].s = headerStyle; });

        const dataRowStart = 6;
        for (let r = dataRowStart; r < dataRowStart + data.length; r++) {
            headerCols.forEach((col, index) => {
                const cell = ws[col + r];
                if (!cell) return;
                cell.s = {
                    font: { name: 'Times New Roman', sz: 11 },
                    alignment: {
                        horizontal: index <= 1 ? 'center' : 'left',
                        vertical: 'top',
                        wrapText: true
                    },
                    border
                };
            });
        }

        XLSX.utils.book_append_sheet(wb, ws, 'Internship Report');
        XLSX.writeFile(wb, `Internship_${start}_to_${end}.xlsx`);

        Swal.fire({ icon: "success", title: "Success", text: "Excel downloaded successfully!" });

    } catch (err) {
        Swal.fire({ icon: "error", title: "Error", text: err.message });
    }
}

function formatDate(dateString) {
    const d = new Date(dateString);
    return `${String(d.getDate()).padStart(2,'0')}/${String(d.getMonth()+1).padStart(2,'0')}/${d.getFullYear()}`;
}

async function fetchInternshipData(start, end) {
    try {
        const url = `${apiBase}/lecturer/export-internship?start_date=${start}&end_date=${end}&nim_nik_unit=${lecturerId}`;
        const res = await fetch(url);
        const json = await res.json();

        if (!json.success || !json.data || json.data.length === 0) return null;

        return json.data;

    } catch (err) {
        Swal.fire({ icon: "error", title: "Error", text: err.message });
        return null;
    }
}

        </script>
        
</body>

</html>