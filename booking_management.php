<?php
session_start();
include("config.php");

// ✅ Secure session handling
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Admin-only access
if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== "ADMIN") {
    header("Location: login.php?error=unauthorized");
    exit();
}

// --- Fetch stats ---
$totalBookings = $conn->query("SELECT COUNT(*) AS total FROM booking")->fetch_assoc()['total'];
$pending = $conn->query("SELECT COUNT(*) AS total FROM booking WHERE Status='Pending'")->fetch_assoc()['total'];
$cancelled = $conn->query("SELECT COUNT(*) AS total FROM booking WHERE Status='Cancelled'")->fetch_assoc()['total'];

// --- Fetch booking details ---
$sql = "
  SELECT 
    b.BookingID,
    p.Name AS Passenger,
    CONCAT(r.StartLocation, ' ➝ ', r.EndLocation) AS Route,
    v.VehicleID,
    b.BookingDate
  FROM booking b
  LEFT JOIN passenger p ON b.PassengerID = p.PassengerID
  LEFT JOIN schedule s ON b.ScheduleID = s.ScheduleID
  LEFT JOIN route r ON s.RouteID = r.RouteID
  LEFT JOIN vehicle v ON b.VehicleID = v.VehicleID
  ORDER BY b.BookingDate DESC
";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Booking Management</title>
  <style>
    body { margin: 0; font-family: Arial, sans-serif; background: #fdfdfd; }
    header { background: #90ee90; padding: 12px 20px; display: flex; align-items: center; justify-content: space-between; color: #000; font-weight: bold; position: fixed; width: 100%; top: 0; z-index: 1000; }
    .menu { font-size: 24px; cursor: pointer; user-select: none; }
    .header-title { flex-grow: 1; text-align: center; font-size: 20px; }
    .sidebar { height: 100%; width: 0; position: fixed; top: 0; left: 0; background-color: #333; overflow-x: hidden; transition: width 0.28s ease; padding-top: 60px; z-index: 1200; }
    .sidebar a { padding: 12px 24px; text-decoration: none; font-size: 18px; color: white; display: block; transition: background 0.18s; }
    .sidebar a:hover { background: #575757; }
    .sidebar .closebtn { position: absolute; top: 10px; right: 18px; font-size: 28px; cursor: pointer; color: white; }
    .content { margin-top: 70px; padding: 20px; }
    h1 { margin: 0 0 20px; font-size: 28px; }
    .stats { display: flex; gap: 20px; margin-bottom: 20px; }
    .stat-box { background: #f2eee9; padding: 10px 20px; border-radius: 8px; font-size: 16px; font-weight: bold; }
    .stat-value { background: #e1e1e1; padding: 5px 15px; border-radius: 6px; margin-left: 8px; }
    .search-filter { display: flex; justify-content: flex-end; gap: 10px; margin-bottom: 15px; }
    .search-filter input { padding: 8px; border: 1px solid #ccc; border-radius: 6px; width: 260px; }
    .filter-btn { padding: 8px 14px; border: none; background: #eee; border-radius: 6px; cursor: pointer; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #000; padding: 10px; text-align: center; }
    th { background: #000; color: #fff; }
    td[contenteditable="true"] { background: #fafafa; cursor: text; }
    .actions { margin-top: 20px; display: flex; gap: 10px; flex-wrap: wrap; }
    .btn { padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; }
    .btn-edit { background: #ddd; }
    .btn-cancel { background: #f55; color: #fff; }
    .btn-print { background: #6f6; }
    .btn-add { background: #5ba9f7; color: #fff; }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div id="sidebar" class="sidebar">
    <span class="closebtn" onclick="closeSidebar()">&times;</span>
    <a href="admin_dashboard.php">Homepage</a>
    <a href="Analytics.php">Analytics</a>
    <a href="schedule_management.php">Schedule Management</a>
    <a href="vehicle_management.php">Vehicle Management</a>
    <a href="logout.php">Logout</a>
  </div>

  <!-- Header -->
  <header>
    <span class="menu" onclick="openSidebar()">&#9776;</span>
    <div class="header-title">Booking Management</div>
  </header>

  <!-- Main Content -->
  <div class="content">
    <h1>BOOKING MANAGEMENT</h1>

    <!-- Stats -->
    <div class="stats">
      <div class="stat-box">Total Bookings: <span id="totalBookings" class="stat-value"><?= $totalBookings; ?></span></div>
      <div class="stat-box">Pending: <span class="stat-value"><?= $pending; ?></span></div>
      <div class="stat-box">Cancelled: <span class="stat-value"><?= $cancelled; ?></span></div>
    </div>

    <!-- Search + Filter -->
    <div class="search-filter">
      <input type="text" id="searchInput" placeholder="Search by Passenger, ID, or Route">
      <button class="filter-btn">Filter +</button>
    </div>

    <!-- Table -->
    <table id="bookingTable">
      <thead>
        <tr>
          <th>Booking ID</th>
          <th>Passenger</th>
          <th>Route</th>
          <th>Vehicle ID</th>
          <th>Date/Time</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $row['BookingID']; ?></td>
          <td><?= $row['Passenger']; ?></td>
          <td><?= $row['Route']; ?></td>
          <td><?= $row['VehicleID']; ?></td>
          <td><?= $row['BookingDate']; ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>

    <!-- Actions -->
    <div class="actions">
      <button class="btn btn-edit">View/Edit</button>
      <button class="btn btn-cancel">Cancel Booking</button>
      <button class="btn btn-print">Print Ticket</button>
      <button class="btn btn-add" onclick="addRow()">+ Add Ticket</button>
    </div>
  </div>

  <script>
    function openSidebar() { document.getElementById("sidebar").style.width = "250px"; }
    function closeSidebar() { document.getElementById("sidebar").style.width = "0"; }

    // Simple search filter
    document.getElementById("searchInput").addEventListener("keyup", function() {
      const filter = this.value.toLowerCase();
      document.querySelectorAll("#bookingTable tbody tr").forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(filter) ? "" : "none";
      });
    });

    function addRow() {
      alert("This will open a booking form (to be added).");
    }
  </script>
</body>
</html>
