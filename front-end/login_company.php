<?php
error_reporting(0);
session_start();

// --- Simulasi data user di database ---
$users = [
  ['username' => 'admin', 'password' => '12345'],
  ['username' => 'student', 'password' => 'abcd']
];

// --- Proses login ---
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $username = $_POST['username'];
  $password = $_POST['password'];

  $found = false;
  foreach ($users as $user) {
    if ($user['username'] === $username && $user['password'] === $password) {
      $found = true;
      break;
    }
  }

  if ($found) {
    $_SESSION['username'] = $username;
    header("Location: dashboard.php");
    exit;
  } else {
    $message = "❌ Username atau password salah!";
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>Login MyInternship</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
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
      margin-top: -20px;
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

          <?php if ($message): ?>
            <div class="alert alert-danger text-center py-2"><?= $message ?></div>
          <?php endif; ?>

          <form method="POST">
            <div class="mb-3">
              <label for="username">Username <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="username" name="username"
                placeholder="Enter Your Username" required>
              <small class="form-text">
                <b></b><br>
                <br>
              </small>
            </div>

            <div class="mb-3 password-wrapper">
              <label for="password">Password <span class="text-danger">*</span></label>
              <input type="password" class="form-control" id="password" name="password" placeholder="Enter Your Password" required>
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

  <script>
    const togglePassword = document.querySelector("#togglePassword");
    const password = document.querySelector("#password");
    togglePassword.addEventListener("click", function() {
      const type = password.getAttribute("type") === "password" ? "text" : "password";
      password.setAttribute("type", type);
      this.classList.toggle("fa-eye-slash");
    });
  </script>
</body>

</html>