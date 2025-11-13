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
    <title>Approval CDC</title>
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

        .badge-empty {
            display: inline-block;
            background-color: #adb5bd;
            /* abu-abu */
            color: #fff;
            border-radius: 8px;
            padding: 5px 10px;
            font-weight: 500;
            text-align: center;
            cursor: default;
            pointer-events: none;
            opacity: 0.85;
            min-width: 60px;
            /* supaya tetap rata tengah walau cuma strip */
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
                                    <img src="assets/img/profile.png" alt="..." class="avatar-img rounded-circle">
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-user animated fadeIn">
                                <div class="dropdown-user-scroll scrollbar-outer">
                                    <li>
                                        <div class="user-box">
                                            <div class="avatar-lg"><img src="./assets/img/profile.png" alt="image profile" class="avatar-img rounded"></div>
                                            <div class="u-text">
                                                <h5><?php echo htmlspecialchars($user['name']); ?></h5>
                                                <p class="text-muted"> CDC at :<br><?= htmlspecialchars($nama_kampus) ?></p>
                                                <a href="#" class="btn btn-xs btn-secondary btn-sm">View Profile</a>
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
                            <img src="./assets/img/profile.png" alt="..." class="avatar-img rounded-circle">
                        </div>
                        <div class="info">
                            <a data-toggle="collapse" href="#collapseExample" aria-expanded="true">
                                <span>
                                    <span class="wrap2"><?php echo htmlspecialchars($user['name']); ?></span>
                                    <span class="user-level wrap2">CDC at : <br><?php echo htmlspecialchars($nama_kampus); ?></span>
                                </span>
                            </a>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                    <ul class="nav nav-primary">
                        <li class="nav-item">
                            <a href="dashboard_cdc.php" class="collapsed" aria-expanded="false">
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
                        <li class="nav-item active">
                            <a href="approval_of_letter.php" class="collapsed" aria-expanded="false">
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
                                        <!-- Filter By Study Program -->
                                        <div class="col-md mb-3">
                                            <label for="filter_study_program" class="form-label">Filter By Study Program</label>
                                            <select class="form-control" id="filter_study_program" name="study_program" onchange="applyFilter()">
                                                <option value="">Select Study Program</option>
                                            </select>
                                        </div>

                                        <!-- Filter By Student Name -->
                                        <div class="col-md mb-3">
                                            <label for="filter_student_name" class="form-label">Filter by Student Name</label>
                                            <input type="text" class="form-control" id="filter_student_name" name="student_name" placeholder="Enter Student Name" onkeyup="applyFilter()">
                                        </div>

                                        <!-- Filter By Approval Coordinator -->
                                        <div class="col-md mb-3">
                                            <label for="filter_coordinator" class="form-label">Filter by Approval Coordinator</label>
                                            <select class="form-control" id="filter_coordinator" name="coordinator" onchange="applyFilter()">
                                                <option value="">ALL</option>
                                                <option value="approved">Approved</option>
                                                <option value="waiting">Waiting</option>
                                                <option value="rejected">Rejected</option>
                                            </select>
                                        </div>

                                        <!-- Filter By Approval CDC -->
                                        <div class="col-md mb-3">
                                            <label for="filter_cdc" class="form-label">Filter by Approval CDC</label>
                                            <select class="form-control" id="filter_cdc" name="cdc" onchange="applyFilter()">
                                                <option value="">ALL</option>
                                                <option value="approve">Approve</option>
                                                <option value="waiting">Waiting</option>
                                                <option value="reject">Reject</option>
                                            </select>
                                        </div>

                                        <!-- Filter By Result Company -->
                                        <div class="col-md mb-3">
                                            <label for="filter_company" class="form-label">Filter by Result Company</label>
                                            <select class="form-control" id="filter_company" name="company" onchange="applyFilter()">
                                                <option value="">ALL</option>
                                                <option value="accepted">Accepted</option>
                                                <option value="rejected">Rejected</option>
                                            </select>
                                        </div>
                                    </div>
                                </form>
                            </div>
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
                        © 2025, made with <i class="fa fa-heart heart text-danger"></i> by <a href="https://github.com/nelifauziyah88/myinternship-development">PBLIFPagi3A-3</a>
                    </div>
                </div>
            </footer>
        </div>

        <!--   Core JS Files   -->
        <script src="./assets/js/core/popper.min.js"></script>
        <script src="./assets/js/core/bootstrap.min.js"></script>
        <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

        <script>
            // ============================================
            // KONFIGURASI & INISIALISASI
            // ============================================

            // Data user CDC dari PHP session
            const currentUserId = <?= json_encode($user['id_upkpk'] ?? "-") ?>;
            const currentUserName = <?= json_encode($user['name'] ?? "-") ?>;
            const cdcKampusId = "<?php echo $id_kampus; ?>";

            // Base URL untuk API
            const apiBase = "http://localhost:8000/api";

            let allSubmissions = [];
            let sortAscending = true;


            // ============================================
            // EVENT LISTENER - LOAD AWAL
            // ============================================

            document.addEventListener("DOMContentLoaded", function() {
                loadStudyPrograms(); // Load dropdown study program untuk filter
                loadSubmissions(); // Load data submissions default (tanpa filter)
            });


            // ============================================
            // FUNGSI UTAMA - LOAD DATA SUBMISSIONS
            // ============================================

            /**
             * Load submissions dengan atau tanpa filter
             * Fungsi ini menggabungkan loadSubmissions() dan loadSubmissionsWithFilter()
             * 
             * @param {boolean} useFilter - true jika menggunakan filter, false untuk load semua data
             */
            async function loadSubmissions(useFilter = false) {
                const body = document.getElementById("tableBody");
                body.innerHTML = "<tr><td colspan='8' class='text-center'>Loading...</td></tr>";

                try {
                    let apiUrl = `${apiBase}/cdc/submissions`;

                    // Jika menggunakan filter, build query params
                    if (useFilter) {
                        const studyProgram = document.getElementById("filter_study_program").value;
                        const studentName = document.getElementById("filter_student_name").value;
                        const coordinator = document.getElementById("filter_coordinator").value;
                        const cdcFilter = document.getElementById("filter_cdc").value;
                        const company = document.getElementById("filter_company").value;

                        let queryParams = new URLSearchParams();
                        queryParams.append('id_kampus', cdcKampusId);

                        if (studyProgram) queryParams.append('study_program', studyProgram);
                        if (studentName) queryParams.append('student_name', studentName);
                        if (coordinator) queryParams.append('coordinator', coordinator);
                        if (cdcFilter) queryParams.append('cdc', cdcFilter);
                        // if (company) queryParams.append('company', company);

                        apiUrl = `${apiBase}/cdc/submissions-filtered?${queryParams.toString()}`;
                    }

                    // Fetch data dari API
                    const res = await fetch(apiUrl);
                    const json = await res.json();

                    // Simpan data ke variabel global
                    allSubmissions = json.data || [];

                    if (!json.success || !allSubmissions.length) {
                        body.innerHTML = "<tr><td colspan='8' class='text-center text-muted'>No data found.</td></tr>";
                        return;
                    }

                    // Render table
                    renderTable(allSubmissions);

                    // Validasi response
                    if (!json.success || !json.data.length) {
                        body.innerHTML = "<tr><td colspan='8' class='text-center text-muted'>No data found.</td></tr>";
                        return;
                    }

                    // Render table rows
                    body.innerHTML = "";
                    json.data.forEach((item, i) => {
                        body.innerHTML += buildTableRow(item, i);
                    });

                } catch (err) {
                    console.error("Error loading submissions:", err);
                    body.innerHTML = "<tr><td colspan='8' class='text-danger text-center'>Error loading data.</td></tr>";
                }
            }

            // ============================================
            // HELPER BARU
            // ============================================
            function renderTable(data) {
                const body = document.getElementById("tableBody");
                body.innerHTML = "";
                data.forEach((item, i) => {
                    body.innerHTML += buildTableRow(item, i);
                });
            }


            // ============================================
            // FUNGSI HELPER - BUILD TABLE ROW
            // ============================================

            /**
             * Build single table row untuk submission
             * 
             * @param {Object} item - Data submission dari API
             * @param {number} index - Index row untuk numbering
             * @returns {string} HTML string untuk table row
             */
            function buildTableRow(item, index) {
                const date = formatDate(item.created_at);
                const koorHtml = buildKoordinatorBadge(item);
                const cdcHtml = buildCDCApprovalHtml(item);
                const resultHtml = buildResultBadge(item.status);

                return `
        <tr>
            <td class="text-center">${index + 1}</td>
            <td class="text-center">${date}</td>
            <td class="text-center">${item.nim}</td>
            <td>${item.student_name}</td>
            <td class="text-center">${koorHtml}</td>
            <td class="text-center">${cdcHtml}</td>
            <td class="text-center">${resultHtml}</td>
            <td>
                <button class="btn btn-info btn-sm" onclick="viewDetail(${item.id_letter})">
                    <i class="fa fa-eye"></i> Detail Submission
                </button>
            </td>
        </tr>
    `;
            }


            // ============================================
            // FUNGSI HELPER - BUILD BADGE STATUS
            // ============================================

            /**
             * Build badge untuk status approval koordinator
             * 
             * @param {Object} item - Data submission
             * @returns {string} HTML string untuk badge koordinator
             */
            function buildKoordinatorBadge(item) {
                const updatedDate = formatDate(item.updated_at);

                switch (item.koor_approval) {
                    case "WAITING":
                        return `<span class='badge waiting'>Waiting</span>`;

                    case "ACCEPTED":
                        return `
                <div class="text-center">
                    <span class='badge approved'>Approved</span>
                    <div class="text-muted" style="font-size:12px;margin-top:2px;">${updatedDate}</div>
                </div>
            `;

                    case "REJECTED":
                        return `
                <div class="text-center">
                    <span class='badge rejected'>Rejected</span>
                    <div class="text-muted" style="font-size:12px;margin-top:2px;">${updatedDate}</div>
                </div>
            `;

                    default:
                        return "-";
                }
            }

            /**
             * Build HTML untuk approval CDC
             * Logic: 
             * - Jika koordinator REJECTED → CDC otomatis REJECTED (tanpa tanggal & tanpa reason)
             * - Jika koordinator WAITING → CDC belum bisa action (tampil -)
             * - Jika koordinator ACCEPTED & CDC WAITING → tampil dropdown action
             * - Jika CDC sudah ACCEPTED/REJECTED → tampil badge dengan tanggal
             * 
             * @param {Object} item - Data submission
             * @returns {string} HTML string untuk CDC approval
             */
            function buildCDCApprovalHtml(item) {
                const updatedDate = formatDate(item.updated_at);

                // Case 1: Koordinator REJECTED → CDC otomatis REJECTED
                if (item.koor_approval === "REJECTED") {
                    return `
            <div class="text-center">
                <span class="badge rejected">Rejected</span>
                <div class="text-muted" style="font-size:12px;margin-top:2px;">-</div>
            </div>
        `;
                }

                // Case 2: Koordinator masih WAITING → CDC belum bisa action
                if (item.koor_approval === "WAITING") {
                    return `-`;
                }

                // Case 3: Koordinator ACCEPTED & CDC WAITING → tampil dropdown action
                if (item.cdc_approval === "WAITING" && item.koor_approval === "ACCEPTED") {
                    return `
            <div class="dropdown text-center">
                <button class="btn btn-warning btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                    Waiting
                </button>
                <div class="dropdown-menu">
                    <a class="dropdown-item text-success" href="#" onclick="handleApproval(${item.id_letter}, 'ACCEPTED')">
                        <i class="fas fa-check text-success"></i> Approve
                    </a>
                    <a class="dropdown-item text-danger" href="#" onclick="handleApproval(${item.id_letter}, 'REJECTED')">
                        <i class="fas fa-times text-danger"></i> Reject
                    </a>
                </div>
            </div>
        `;
                }

                // Case 4: CDC sudah ACCEPTED
                if (item.cdc_approval === "ACCEPTED") {
                    return `
            <div class="text-center">
                <span class="badge approved">Approved</span>
                <div class="text-muted" style="font-size:12px;margin-top:2px;">${updatedDate}</div>
            </div>
        `;
                }

                // Case 5: CDC sudah REJECTED (tampil badge + button show reason)
                if (item.cdc_approval === "REJECTED") {
                    return `
            <div class="text-center">
                <span class="badge rejected">Rejected</span>
                <div class="text-muted" style="font-size:12px;margin-top:2px;">${updatedDate}</div>
                <button class="btn btn-sm btn-light mt-1" onclick="viewReason(${item.id_letter})" title="Show reason">
                    <i class="fas fa-comment"></i> Show reason
                </button>
            </div>
        `;
                }

                // Default
                return `-`;
            }

            /**
             * Build badge untuk result company
             * 
             * @param {string} status - Status result (ACCEPTED/WAITING/REJECTED)
             * @returns {string} HTML string untuk badge result
             */
            function buildResultBadge(status) {
                return "-";
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

                renderTable(allSubmissions);
            }

            // ============================================
            // FUNGSI HELPER - FORMAT TANGGAL
            // ============================================

            /**
             * Format timestamp menjadi tanggal DD/MM/YYYY
             * 
             * @param {string} timestamp - Timestamp dari database
             * @returns {string} Formatted date atau "-" jika null
             */
            function formatDate(timestamp) {
                if (!timestamp) return "-";
                return new Date(timestamp).toLocaleDateString("en-GB");
            }


            // ============================================
            // FUNGSI APPROVAL - HANDLE USER ACTION
            // ============================================

            /**
             * Handle approval action (Approve/Reject)
             * Untuk REJECTED, akan muncul textarea untuk input alasan
             * Untuk ACCEPTED, langsung konfirmasi
             * 
             * @param {number} id - ID letter submission
             * @param {string} status - Status approval (ACCEPTED/REJECTED)
             */
            async function handleApproval(id, status) {
                let comment = null;

                // Jika reject, minta alasan dulu
                if (status === "REJECTED") {
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
                    comment = reason;
                }

                // Konfirmasi action
                const confirm = await Swal.fire({
                    title: "Confirm?",
                    text: `You are about to mark this submission as ${status}`,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes, confirm"
                });

                if (!confirm.isConfirmed) return;

                // Kirim ke API
                await sendApprovalToAPI(id, status, comment);
            }

            /**
             * Kirim approval ke API
             * 
             * @param {number} id - ID letter submission
             * @param {string} status - Status approval (ACCEPTED/REJECTED)
             * @param {string|null} comment - Alasan reject (optional)
             */
            async function sendApprovalToAPI(id, status, comment = null) {
                try {
                    const res = await fetch(`${apiBase}/cdc/approval`, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            id_letter: id,
                            status,
                            user_id: currentUserId,
                            user_name: currentUserName,
                            comment
                        })
                    });

                    const json = await res.json();

                    if (json.success) {
                        Swal.fire("Success!", json.message, "success");
                        loadSubmissions(); // Reload data
                    } else {
                        Swal.fire("Error", json.message, "error");
                    }
                } catch (err) {
                    console.error("Error sending approval:", err);
                    Swal.fire("Error", err.message, "error");
                }
            }


            // ============================================
            // FUNGSI REASON - VIEW & EDIT
            // ============================================

            /**
             * View rejection reason dengan opsi edit
             * 
             * @param {number} id_letter - ID letter submission
             */
            async function viewReason(id_letter) {
                try {
                    const res = await fetch(`${apiBase}/cdc/reason/${id_letter}`);

                    if (!res.ok) {
                        const j = await res.json().catch(() => ({
                            message: 'Unknown error'
                        }));
                        return Swal.fire("Error", j.message || "Reason not found", "error");
                    }

                    const json = await res.json();
                    const reason = json.comment || "-";

                    // Tampilkan reason dengan opsi edit
                    const result = await Swal.fire({
                        title: "Rejection reason",
                        html: `<div style="text-align:left; white-space:pre-wrap;">${escapeHtml(reason)}</div>`,
                        showCancelButton: false,
                        showDenyButton: true,
                        denyButtonText: "Edit",
                        confirmButtonText: "Close"
                    });

                    // Jika user klik Edit
                    if (result.isDenied) {
                        editReason(id_letter, reason);
                    }
                } catch (err) {
                    console.error("Error viewing reason:", err);
                    Swal.fire("Error", err.message, "error");
                }
            }

            /**
             * Edit rejection reason
             * 
             * @param {number} id_letter - ID letter submission
             * @param {string} currentReason - Current reason text
             */
            async function editReason(id_letter, currentReason) {
                const {
                    value: newReason,
                    isConfirmed
                } = await Swal.fire({
                    title: "Edit rejection reason",
                    input: "textarea",
                    inputValue: currentReason || "",
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

                // Kirim update ke API
                try {
                    const res = await fetch(`${apiBase}/cdc/history/${id_letter}/edit`, {
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
                        loadSubmissions(); // Reload data
                    } else {
                        Swal.fire("Error", json.message, "error");
                    }
                } catch (err) {
                    console.error("Error editing reason:", err);
                    Swal.fire("Error", err.message, "error");
                }
            }

            /**
             * Escape HTML untuk prevent XSS injection di SweetAlert
             * 
             * @param {string} str - String yang akan di-escape
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


            // ============================================
            // FUNGSI FILTER & STUDY PROGRAM
            // ============================================

            /**
             * Load study programs untuk dropdown filter
             * Hanya load program studi yang sesuai dengan kampus CDC
             */
            async function loadStudyPrograms() {
                if (!cdcKampusId) {
                    console.error("CDC Kampus ID tidak tersedia");
                    return;
                }

                try {
                    const res = await fetch(`${apiBase}/cdc/study-programs/${cdcKampusId}`);
                    const json = await res.json();

                    if (json.success && json.data.length > 0) {
                        const select = document.getElementById("filter_study_program");
                        select.innerHTML = '<option value="">All Study Programs</option>';

                        json.data.forEach(item => {
                            const option = document.createElement("option");
                            option.value = item.kode_prodi;
                            option.textContent = `${item.kode_prodi} - ${item.program_name}`;
                            select.appendChild(option);
                        });
                    }
                } catch (err) {
                    console.error("Error loading study programs:", err);
                }
            }

            /**
             * Apply filter - dipanggil saat user mengubah filter
             * Fungsi ini akan reload submissions dengan parameter filter
             */
            function applyFilter() {
                loadSubmissions(true); // true = gunakan filter
            }


            // ============================================
            // FUNGSI NAVIGASI & UTILITY
            // ============================================

            /**
             * View detail submission - redirect ke halaman detail
             * 
             * @param {number} id - ID letter submission
             */
            function viewDetail(id) {
                console.log("Opening submission detail for:", id);
                window.location.href = `detail_submissions_cdc.php?id=${id}`;
            }

            /**
             * Logout confirmation & redirect
             * Menghapus session PHP dan localStorage
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
        </script>
</body>

</html>