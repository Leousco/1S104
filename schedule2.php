<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Schedule</title>
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
    }

    /* Header bar (like the old one with three dots) */
    header {
      background: #a9d6af;
      padding: 10px 20px;
      display: flex;
      align-items: center;
      justify-content: flex-start;
    }

    .menu-icon {
      font-size: 22px;
      cursor: pointer;
    }

    /* Sidebar hidden by default */
    .sidebar {
      height: 100%;
      width: 0;
      position: fixed;
      top: 0;
      left: 0;
      background-color: #a9d6af;
      overflow-x: hidden;
      transition: 0.3s;
      padding-top: 60px;
    }

    .sidebar a {
      padding: 10px 20px;
      text-decoration: none;
      font-size: 18px;
      color: black;
      display: block;
      transition: 0.2s;
    }

    .sidebar a:hover {
      background: #90ee90;
    }

    .sidebar .closebtn {
      position: absolute;
      top: 10px;
      right: 20px;
      font-size: 28px;
      cursor: pointer;
    }

    /* Main content */
    .main-content {
      padding: 20px;
      text-align: center;
    }

    h2 {
      margin-top: 20px;
    }

    .table-container {
      display: flex;
      justify-content: center;
      align-items: center;
      margin-top: 20px;
    }

    table {
      border-collapse: collapse;
      width: 60%;
      background: white;
      box-shadow: 0 2px 6px rgba(0,0,0,0.2);
      text-align: center;
    }

    th, td {
      border: 1px solid #000;
      padding: 15px;
    }

    th {
      background: black;
      color: white;
    }
  </style>
</head>
<body>
  <!-- Header -->
  <header>
    <span class="menu-icon" onclick="openSidebar()">☰</span>
  </header>

  <!-- Sidebar -->
  <div id="mySidebar" class="sidebar">
    <span class="closebtn" onclick="closeSidebar()">×</span>
    <a href="index.php">Dashboard</a>
    <a href="Bus.php">Reports</a>
    <a href="jeep.php">Schedule</a>

  </div>

  <!-- Main Content -->
  <div class="main-content">
    <h2>SCHEDULE</h2>
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>DEPARTURE TIME</th>
            <th>ROUTE</th>
            <th>VEHICLE ID</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>5:30</td>
            <td>SM MOA TO SM FAIRVIEW</td>
            <td>1-3457</td>
          </tr>
          <tr>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <td></td>
            <td></td>
            <td></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <script>
    function openSidebar() {
      document.getElementById("mySidebar").style.width = "220px";
    }
    function closeSidebar() {
      document.getElementById("mySidebar").style.width = "0";
    }
  </script>
</body>
</html>
```
