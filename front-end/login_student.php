<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>Student Login | MyInternship</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- Icon -->
  <link rel="icon" href="./assets/img/iconM.png" type="image/x-icon" />
  <link href="./assets/img/iconM.png" rel="apple-touch-icon" type="image/x-icon">

  <!-- SweetAlert2 untuk Toast -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

  <!-- Bootstrap CSS -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
  <style>
    body.login {
      background-color: #f5f7fa;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .login-container {
      height: 100vh;
    }

    .left-section {
      background: #e9f6ff;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 40px;
    }

    .left-section img {
      max-width: 320px;
      filter: drop-shadow(0 4px 15px rgba(0, 0, 0, 0.1));
    }

    .right-section {
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 40px;
      background-color: #fff;
    }

    .card-login {
      width: 530px;
      padding: 70px;
      border-radius: 10px;
      min-height: 600px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
      background-color: #fff;
    }

    .card-login h4 {
      font-weight: 700;
      font-size: 22px;
      color: #1e3a8a;
      margin-bottom: 25px;
      text-align: center;
    }

    label {
      font-weight: 600;
      font-size: 14px;
      color: #374151;
      margin-bottom: 8px;
    }

    .form-control {
      height: 46px;
      font-size: 13.5px;
      border-radius: 8px;
      border: 1.5px solid #d1d5db;
      padding: 12px 16px;
    }

    .form-control:focus {
      border-color: #3b82f6;
      box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.15);
    }

    .form-text {
      font-size: 12.5px;
      color: #6b7280;
      margin-top: 6px;
      line-height: 1.4;
    }

    .form-text b {
      color: #374151;
    }

    .form-check-label {
      font-size: 13px;
      color: #6b7280;
    }

    .btn-signin {
      background-color: #3b82f6;
      border: none;
      color: white;
      font-weight: 600;
      font-size: 15px;
      padding: 10px 0;
      border-radius: 8px;
      width: 120px;
    }

    .btn-signin:hover {
      background-color: #2563eb;
    }

    .btn-go-back {
      border: 1.5px solid #facc15;
      background-color: #fff8db;
      color: #ca8a04;
      font-weight: 600;
      font-size: 15px;
      padding: 10px 0;
      border-radius: 8px;
      width: 100%;
      margin-bottom: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      transition: all 0.2s ease;
    }

    .btn-go-back:hover {
      background-color: #fef9c3;
    }

    .btn-go-home {
      border: 1.5px solid #d1d5db;
      background-color: #fff;
      color: #374151;
      font-weight: 600;
      font-size: 15px;
      padding: 10px 0;
      border-radius: 8px;
      width: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      transition: all 0.2s ease;
    }

    .btn-go-home:hover {
      background-color: #f9fafb;
    }

    .password-wrapper {
      position: relative;
    }

    .password-wrapper .form-control {
      padding-right: 40px;
    }

    .password-wrapper .toggle-password {
      position: absolute;
      right: 20px;
      top: 70%;
      transform: translateY(-50%);
      color: #9ca3af;
      cursor: pointer;
      font-size: 14px;
    }

    .swal2-popup.swal2-toast {
      width: 550px !important;
      padding: 16px 20px !important;
      border-radius: 10px !important;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15) !important;
      border-left: 5px solid #e91e63 !important;
      display: flex !important;
      align-items: center !important;
      column-gap: 15px !important;
    }

    .swal2-popup.swal2-toast .swal2-icon {
      margin: 0 !important;
      width: 32px !important;
      height: 32px !important;
      flex-shrink: 0 !important;
    }

    .swal2-popup.swal2-toast .swal2-icon .swal2-icon-content {
      font-size: 20px !important;
    }

    .swal2-popup.swal2-toast .swal2-html-container {
      margin: 0 !important;
      padding: 0 !important;
      display: flex !important;
      align-items: center !important;
    }

    .swal2-popup.swal2-toast .swal2-title {
      font-size: 15px !important;
      font-weight: 700 !important;
      color: #222 !important;
    }

    .swal2-popup.swal2-toast .swal2-html-container {
      font-size: 13px !important;
      color: #555 !important;
      line-height: 1.7 !important;
    }

    .swal2-popup.swal2-toast.swal2-icon-error {
      border-left-color: #e91e63 !important;
    }

    .swal2-popup.swal2-toast.swal2-icon-success {
      border-left-color: #43b747ff !important;
    }

    .swal2-popup.swal2-toast.swal2-icon-info {
      border-left-color: #2196f3 !important;
    }

    .swal2-popup.swal2-toast.swal2-icon-warning {
      border-left-color: #ff9800 !important;
    }

    @media (max-width: 768px) {
      .left-section {
        display: none;
      }

      .card-login {
        width: 100%;
        max-width: 400px;
        padding: 30px;
      }
    }
  </style>
</head>

<body class="login">
  <div class="container-fluid login-container d-flex">
    <div class="row w-100 g-0">
      <!-- Left Section -->
      <div class="col-lg-6 left-section">
        <img src="assets/img/logo.png" alt="MyInternship Logo">
      </div>
      <!-- Right Section -->
      <div class="col-lg-6 right-section">
        <div class="card card-login">
          <h4>Sign In To Start Your Session</h4>

          <form method="POST">
            <div class="mb-3">
              <label for="username">Learning Account or MyInternship Username <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="username" name="username"
                placeholder="fulan.3311811022 or tika.4311811022@students.polibatam" required>
              <small class="form-text">
                <b>Polibatam Student ?</b><br>
                Use your learning account to login or<br>
                MyInternship Account if you have one !
              </small>
            </div>

            <div class="mb-3 password-wrapper">
              <label for="password">Password <span class="text-danger">*</span></label>
              <input type="password" class="form-control" id="password" name="password"
                placeholder="Enter Your Password" required>
              <i class="fas fa-eye toggle-password" id="togglePassword"></i>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
              <div class="form-check">
                <input type="checkbox" class="form-check-input" id="remember">
                <label class="form-check-label" for="remember">Remember Me</label>
              </div>
              <button type="submit" class="btn btn-signin">Sign In</button>
            </div>

            <a href="role_login.php" class="btn btn-go-back">
              <i class="fas fa-arrow-left"></i> Go Back
            </a>

            <a href="landing_page.php" class="btn btn-go-home">
              <i class="fas fa-home"></i> Go to Home
            </a>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- SweetAlert2 Library -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>

    const Toast = Swal.mixin({
      toast: true,
      position: 'top',
      showConfirmButton: false,
      timer: 2000,
      timerProgressBar: true,
      didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
      }
    });

    // Toggle password visibility
    const togglePassword = document.querySelector("#togglePassword");
    const password = document.querySelector("#password");
    togglePassword.addEventListener("click", function () {
      const type = password.getAttribute("type") === "password" ? "text" : "password";
      password.setAttribute("type", type);
      this.classList.toggle("fa-eye-slash");
    });

    // Handle login form
    const form = document.querySelector("form");

    form.addEventListener("submit", async (e) => {
      e.preventDefault();

      const username = document.getElementById("username").value.trim();
      const password = document.getElementById("password").value.trim();

      if (!username || !password) {
        Toast.fire({
          icon: 'error',
          title: 'Validation Error',
          html: 'Username and password are required !',
          timer: 2000
        });
        return;
      }

      try {
        const response = await fetch("http://localhost:8000/api/student/login_student", {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify({
            username,
            password
          }),
        });

        const data = await response.json();

        if (data.success) {
          try {
            const rememberChecked = document.getElementById('remember').checked;

            const sessionResp = await fetch('session_login.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json'
              },
              credentials: "include",
              body: JSON.stringify({
                user: data.user,
                remember: rememberChecked,
                role: 'student'
              })
            });

            const sessionData = await sessionResp.json();

            if (sessionData.success) {
              window.location.href = 'dashboard_student_final.php';
            } else {
              Toast.fire({
                icon: 'error',
                title: 'Session Error',
                html: `Failed to create session: ${sessionData.message || 'Unknown'}`,
                timer: 2000
              });
            }
          } catch (err) {
            console.error('Session error:', err);
            Toast.fire({
              icon: 'error',
              title: 'Session Error',
              html: 'Failed to create session. Please try again !',
              timer: 2000
            });
          }
        } else {
          Toast.fire({
            icon: 'error',
            title: 'Login Failed',
            html: `${data.message}`,
            timer: 2000
          });
        }

      } catch (error) {
        console.error("Error:", error);
        Toast.fire({
          icon: 'error',
          title: 'Connection Error',
          html: 'An error occurred during login. Please try again !',
          timer: 2000
        });
      }
    });
  </script>

</body>

</html>