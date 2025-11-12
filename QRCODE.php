<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>QR Code</title>
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background: #fdfdfd;
      display: flex;
      flex-direction: column;
      height: 100vh;
    }

    header {
      background: #a9d6af;
      padding: 10px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    header input[type="text"] {
      padding: 5px;
      width: 200px;
      border-radius: 6px;
      border: 1px solid rgba(0,0,0,.12);
    }

    .menu {
      font-size: 24px;
      cursor: pointer;
      user-select: none;
      padding: 4px 8px;
    }

    .profile {
      font-size: 22px;
      cursor: pointer;
    }

    .sidebar {
      height: 100%;
      width: 0;
      position: fixed;
      top: 0;
      left: 0;
      background-color: #333;
      overflow-x: hidden;
      transition: width 0.28s ease;
      padding-top: 60px;
      z-index: 1000;
    }

    .sidebar a {
      padding: 12px 24px;
      text-decoration: none;
      font-size: 18px;
      color: white;
      display: block;
      transition: background 0.18s;
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

    .qr-container {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
    }

    .qr-container img {
      width: 200px;
      height: 200px;
      margin-bottom: 20px;
    }

    .qr-container p {
      font-size: 16px;
      font-weight: bold;
    }
  </style>
</head>
<body>

  <div id="sidebar" class="sidebar" aria-hidden="true">
    <span class="closebtn" onclick="closeNav()">&times;</span>
    <a href="Index.php">Homepage</a>
    <a href="bus.php">Bus</a>
    <a href="train.php">Train</a>
    <a href="ejeep.php">E-Jeep</a>
    <a href="schedule.php">Schedules</a>
    <a href="buyticket.php">Buy Ticket</a>
    <a href="feedback.php">Feedback</a>
    <a href="about.php">About Us</a>
    <a href="faqs.php">FAQs</a>
  </div>

  <header>
    <div class="menu" onclick="openNav()">☰</div>
    <input type="text" id="searchBar" placeholder="Search (sidebar items)">
    <div class="profile" onclick="location.href='userlogin.php'">👤</div>
  </header>

  <div class="qr-container">

    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=Ticket123" alt="QR Code">
    <p>Purchase has been completed.</p>
  </div>

  <script>
    function openNav(){
      const sb = document.getElementById('sidebar');
      sb.style.width = '260px';
      sb.setAttribute('aria-hidden','false');
    }
    function closeNav(){
      const sb = document.getElementById('sidebar');
      sb.style.width = '0';
      sb.setAttribute('aria-hidden','true');
    }

    const searchBar = document.getElementById('searchBar');
    const sidebarLinks = Array.from(document.querySelectorAll('#sidebar a'));

    searchBar.addEventListener('keyup', () => {
      const q = (searchBar.value || '').trim().toLowerCase();
      sidebarLinks.forEach(a => {
        a.style.display = a.textContent.toLowerCase().includes(q) ? 'block' : 'none';
      });
    });
  </script>

</body>
</html>