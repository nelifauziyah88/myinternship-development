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
                                                <option value="approve">Approve</option>
                                                <option value="waiting">Waiting</option>
                                                <option value="reject">Reject</option>
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
                    </div>

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
            const lecturerId = "<?php echo $nim_nik_unit; ?>";
            const apiBase = "http://localhost:8000/api";

            document.addEventListener("DOMContentLoaded", loadSubmissions);

            async function loadSubmissions() {
                const body = document.getElementById("tableBody");
                body.innerHTML = "<tr><td colspan='8'>Loading...</td></tr>";

                try {
                    const res = await fetch(`${apiBase}/lecturer/submissions/${lecturerId}`);
                    const json = await res.json();

                    if (!json.success || !json.data || json.data.length === 0) {
                        body.innerHTML = "<tr><td colspan='8' class='text-center text-muted'>No data found.</td></tr>";
                        return;
                    }

                    body.innerHTML = "";

                    json.data.forEach((item, index) => {
                        const date = new Date(item.created_at).toLocaleDateString("en-GB");

                        const formatTime = (t) => {
                            if (!t) return "-";
                            const d = new Date(t);
                            return d.toLocaleDateString("en-GB");
                        };

                        // === KOOR APPROVAL ===
                        let coordinatorHtml = "";
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
              <a class="dropdown-item text-danger" href="#" onclick="updateApproval(${item.id_letter}, 'REJECTED', this)">
                <i class="fas fa-times text-danger"></i> Reject
              </a>
            </div>
          </div>`;
                        } else if (item.koor_approval === "ACCEPTED") {
                            coordinatorHtml = `
          <div class="text-center">
            <span class="badge approved">Approved</span>
            <div class="text-muted" style="font-size:12px;margin-top:2px;">${formatTime(item.updated_at)}</div>
          </div>`;
                        } else if (item.koor_approval === "REJECTED") {
                            coordinatorHtml = `
          <div class="text-center">
            <span class="badge rejected">Rejected</span>
            <div class="text-muted" style="font-size:12px;margin-top:2px;">${formatTime(item.updated_at)}</div>
          </div>`;
                        }

                        // === CDC APPROVAL ===
                        let cdcHtml = "";
                        if (item.koor_approval === "WAITING") {
                            // Jika koor belum approve → CDC tidak bisa bertindak
                            cdcHtml = `<span class="badge-empty">-</span>`;
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
            <div class="text-muted" style="font-size:12px;margin-top:2px;">${formatTime(item.updated_at)}</div>
          </div>`;
                        } else {
                            cdcHtml = `<span class="badge-empty">-</span>`;
                        }

                        // === HASIL / RESULT ===
                        const result = "-";

                        // === ROW ===
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
                    console.error(err);
                    body.innerHTML = "<tr><td colspan='8' class='text-danger'>Error loading data.</td></tr>";
                }
            }

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
                            status
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

            function viewDetail(id) {
                window.location.href = `detail_submission.php?id=${id}`;
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

            // Script Filter All
            // Variabel global untuk menyimpan semua data
            let allSubmissions = [];
            let sortAscending = true;

            document.addEventListener("DOMContentLoaded", loadSubmissions);

            async function loadSubmissions() {
                const body = document.getElementById("tableBody");
                body.innerHTML = "<tr><td colspan='8'>Loading...</td></tr>";

                try {
                    const res = await fetch(`${apiBase}/lecturer/submissions/${lecturerId}`);
                    const json = await res.json();

                    if (!json.success || !json.data || json.data.length === 0) {
                        body.innerHTML = "<tr><td colspan='8' class='text-center text-muted'>No data found.</td></tr>";
                        allSubmissions = [];
                        return;
                    }

                    // Simpan semua data ke variabel global
                    allSubmissions = json.data;

                    // Render data pertama kali
                    renderTable(allSubmissions);

                } catch (err) {
                    console.error(err);
                    body.innerHTML = "<tr><td colspan='8' class='text-danger'>Error loading data.</td></tr>";
                    allSubmissions = [];
                }
            }

            function renderTable(data) {
                const body = document.getElementById("tableBody");

                if (!data || data.length === 0) {
                    body.innerHTML = "<tr><td colspan='8' class='text-center text-muted'>No data found.</td></tr>";
                    return;
                }

                body.innerHTML = "";

                data.forEach((item, index) => {
                    const date = new Date(item.created_at).toLocaleDateString("en-GB");

                    const formatTime = (t) => {
                        if (!t) return "-";
                        const d = new Date(t);
                        return d.toLocaleDateString("en-GB");
                    };

                    // === KOOR APPROVAL ===
                    let coordinatorHtml = "";
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
                        <a class="dropdown-item text-danger" href="#" onclick="updateApproval(${item.id_letter}, 'REJECTED', this)">
                            <i class="fas fa-times text-danger"></i> Reject
                        </a>
                    </div>
                </div>`;
                    } else if (item.koor_approval === "ACCEPTED") {
                        coordinatorHtml = `
                <div class="text-center">
                    <span class="badge approved">Approved</span>
                    <div class="text-muted" style="font-size:12px;margin-top:2px;">${formatTime(item.updated_at)}</div>
                </div>`;
                    } else if (item.koor_approval === "REJECTED") {
                        coordinatorHtml = `
                <div class="text-center">
                    <span class="badge rejected">Rejected</span>
                    <div class="text-muted" style="font-size:12px;margin-top:2px;">${formatTime(item.updated_at)}</div>
                </div>`;
                    }

                    // === CDC APPROVAL ===
                    let cdcHtml = "";
                    if (item.koor_approval === "WAITING") {
                        cdcHtml = `<span class="badge-empty">-</span>`;
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
                    <div class="text-muted" style="font-size:12px;margin-top:2px;">${formatTime(item.updated_at)}</div>
                </div>`;
                    } else {
                        cdcHtml = `<span class="badge-empty">-</span>`;
                    }

                    // === HASIL / RESULT ===
                    const result = "-";

                    // === ROW ===
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
            }

            // Fungsi untuk filter data
            function applyFilter() {
                const studentName = document.getElementById("filter_student_name").value.toLowerCase().trim();
                const coordinatorStatus = document.getElementById("filter_coordinator").value.toLowerCase();
                const cdcStatus = document.getElementById("filter_cdc").value.toLowerCase();
                const companyResult = document.getElementById("filter_company").value.toLowerCase();

                let filteredData = allSubmissions.filter(item => {
                    // Filter by Student Name
                    const matchName = !studentName || item.student_name.toLowerCase().includes(studentName);

                    // Filter by Coordinator Approval
                    let matchCoordinator = true;
                    if (coordinatorStatus) {
                        if (coordinatorStatus === "approved") {
                            matchCoordinator = item.koor_approval === "ACCEPTED";
                        } else if (coordinatorStatus === "waiting") {
                            matchCoordinator = item.koor_approval === "WAITING";
                        } else if (coordinatorStatus === "rejected") {
                            matchCoordinator = item.koor_approval === "REJECTED";
                        }
                    }

                    // Filter by CDC Approval
                    let matchCDC = true;
                    if (cdcStatus) {
                        if (cdcStatus === "approve") {
                            matchCDC = item.cdc_approval === "ACCEPTED";
                        } else if (cdcStatus === "waiting") {
                            matchCDC = item.cdc_approval === "WAITING";
                        } else if (cdcStatus === "reject") {
                            matchCDC = item.cdc_approval === "REJECTED";
                        }
                    }

                    // Filter by Company Result
                    let matchCompany = true;
                    if (companyResult) {
                        // CEK DULU apakah field company_result ada di database
                        if (item.company_result && item.company_result !== "-") {
                            // Jika ada data company_result, baru filter
                            if (companyResult === "accepted") {
                                matchCompany = item.company_result === "ACCEPTED";
                            } else if (companyResult === "waiting") {
                                matchCompany = item.company_result === "WAITING";
                            } else if (companyResult === "rejected") {
                                matchCompany = item.company_result === "REJECTED";
                            }
                        } else {
                            // Jika masih "-" atau null, anggap tidak match
                            matchCompany = false;
                        }
                    }

                    return matchName && matchCoordinator && matchCDC && matchCompany;
                });

                renderTable(filteredData);
            }

            // Fungsi sorting berdasarkan tanggal
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

                applyFilter(); // Re-apply filter setelah sorting
            }

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
                            status
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

            function viewDetail(id) {
                window.location.href = `detail_submissions_koor.php?id=${id}`;
            }
        </script>

        <script src="./assets/js/core/bootstrap.min.js"></script>
        <script src="./assets/js/atlantis.min.js"></script>
        <script src="https://kit.fontawesome.com/a076d05399.js"></script>
</body>

</html>