<?php
session_start();
include("../config.php"); // Assumed location of your database connection file

// ✅ Admin-only access
if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== "ADMIN") {
    header("Location: ../login.php?error=unauthorized");
    exit();
}

// ✅ Fetch all vouchers
$result = $conn->query("
  SELECT 
    VoucherID, Code, DiscountValue, ValidFrom, ValidTo, Status
  FROM voucher
  ORDER BY VoucherID DESC
");

// Note: The 'voucher' table has columns: VoucherID, Code, Description, DiscountType, DiscountValue, ValidFrom, ValidTo, Status
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Voucher Management</title>
  
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>

  <style>
    :root {
            --header-green: #a7f3d0;
            --accent-green: #4ade80;
            --bg: #f9fafb;
            --card: #ffffff;
            --text-dark: #111827;
            --sidebar-bg: #1f2937;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg); color: var(--text-dark); overflow-x: hidden; }
        body::-webkit-scrollbar { display: none; width: 0; }
    header {
         background-color: var(--header-green);
            color: #064e3b;
            display: flex;
            align-items: center;
            padding: 12px 16px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 20;
    }
    header h1 {
           margin-left: 10px;
            font-size: 20px;
            font-weight: 600;
    }

    .sidebar {
       position: fixed;
            top: 0;
            left: -280px;
            width: 280px;
            height: 100%;
            background-color: var(--sidebar-bg); /* Use dark variable */
            color: white;
            padding: 20px;
            transition: left 0.3s ease;
            z-index: 30;
            box-shadow: 2px 0 10px rgba(0,0,0,0.3);
            display: flex;
            flex-direction: column; /* Ensure content flows vertically */
    }
    .sidebar.open { left: 0; }

    .sidebar h2 {
            font-size: 22px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
            font-weight: 700;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            color: white;
            text-decoration: none;
            padding: 12px 15px;
            border-radius: 6px;
            transition: background-color 0.2s ease, color 0.2s ease;
            margin-bottom: 5px;
            border-bottom: none; /* Removed the line border for cleaner look */
        }
        main { padding: 20px; animation: slideIn 0.6s ease; }
        @keyframes slideIn { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

    .sidebar a i {
            margin-right: 12px;
            width: 20px; /* Fixed width for icon alignment */
            text-align: center;
        }
        .sidebar a:hover { 
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--accent-green); 
        }

        .menu-icon {
            font-size: 24px;
            cursor: pointer;
            color: #064e3b;
        }

    table {
      border-collapse: collapse;
      width: 95%;
      margin: 20px auto;
      background: #fff;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
    th { background: #8de699; font-weight: bold; }

    .btn { padding: 6px 12px; border: none; cursor: pointer; border-radius: 5px; font-size: 14px; }
    .btn-add { background: #90ee90; margin-left: 35px; margin-top: 30px; }
    .btn-add:hover { background-color: green; transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.15); color: white; }
    .btn-edit { background: orange; color: #fff; }
    .btn-edit:hover { background-color: brown; box-shadow: 0 4px 8px rgba(0,0,0,0.15); color: white; }
    .btn-delete { background: red; color: #fff; }
    .btn-delete:hover { background-color: #870718; box-shadow: 0 4px 8px rgba(0,0,0,0.15); color: white; }

    /* ✅ Modal */
    .modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      background: rgba(0, 0, 0, 0.3);
      backdrop-filter: blur(3px);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 2000;
    }
    .modal-content {
      width: 450px; /* Slightly wider modal for more fields */
      background: #fff;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    }
    .modal-content h3 { margin-top: 0; text-align: center; }
    .modal-content label { display: block; margin: 8px 0 4px; }
    .modal-content input, .modal-content select {
      width: 100%; padding: 6px; border-radius: 4px; border: 1px solid #aaa; margin-bottom: 10px;
    }
    .modal-buttons {
      display: flex;
      justify-content: space-between;
      gap: 10px;
      margin-top: 18px;
    }
    .btn-save {
      flex: 1;
      background: #4ade80;
      color: #064e3b;
      border: none;
      padding: 10px 0;
      border-radius: 6px;
      font-weight: 600;
      cursor: pointer;
    }
    .btn-cancel {
      flex: 1;
      background: #9ca3af;
      color: white;
      border: none;
      padding: 10px 0;
      border-radius: 6px;
      font-weight: 600;
      cursor: pointer;
    }
    .overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.4); display: none; z-index: 25; opacity: 0; transition: opacity 0.3s ease; }
    .overlay.show { display: block; opacity: 1; }

    .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-top: 20px; }
        .card { background-color: var(--card); padding: 20px; border-radius: 12px; box-shadow: 0 6px 15px rgba(0,0,0,0.08); transition: transform 0.3s; border-left: 5px solid var(--accent-green); }
        .card:hover { transform: translateY(-5px); }
        .card h3 { font-size: 16px; color: #6b7280; font-weight: 500; }
        .card p { font-size: 32px; font-weight: 800; margin-top: 8px; color: #065f46; }
  </style>
</head>
<body>


<!-- HEADER AND SIDEBAR -->

<header class="bg-green-200 text-green-900 p-4 shadow-md flex items-center">
    <div class="menu-icon" id="menuBtn"><i class="fas fa-bars"></i></div>
    <h1><i class="fas fa-ticket-alt text-green-700"></i> Voucher Management</h1>
</header>

<div class="sidebar" id="sidebar">
    <h2 class="text-green-400"><i class="fas fa-grip-vertical"></i> Menu</h2>
    <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="Analytics.php"><i class="fas fa-chart-line"></i> Analytics</a>
    <a href="admin_bugreport.php"><i class="fas fa-bug"></i> User Report</a>
    <a href="user_management.php"><i class="fas fa-users-cog"></i> User Management</a>
    <a href="Voucher_management.php" class="bg-green-600 rounded"><i class="fas fa-ticket-alt"></i> Voucher Management</a>
    <a href="vehicle_management.php"><i class="fas fa-car-side"></i> Vehicle Management</a>
    <a href="schedule_management.php"><i class="fas fa-calendar-alt"></i> Schedule Management</a>
    <a href="route_management.php"><i class="fas fa-route"></i> Routes Management</a>
    <a href="discount_applications.php"><i class="fas fa-tags"></i> Discount Applications</a>
</div>

<div class="overlay" id="overlay"></div>


<!-- MAIN CONTENT -->

<main>
  <h2 class="text-2xl font-bold mb-6 text-gray-800">Vouchers Overview</h2>
    <section class="cards">
        <div class="card"><h3>Total Vouchers</h3><p id="totalVouchers">0</p></div>
        <div class="card"><h3>Active Vouchers</h3><p id="activeVouchers">0</p></div>
        <div class="card"><h3>Used Vouchers</h3><p id="usedVouchers">0</p></div>
        <div class="card"><h3>Expired Vouchers</h3><p id="expiredVouchers">0</p></div>
        <div class="card"><h3>Unused Vouchers</h3><p id="unusedVouchers">0</p></div>
    </section>
<button class="btn btn-add" onclick="showForm()">+ Add Voucher</button>
<table>
  <thead>
    <tr>
      <th class="bg-green-200">ID</th>
      <th class="bg-green-200">Code</th>
      <th class="bg-green-200">Value</th>
      <th class="bg-green-200">Valid From</th>
      <th class="bg-green-200">Valid To</th>
      <th class="bg-green-200">Status</th>
      <th class="bg-green-200">Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php while ($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= $row['VoucherID'] ?></td>
        <td><?= htmlspecialchars($row['Code']) ?></td>
        <td><?= number_format($row['DiscountValue'], 2) ?></td>
        <td><?= htmlspecialchars($row['ValidFrom'] ?? 'N/A') ?></td>
        <td><?= htmlspecialchars($row['ValidTo'] ?? 'N/A') ?></td>
        <td><?= htmlspecialchars($row['Status']) ?></td>
        <td>
          <button class="btn btn-edit" onclick="editVoucher(
            <?= $row['VoucherID'] ?>, 
            '<?= $row['Code'] ?>', 
            '<?= $row['DiscountValue'] ?>', 
            '<?= $row['ValidFrom'] ?>', 
            '<?= $row['ValidTo'] ?>', 
            '<?= $row['Status'] ?>'
          )">Edit</button>
          <button class="btn btn-delete" onclick="deleteVoucher(<?= $row['VoucherID'] ?>)">Delete</button>
        </td>
      </tr>
    <?php endwhile; ?>
  </tbody>
</table>
<main>

<div id="formOverlay" class="modal-overlay">
  <div class="modal-content">
    <h3 id="formTitle">Add Voucher</h3>
    <form id="voucherForm">
      <input type="hidden" id="editId" name="id">

      <label>Voucher Code</label>
      <input type="text" id="code" name="code" readonly>
      
      <label>Discount Value</label>
      <input type="number" id="discountValue" name="discountValue" step="0.01" min="0" required>

      <label>Valid From</label>
      <input type="date" id="validFrom" name="validFrom">

      <label>Valid To</label>
      <input type="date" id="validTo" name="validTo">

      <label>Status</label>
      <select id="status" name="status" required>
        <option value="ACTIVE">Active</option>
        <option value="EXPIRED">Expired</option>
        <option value="USED">Used</option>
      </select>

      <div class="modal-buttons">
        <button type="submit" class="btn-save">Save</button>
        <button type="button" class="btn-cancel" onclick="hideForm()">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
// Sidebar and Overlay Toggle (Copied from vehicle_management.php)
const sidebar = document.getElementById('sidebar');
const menuBtn = document.getElementById('menuBtn');
const overlay = document.getElementById('overlay');
menuBtn.addEventListener('click', () => {
  sidebar.classList.toggle('open');
  overlay.classList.toggle('show');
});
overlay.addEventListener('click', () => {
  sidebar.classList.remove('open');
  overlay.classList.remove('show');
});

// ✅ Helper function to generate an 8-digit NUMERIC code
function generateUniqueCode(length = 8) {
  const chars = '0123456789'; // Only numbers
  let result = '';
  for (let i = 0; i < length; i++) {
    result += chars.charAt(Math.floor(Math.random() * chars.length));
  }
  return result;
}

// Modal Logic
const formOverlay = document.getElementById('formOverlay');
const formEl = document.getElementById('voucherForm');
const formTitle = document.getElementById('formTitle');
const hiddenId = document.getElementById('editId');
const codeInput = document.getElementById('code');

function showForm(edit = false) {
  formOverlay.style.display = 'flex';
  formTitle.innerText = edit ? 'Edit Voucher' : 'Add Voucher';
  formEl.reset();
  if (!edit) {
    hiddenId.value = '';
    // ✅ Generate Code for new voucher
    codeInput.value = generateUniqueCode(); 
    // New vouchers must be ACTIVE
    document.getElementById('status').value = 'ACTIVE';
  }
  // Code is always read-only
  codeInput.readOnly = true; 
}

function hideForm() {
  formOverlay.style.display = 'none';
  formEl.reset();
  hiddenId.value = '';
}

function editVoucher(id, code, discountValue, validFrom, validTo, status) {
  showForm(true);
  hiddenId.value = id;
  codeInput.value = code; // Existing code is loaded and kept read-only
  document.getElementById('discountValue').value = discountValue;
  document.getElementById('validFrom').value = validFrom;
  document.getElementById('validTo').value = validTo;
  document.getElementById('status').value = status;
}

formEl.addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(formEl);
  const payload = Object.fromEntries(fd.entries());
  const idVal = (hiddenId.value || '').trim();
  const isEdit = idVal.length > 0;
  if (isEdit) payload.id = idVal;

  // IMPORTANT: You need to create these PHP files to handle the logic
  const url = isEdit ? 'update_voucher.php' : 'add_voucher.php'; 
  
  // ✅ The code is now passed for both add and edit
  payload.code = codeInput.value; 

  const res = await fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  });
  const result = await res.json();
  if (result.success) {
    alert(isEdit ? 'Voucher updated successfully.' : 'Voucher added successfully.');
    hideForm();
    location.reload();
  } else {
    alert(result.error || 'Operation failed.');
  }
});

async function deleteVoucher(id) {
  if (!confirm('Delete this voucher? This action cannot be undone.')) return;
  // IMPORTANT: You need to create this PHP file to handle the logic
  const res = await fetch('delete_voucher.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id })
  });
  const result = await res.json();
  if (result.success) {
    alert('Voucher deleted.');
    location.reload();
  } else {
    alert(result.error || 'Delete failed.');
  }
}
</script>
</body>
</html>