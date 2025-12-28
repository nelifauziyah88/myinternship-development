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
    <meta charset="utf-8">
    <title>Approval CDC</title>
    <meta content='width=device-width, initial-scale=1.0, shrink-to-fit=no' name='viewport' />
    <!-- Icon -->
    <link rel="icon" href="./assets/img/iconM.png" type="image/x-icon" />
    <link href="./assets/img/iconM.png" rel="apple-touch-icon" type="image/x-icon">

    <!-- JS PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>

    <link rel='stylesheet' href='./core/component/sweetalert2.min.css'>
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- CSS Files -->
    <link rel="stylesheet" href="./assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="./assets/css/atlantis.css">

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

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
        }

        .wrap2 {
            white-space: normal !important;
            word-wrap: break-word;
            min-width: 170px;
            max-width: 170px;
        }

        .main-panel {
            padding-top: 50px;
        }

        .btn-xs {
            padding: 4px 8px;
            font-size: 0.75rem;
            line-height: 1.2;
            border-radius: 4px;
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

        .same-width {
            max-width: 243px;
        }

        .letter-popover {
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            margin-top: 12px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1050;
            min-width: 250px;
        }

        .popover-arrow {
            position: absolute;
            top: -7px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 0;
            border-left: 8px solid transparent;
            border-right: 8px solid transparent;
            border-bottom: 8px solid white;
        }

        .popover-arrow::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -9px;
            width: 0;
            height: 0;
            border-left: 9px solid transparent;
            border-right: 9px solid transparent;
            border-bottom: 9px solid #ddd;
        }

        .letter-number-clickable {
            color: #007bff;
            cursor: pointer;
            font-weight: 500;
        }

        .letter-number-clickable:hover {
            color: #0056b3;
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

                            <!-- Date -->
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

                            <!-- Time -->
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

                        <!-- Profile -->
                        <li class="nav-item dropdown hidden-caret">
                            <a class="dropdown-toggle profile-pic" data-toggle="dropdown" href="#"
                                aria-expanded="false">
                                <div class="avatar-sm">
                                    <img src="assets/img/profile.png" alt="..." class="avatar-img rounded-circle">
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-user animated fadeIn">
                                <div class="dropdown-user-scroll scrollbar-outer">
                                    <li>
                                        <div class="user-box">
                                            <div class="avatar-lg"><img src="./assets/img/profile.png"
                                                    alt="image profile" class="avatar-img rounded"></div>
                                            <div class="u-text">
                                                <h5><?php echo htmlspecialchars($user['name']); ?></h5>
                                                <p class="text-muted"> CDC at :<br><?= htmlspecialchars($nama_kampus) ?>
                                                </p>
                                                <a href="#" class="btn btn-xs btn-secondary btn-sm">View Profile</a>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="cdc_dashboard.php">My Dashboard</a>
                                        <a class="dropdown-item" href="#">My Profile</a>
                                        <a class="dropdown-item" href="#">My Company</a>
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
                                    <span class="user-level wrap2">CDC at :
                                        <br><?php echo htmlspecialchars($nama_kampus); ?></span>
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
                            <a href="approval_of_letter_cdc.php" class="collapsed" aria-expanded="false">
                                <i class="fas fa-briefcase"></i>
                                <p>Approval Letter</p>
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
                            <h1 class="text-white pb-2 fw-bold">Approval Status</h1>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
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
                                            <label for="filter_study_program" class="form-label">Filter By Study
                                                Program</label>
                                            <select class="form-control" id="filter_study_program" name="study_program"
                                                onchange="applyFilter()">
                                                <option value="">Select Study Program</option>
                                            </select>
                                        </div>

                                        <!-- Filter By Department -->
                                        <div class="col-md mb-3">
                                            <label for="filter_department" class="form-label">Filter By
                                                Department</label>
                                            <select class="form-control" id="filter_department" name="department"
                                                onchange="onDepartmentChange()">
                                                <option value="">All Departments</option>
                                            </select>
                                        </div>

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
                                    </div>
                                    <div class="row">
                                        <!-- Filter By Result Company -->
                                        <div class="col-md-3 mb-3 same-width">
                                            <label for="filter_company" class="form-label">Filter by Company Decision</label>
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
                        <!-- Export Excel -->
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
                                <div class="table-responsive" style="overflow-x: auto;">
                                    <table class="table table-bordered table-hover" id="approvalTable" style="min-width: 1400px;">
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
                                                <th style="width: 100px;">Letter Number</th>
                                                <th style="width: 150px;">Result</th>
                                                <th style="width: 150px;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tableBody">
                                        </tbody>
                                    </table>
                                </div>
                                <!-- Next and Previous -->
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

        <!--   Core JS Files   -->
        <script src="./assets/js/core/popper.min.js"></script>
        <script src="./assets/js/core/bootstrap.min.js"></script>
        <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/xlsx-js-style/dist/xlsx.min.js"></script>

        <script>
            const currentUserId = <?= json_encode($user['id_upkpk'] ?? "-") ?>;
            const currentUserName = <?= json_encode($user['name'] ?? "-") ?>;
            const cdcKampusId = "<?php echo $id_kampus; ?>";

            const apiBase = "http://localhost:8000/api";

            let allSubmissions = [];
            let sortAscending = true;

            document.addEventListener("DOMContentLoaded", function() {
                loadDepartments();
                loadStudyPrograms();
                loadSubmissions();
            });

            function loadYears() {
                const yearSelect = document.getElementById("filter_year");
                const currentYear = new Date().getFullYear();

                yearSelect.innerHTML = `<option value="">ALL</option>`; // Default option

                for (let y = currentYear; y >= 2021; y--) {
                    yearSelect.innerHTML += `<option value="${y}">${y}</option>`;
                }
            }

            async function loadDepartments() {
                if (!cdcKampusId) {
                    console.error("CDC Kampus ID tidak tersedia");
                    return;
                }

                try {
                    const res = await fetch(`${apiBase}/cdc/departments/${cdcKampusId}`);
                    const json = await res.json();

                    if (json.success && json.data.length > 0) {
                        const select = document.getElementById("filter_department");
                        select.innerHTML = '<option value="">All Departments</option>';

                        json.data.forEach(item => {
                            const option = document.createElement("option");
                            option.value = item.department;
                            option.textContent = item.department;
                            select.appendChild(option);
                        });
                    }
                } catch (err) {
                    console.error("Error loading departments:", err);
                }
            }

            async function onDepartmentChange() {
                const department = document.getElementById("filter_department").value;
                const studyProgramSelect = document.getElementById("filter_study_program");

                if (!department) {
                    await loadStudyPrograms();
                    applyFilter();
                    return;
                }

                try {
                    // Load study programs by selected department
                    const res = await fetch(`${apiBase}/cdc/study-programs/${cdcKampusId}`);
                    const json = await res.json();

                    if (json.success && json.data.length > 0) {
                        // Filter study programs by selected department
                        const filteredPrograms = json.data.filter(item => item.major === department);

                        studyProgramSelect.innerHTML = '<option value="">All Study Programs</option>';

                        filteredPrograms.forEach(item => {
                            const option = document.createElement("option");
                            option.value = item.kode_prodi;
                            option.textContent = `${item.kode_prodi} - ${item.study_program}`;
                            studyProgramSelect.appendChild(option);
                        });
                    }

                    applyFilter();

                } catch (err) {
                    console.error("Error loading study programs by department:", err);
                }
            }

            async function loadSubmissions(useFilter = false) {
                const body = document.getElementById("tableBody");
                body.innerHTML = "<tr><td colspan='8' class='text-center'>Loading...</td></tr>";

                try {
                    let apiUrl = `${apiBase}/cdc/submissions`;
                    if (useFilter) {
                        const department = document.getElementById("filter_department").value;
                        const studyProgram = document.getElementById("filter_study_program").value;
                        const studentName = document.getElementById("filter_student_name").value;
                        const coordinator = document.getElementById("filter_coordinator").value;
                        const cdcFilter = document.getElementById("filter_cdc").value;
                        const company = document.getElementById("filter_company").value;

                        let queryParams = new URLSearchParams();
                        queryParams.append('id_kampus', cdcKampusId);

                        if (department) queryParams.append('department', department);
                        if (studyProgram) queryParams.append('study_program', studyProgram);
                        if (studentName) queryParams.append('student_name', studentName);
                        if (coordinator) queryParams.append('coordinator', coordinator);
                        if (cdcFilter) queryParams.append('cdc', cdcFilter);
                        if (company) queryParams.append('company', company);

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

            function renderTable(data) {
                const body = document.getElementById("tableBody");

                // Handle empty data
                if (!data || data.length === 0) {
                    body.innerHTML = "<tr><td colspan='9' class='text-center text-muted'>No data found.</td></tr>";
                    return;
                }

                // SORTING BERDASARKAN PRIORITAS YANG BENAR
                const sortedData = data.sort((a, b) => {
                    const koorA = a.koor_approval?.toUpperCase() || '';
                    const cdcA = a.cdc_approval?.toUpperCase() || '';
                    const resultA = a.acceptance_status?.toUpperCase() || '';

                    const koorB = b.koor_approval?.toUpperCase() || '';
                    const cdcB = b.cdc_approval?.toUpperCase() || '';
                    const resultB = b.acceptance_status?.toUpperCase() || '';

                    // Priority 1: Koor WAITING + CDC WAITING
                    const isWaitingA = (koorA === 'WAITING' && cdcA === 'WAITING');
                    const isWaitingB = (koorB === 'WAITING' && cdcB === 'WAITING');

                    if (isWaitingA && !isWaitingB) return -1;
                    if (!isWaitingA && isWaitingB) return 1;
                    if (isWaitingA && isWaitingB) {
                        // Yang LAMA di atas (FIFO)
                        return new Date(a.created_at) - new Date(b.created_at);
                    }

                    // Priority 2: Koor ACC + CDC WAITING (CDC harus action)
                    const isCDCWaitingA = (koorA === 'ACCEPTED' && cdcA === 'WAITING');
                    const isCDCWaitingB = (koorB === 'ACCEPTED' && cdcB === 'WAITING');

                    if (isCDCWaitingA && !isCDCWaitingB) return -1;
                    if (!isCDCWaitingA && isCDCWaitingB) return 1;
                    if (isCDCWaitingA && isCDCWaitingB) {
                        // Yang LAMA di atas (FIFO)
                        return new Date(a.created_at) - new Date(b.created_at);
                    }

                    // Priority 3: Koor ACC + CDC ACC + Result ACC (COMPLETED - ACCEPTED)
                    const isAccAccAccA = (koorA === 'ACCEPTED' && cdcA === 'ACCEPTED' && resultA === 'ACCEPTED');
                    const isAccAccAccB = (koorB === 'ACCEPTED' && cdcB === 'ACCEPTED' && resultB === 'ACCEPTED');

                    if (isAccAccAccA && !isAccAccAccB) return -1;
                    if (!isAccAccAccA && isAccAccAccB) return 1;
                    if (isAccAccAccA && isAccAccAccB) {
                        // Yang BARU di atas (terbaru muncul paling atas)
                        return new Date(b.created_at) - new Date(a.created_at);
                    }

                    // Priority 4: Koor ACC + CDC ACC + Result - (WAITING FOR COMPANY REPLY)
                    const isAccAccEmptyA = (koorA === 'ACCEPTED' && cdcA === 'ACCEPTED' && (!resultA || resultA === '-'));
                    const isAccAccEmptyB = (koorB === 'ACCEPTED' && cdcB === 'ACCEPTED' && (!resultB || resultB === '-'));

                    if (isAccAccEmptyA && !isAccAccEmptyB) return -1;
                    if (!isAccAccEmptyA && isAccAccEmptyB) return 1;
                    if (isAccAccEmptyA && isAccAccEmptyB) {
                        // Yang BARU di atas
                        return new Date(b.created_at) - new Date(a.created_at);
                    }

                    // Priority 5: Koor ACC + CDC ACC + Result REJECT (COMPLETED - REJECTED BY COMPANY)
                    const isAccAccRejA = (koorA === 'ACCEPTED' && cdcA === 'ACCEPTED' && resultA === 'REJECTED');
                    const isAccAccRejB = (koorB === 'ACCEPTED' && cdcB === 'ACCEPTED' && resultB === 'REJECTED');

                    if (isAccAccRejA && !isAccAccRejB) return -1;
                    if (!isAccAccRejA && isAccAccRejB) return 1;
                    if (isAccAccRejA && isAccAccRejB) {
                        // Yang BARU di atas
                        return new Date(b.created_at) - new Date(a.created_at);
                    }

                    // Priority 6: Koor ACC + CDC REJECT
                    const isAccRejA = (koorA === 'ACCEPTED' && cdcA === 'REJECTED');
                    const isAccRejB = (koorB === 'ACCEPTED' && cdcB === 'REJECTED');

                    if (isAccRejA && !isAccRejB) return -1;
                    if (!isAccRejA && isAccRejB) return 1;
                    if (isAccRejA && isAccRejB) {
                        // Yang BARU di atas
                        return new Date(b.created_at) - new Date(a.created_at);
                    }

                    // Priority 7: Koor REJECT + CDC REJECT
                    const isRejRejA = (koorA === 'REJECTED' && cdcA === 'REJECTED');
                    const isRejRejB = (koorB === 'REJECTED' && cdcB === 'REJECTED');

                    if (isRejRejA && !isRejRejB) return -1;
                    if (!isRejRejA && isRejRejB) return 1;
                    if (isRejRejA && isRejRejB) {
                        // Yang BARU di atas
                        return new Date(b.created_at) - new Date(a.created_at);
                    }

                    // Default: urutkan berdasarkan tanggal (terbaru di atas)
                    return new Date(b.created_at) - new Date(a.created_at);
                });

                body.innerHTML = "";

                // Loop through each sorted submission
                sortedData.forEach((item, i) => {
                    body.innerHTML += buildTableRow(item, i);
                });
            }

            function buildTableRow(item, index) {
                const date = formatDate(item.created_at);
                const koorHtml = buildKoordinatorBadge(item);
                const cdcHtml = buildCDCApprovalHtml(item);
                const letterNumberHtml = buildLetterNumberHtml(item);
                const resultHtml = buildResultBadge(item);

                return `
        <tr>
            <td class="text-center">${index + 1}</td>
            <td class="text-center">${date}</td>
            <td class="text-center">${item.nim}</td>
            <td>${item.student_name}</td>
            <td class="text-center">${koorHtml}</td>
            <td class="text-center">${cdcHtml}</td>
            <td class="text-center">${letterNumberHtml}</td>
            <td class="text-center">${resultHtml}</td>
            <td class="text-center align-middle">
                <button class="btn btn-info btn-sm" onclick="viewDetail(${item.id_letter})">
                    <i class="fa fa-eye"></i> Details
                </button>
            </td>
        </tr>
    `;
            }

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

            function buildCDCApprovalHtml(item) {
                const updatedDate = formatDate(item.updated_at);

                if (item.koor_approval === "REJECTED") {
                    return `
            <div class="text-center">
                <span class="badge rejected">Rejected</span>
                <div class="text-muted" style="font-size:12px;margin-top:2px;">-</div>
            </div>
        `;
                }
                if (item.koor_approval === "WAITING") {
                    return `-`;
                }
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
                if (item.cdc_approval === "ACCEPTED") {
                    return `
            <div class="text-center">
                <span class="badge approved">Approved</span>
                <div class="text-muted" style="font-size:12px;margin-top:2px;">${updatedDate}</div>
            </div>
        `;
                }
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
                return `-`;
            }

            function buildLetterNumberHtml(item) {
                if (item.koor_approval !== "ACCEPTED") {
                    return `-`;
                }

                function extractFrontNumber(fullNumber) {
                    if (!fullNumber || fullNumber === '-') return '-';
                    if (fullNumber.includes('/')) {
                        return fullNumber.split('/')[0];
                    }
                    return fullNumber;
                }

                if (item.cdc_approval === "ACCEPTED" || item.cdc_approval === "REJECTED") {
                    const displayNumber = extractFrontNumber(item.letter_number);
                    return `<span style="color: #000;">${displayNumber}</span>`;
                }

                if (item.cdc_approval === "WAITING") {
                    const hasLetterNumber = item.letter_number && item.letter_number.trim() !== '';

                    if (hasLetterNumber) {
                        const displayNumber = extractFrontNumber(item.letter_number);
                        return `<span style="color: #000;">${displayNumber}</span>`;
                    }

                    return `
            <div class="position-relative d-inline-block" style="min-width: 80px;">
                <span 
                    id="letter-display-${item.id_letter}" 
                    class="letter-number-clickable"
                    onclick="openLetterPopover(${item.id_letter}, event)"
                >-</span>
                
                <!-- Popover -->
                <div 
                    id="popover-${item.id_letter}" 
                    class="letter-popover" 
                    style="display: none;"
                >
                    <div class="popover-arrow"></div>
                    <div style="margin-bottom: 10px;">
                        <label style="font-size: 12px; margin-bottom: 5px; display: block; font-weight: 600; color: #333;">
                            Enter Internship Letter Number
                        </label>
                        <div style="display: flex; gap: 5px; align-items: center;">
                            <input 
                                type="text" 
                                id="letter-input-${item.id_letter}" 
                                class="form-control form-control-sm" 
                                placeholder="e.g. 15"
                                style="width: 70px; font-size: 13px;"
                                inputmode="numeric"
                                onkeypress="if(event.key==='Enter') saveLetterNumber(${item.id_letter})"
                            />
                            <span style="font-size: 11px; color: #6c757d;">${getLetterSuffix()}</span>
                        </div>
                    </div>
                    <div style="display: flex; gap: 6px; justify-content: flex-end;">
                        <button 
                            class="btn btn-sm btn-secondary" 
                            onclick="closeLetterPopover(${item.id_letter})"
                            style="font-size: 11px; padding: 4px 10px;"
                        >
                            Cancel
                        </button>
                        <button 
                            class="btn btn-sm btn-primary" 
                            onclick="saveLetterNumber(${item.id_letter})"
                            style="font-size: 11px; padding: 4px 10px;"
                        >
                            Save
                        </button>
                    </div>
                </div>
            </div>
        `;
                }

                return `-`;
            }

            function getLetterSuffix() {
                const roman = monthToRoman(new Date().getMonth());
                const year = new Date().getFullYear();
                return `/WDIII.PL29/${roman}/${year}`;
            }

            function openLetterPopover(id, event) {
                event.stopPropagation();

                document.querySelectorAll('.letter-popover').forEach(pop => {
                    pop.style.display = 'none';
                });

                const popover = document.getElementById(`popover-${id}`);
                popover.style.display = 'block';

                setTimeout(() => {
                    document.getElementById(`letter-input-${id}`)?.focus();
                }, 50);
            }

            function closeLetterPopover(id) {
                document.getElementById(`popover-${id}`).style.display = 'none';
            }

            async function saveLetterNumber(id) {
                const inputEl = document.getElementById(`letter-input-${id}`);
                const value = inputEl?.value.trim();

                if (!value) {
                    alert("Letter number is required");
                    inputEl?.focus();
                    return;
                }

                if (!/^\d+$/.test(value)) {
                    alert("Only digits allowed");
                    inputEl?.focus();
                    return;
                }

                const fullNumber = `${value}${getLetterSuffix()}`;

                try {
                    const res = await fetch(`${apiBase}/cdc/update-letter-number`, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            id_letter: id,
                            letter_number: fullNumber
                        })
                    });

                    const json = await res.json();

                    if (json.success) {
                        closeLetterPopover(id);

                        const displayEl = document.getElementById(`letter-display-${id}`);
                        displayEl.textContent = value;
                        displayEl.style.color = '#000';
                        displayEl.classList.remove('letter-number-clickable');
                        displayEl.onclick = null;
                    } else {
                        alert(json.message);
                    }
                } catch (err) {
                    console.error("Error saving letter number:", err);
                    alert("Failed to save: " + err.message);
                }
            }

            document.addEventListener('click', function(e) {
                if (!e.target.closest('.position-relative')) {
                    document.querySelectorAll('.letter-popover').forEach(pop => {
                        pop.style.display = 'none';
                    });
                }
            });

            function buildResultBadge(item) {
                const acceptance = item.acceptance_status;

                if (!acceptance || acceptance === '-') {
                    return `<span>-</span>`;
                }
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

            function formatDate(timestamp) {
                if (!timestamp) return "-";
                return new Date(timestamp).toLocaleDateString("en-GB");
            }

            // Bulan ke angka Romawi
            function monthToRoman(monthIndexZeroBased) {
                const map = ["I", "II", "III", "IV", "V", "VI", "VII", "VIII", "IX", "X", "XI", "XII"];
                return map[monthIndexZeroBased] || "";
            }

            // Perubahan pada handleApproval
            async function handleApproval(id, status) {
                let comment = null;

                if (status === "REJECTED") {
                    const {
                        value: reason,
                        isConfirmed
                    } = await Swal.fire({
                        title: "Why are you rejecting?",
                        text: "Please provide your reason for rejecting this submission.",
                        input: "textarea",
                        inputPlaceholder: "Write the reason here...",
                        showCancelButton: true,
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

                const confirm = await Swal.fire({
                    title: "Confirm?",
                    text: `You are about to mark this submission as ${status}`,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes, confirm"
                });

                if (!confirm.isConfirmed) return;

                await sendApprovalToAPI(id, status, comment);
            }

            // sendApprovalToAPI menerima optional letter_number
            async function sendApprovalToAPI(id, status, comment = "-") {
                try {
                    const payload = {
                        id_letter: id,
                        status,
                        user_id: currentUserId,
                        user_name: currentUserName,
                        comment
                    };

                    const res = await fetch(`${apiBase}/cdc/approval`, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify(payload)
                    });

                    const json = await res.json();

                    if (json.success) {
                        Swal.fire("Success!", json.message, "success");
                        loadSubmissions();
                    } else {
                        Swal.fire("Warning", json.message, "warning");
                    }
                } catch (err) {
                    console.error("Error sending approval:", err);
                    Swal.fire("Error", err.message, "error");
                }
            }
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

                    // Show reason with edit option
                    const result = await Swal.fire({
                        title: "Rejection reason",
                        html: `<div style="text-align:left; white-space:pre-wrap;">${escapeHtml(reason)}</div>`,
                        showCancelButton: false,
                        showDenyButton: true,
                        denyButtonText: "Edit",
                        confirmButtonText: "Close"
                    });

                    if (result.isDenied) {
                        editReason(id_letter, reason);
                    }
                } catch (err) {
                    console.error("Error viewing reason:", err);
                    Swal.fire("Error", err.message, "error");
                }
            }

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
                        loadSubmissions();
                    } else {
                        Swal.fire("Error", json.message, "error");
                    }
                } catch (err) {
                    console.error("Error editing reason:", err);
                    Swal.fire("Error", err.message, "error");
                }
            }

            // Escape HTML to prevent XSS injection in SweetAlert
            function escapeHtml(str) {
                if (!str) return "";
                return String(str)
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }

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

            function applyFilter() {
                console.log("Applying filters...");
                loadSubmissions(true);
            }

            function viewDetail(id) {
                console.log("Opening submission detail for:", id);
                window.location.href = `detail_submissions_cdc.php?id=${id}`;
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

                    if (data.company_reply_letter && data.company_reply_letter !== '-') {
                        const fileName = data.company_reply_letter;
                        const fileUrl = `./${data.company_reply_letter}`;

                        // Tentukan apakah PDF atau gambar
                        const isPDF = fileName.toLowerCase().endsWith('.pdf');

                        let statusBadge = '';
                        if (data.acceptance_status === 'ACCEPTED') {
                            statusBadge = '<span class="badge approved">Accepted</span>';
                        } else if (data.acceptance_status === 'REJECTED') {
                            statusBadge = '<span class="badge rejected">Rejected</span>';
                        } else {
                            statusBadge = '<span class="badge waiting">Waiting</span>';
                        }

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
                            title: '',
                            html: modalContent,
                            width: '900px',
                            showConfirmButton: true,
                            confirmButtonText: 'Close',
                            customClass: {
                                popup: 'animated fadeIn'
                            }
                        });
                    }

                    // REJECTED tanpa file (overdue)
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
                    // Tidak ada data sama sekali
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

            // Fetch internship data dengan filter dari API
            async function fetchInternshipData() {
                const year = document.getElementById("filter_year").value;
                const studyProgram = document.getElementById("filter_study_program").value;
                const department = document.getElementById("filter_department").value;
                const yearDisplay = year ? year : "All Year";

                const programMap = {
                    "AB": "Applied Business Administration",
                    "AK": "Accounting",
                    "AM": "Managerial Accounting",
                    "AN": "Animation",
                    "Bengkalis-IF": "Informatics Engineering",
                    "DBG": "Goods Distribution",
                    "EM": "Manufacturing Electronics Engineering",
                    "GM": "Geomatics Engineering",
                    "IF": "Informatics Engineering",
                    "IF-FR": "Informatics",
                    "INS": "Instrumentation Engineering",
                    "LPI": "International Trade Logistics",
                    "ME-FR": "Mechanical engineering",
                    "MJ": "Multimedia Engineering",
                    "MK": "Mechatronic Engineering",
                    "MS": "Mechanical Engineering",
                    "OT": "Automation Engineering",
                    "PPI": "Program Profesi Insinyur",
                    "RE": "Robotics Engineering",
                    "RKS": "Cyber Security Engineering",
                    "RPE": "Energy Generation Engineering Technology",
                    "TPKP": "Ship Design and Construction Engineering",
                    "TPPU": "Aircraft Maintenance Engineering",
                    "TRE": "Electrical Engineering",
                    "TRPL": "Software Development Engineering"
                };

                const programDisplay = studyProgram ?
                    (programMap[studyProgram] || studyProgram) :
                    "All Programs";

                const departmentDisplay = department || "All Departments";

                try {
                    let url = `${apiBase}/cdc/export-internship?year=${year}`;

                    if (department && department.trim() !== '') {
                        url += `&department=${encodeURIComponent(department)}`;
                    }

                    if (studyProgram && studyProgram.trim() !== '') {
                        url += `&study_program=${encodeURIComponent(studyProgram)}`;
                    }

                    const res = await fetch(url);
                    const json = await res.json();

                    if (!json.success) {
                        let message = "";

                        if (department && studyProgram && year) {
                            message = `No internship data found for ${programDisplay} in department ${departmentDisplay} in ${yearDisplay}`;
                        } else if (department && studyProgram && !year) {
                            message = `No internship data found for ${programDisplay} in department ${departmentDisplay} across all years`;
                        } else if (department && !studyProgram && year) {
                            message = `No internship data found for department ${departmentDisplay} in ${yearDisplay}`;
                        } else if (department && !studyProgram && !year) {
                            message = `No internship data found for department ${departmentDisplay} across all years`;
                        } else if (!department && studyProgram && year) {
                            message = `No internship data found for ${programDisplay} in ${yearDisplay}`;
                        } else if (!department && studyProgram && !year) {
                            message = `No internship data found for ${programDisplay} across all years`;
                        } else if (!department && !studyProgram && year) {
                            message = `No internship data found for all programs in ${yearDisplay}`;
                        } else {
                            message = `No internship data found`;
                        }

                        Swal.fire({
                            icon: "info",
                            title: "No Data Found",
                            text: message,
                            confirmButtonText: "OK"
                        });
                        return null;
                    }

                    if (!json.data || json.data.length === 0) {
                        let message = "";

                        if (department && studyProgram && year) {
                            message = `No active internship students found for ${programDisplay} in department ${departmentDisplay} in ${yearDisplay}`;
                        } else if (department && studyProgram && !year) {
                            message = `No active internship students found for ${programDisplay} in department ${departmentDisplay} across all years`;
                        } else if (department && !studyProgram && year) {
                            message = `No active internship students found for department ${departmentDisplay} in ${yearDisplay}`;
                        } else if (department && !studyProgram && !year) {
                            message = `No active internship students found for department ${departmentDisplay} across all years`;
                        } else if (!department && studyProgram && year) {
                            message = `No active internship students found for ${programDisplay} in ${yearDisplay}`;
                        } else if (!department && studyProgram && !year) {
                            message = `No active internship students found for ${programDisplay} across all years`;
                        } else if (!department && !studyProgram && year) {
                            message = `No active internship students found for all programs in ${yearDisplay}`;
                        } else {
                            message = `No active internship students found`;
                        }

                        Swal.fire({
                            icon: "info",
                            title: "No Data Found",
                            text: message,
                            confirmButtonText: "OK"
                        });
                        return null;
                    }

                    console.log(` Export data loaded: ${json.count} students`);
                    console.log(` Filter: ${json.filter}`);
                    return json.data;

                } catch (err) {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Failed to fetch data: " + err.message,
                        confirmButtonText: "OK"
                    });
                    return null;
                }
            }

            function formatDate(dateString) {
                if (!dateString) return "-";
                const date = new Date(dateString);
                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const year = date.getFullYear();
                return `${day}/${month}/${year}`;
            }

            /**
            Fetch internship data (CDC) with start/end + optional filters
             */
            async function fetchInternshipData(startDate, endDate) {
                const yearDisplay = (startDate && endDate) ? `${startDate} to ${endDate}` : "All Range";
                const studyProgram = document.getElementById("filter_study_program").value;
                const department = document.getElementById("filter_department").value;

                try {
                    let params = new URLSearchParams();
                    params.append('start_date', startDate);
                    params.append('end_date', endDate);
                    params.append('id_kampus', cdcKampusId);

                    if (department && department.trim() !== '') params.append('department', department);
                    if (studyProgram && studyProgram.trim() !== '') params.append('study_program', studyProgram);

                    const res = await fetch(`${apiBase}/cdc/export-internship?${params.toString()}`);
                    const json = await res.json();

                    if (!json.success) {
                        Swal.fire({
                            icon: "info",
                            title: "No Data Found",
                            text: json.message || `No internship data found for the selected date range (${startDate} - ${endDate})`,
                            confirmButtonText: "OK"
                        });
                        return null;
                    }

                    if (!json.data || json.data.length === 0) {
                        Swal.fire({
                            icon: "info",
                            title: "No Data Found",
                            text: `No students are doing internships in the selected date range (${startDate} - ${endDate})`,
                            confirmButtonText: "OK"
                        });
                        return null;
                    }

                    console.log(`Export data loaded: ${json.count || json.total_data || json.data.length} students`);
                    return json.data;
                } catch (err) {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Failed to fetch data: " + err.message,
                        confirmButtonText: "OK"
                    });
                    return null;
                }
            }

            function getYearDisplay() {
                const year = document.getElementById("filter_year").value;
                return year ? year : "All Year";
            }

            // Export to Excel (CDC)
            async function exportToExcel() {
                const {
                    value: formValues
                } = await Swal.fire({
                    title: 'Select Internship Period',
                    html: '<label for="swal-start" style="display:block;text-align:left;margin-bottom:6px">Start date</label>' +
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
                        if (new Date(start) > new Date(end))
                            return Swal.showValidationMessage('Start date must be before or equal to End date');

                        return {
                            start,
                            end
                        };
                    }
                });

                if (!formValues) return;
                const {
                    start,
                    end
                } = formValues;

                Swal.fire({
                    title: 'Fetching data...',
                    text: 'Please wait...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                const data = await fetchInternshipData(start, end);

                if (!data || data.length === 0) {
                    Swal.close();
                    Swal.fire({
                        icon: "info",
                        title: "No Data Found",
                        text: "No internship records found."
                    });
                    return;
                }

                try {
                    const wb = XLSX.utils.book_new();

                    const excelData = [
                        ['INTERNSHIP DATA REPORT'],
                        [`Period: ${formatDate(start)} - ${formatDate(end)}`],
                        [`Total Students: ${data.length}`],
                        [],
                        [
                            'No', 'NIM', 'Name', 'Study Program', 'Department', 'Class', 'Semester',
                            'Coordinator', 'Company Name', 'Company Contact', 'Company Address',
                            'Start Date', 'End Date', 'Email', 'WhatsApp'
                        ],
                        ...data.map((item, index) => [
                            index + 1,
                            item.nim || '-',
                            item.student_name || '-',
                            item.program_study || '-',
                            item.department || '-',
                            item.class || '-',
                            item.semester || '-',
                            item.internship_coordinator || '-',
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

                    // Column Widths
                    ws['!cols'] = [{
                            wch: 5
                        }, {
                            wch: 15
                        }, {
                            wch: 25
                        }, {
                            wch: 35
                        }, {
                            wch: 20
                        }, {
                            wch: 10
                        },
                        {
                            wch: 15
                        }, {
                            wch: 25
                        }, {
                            wch: 50
                        }, {
                            wch: 15
                        },
                        {
                            wch: 60
                        }, {
                            wch: 14
                        }, {
                            wch: 14
                        },
                        {
                            wch: 30
                        }, {
                            wch: 20
                        }
                    ];

                    // Merge
                    ws['!merges'] = [{
                            s: {
                                r: 0,
                                c: 0
                            },
                            e: {
                                r: 0,
                                c: 14
                            }
                        },
                        {
                            s: {
                                r: 1,
                                c: 0
                            },
                            e: {
                                r: 1,
                                c: 14
                            }
                        },
                        {
                            s: {
                                r: 2,
                                c: 0
                            },
                            e: {
                                r: 2,
                                c: 14
                            }
                        }
                    ];

                    // Styles
                    const border = {
                        top: {
                            style: 'thin'
                        },
                        bottom: {
                            style: 'thin'
                        },
                        left: {
                            style: 'thin'
                        },
                        right: {
                            style: 'thin'
                        }
                    };

                    ws['A1'].s = {
                        font: {
                            name: 'Times New Roman',
                            sz: 16,
                            bold: true
                        },
                        alignment: {
                            horizontal: 'center'
                        }
                    };
                    ws['A2'].s = {
                        alignment: {
                            horizontal: 'center'
                        }
                    };
                    ws['A3'].s = {
                        alignment: {
                            horizontal: 'center'
                        }
                    };

                    const headerStyleWrap = {
                        font: {
                            name: 'Times New Roman',
                            sz: 11,
                            bold: true,
                            color: {
                                rgb: 'FFFFFF'
                            }
                        },
                        fill: {
                            fgColor: {
                                rgb: '4472C4'
                            }
                        },
                        alignment: {
                            horizontal: 'center',
                            vertical: 'center',
                            wrapText: true
                        },
                        border
                    };

                    const headerStyleNoWrap = {
                        font: {
                            name: 'Times New Roman',
                            sz: 11,
                            bold: true,
                            color: {
                                rgb: 'FFFFFF'
                            }
                        },
                        fill: {
                            fgColor: {
                                rgb: '4472C4'
                            }
                        },
                        alignment: {
                            horizontal: 'center',
                            vertical: 'center',
                            wrapText: false
                        },
                        border
                    };

                    const headerCols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O'];

                    headerCols.forEach((col, idx) => {
                        if (!ws[col + '5']) return;

                        if (idx === 9) ws[col + '5'].s = headerStyleNoWrap;
                        else ws[col + '5'].s = headerStyleWrap;
                    });

                    // === Data Style ===
                    const dataStart = 6;

                    for (let r = dataStart; r < dataStart + data.length; r++) {
                        headerCols.forEach((col, index) => {
                            const cell = ws[col + r];
                            if (!cell) return;

                            const isCenter = (index === 0 || index === 1);

                            cell.s = {
                                font: {
                                    name: 'Times New Roman',
                                    sz: 11
                                },
                                alignment: {
                                    horizontal: isCenter ? 'center' : 'left',
                                    vertical: 'top',
                                    wrapText: true
                                },
                                border
                            };
                        });
                    }

                    XLSX.utils.book_append_sheet(wb, ws, 'Internship Report');
                    XLSX.writeFile(wb, `Internship_${start}_to_${end}.xlsx`);

                    Swal.fire({
                        icon: "success",
                        title: "Success",
                        text: "Excel downloaded successfully!"
                    });

                } catch (err) {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: err.message
                    });
                }
            }

            function formatDate(dateString) {
                const d = new Date(dateString);
                return `${String(d.getDate()).padStart(2, '0')}/${String(d.getMonth() + 1).padStart(2, '0')}/${d.getFullYear()}`;
            }

            async function fetchInternshipData(start, end) {
                try {
                    const url = `${apiBase}/cdc/export-internship?start_date=${start}&end_date=${end}`;
                    const res = await fetch(url);
                    const json = await res.json();

                    if (!json.success || !json.data || json.data.length === 0) return null;

                    return json.data;

                } catch (err) {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: err.message
                    });
                    return null;
                }
            }
        </script>
</body>

</html>