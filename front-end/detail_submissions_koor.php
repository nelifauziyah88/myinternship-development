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
  <title>Detail Submission - Koordinator</title>
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

    .btn-back {
      color: #fff;
      border-radius: 8px;
      padding: 8px 16px;
      border: none;
      font-weight: 700;
      font-size: 14px;
      transition: 0.2s;
      background-color: #5a9bf6;
    }

    .btn-back:hover {
      background-color: #4e73df;
    }

    .loading-text {
      text-align: center;
      color: #777;
      padding: 20px;
      font-size: 16px;
    }

    .role-badge {
      background-color: #1cc88a;
      color: white;
      padding: 5px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
    }
  </style>
</head>

<body>

  <div class="container">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <h4><i class="fas fa-file-alt mr-2"></i>Detail Form Submission</h4>
          <span class="role-badge">Koordinator View</span>
        </div>
        <div>
          <button onclick="window.history.back()" class="btn-back">
            <i class="fas fa-arrow-left mr-1"></i> Back
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

    async function loadDetail() {
      try {
        const res = await fetch(`${apiBase}/lecturer/submissions/detail/${id}`);
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const json = await res.json();
        if (!json.success) throw new Error(json.message);

        renderTable(json.data);

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
    <tr><th>Student Name</th><td>${d.student_name}</td></tr>
   <tr><th>Study Program</th><td>${d.study_program}</td></tr>
    <tr><th>Company Name</th><td>${d.company_name}</td></tr>
    <tr><th>Company Address</th><td>${d.company_address}</td></tr>
    <tr><th>Company Contact</th><td>${formatContact(d.company_contact)}</td></tr>
    <tr><th>Start Date</th><td>${new Date(d.start_date).toLocaleDateString("en-GB")}</td></tr>
    <tr><th>End Date</th><td>${new Date(d.end_date).toLocaleDateString("en-GB")}</td></tr>
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
    <tr><th>Created at</th><td>${new Date(d.created_at).toLocaleString()}</td></tr>
    ${d.updated_at ? `<tr><th>Updated at</th><td>${new Date(d.updated_at).toLocaleString()}</td></tr>` : ""}
  `;
    }

    loadDetail();
  </script>

</body>

</html>