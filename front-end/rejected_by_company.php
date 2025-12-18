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
  $api_url = "http://localhost:8000/api/kampus/" . urlencode($id_kampus);

  $response = @file_get_contents($api_url);

  if ($response !== false) {
    $data = json_decode($response, true);

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
  <meta charset="utf-8">
  <meta content='width=device-width, initial-scale=1.0, shrink-to-fit=no' name='viewport' />
  <title>Internship Rejectance Confirmation Form</title>
  <link rel="icon" href="./assets/img/iconM.png" type="image/x-icon" />
  <link rel='stylesheet' href='./core/component/sweetalert2.min.css'>
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="./assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="./assets/css/atlantis.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
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

  <script src='./core/component/jquery.min.js'></script>
  <script defer src='./core/component/sweetalert2.min.js'></script>

  <style type="text/css">
    .main-panel {
      padding-top: 50px;
    }

    .wrap2 {
      white-space: normal !important;
      word-wrap: break-word;
      min-width: 170px;
      max-width: 170px;
    }

    .sidebar a.active {
      background-color: #007bff;
      color: white !important;
      border-radius: 10px;
    }

    .sidebar a.active i {
      color: white;
    }

    .sidebar {
      position: fixed;
      z-index: 1000;
    }

    .page-inner {
      padding-bottom: 20px;
    }

    .form-group {
      margin-bottom: 24px;
    }

    .form-container label {
      font-weight: 500;
      color: #1f2937;
      display: block;
      margin-bottom: 8px;
      font-size: 14px;
    }

    .form-container select {
      width: 100%;
      padding: 10px 12px;
      border: 1.5px solid #d1d5db;
      border-radius: 6px;
      font-size: 14px;
      background-color: #f9fafb;
      color: #374151;
      appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23374151' d='M10.293 3.293L6 7.586 1.707 3.293A1 1 0 00.293 4.707l5 5a1 1 0 001.414 0l5-5a1 1 0 10-1.414-1.414z'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 12px center;
      cursor: pointer;
    }

    .form-container select:focus {
      outline: none;
      border-color: #2563eb;
      box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .form-container select option {
      padding: 10px;
    }

    .upload-box {
      border: 2px dashed #d1d5db;
      border-radius: 8px;
      padding: 50px 40px;
      text-align: center;
      color: #6b7280;
      background: #f9fafb;
      cursor: pointer;
      transition: all 0.3s;
    }

    .upload-box:hover {
      border-color: #2563eb;
      background: #eff6ff;
    }

    .upload-box i {
      font-size: 48px;
      color: #9ca3af;
      margin-bottom: 12px;
    }

    .upload-box .upload-title {
      font-size: 14px;
      font-weight: 600;
      color: #1f2937;
      margin-bottom: 4px;
    }

    .upload-box .upload-subtitle {
      font-size: 13px;
      color: #6b7280;
    }

    .btn-back {
      background-color: #e5e7eb;
      color: #374151;
      border: none;
      padding: 10px 24px;
      border-radius: 6px;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.2s;
      font-size: 14px;
    }

    .btn-back:hover {
      background-color: #d1d5db;
    }

    .btn-submit {
      background-color: #2563eb;
      color: white;
      border: none;
      padding: 10px 24px;
      border-radius: 6px;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.2s;
      font-size: 14px;
    }

    .btn-submit:hover {
      background-color: #1d4ed8;
    }

    .form-container {
      background-color: white;
      border-radius: 8px;
      padding: 40px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
      margin-bottom: 130px;
    }

    .disabled-btn {
      opacity: 0.5;
      cursor: not-allowed;
    }

    .remove-file-btn {
      position: absolute;
      top: 8px;
      right: 12px;
      font-size: 18px;
      color: #d9534f;
      cursor: pointer;
      display: none;
    }

    .upload-box-wrapper {
      position: relative;
    }
  </style>
</head>

<body>

  <div class="wrapper">

    <!-- Calendar Modal -->
    <div class="modal fade" id="Modalkalender" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header no-bd">
            <h5 class="modal-title"><span class="fw-mediumbold">Calendar</span></h5>
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

    <!-- Header -->
    <div class="main-header">
      <div class="logo-header" data-background-color="blue2">
        <a href="landing_page.php" class="logo">
          <img src="assets/img/logo_header.png" alt="navbar brand" class="navbar-brand"
            style="width: 180px; height: auto;">
        </a>
        <button class="navbar-toggler sidenav-toggler ml-auto" type="button">
          <span class="navbar-toggler-icon"><i class="icon-menu"></i></span>
        </button>
        <button class="topbar-toggler more"><i class="icon-options-vertical"></i></button>
        <div class="nav-toggle">
          <button class="btn btn-toggle toggle-sidebar"><i class="icon-menu"></i></button>
        </div>
      </div>

      <nav class="navbar navbar-header navbar-expand-lg" data-background-color="blue">
        <div class="container-fluid">
          <div class="collapse" id="search-nav">
            <ul class="navbar-nav navbar-left topbar-nav nav-search mr-md-3 align-items-center">
              <li class="nav-item dropdown hidden-caret">
                <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#">
                  <span id="date">Wed, 08 Oct 2025</span>
                </a>
              </li>
              <li class="nav-item dropdown hidden-caret">
                <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#">
                  <span id="clock">22 : 12 : 24</span>
                </a>
              </li>
            </ul>
          </div>
          <ul class="navbar-nav topbar-nav ml-md-auto align-items-center">
            <li class="nav-item toggle-nav-search hidden-caret">
              <a class="nav-link" data-toggle="collapse" href="#search-nav"><i class="fa fa-clock"></i></a>
            </li>
            <li class="nav-item dropdown hidden-caret">
              <a class="nav-link" data-toggle="modal" data-target="#Modalkalender"><i class="fa fa-calendar"></i></a>
            </li>
            <li class="nav-item dropdown hidden-caret">
              <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-bell"></i></a>
            </li>
            <li class="nav-item dropdown hidden-caret">
              <a class="dropdown-toggle profile-pic" data-toggle="dropdown" href="#">
                <div class="avatar-sm">
                  <img src="assets/img/profile.png" alt="..." class="avatar-img rounded-circle">
                </div>
              </a>
              <ul class="dropdown-menu dropdown-user animated fadeIn">
                <div class="dropdown-user-scroll scrollbar-outer">
                  <li>
                    <div class="user-box">
                      <div class="avatar-lg"><img src="assets/img/profile.png" class="avatar-img rounded"></div>
                      <div class="u-text">
                        <h5><?php echo htmlspecialchars($user['name'] ?? ''); ?></h5>
                        <p class="text-muted">Student at : <br?<?php echo htmlspecialchars($nama_kampus); ?></p>
                      </div>
                    </div>
                  </li>
                  <li>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="index.php">My Dashboard</a>
                    <a class="dropdown-item" href="#">My Profile</a>
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
              <a data-toggle="collapse" href="#collapseExample">
                <span>
                  <span class="wrap2"><?php echo htmlspecialchars($user['name'] ?? ''); ?></span>
                  <span class="user-level">NIM: <?php echo htmlspecialchars($user['nim'] ?? ''); ?></span>
                  <span class="user-level">Student at <br> <?php echo htmlspecialchars($nama_kampus); ?></span>
                </span>
              </a>
            </div>
          </div>

          <ul class="nav nav-primary">
            <li class="nav-item"><a href="dashboard_student.php"><i class="fas fa-tachometer-alt"></i>
                <p>Dashboard</p>
              </a></li>
            <li class="nav-item"><a href="home.php"><i class="fas fa-home"></i>
                <p>Home</p>
              </a></li>
            <li class="nav-item"><a href="student_identity.php"><i class="fas fa-id-card"></i>
                <p>Student Identity</p>
              </a></li>

            <li class="nav-section">
              <span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
              <h4 class="text-section">Student</h4>
            </li>
            <li class="nav-item"><a href="company_list.php"><i class="fas fa-building"></i>
                <p>Company List</p>
              </a></li>

            <li class="nav-section">
              <span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
              <h4 class="text-section">Internship Approval</h4>
            </li>
            <li class="nav-item"><a href="form_submission.php"><i class="fas fa-file-alt"></i>
                <p>Form Submission</p>
              </a></li>
            <li class="nav-item active"><a href="approval_status.php"><i class="fas fa-clipboard-check"></i>
                <p>Approval Status</p>
              </a></li>

            <li class="nav-section">
              <span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
              <h4 class="text-section">Account</h4>
            </li>
            <li class="nav-item"><a href="#"><i class="fas fa-user"></i>
                <p>Profile</p>
              </a></li>
            <li class="nav-item"><a href="#" onclick="logout_confirm()"><i class="fas fa-sign-out-alt"></i>
                <p>Logout</p>
              </a></li>
          </ul>
        </div>
      </div>
    </div>
    <!-- End Sidebar -->

    <div class="main-panel">
      <!-- Form Header -->
      <div class="panel-header bg-primary-gradient">
        <div class="page-inner py-5">
          <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row">
            <div>
              <h1 class="text-white pb-2 fw-bold">Internship Rejectance Confirmation Form</h1>
            </div>
          </div>
        </div>
      </div>

      <!-- Content -->
      <div class="page-inner mt--5">
        <div class="row mt--2">
          <div class="col-md-12">
            <div class="form-container">
              <form id="internshipForm">
                <div class="form-group">
                  <label>Have you received an internship response letter?</label>
                  <select id="responseReceived">
                    <option value="">-- Select --</option>
                    <option value="yes">Yes</option>
                    <option value="no">No</option>
                  </select>
                </div>

                <div class="form-group" id="within14Section" style="display:none;">
                  <label>Have 14 days or more passed since you submitted your internship application letter?</label>
                  <select id="responseWithin14Days">
                    <option value="">-- Select --</option>
                    <option value="yes">Yes</option>
                    <option value="no">No</option>
                  </select>
                </div>

                <!-- Upload jika pilih YES -->
                <div id="uploadSection" style="display:none;">
                  <div class="form-group">
                    <label>Upload your internship response letter</label>
                    <div class="upload-box-wrapper">
                      <span id="removeFileBtn" class="remove-file-btn" style="display:none;">✖</span>

                      <div class="upload-box" id="uploadBox" onclick="document.getElementById('fileInput').click()">
                        <i class="fa fa-cloud-upload-alt"></i>
                        <div class="upload-title">Browse Files</div>
                        <div class="upload-subtitle">Drag and drop files here</div>
                      </div>

                      <input type="file" id="fileInput" style="display:none;" accept=".pdf,.jpg,.jpeg,.png">
                    </div>

                  </div>
                </div>

                <div style="display:flex; justify-content: space-between; margin-top: 30px;">
                  <button type="button" class="btn-back" onclick="window.history.back()">
                    <i class="fas fa-arrow-left" style="margin-right:6px;"></i> Back
                  </button>

                  <button type="submit" id="submitBtn" class="btn-submit">
                    Submit <i class="fas fa-arrow-right" style="margin-left:6px;"></i>
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

      <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

      <!-- Core JS Files -->
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

      <!-- SWEETALERT -->
      <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

      <script>
        $(document).ready(function () {
          clock_run();

          const navItems = document.querySelectorAll(".sidebar .nav-item");
          const currentPage = window.location.href;
          navItems.forEach(item => {
            const link = item.querySelector("a");
            if (link && currentPage.includes(link.getAttribute("href"))) {
              navItems.forEach(i => i.classList.remove("active"));
              item.classList.add("active");
            }
          });
        });

        // CLOCK FUNCTION 
        function clock_run() {
          let d = new Date();
          let en_day = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
          let en_month = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
          let day = en_day[d.getDay()];
          let date = d.getDate();
          let month = en_month[d.getMonth()];
          let year = (d.getYear() + 1900);
          let curr_date = day + ', ' + date + ' ' + month + ' ' + year;

          $("#date").text(curr_date);

          setInterval(function () {
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

        // LOGOUT CONFIRM 
        function logout_confirm() {
          if (confirm('Are you sure you want to logout?')) {
            window.location.href = 'landing_page.php';
          }
        }
      </script>
    </div>
  </div>

  <script>
  const apiBase = "http://localhost:8000/api";
  const id = new URLSearchParams(window.location.search).get("id");

  const responseReceived = document.getElementById("responseReceived");
  const within14Section = document.getElementById("within14Section");
  const responseWithin14Days = document.getElementById("responseWithin14Days");
  const uploadSection = document.getElementById("uploadSection");
  const fileInput = document.getElementById("fileInput");
  const submitBtn = document.getElementById("submitBtn");
  const internshipForm = document.getElementById("internshipForm");
  const uploadBox = document.getElementById("uploadBox");
  const removeFileBtn = document.getElementById("removeFileBtn");

  document.querySelector("#within14Section label").textContent =
    "Have 14 days or more passed since you submitted your internship application letter?";

  submitBtn.disabled = true;
  submitBtn.classList.add("disabled-btn");

  responseReceived.addEventListener("change", () => {
      responseWithin14Days.value = "";
  submitBtn.disabled = true;
  submitBtn.classList.add("disabled-btn");
    if (responseReceived.value === "yes") {
      uploadSection.style.display = "block";
      within14Section.style.display = "none";
      submitBtn.disabled = false;
      submitBtn.classList.remove("disabled-btn");
    } else if (responseReceived.value === "no") {
      uploadSection.style.display = "none";
      within14Section.style.display = "block";
      fileInput.value = "";
      submitBtn.disabled = true;
      submitBtn.classList.add("disabled-btn");
    } else {
      uploadSection.style.display = "none";
      within14Section.style.display = "none";
      submitBtn.disabled = true;
      submitBtn.classList.add("disabled-btn");
    }
  });

  responseWithin14Days.addEventListener("change", () => {
    const value = responseWithin14Days.value;

    if (value === "yes") {
      submitBtn.disabled = false;
      submitBtn.classList.remove("disabled-btn");
    } else if (value === "no") {
      submitBtn.disabled = true;
      submitBtn.classList.add("disabled-btn");
      Swal.fire({
        icon: "warning",
        title: "Please wait",
        text: "You must wait 14 days for the company to respond to your internship claim.",
      });
    } else {
      submitBtn.disabled = true;
      submitBtn.classList.add("disabled-btn");
    }
  });

  fileInput.addEventListener("change", (e) => {
    const file = e.target.files[0];
    if (file) {
      uploadBox.innerHTML = `
        <i class="fa fa-file-alt"></i>
        <div class="upload-title">${file.name}</div>
        <div class="upload-subtitle">Click to change file</div>`;
      removeFileBtn.style.display = "block";
    }
  });

  removeFileBtn.addEventListener("click", (e) => {
    e.stopPropagation();
    fileInput.value = "";
    uploadBox.innerHTML = `
      <i class="fa fa-cloud-upload-alt"></i>
      <div class="upload-title">Browse Files</div>
      <div class="upload-subtitle">Drag and drop files here</div>`;
    removeFileBtn.style.display = "none";
  });

  internshipForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    if (submitBtn.disabled) return;

    if (responseReceived.value === "yes" && fileInput.files.length === 0) {
      return Swal.fire({
        icon: "warning",
        title: "File Required",
        text: "You must provide proof of rejection of the internship by the company.",
      });
    }

    let filePath = "-";

    try {
      if (fileInput.files.length > 0) {
        const fd = new FormData();
        fd.append("attachment", fileInput.files[0]);

        const uploadRes = await fetch("upload_company_reply.php", {
          method: "POST",
          body: fd,
        });

        const uploadJson = await uploadRes.json();

        if (!uploadJson.success) {
          return Swal.fire({
            icon: "error",
            title: "Upload Failed",
            text: uploadJson.message,
          });
        }

        filePath = uploadJson.path;
      }

      const res = await fetch(`${apiBase}/student/rejected-by-company/${id}`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          acceptance_status: "REJECTED",
          company_reply_letter: filePath,
        }),
      });

      const json = await res.json();

      if (json.success) {
        Swal.fire({
          icon: "success",
          title: "Submitted",
          text: "Company rejection recorded.",
        }).then(() => window.location.href = "approval_status.php");
      } else {

        Swal.fire({
          icon: "warning",
          title: "Please wait",
          text: json.message || "System has detected that the letter is less than 14 days old.",
        });
      }

    } catch (error) {
      Swal.fire({
        icon: "error",
        title: "Server Error",
        text: "Server unreachable.",
      });
    }
  });
</script>


</body>

</html>