<?php
$id_letter = $_GET['id'] ?? null;
if (!$id_letter) {
  die("Invalid request");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Detail Submission - CDC</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="./assets/img/iconM.png" type="image/x-icon" />

  <!-- Bootstrap & Atlantis -->
  <link rel="stylesheet" href="./assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="./assets/css/atlantis.css">

  <!-- FontAwesome -->
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    body {
      background: #f5f7fb;
      font-family: 'Lato', sans-serif;
      color: #333;
    }

    .container {
      max-width: 850px;
      margin-top: 60px;
    }

    .card {
      border: none;
      border-radius: 15px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    }

    .card-header {
      background: linear-gradient(135deg, #4e73df, #5a9bf6);
      color: #fff;
      border-top-left-radius: 15px;
      border-top-right-radius: 15px;
      padding: 20px 25px;
    }

    .card-header h4 {
      font-weight: 700;
      margin: 0;
    }

    .table th {
      width: 35%;
      background: #f0f3fa;
      border: none;
      color: #495057;
      font-weight: 600;
      vertical-align: middle;
    }

    .table td {
      border: none;
      color: #333;
    }

    .table tr:nth-child(even) td {
      background: #fafbfc;
    }

    .badge-status {
      padding: 6px 10px;
      border-radius: 6px;
      font-size: 13px;
      font-weight: 600;
      color: #fff;
      display: inline-block;
      min-width: 90px;
      text-align: center;
    }

    .badge-status.waiting {
      background-color: #f6c23e;
    }

    .badge-status.accepted {
      background-color: #28a745;
    }

    .badge-status.rejected {
      background-color: #e74a3b;
    }

    .approval-time {
      display: block;
      font-size: 12px;
      color: #666;
      margin-top: 4px;
    }

    .btn-back,
    .btn-edit {
      color: #fff;
      border-radius: 8px;
      padding: 8px 16px;
      border: none;
      font-weight: 700;
      font-size: 14px;
      transition: 0.2s;
      margin-left: 8px;
    }

    .btn-back {
      background-color: #5a9bf6;
    }

    .btn-back:hover {
      background-color: #4e73df;
    }

    .btn-edit {
      background-color: #1cc88a;
    }

    .btn-edit:hover {
      background-color: #17a673;
    }

    .btn-edit:disabled {
      background-color: #ccc;
      cursor: not-allowed;
      opacity: 0.6;
    }

    .btn-edit:disabled:hover {
      background-color: #ccc;
    }

    .loading-text {
      text-align: center;
      color: #777;
      padding: 20px;
      font-size: 16px;
    }

    .swal2-html-container {
      max-height: 500px;
      overflow-y: auto;
    }

    .role-badge {
      background-color: #1cc88a;
      color: white;
      padding: 5px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
    }

    .lock-badge {
      background-color: #e74a3b;
      color: white;
      padding: 5px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      margin-left: 8px;
    }

    /* Form field styles */
    .form-group {
      margin-bottom: 15px;
      text-align: left;
    }

    .form-group label {
      display: block;
      font-weight: 600;
      margin-bottom: 5px;
      color: #495057;
    }

    .form-group input,
    .form-group textarea {
      width: 100% !important;
      max-width: 100% !important;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 14px;
      box-sizing: border-box;
      margin: 0 !important;
    }

    .form-group input:disabled,
    .form-group textarea:disabled {
      background-color: #f5f5f5;
      cursor: not-allowed;
      color: #666;
    }

    .form-group textarea {
      resize: vertical;
      min-height: 70px;
    }

    .swal2-input {
      width: 100% !important;
      max-width: 100% !important;
      margin: 0 !important;
    }

    /* SweetAlert2 buttons */
    .swal2-confirm,
    .swal2-cancel {
      padding: 8px 16px !important;
      font-weight: 700 !important;
      font-size: 14px !important;
    }
  </style>
</head>

<body>

  <div class="container">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <h4><i class="fas fa-file-alt mr-2"></i>Detail Form Submission</h4>
          <span class="role-badge">CDC View - Full Access</span>
          <span id="lockBadge" class="lock-badge" style="display: none;">
            <i class="fas fa-lock mr-1"></i>Locked
          </span>
        </div>
        <div>
          <button onclick="window.history.back()" class="btn-back">
            <i class="fas fa-arrow-left mr-1"></i> Back
          </button>
          <button id="editButton" onclick="openEditModal()" class="btn-edit">
            <i class="fas fa-edit mr-1"></i> Edit
          </button>
        </div>
      </div>
      <div class="card-body p-4">
        <div id="loading" class="loading-text">Loading data...</div>
        <table class="table" id="detailTable" style="display:none;">
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>

  <script>
    const id = "<?php echo $id_letter; ?>";
    const apiBase = "http://localhost:8000/api";
    let currentData = null;

    function isEditLocked(data) {
      // Cek apakah CDC dan Koordinator sudah approve
      const cdcApproved = data.cdc_approval === "ACCEPTED";
      const koorApproved = data.koor_approval === "ACCEPTED";

      // Cek apakah ada yang reject
      const cdcRejected = data.cdc_approval === "REJECTED";
      const koorRejected = data.koor_approval === "REJECTED";

      // Lock jika keduanya approve ATAU ada salah satu yang reject
      return (cdcApproved && koorApproved) || cdcRejected || koorRejected;
    }

    async function loadDetail() {
      try {
        const res = await fetch(`${apiBase}/lecturer/submissions/detail/${id}`);
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const json = await res.json();
        if (!json.success) throw new Error(json.message);

        currentData = json.data;
        renderTable(currentData);

        // Cek apakah edit harus dikunci
        const editBtn = document.getElementById('editButton');
        const lockBadge = document.getElementById('lockBadge');

        if (isEditLocked(currentData)) {
          editBtn.disabled = true;
          editBtn.title = "Cannot edit: Both CDC and Coordinator have approved";
          lockBadge.style.display = 'inline-block';
        } else {
          editBtn.disabled = false;
          editBtn.title = "";
          lockBadge.style.display = 'none';
        }

        document.getElementById("loading").style.display = "none";
        document.getElementById("detailTable").style.display = "table";
      } catch (err) {
        console.error(err);
        Swal.fire({
          icon: "error",
          title: "Error loading data",
          text: err.message,
        });
        document.getElementById("loading").innerHTML = `<span class="text-danger">${err.message}</span>`;
      }
    }

    function renderTable(d) {
      const badge = (status) => {
        const s = status?.toUpperCase() || "WAITING";
        if (s.includes("REJECT"))
          return `<span class='badge-status rejected'>Rejected</span>`;
        if (s.includes("ACCEPT") || s.includes("APPROVE"))
          return `<span class='badge-status accepted'>Approved</span>`;
        return `<span class='badge-status waiting'>Waiting</span>`;
      };

      const formatTime = (datetime) => {
        if (!datetime) return "";
        const d = new Date(datetime);
        return d.toLocaleDateString("en-GB");
      };

      function formatContact(contact) {
        if (!contact) return '-';
        const parts = contact.split(/\s+/);
        let phone = parts.find(p => /^[0-9+]/.test(p));
        let email = parts.find(p => p.includes('@'));
        let html = '';
        if (phone) html += `${phone} (phone)<br>`;
        if (email) html += `${email} (email)`;
        return html || contact;
      }

      const tbody = document.querySelector("#detailTable tbody");
      tbody.innerHTML = `
    <tr><th>NIM</th><td>${d.nim}</td></tr>
    <tr><th>Nama Mahasiswa</th><td>${d.student_name}</td></tr>
    <tr><th>Program Studi</th><td>${d.program_study}</td></tr>
    <tr><th>Nama Perusahaan</th><td>${d.company_name}</td></tr>
    <tr><th>Alamat Perusahaan</th><td>${d.company_address}</td></tr>
    <tr><th>Kontak Perusahaan</th><td>${formatContact(d.company_contact)}</td></tr>
    <tr><th>Tanggal Mulai</th><td>${new Date(d.start_date).toLocaleDateString("en-GB")}</td></tr>
    <tr><th>Tanggal Selesai</th><td>${new Date(d.end_date).toLocaleDateString("en-GB")}</td></tr>
    <tr><th>Status</th><td>${badge(d.status)}</td></tr>
    <tr>
      <th>Koordinator Approval</th>
      <td>
        ${badge(d.koor_approval)}
        ${(d.koor_approval === "ACCEPTED" || d.koor_approval === "REJECTED")
          ? `<span class="approval-time">${formatTime(d.updated_at)}</span>`
          : ""}
      </td>
    </tr>
    <tr>
      <th>CDC Approval</th>
      <td>
        ${badge(d.cdc_approval)}
        ${(d.cdc_approval === "ACCEPTED" || d.cdc_approval === "REJECTED")
          ? `<span class="approval-time">${formatTime(d.updated_at)}</span>`
          : ""}
      </td>
    </tr>
    <tr><th>Dibuat Pada</th><td>${new Date(d.created_at).toLocaleString()}</td></tr>
    ${d.updated_at ? `<tr><th>Diperbarui Pada</th><td>${new Date(d.updated_at).toLocaleString()}</td></tr>` : ""}
  `;
    }

    function formatDateForInput(dateString) {
      const d = new Date(dateString);
      const year = d.getFullYear();
      const month = String(d.getMonth() + 1).padStart(2, '0');
      const day = String(d.getDate()).padStart(2, '0');
      return `${year}-${month}-${day}`;
    }

    function parseContact(contact) {
      if (!contact) return {
        phone: '',
        email: ''
      };
      const parts = contact.split(/\s+/);
      let phone = parts.find(p => /^[0-9+]/.test(p)) || '';
      let email = parts.find(p => p.includes('@')) || '';
      return {
        phone,
        email
      };
    }

    async function openEditModal() {
      if (!currentData) {
        Swal.fire('Error', 'Data not loaded yet', 'error');
        return;
      }

      // Double check jika edit dikunci
      if (isEditLocked(currentData)) {
        Swal.fire({
          icon: 'warning',
          title: 'Cannot Edit',
          text: 'This submission cannot be edited because both CDC and Coordinator have already approved it.',
          confirmButtonColor: '#e74a3b'
        });
        return;
      }

      const contactData = parseContact(currentData.company_contact);

      const {
        value: formValues
      } = await Swal.fire({
        title: '<strong>Edit Detail Submission</strong>',
        html: `
      <div class="form-group">
        <label>NIM</label>
        <input id="edit-nim" class="swal2-input" value="${currentData.nim}" disabled>
      </div>
      <div class="form-group">
        <label>Nama Mahasiswa</label>
        <input id="edit-student-name" class="swal2-input" value="${currentData.student_name}" disabled>
      </div>
      <div class="form-group">
        <label>Program Studi</label>
        <input id="edit-program-study" class="swal2-input" value="${currentData.program_study}" disabled>
      </div>
      
      <div class="form-group">
        <label>Nama Perusahaan</label>
        <input id="edit-company-name" class="swal2-input" value="${currentData.company_name}">
      </div>
      <div class="form-group">
        <label>Alamat Perusahaan</label>
        <textarea id="edit-company-address" class="swal2-input">${currentData.company_address}</textarea>
      </div>
      <div class="form-group">
        <label>Phone Perusahaan</label>
        <input id="edit-company-phone" class="swal2-input" value="${contactData.phone}" placeholder="e.g., +6281234567890">
      </div>
      <div class="form-group">
        <label>Email Perusahaan</label>
        <input id="edit-company-email" type="email" class="swal2-input" value="${contactData.email}" placeholder="e.g., company@example.com">
      </div>
      
      <div class="form-group">
        <label>Tanggal Mulai</label>
        <input type="date" id="edit-start-date" class="swal2-input" value="${formatDateForInput(currentData.start_date)}" disabled>
      </div>
      <div class="form-group">
        <label>Tanggal Selesai</label>
        <input type="date" id="edit-end-date" class="swal2-input" value="${formatDateForInput(currentData.end_date)}" disabled>
      </div>
    `,
        width: '600px',
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Save',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#1cc88a',
        preConfirm: () => {
          const companyName = document.getElementById('edit-company-name').value.trim();
          const companyAddress = document.getElementById('edit-company-address').value.trim();
          const companyPhone = document.getElementById('edit-company-phone').value.trim();
          const companyEmail = document.getElementById('edit-company-email').value.trim();

          if (!companyName) {
            Swal.showValidationMessage('Company name is required');
            return false;
          }
          if (!companyAddress) {
            Swal.showValidationMessage('Company address is required');
            return false;
          }
          if (!companyPhone && !companyEmail) {
            Swal.showValidationMessage('At least one contact (phone or email) is required');
            return false;
          }
          if (companyEmail && !companyEmail.includes('@')) {
            Swal.showValidationMessage('Invalid email format');
            return false;
          }

          return {
            company_name: companyName,
            company_address: companyAddress,
            company_phone: companyPhone,
            company_email: companyEmail
          };
        }
      });

      if (formValues) {
        await submitEdit(formValues);
      }
    }

    async function submitEdit(data) {
      try {
        Swal.fire({
          title: 'Saving...',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        const res = await fetch(`${apiBase}/lecturer/submissions/edit/${id}`, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(data)
        });

        const json = await res.json();

        if (!res.ok || !json.success) {
          throw new Error(json.message || 'Failed to update');
        }

        Swal.fire({
          icon: 'success',
          title: 'Success!',
          text: json.message,
          timer: 2000
        });

        await loadDetail();

      } catch (err) {
        console.error(err);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: err.message
        });
      }
    }

    loadDetail();
  </script>

</body>

</html>