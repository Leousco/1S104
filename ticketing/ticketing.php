<?php
require_once '../config.php';
session_start();
if (!isset($_SESSION['Role']) || $_SESSION['Role'] !== "PASSENGER") {
  header("Location: ../login.php?error=unauthorized");
  exit();
}

// Get logged-in user ID
$user_id = $_SESSION['UserID'] ?? 1;

// Fetch discount status for user from discount_applications table
$discountQuery = $conn->prepare("
    SELECT Status 
    FROM discount_applications 
    WHERE UserID = ? 
    ORDER BY ApplicationID DESC 
    LIMIT 1
");
$discountQuery->bind_param("i", $user_id);
$discountQuery->execute();
$discountResult = $discountQuery->get_result();
$discountRow = $discountResult->fetch_assoc();
$discountStatus = $discountRow['Status'] ?? 'none';
$discountQuery->close();

// Determine discount eligibility
$isDiscountApproved = ($discountStatus === 'Approved');

// Fetch user details for autofill
$userDetailsQuery = $conn->prepare("
    SELECT FirstName, LastName, Email 
    FROM users 
    WHERE UserID = ?
");
$userDetailsQuery->bind_param("i", $user_id);
$userDetailsQuery->execute();
$userDetailsResult = $userDetailsQuery->get_result();
$userDetails = $userDetailsResult->fetch_assoc();
$userDetailsQuery->close();

// Set variables for use in HTML
$userFirstName = htmlspecialchars($userDetails['FirstName'] ?? '');
$userLastName  = htmlspecialchars($userDetails['LastName'] ?? '');
$userEmail     = htmlspecialchars($userDetails['Email'] ?? '');

// Fetch schedules with correct fare matching
$schedules = $conn->query("
    SELECT 
        s.ScheduleID, 
        s.DepartureTime, 
        s.ArrivalTime, 
        s.Date,
        r.RouteID, 
        r.StartLocation, 
        r.EndLocation, 
        r.TypeID, 
        f.Amount AS Fare
    FROM schedule s
    JOIN route r ON s.RouteID = r.RouteID
    JOIN fare f ON r.RouteID = f.RouteID AND f.TypeID = r.TypeID
    ORDER BY s.Date, s.DepartureTime
");
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Book Ticket - Bus Ticketing System</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
   /* ... (Your CSS styles here) ... */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            color: #333;
        }

        body::-webkit-scrollbar {
    display: none; /* Hides the scrollbar */
    width: 0; /* Ensures no width space is reserved for the scrollbar */
    }


        header {
            background: linear-gradient(90deg, #2e7d32, #66bb6a);
            padding: 15px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: white;
            box-shadow: 0 3px 6px rgba(0,0,0,0.15);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        /* Right side header controls (coins + profile) matched from passenger_dashboard.php */
        .right-header {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .coin-balance {
            display: flex;
            align-items: center;
            background: #ffffff22;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: bold;
            transition: all 0.3s;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            cursor: pointer;
            text-decoration: none;
            color: white;
        }
        .coin-balance:hover {
            background: #ffffff33;
            transform: scale(1.05);
        }
        .coin-balance img {
            width: 22px;
            height: 22px;
            margin-right: 8px;
        }

        .menu { font-size: 26px; cursor: pointer; transition: transform 0.2s; }
        .menu:hover { transform: scale(1.1); }

        .profile {
            width: 35px;
            height: 35px;
            background-color: #2e7d32;
            color: white;
            font-size: 22px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 50%;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
            margin-right: 10px;
        }
        .profile:hover {
            background-color: #66bb6a;
            transform: scale(1.1);
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        }


  .sidebar {
    height: 100%;
    width: 0;
    position: fixed;
    top: 0;
    left: 0;
    background-color: #1b1b1b;
    overflow-x: hidden;
    transition: 0.4s;
    padding-top: 60px;
    z-index: 1000;
  }

  .sidebar a {
    padding: 14px 28px;
    text-decoration: none;
    font-size: 18px;
    color: #ddd;
    display: block;
    transition: 0.3s;
  }

  .sidebar a:hover {
    background: #2e7d32;
    color: #fff;
    padding-left: 35px;
  }

  .sidebar .closebtn {
    position: absolute;
    top: 10px;
    right: 20px;
    font-size: 30px;
    color: white;
    cursor: pointer;
  }

  :root {
    --accent: #2e7d32;
    --muted: #666;
  }

  .container {
    min-width: 320px;
    padding: 20px 24px; 
    max-width: 960px;
    margin: 20px auto; 
    padding-top: 9px;
    box-sizing: border-box; 
  }

  @media (max-width: 600px) {
    .container {
      padding: 15px 15px; 
      padding-top: 80px;
    }
  }

  .card {
    background: #ffffff;
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 25px;
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08); 
    border: 1px solid #e9e9e9; 
    transition: transform 0.3s ease;
  }

  h3 {
    margin-bottom: 16px;
    font-size: 20px;
    font-weight: 700;
    color: #333;
  }

  label {
    display: block;
    font-size: 14px;
    margin-bottom: 8px;
    color: var(--muted, #666);
    font-weight: 500;
  }

  input,
  select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #dcdcdc;
    border-radius: 8px;
    margin-bottom: 18px;
    font-size: 16px;
    font-weight: 500;
    background-color: #fcfcfc;
    transition: all 0.2s ease;
  }

  input:focus,
  select:focus {
    border-color: var(--accent, #2e7d32);
    outline: none;
    box-shadow: 0 0 0 3px #2e7d3233;
    background-color: #fff;
  }

  input[readonly] {
    background-color: #f7f7f7;
    color: #4a4a4a;
    border-color: #e0e0e0 !important; 
    box-shadow: none !important;
    cursor: not-allowed;
    transition: background-color 0.2s, color 0.2s;
  }

  .btn {
    background: var(--accent, #2e7d32);
    color: #fff;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    font-weight: 600;
    font-size: 16px;
    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.15);
    margin-top: 10px;
    width: 100%;
  }

  .btn:hover {
    background: #388e3c;
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
  }

  .btn.secondary {
    background: #fff;
    color: var(--accent, #2e7d32);
    border: 1px solid var(--accent, #2e7d32);
    box-shadow: none;
  }

  .btn.secondary:hover {
    background: #f0fdf0;
    transform: translateY(-1px);
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
  }

  .fare-box {
    margin-top: 20px;
    padding: 15px 20px;
    background: #e6f5e6;
    border: 1px solid var(--accent, #2e7d32);
    border-radius: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .fare-box span:first-child {
    color: #1b5e20;
    font-weight: 600;
    font-size: 18px;
  }

  #fareDisplay {
    font-weight: 800;
    font-size: 24px;
    color: #1b5e20;
    margin-left: 15px;
  }

  .global-map-bg {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: url('https://upload.wikimedia.org/wikipedia/commons/thumb/8/80/World_map_-_low_resolution.svg/1280px-World_map_-_low_resolution.svg.png') center center no-repeat;
    background-size: cover;
    opacity: 0.07;
    z-index: -1;
  }

  .schedule-options {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 20px;
    margin-bottom: 20px;
  }

  .schedule-box {
    display: flex;
    align-items: center;
    border: 2px solid #ddd;
    border-radius: 8px;
    padding: 12px 16px;
    cursor: pointer;
    background: #f9f9f9;
    transition: all 0.2s;
    flex: 1 1 280px;
  }

  .schedule-box:hover {
    background: #e6f5e6;
    border-color: #2e7d32;
  }

  .schedule-box input[type="radio"] {
    margin-right: 12px;
    cursor: pointer;
    width: 18px;
    height: 18px;
  }

  .schedule-box input[type="radio"]:checked {
    accent-color: #2e7d32;
  }

  .schedule-box.selected {
    background: #e6f5e6;
    border-color: #2e7d32;
    border-width: 2px;
  }

  .schedule-info div {
    font-size: 14px;
    line-height: 1.5;
    color: #333;
  }

  .schedule-info div:first-child {
    font-weight: 600;
    color: #2e7d32;
  }

  .muted {
    color: #666;
    font-size: 14px;
  }

  .status-message {
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-weight: 600;
  }

  .status-approved {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
  }

  .status-pending {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
  }

  .status-rejected {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
  }

  @media (max-width: 640px) {
    .container {
      padding: 12px;
    }
    header {
      flex-direction: row;
      gap: 10px;
    }
    .schedule-box {
      flex: 1 1 100%;
    }
  }

  .schedule-label{
    margin-top: 10px;
  }

  .ticket-qr {
    max-width: 250px;
    margin: 15px auto;
    display: block;
    border: 2px solid #2e7d32;
    border-radius: 8px;
    padding: 10px;
    background: white;
  }

  .notification {
  position: fixed;
  top: 500px;
  left: 50%;
  transform: translateX(-50%);
  background-color: #28a745;
  color: white;
  padding: 15px 25px;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  font-weight: bold;
  font-size: 16px;
  z-index: 1000;
  animation: fadeInOut 4s ease forwards;
  }

  .notification.success {
    background-color: #28a745;
    color: white;
  }

  .notification.error {
    background-color: #dc3545;
    color: white;
  }

  @keyframes fadeInOut {
    0% { opacity: 0; transform: translateX(-50%) translateY(-10px); }
    10% { opacity: 1; transform: translateX(-50%) translateY(0); }
    90% { opacity: 1; }
    100% { opacity: 0; transform: translateX(-50%) translateY(-10px); }
  }
.notenough-error {
  position: fixed;
  top: 20px;
  left: 50%;
  transform: translateX(-50%);
  background-color: #dc3545; /* red for error */
  color: white;
  padding: 15px 25px;
  border-radius: 8px;
  font-weight: bold;
  font-size: 16px;
  z-index: 1000;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  animation: fadeInOut 4s ease forwards;
}

@keyframes fadeInOut {
  0% { opacity: 0; transform: translateX(-50%) translateY(-10px); }
  10% { opacity: 1; transform: translateX(-50%) translateY(0); }
  90% { opacity: 1; }
  100% { opacity: 0; transform: translateX(-50%) translateY(-10px); }
}
    .sidebar-power {
    position: absolute;
    bottom: 20px;
    left: 0;
    width: 100%;
    padding: 0 20px;
  }

  #power-toggle {
    background: none;
    border: none;
    color: #ddd;
    font-size: 20px;
    cursor: pointer;
    width: 100%;
    text-align: left;
  }

  #power-toggle:hover {
    color: #2e7d32;
  }

  .power-menu {
    margin-top: 10px;
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .power-menu a {
    font-size: 18px;
    color: #ddd;
    text-decoration: none;
    display: flex;
    align-items: center;
    transition: 0.3s;
  }

  .power-menu a i {
    width: 25px;
    margin-right: 10px;
    text-align: center;
  }

  .power-menu a:hover {
    background: #2e7d32;
    color: #fff;
    padding-left: 7px;
    border-radius: 6px;
  }

  .hidden {
    display: none;
  }

  </style>
</head>
<body>

<div class="global-map-bg"></div>

<div id="sidebar" class="sidebar" aria-hidden="true">
  <span class="closebtn" onclick="closeNav()">&times;</span>
  <a href="../passenger_dashboard.php"><i class="fas fa-home"></i> Homepage</a>
  <a href="../vehicle.php"><i class="fas fa-bus"></i> Vehicles</a>
  <a href="ticketing.php"><i class="fas fa-ticket-alt"></i> Buy Ticket</a>
  <a href="../redeem_voucher.php"><i class="fas fa-gift"></i> Redeem Voucher</a>
  <a href="../feedback.php"><i class="fas fa-comment-dots"></i> Feedback</a>
  <a href="../about.php"><i class="fas fa-info-circle"></i> About Us</a>
  <a href="../discountPage/discount_page.php"><i class="fas fa-percent"></i> Apply for a Discount</a>
    <div class="sidebar-power">
      <button id="power-toggle">
        <i class="fas fa-sign-out-alt"></i>
      </button>
      <div id="power-menu" class="power-menu hidden">
        <a href="../login.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        <a href="ticketing.php"><i class="fas fa-arrow-left"></i> Back</a>
      </div>
    </div>
</div>

<header>
  <div class="menu" onclick="openNav()">☰</div>
  <div class="right-header">
    <a href="../redeem_voucher.php" class="coin-balance">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <circle cx="12" cy="12" r="10" fill="#F4C542" />
        <circle cx="12" cy="12" r="8.2" fill="#F9D66B" />
        <path d="M8 12c0-2 3-2 4-2s4 0 4 2-3 2-4 2-4 0-4-2z" fill="#D39C12" opacity="0.9"/>
      </svg>
      <span id="header-balance">₱0</span>
    </a>
    <div class="profile" onclick="window.location.href='../user_prof.php'">👤</div>
  </div>
</header>

<div id="ticket-success" class="notification success" style="display:none;">
  Ticket booked successfully!
</div>

<div id="ticket-error" class="notification error" style="display:none;">
   Ticket booking failed. Please try again.
</div>

<div id="ticket-error" class="notenough-error" style="display:none;"></div>


<div class="container">
  <div class="card">
    <h3>Wallet Balance</h3>
    <div id="user-balance" style="font-size:22px;font-weight:700">₱0</div>
    <div id="user-history-count" class="muted">Loading...</div>
  </div>

  <div class="card ticket-card">
    <h3>Book a Ticket</h3>

    <?php if (isset($_SESSION['UserID'])): ?>
      <?php if ($isDiscountApproved): ?>
        <div class="status-message status-approved">✅ Your discount application was approved! (20% off applied)</div>
      <?php elseif ($discountStatus === 'Pending'): ?>
        <div class="status-message status-pending">🕐 Your discount application is still under review.</div>
      <?php elseif ($discountStatus === 'Rejected'): ?>
        <div class="status-message status-rejected">❌ Your discount application was rejected.</div>
      <?php endif; ?>
    <?php endif; ?>

    <form id="booking-form">
      <label>First Name</label>
      <input type="text" id="first-name" required value="<?= $userFirstName ?>" readonly />
      
      <label>Last Name</label>
      <input type="text" id="last-name" required value="<?= $userLastName ?>" readonly />
      
      <label>Email</label>
      <input type="email" id="email" required value="<?= $userEmail ?>" readonly />

      <label for="vehicleType">Vehicle Type</label>
      <select id="vehicleType" name="vehicleType" required>
        <option value="">Select vehicle type</option>
        <option value="1">Bus</option>
        <option value="2">E-Jeep</option>
      </select>

      <div class="schedule-label">
    <label>Select Schedule</label>
</div>
<div class="schedule-options" id="schedule-container">
    <?php if ($schedules->num_rows > 0): ?>
        <?php while($sch = $schedules->fetch_assoc()): ?>
            <label class="schedule-box" 
                   data-type="<?= $sch['TypeID'] ?>" 
                   data-fare="<?= $sch['Fare'] ?>"
                   data-destination="<?= htmlspecialchars($sch['StartLocation'] . ' to ' . $sch['EndLocation']) ?>">
              <input type="radio" name="schedule_id" value="<?= $sch['ScheduleID'] ?>" data-fare="<?= $sch['Fare'] ?>" required>
              <div class="schedule-info">
                <div><?= htmlspecialchars($sch['StartLocation'] . ' → ' . $sch['EndLocation']) ?></div>
                <div><?= date('l', strtotime($sch['Date'])) ?> <!-- Day of week -->
                </div>
                <div><?= htmlspecialchars($sch['DepartureTime'] . ' → ' . $sch['ArrivalTime']) ?></div>
                <div><?= $sch['TypeID'] == 1 ? 'Bus' : 'E-Jeep' ?> | ₱<?= number_format($sch['Fare'], 2) ?></div>
              </div>
            </label>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="muted">No schedules available at this time.</p>
    <?php endif; ?>
</div>


      <div class="fare-box">
        <span>Total Fare:</span>
        <span id="fareDisplay">₱0.00</span>
      </div>

      <button type="submit" class="btn">Book Ticket</button>
    </form>
  </div>

  <div class="card">
    <h3>Your Ticket</h3>
    <div id="ticket-preview" class="muted">No ticket booked yet.</div>
  </div>
</div>

<script>

  document.getElementById('power-toggle').addEventListener('click', function () {
    const menu = document.getElementById('power-menu');
    menu.classList.toggle('hidden');
  });

  function showNotification(id, message) {
  const box = document.getElementById(id);
  box.textContent = message;

  box.style.display = 'none';
  box.style.animation = 'none';
  void box.offsetWidth;
  box.style.display = 'block';
  box.style.animation = 'fadeInOut 4s ease forwards';
}

  
// Menu functions
function openNav() { 
  document.getElementById("sidebar").style.width = "280px"; 
}

function closeNav() { 
  document.getElementById("sidebar").style.width = "0"; 
}

// Global variables
const vehicleSelect = document.getElementById('vehicleType');
const scheduleBoxes = document.querySelectorAll('.schedule-box');
const fareDisplay = document.getElementById('fareDisplay');
const isDiscountApproved = <?= $isDiscountApproved ? 'true' : 'false' ?>;


// Filter schedules by vehicle type
function filterSchedules() {
  const selectedType = vehicleSelect.value;

  scheduleBoxes.forEach(box => {
    const radio = box.querySelector('input[type="radio"]');
    if (!selectedType) {
      box.style.display = 'flex';  // show all initially
    } else if (String(box.dataset.type) === selectedType) {
      box.style.display = 'flex';
    } else {
      box.style.display = 'none';
      radio.checked = false;
    }
  });

  updateFareDisplay();
}




// Update fare display
function updateFareDisplay() {
  const selectedRadio = document.querySelector('input[name="schedule_id"]:checked');
  
  if (!selectedRadio) {
    fareDisplay.textContent = '₱0.00';
    return;
  }

  let baseFare = parseFloat(selectedRadio.dataset.fare) || 0;
  let finalFare = baseFare;

  if (isDiscountApproved) {
    finalFare = baseFare * 0.8;
    fareDisplay.innerHTML = `<span style="text-decoration:line-through;opacity:0.6;">₱${baseFare.toFixed(2)}</span> ₱${finalFare.toFixed(2)}`;
  } else {
    fareDisplay.textContent = `₱${finalFare.toFixed(2)}`;
  }
}

// Add event listeners
vehicleSelect.addEventListener('change', filterSchedules);

scheduleBoxes.forEach(box => {
  box.addEventListener('click', function() {
    scheduleBoxes.forEach(b => b.classList.remove('selected'));
    this.classList.add('selected');
    updateFareDisplay();
  });
  
  const radio = box.querySelector('input[type="radio"]');
  radio.addEventListener('change', updateFareDisplay);
});

// Fetch and render user balance
async function renderUserBalance() {
  document.getElementById('user-balance').textContent = 'Loading...';
  const hb = document.getElementById('header-balance');
  if (hb) hb.textContent = '...';
  document.getElementById('user-history-count').textContent = 'Loading...'; 

  try {
    const res = await fetch('../get_passenger_data.php');
    const data = await res.json();
    
    if (data.success) {
      const balance = data.user.balance || 0; 
      const formattedBalance = '₱' + parseFloat(balance).toFixed(2);

      document.getElementById('user-balance').textContent = formattedBalance;
      if (hb) hb.textContent = formattedBalance;
      document.getElementById('user-history-count').textContent = 'Balance loaded from server'; 
    } else {
      document.getElementById('user-balance').textContent = 'Error loading balance';
      if (hb) hb.textContent = 'Error';
      document.getElementById('user-history-count').textContent = 'Error fetching data';
      console.error('Failed to load user data:', data.error);
    }
  } catch (error) {
    document.getElementById('user-balance').textContent = 'Network Error';
    if (hb) hb.textContent = 'Error';
    document.getElementById('user-history-count').textContent = 'Network Error';
    console.error('Network error during fetch:', error);
  }
}

// Handle ticket booking
document.getElementById('booking-form').addEventListener('submit', async function(e) {
  e.preventDefault();

  const selectedRadio = document.querySelector('input[name="schedule_id"]:checked');
  if (!selectedRadio) {
    alert('Please select a schedule');
    return;
  }

  // Get the parent schedule box to access destination data
  const selectedBox = selectedRadio.closest('.schedule-box');
  const destination = selectedBox.dataset.destination;

  const scheduleId = selectedRadio.value;
  const baseFare = parseFloat(selectedRadio.dataset.fare) || 0;
  
  // Apply discount if approved
  let finalFare = baseFare;
  if (isDiscountApproved) {
    finalFare = baseFare * 0.8;
  }
  
  const firstName = document.getElementById('first-name').value;
  const lastName = document.getElementById('last-name').value;
  const email = document.getElementById('email').value;

  const formData = new FormData();
  formData.append('schedule_id', scheduleId);
  formData.append('fare', finalFare.toFixed(2));
  formData.append('destination', destination);
  formData.append('first_name', firstName);
  formData.append('last_name', lastName);
  formData.append('email', email);

  // Show loading state
  const submitBtn = this.querySelector('button[type="submit"]');
  const originalText = submitBtn.textContent;
  submitBtn.textContent = 'Processing...';
  submitBtn.disabled = true;

  try {
    const res = await fetch('process_ticket.php', { 
      method: 'POST', 
      body: formData 
    });

    const text = await res.text();
    console.log('Server response:', text); // Debug log
    
    let data;
    try {
      data = JSON.parse(text);
    } catch (jsonError) {
      console.error('Invalid JSON returned by server:', text);
      alert('Server returned invalid response. Check console.');
      submitBtn.textContent = originalText;
      submitBtn.disabled = false;
      return;
    }

if (!data.success) {
  const errorBox = document.getElementById('ticket-error');
  errorBox.textContent = data.error || 'Booking failed.';

  // Reset animation
  errorBox.style.display = 'none';
  errorBox.style.animation = 'none';
  void errorBox.offsetWidth;
  errorBox.style.display = 'block';
  errorBox.style.animation = 'fadeInOut 4s ease forwards';

  submitBtn.textContent = originalText;
  submitBtn.disabled = false;
  return;
}


    // ✅ Only runs if data.success is true
    if (data.balance !== undefined && !isNaN(data.balance)) {
      const newBalance = parseFloat(data.balance).toFixed(2);
      document.getElementById('user-balance').textContent = '₱' + newBalance;
      document.getElementById('header-balance').textContent = '₱' + newBalance;
    }
    
    // Update ticket preview with QR code
    document.getElementById('ticket-preview').innerHTML = `
      <div style="padding:15px;background:#f0fdf0;border-radius:8px;">
        <div style="margin-bottom:8px;"><b>Ticket ID:</b> #${data.ticket_id}</div>
        <div style="margin-bottom:8px;"><b>Passenger:</b> ${data.name}</div>
        <div style="margin-bottom:8px;"><b>Email:</b> ${email}</div>
        <div style="margin-bottom:8px;"><b>Destination:</b> ${data.destination}</div>
        <div style="margin-bottom:8px;"><b>Fare:</b> ₱${parseFloat(data.fare).toFixed(2)}</div>
        <div style="margin-bottom:12px;"><b>Status:</b> <span style="color:#2e7d32;">✓ Confirmed</span></div>
        ${data.qr ? `
          <div style="text-align:center;">
            <p style="margin-bottom:10px;font-weight:600;color:#2e7d32;">Scan this QR Code:</p>
            <img src="${data.qr}" alt="Ticket QR Code" class="ticket-qr">
            <button class="btn secondary" style="margin-top:10px;" onclick="downloadTicket('${data.qr}', '${data.name}', '${data.destination}')">
              <i class="fas fa-download"></i> Download Ticket
            </button>
          </div>
        ` : '<p style="color:orange;">QR Code generation in progress...</p>'}
      </div>
    `;
    
    // Reset form
    this.reset();
    vehicleSelect.value = '';
    scheduleBoxes.forEach(b => b.classList.remove('selected'));
    filterSchedules(); 
    fareDisplay.textContent = '₱0.00';
    
    submitBtn.textContent = originalText;
    submitBtn.disabled = false;
    
    document.getElementById('ticket-success').style.display = 'block';

    // Scroll to ticket preview
    document.getElementById('ticket-preview').scrollIntoView({ behavior: 'smooth' });
    
  } catch (error) {
    console.error('Network or fetch error:', error);
    alert('Could not connect to the server. Check console for details.');
    submitBtn.textContent = originalText;
    submitBtn.disabled = false;
  }
});

// Download ticket function
function downloadTicket(qrPath, name, destination) {
  const link = document.createElement('a');
  link.href = qrPath;
  link.download = `Ticket_${name}_${destination}.png`;
  link.click();
}

// Initialize
filterSchedules();
renderUserBalance();
</script>
</body>
</html>