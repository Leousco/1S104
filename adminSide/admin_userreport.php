<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>User Accounts</title>
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background: #f4f4f4;
    }

    /* ✅ Header */
    header {
      background: #90ee90;
      padding: 12px 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: fixed;
      width: 100%;
      top: 0;
      z-index: 1000;
      box-sizing: border-box;
    }

    .menu {
      font-size: 24px;
      cursor: pointer;
      user-select: none;
    }

    .header-title {
      flex-grow: 1;
      text-align: center;
      font-size: 20px;
      font-weight: bold;
    }

    .profile {
      font-size: 18px;
      margin-right: 10px;
      white-space: nowrap;
    }

    /* ✅ Sidebar */
    .sidebar {
      height: 100%;
      width: 0;
      position: fixed;
      top: 0;
      left: 0;
      background-color: #333;
      overflow-x: hidden;
      transition: width 0.3s ease;
      padding-top: 60px;
      z-index: 1200;
    }

    .sidebar a {
      padding: 12px 24px;
      text-decoration: none;
      font-size: 18px;
      color: white;
      display: block;
      transition: background 0.2s;
    }

    .sidebar a:hover {
      background: #575757;
    }

    .sidebar .closebtn {
      position: absolute;
      top: 10px;
      right: 18px;
      font-size: 28px;
      cursor: pointer;
      color: white;
    }

    /* ✅ Page Title */
    h2 {
      margin: 85px 0 15px;
      text-align: center;
    }

    /* ✅ Table */
    table {
      border-collapse: collapse;
      width: 95%;
      margin: 20px auto;
      background: #fff;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    th, td {
      border: 1px solid #ddd;
      padding: 10px;
      text-align: center;
    }

    th {
      background: #8de699;
      font-weight: bold;
    }

    .no-data {
      text-align: center;
      color: #777;
      padding: 20px;
    }

    .balance {
      font-weight: bold;
      color: #007bff;
    }
  </style>
</head>
<body>

  <!-- ✅ Sidebar -->
 <div class="sidebar" id="sidebar">
    <h2 class="mb-4 text-green-400">Menu</h2>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="Analytics.php">Analytics</a>
    <a href="admin_bugreport.php">Bug Reports</a>
    <a href="user_management.php">User Management</a>
    <a href="vehicle_management.php">Vehicle Management</a>
    <a href="schedule_management.php">Schedule Management</a>
    <a href="route_management.php">Routes Management</a>
    <a href="discount_applications.php">Discount Applications</a>
    <a href="voucher_management.php">Voucher Management</a>
  </div>

  <!-- ✅ Header -->
  <header>
    <div class="menu" onclick="openNav()">☰</div>
    <div class="header-title">User Accounts</div>
    <div class="profile">👤 Admin</div>
  </header>

  <!-- ✅ Title -->
  <h2>Registered Users</h2>

  <!-- ✅ User Table -->
  <table id="userTable">
    <thead>
      <tr>
        <th>ID</th>
        <th>Email</th>
        <th>Username</th>
        <th>Account Balance</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>johndenver.give@email.com</td>
        <td>John Denven Gice</td>
        <td class="balance">0.00</td>
      </tr>
      <tr>
        <td>2</td>
        <td>ryan.galvan@email.com</td>
        <td>Ryan Angelo</td>
        <td class="balance">1,000,000.00</td>
      </tr>
    </tbody>
  </table>

  <script>
    function openNav() {
      document.getElementById("sidebar").style.width = "250px";
    }

    function closeNav() {
      document.getElementById("sidebar").style.width = "0";
    }
  </script>

</body>
</html>
