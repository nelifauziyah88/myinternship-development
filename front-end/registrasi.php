<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>Student Registration</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />

  <!-- Icon -->
  <link rel="icon" href="./assets/img/iconM.png" type="image/x-icon" />
  <link href="./assets/img/iconM.png" rel="apple-touch-icon" type="image/x-icon">

  <!-- SweetAlert2 untuk Toast -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

  <style>
    body {
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
      background: #f5f5f5;
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 30px 15px;
    }

    .register-container {
      background: white;
      max-width: 650px;
      width: 100%;
      padding: 30px 35px;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      border: 1px solid #e0e0e0;
    }

    .register-container h3 {
      text-align: center;
      font-weight: 600;
      font-size: 22px;
      color: #1e3a8a;
      margin-bottom: 20px;
    }

    .alert-info {
      background: #ffffff;
      border-left: 3px solid #2196f3;
      padding: 12px 15px;
      font-size: 13px;
      color: #333333;
      margin-bottom: 25px;
      border-radius: 4px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .section-title {
      font-weight: 600;
      font-size: 16px;
      margin: 25px 0 18px;
      color: #333;
      text-align: center;
    }

    label {
      font-size: 13px;
      font-weight: 500;
      color: #555;
      margin-bottom: 6px;
      display: block;
    }

    label .text-danger {
      color: #d32f2f;
      margin-left: 2px;
    }

    .form-control,
    .form-select {
      font-size: 13px;
      padding: 8px 12px;
      height: 38px;
      border-radius: 4px;
      border: 1px solid #d0d0d0;
      transition: all 0.2s ease;
      background-color: #fff;
    }

    .form-control:focus,
    .form-select:focus {
      border-color: #2196f3;
      outline: none;
      box-shadow: 0 0 0 2px rgba(33, 150, 243, 0.1);
      background-color: white;
    }

    .form-control::placeholder {
      color: #999;
      font-size: 13px;
    }

    .form-control:disabled,
    .form-control[readonly] {
      background-color: #f5f5f5;
      cursor: not-allowed;
      color: #999;
    }

    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 15px;
      margin-bottom: 15px;
    }

    .form-group {
      margin-bottom: 15px;
    }

    .password-requirements {
      font-size: 12px;
      color: #555;
      margin-top: 10px;
      margin-bottom: 15px;
      line-height: 1.6;
    }

    .password-requirements strong {
      display: block;
      margin-bottom: 8px;
      color: #333;
    }

    .password-requirements ul {
      margin: 0;
      padding-left: 18px;
    }

    .password-requirements li {
      margin-bottom: 4px;
    }

    .terms-checkbox {
      display: flex;
      align-items: flex-start;
      gap: 8px;
      margin: 20px 0;
      font-size: 13px;
      color: #555;
    }

    .terms-checkbox input[type="checkbox"] {
      margin-top: 3px;
      cursor: pointer;
    }

    .terms-checkbox a {
      color: #2196f3;
      text-decoration: none;
    }

    .terms-checkbox a:hover {
      text-decoration: underline;
    }

    .btn-group {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
      margin-top: 25px;
    }

    .btn-home {
      background: #f5f5f5;
      font-weight: 500;
      font-size: 14px;
      border-radius: 4px;
      padding: 10px 20px;
      color: #555;
      border: 1px solid #d0d0d0;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      cursor: pointer;
      transition: all 0.2s ease;
      text-decoration: none;
    }

    .btn-home:hover {
      background: #e8e8e8;
      color: #333;
    }

    .btn-signin {
      background: #2196f3;
      font-weight: 500;
      font-size: 14px;
      border-radius: 4px;
      padding: 10px 20px;
      color: white;
      border: none;
      transition: all 0.2s ease;
      cursor: pointer;
    }

    .btn-signin:hover {
      background: #1976d2;
    }

    .password-field {
      position: relative;
    }

    .password-toggle {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      color: #999;
      font-size: 14px;
    }

    /* ===== Toast Styling - Minimal & Clean ===== */
    .swal2-popup.swal2-toast {
      width: 550px !important;
      padding: 16px 20px !important;
      border-radius: 10px !important;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15) !important;
      border-left: 5px solid #e91e63 !important;
      column-gap: 18px !important;
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

    /* Warna border sesuai tipe toast */
    .swal2-popup.swal2-toast.swal2-icon-error {
      border-left-color: #e91e63 !important;
    }

    .swal2-popup.swal2-toast.swal2-icon-success {
      border-left-color: #4caf50 !important;
    }

    .swal2-popup.swal2-toast.swal2-icon-info {
      border-left-color: #2196f3 !important;
    }

    .swal2-popup.swal2-toast.swal2-icon-warning {
      border-left-color: #ff9800 !important;
    }

    @media (max-width: 576px) {
      .form-row {
        grid-template-columns: 1fr;
      }

      .register-container {
        padding: 25px 20px;
      }
    }
  </style>
</head>

<body>
  <div class="register-container">
    <h3>Student Registration</h3>

    <div class="alert-info">
      Before you start using MyInternship to manage your internship data, please register an account by filling out the form below.
    </div>

    <form id="registerForm">
      <div class="section-title">Student Information</div>

      <div class="form-group">
        <label for="name">Name <span class="text-danger">*</span></label>
        <input id="name" name="name" class="form-control" type="text" placeholder="Enter Your Name" required />
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="nim">NIM <span class="text-danger">*</span></label>
          <input id="nim" name="nim" class="form-control" type="text" placeholder="Enter Your Student Number (NIM)" required />
        </div>
        <div class="form-group">
          <label for="email">E-Mail <span class="text-danger">*</span></label>
          <input id="email" name="email" class="form-control" type="email" placeholder="Enter Your Email" required />
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="university">University or Polytechnic <span class="text-danger">*</span></label>
          <select id="university" name="university" class="form-select" required>
            <option value="" selected disabled>-Select University or Polytechnic-</option>
            <option value="polbatam">Politeknik Negeri Batam</option>
          </select>
        </div>
        <div class="form-group">
          <label for="study">Study Program <span class="text-danger">*</span></label>
          <select id="study" name="study" class="form-select" required>
            <option selected disabled>- Select Study Program -</option>
            <option>D4-Teknologi Rekayasa Perangkat Lunak (D4 Software Development Engineering)</option>
            <option>D4-Rekayasa Keamanan Siber (D4 Cyber Security Engineering)</option>
            <option>D4-Teknologi Rekayasa Multimedia (D4 Multimedia Engineering)</option>
            <option>D3-Teknik Informatika (D3 Informatics Engineering)</option>
          </select>
        </div>
      </div>

      <div class="section-title">Account Information</div>

      <div class="form-row">
        <div class="form-group">
          <label for="username">Username <span class="text-danger">*</span></label>
          <input id="username" name="username" class="form-control" type="text" placeholder="Your Username will be the same as NIM" readonly />
        </div>
        <div class="form-group">
          <label for="wa">Whatsapp Number <span class="text-danger">*</span></label>
          <input id="wa" name="wa" class="form-control" type="tel" placeholder="Insert WhatsApp Number, e.g., 6281234568xxxx" required />
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="password">Password <span class="text-danger">*</span></label>
          <div class="password-field">
            <input id="password" name="password" class="form-control" type="password" placeholder="Enter Your Password" required />
            <i class="fas fa-eye password-toggle" onclick="togglePassword('password', this)"></i>
          </div>
        </div>
        <div class="form-group">
          <label for="confirm_password">Confirm Password <span class="text-danger">*</span></label>
          <div class="password-field">
            <input id="confirm_password" name="confirm_password" class="form-control" type="password" placeholder="Confirm Your Password" required />
            <i class="fas fa-eye password-toggle" onclick="togglePassword('confirm_password', this)"></i>
          </div>
        </div>
      </div>

      <div class="password-requirements">
        <strong>Password Requirements:</strong>
        <ul>
          <li>At least 8 characters long</li>
          <li>Must include both uppercase and lowercase letters</li>
          <li>Must contain at least one number</li>
          <li>Must have at least one special character (e.g., !, @, #, $, etc.)</li>
          <li>Cannot include your username or email address</li>
        </ul>
      </div>

      <div class="terms-checkbox">
        <input type="checkbox" id="terms" name="terms" required />
        <label for="terms" style="margin-bottom: 0; font-weight: 400;">
          I agree to the <a href="#">Terms and Conditions</a>
        </label>
      </div>

      <div class="btn-group">
        <a href="landing_page.php" class="btn-home"><i class="fas fa-home"></i> Go to Home</a>
        <button class="btn-signin" type="submit">Submit</button>
      </div>
    </form>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
  <!-- SweetAlert2 Library -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    const Toast = Swal.mixin({
      toast: true,
      position: 'top',
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true,
      didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
      }
    });

    // Auto-fill username dari NIM
    document.getElementById('nim').addEventListener('input', function(e) {
      document.getElementById('username').value = e.target.value;
    });

    // Toggle password visibility
    function togglePassword(fieldId, icon) {
      const field = document.getElementById(fieldId);
      if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
      } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
      }
    }

    // --- FETCH DROPDOWN DATA DARI BACKEND ---
    async function loadKampus() {
      try {
        const kampusResponse = await fetch("http://localhost:8000/api/kampus");
        const kampusData = await kampusResponse.json();

        const kampusSelect = document.getElementById("university");
        kampusSelect.innerHTML = '<option value="" disabled selected>- Select University or Polytechnic -</option>';
        kampusData.forEach(kampus => {
          kampusSelect.innerHTML += `<option value="${kampus.id_kampus}">${kampus.nama_kampus}</option>`;
        });
      } catch (err) {
        console.error("Error loading kampus:", err);
        Toast.fire({
          icon: 'error',
          title: 'Failed to load university data'
        });
      }
    }

    async function loadProgramStudy(idKampus) {
      try {
        const prodiResponse = await fetch(`http://localhost:8000/api/program_study/${idKampus}`);
        const prodiData = await prodiResponse.json();

        const prodiSelect = document.getElementById("study");
        prodiSelect.innerHTML = '<option value="" disabled selected>- Select Study Program -</option>';

        if (prodiData.length === 0) {
          prodiSelect.innerHTML += '<option disabled>No study program available for this university.</option>';
          return;
        }

        prodiData.forEach(prodi => {
          prodiSelect.innerHTML += `<option value="${prodi.kode_prodi}">
        ${prodi.jenjang} - ${prodi.prodi} (${prodi.jenjang} - ${prodi.study_program})
      </option>`;
        });
      } catch (err) {
        console.error("Error loading program study:", err);
        Toast.fire({
          icon: 'error',
          title: 'Failed to load study program data'
        });
      }
    }

    // Ketika halaman pertama kali dibuka, muat kampus
    window.addEventListener("DOMContentLoaded", loadKampus);

    // Ketika kampus diubah, muat prodi berdasarkan kampus itu
    document.getElementById("university").addEventListener("change", function() {
      const idKampus = this.value;
      if (idKampus) loadProgramStudy(idKampus);
    });

    // --- KONEKSI KE BACKEND ---
    document.getElementById('registerForm').addEventListener('submit', async function(e) {
      e.preventDefault();

      const name = document.getElementById('name').value.trim();
      const nim = document.getElementById('nim').value.trim();
      const email = document.getElementById('email').value.trim();
      const programStudy = document.getElementById('study').value; // ambil kode_prodi langsung
      const noWhatsapp = document.getElementById('wa').value.trim();
      const username = document.getElementById('username').value.trim();
      const password = document.getElementById('password').value;
      const confirmPassword = document.getElementById('confirm_password').value;

      // Validasi client-side
      const errors = [];

      // Wajib
      if (!name) errors.push('Name is required !');
      if (!nim) errors.push('NIM is required !');
      if (!email) errors.push('E-mail is required !');
      // Cek study: pastikan student memilih opsi selain placeholder
      if (!programStudy || programStudy.toLowerCase().includes('select')) {
        errors.push('Study Program is required !');
      }
      if (!noWhatsapp) errors.push('WhatsaApp number is required !');
      if (!username) errors.push('Username is required !');

      // Confirm password
      if (password !== confirmPassword) errors.push('The password and password confirmation do not match !');

      // Password rules
      function validatePassword(pw) {
        if (pw.length < 8) return 'Password must be at least 8 characters long !';
        if (!(/[a-z]/.test(pw) && /[A-Z]/.test(pw))) return 'Passwords must contain both uppercase and lowercase letters !';
        if (!/\d/.test(pw)) return 'Passwords must contain at least 1 number !';
        if (!/[!@#$%^&*(),.?":{}|<>_\-\[\]\\\/;'+=]/.test(pw)) return 'Passwords must contain at least 1 special character !';
        // Jangan berisi username atau email (case-insensitive)
        const lower = pw.toLowerCase();
        if (username && lower.includes(username.toLowerCase())) return 'Passwords must not contain usernames !';
        if (email && lower.includes(email.toLowerCase())) return 'Passwords must not contain email addresses !';
        return null;
      }

      const pwError = validatePassword(password);
      if (pwError) errors.push(pwError);

      if (errors.length > 0) {
        Toast.fire({
          icon: 'error',
          title: 'Validation Error',
          html: errors.map(err => `• ${err}`).join('<br>'),
          timer: 5000
        });

        // Re-enable button
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        return;
      }

      // Validasi sukses 
      const formData = {
        nim,
        name,
        programStudy,
        email,
        otherEmail: "",
        phone: "", // sementara kosong
        noWhatsapp,
        username,
        password,
        nikDospem: "", // default kosong
        idKampus: document.getElementById('university').value
      };

      try {
        const response = await fetch('http://localhost:8000/api/registrasi', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(formData)
        });

        const result = await response.json();

        if (response.ok) {
          Toast.fire({
            icon: 'success',
            title: result.message || 'Registration successful!',
            timer: 2000
          }).then(() => {
            // Redirect setelah toast hilang
            window.location.href = 'role_login.php';
          });
        } else {
          Toast.fire({
            icon: 'error',
            title: 'Registration Failed',
            text: result.message || 'Please try again',
            timer: 4000
          });

          // Re-enable button
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalText;
        }
      } catch (error) {
        console.error('Error:', error);
        Toast.fire({
          icon: 'error',
          title: 'Connection Error',
          text: 'Unable to connect to the server',
          timer: 4000
        });

        // Re-enable button
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
      }
    });
  </script>
</body>

</html>