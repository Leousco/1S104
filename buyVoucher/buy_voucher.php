<?php
session_start();
include("../config.php");

// Check if the user is logged in
if (!isset($_SESSION['UserID'])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_SESSION['Role']) || $_SESSION['Role'] !== "PASSENGER") {
    header("Location: login.php?error=unauthorized");
    exit();
}

// Fetch vouchers from the database, ordered by type and expiry
$query = "SELECT * FROM voucher WHERE Status = 'Active' 
          ORDER BY 
            CASE WHEN VoucherCategory = 'Standard' THEN 1
                 WHEN VoucherCategory = 'Limited' THEN 2
                 WHEN VoucherCategory = 'Promo' THEN 3
            END, ValidTo ASC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Passenger Dashboard</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
  background: #f5f7fa;
  color: #333;
  overflow-x: hidden;
}
.global-map-bg {
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: url('https://upload.wikimedia.org/wikipedia/commons/thumb/8/80/World_map_-_low_resolution.svg/1280px-World_map_-_low_resolution.svg.png') center/cover no-repeat;
  opacity: 0.1;
  z-index: -1;
}
header {
  background: linear-gradient(90deg, #2e7d32, #66bb6a);
  color: white;
  padding: 15px 20px;
  display: flex; 
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 3px 6px rgba(0,0,0,0.15);
  position: sticky; top: 0; z-index: 10;
}
.menu { font-size: 26px; cursor: pointer; transition: transform 0.2s; }
.menu:hover { transform: scale(1.1); }
.right-header { display: flex; align-items: center; gap: 15px; }
.coin-balance {
  display: flex; align-items: center; background: #ffffff22; padding: 6px 12px; border-radius: 20px; color: white; font-weight: bold; text-decoration: none;
  box-shadow: 0 2px 5px rgba(0,0,0,0.2); transition: all 0.3s;
}
.coin-balance:hover { background: #ffffff33; transform: scale(1.05); }
.coin-balance svg { margin-right: 8px; }
.profile {
  width: 35px; height: 35px; background: #2e7d32; border-radius: 50%; display: flex; justify-content: center; align-items: center; color: white; font-size: 22px; cursor: pointer; transition: 0.3s; box-shadow: 0 2px 6px rgba(0,0,0,0.2);
}
.profile:hover { background: #66bb6a; transform: scale(1.1); }

/* Sidebar */
.sidebar { height: 100%; width: 0; position: fixed; top: 0; left: 0; background-color: #1b1b1b; overflow: hidden; transition: width 0.3s ease; padding-top: 60px; z-index: 1000; }
.sidebar a { display: block; padding: 14px 28px; font-size: 18px; color: #ddd; text-decoration: none; transition: 0.3s; }
.sidebar a:hover { background: #2e7d32; color: white; padding-left: 35px; }
.sidebar .closebtn { position: absolute; top: 10px; right: 20px; font-size: 30px; cursor: pointer; color: white; }
.sidebar-power { position: absolute; bottom: 20px; left: 0; width: 100%; padding: 0 20px; }
#power-toggle { background: none; border: none; color: #ddd; font-size: 20px; cursor: pointer; width: 100%; text-align: left; }
#power-toggle:hover { color: #2e7d32; }
.power-menu { margin-top: 10px; display: flex; flex-direction: column; gap: 8px; }
.power-menu a { font-size: 18px; color: #ddd; text-decoration: none; display: flex; align-items: center; transition: 0.3s; }
.power-menu a i { width: 25px; margin-right: 10px; text-align: center; }
.power-menu a:hover { background: #2e7d32; color: white; padding-left: 7px; border-radius: 6px; }
.hidden { display: none; }

/* Main Content */
.container { max-width: 1000px; margin: 40px auto; padding: 20px; }
h1 { text-align: center; margin-bottom: 30px; color: #333; }
.voucher-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
.voucher-card { background: white; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); padding: 20px; transition: transform 0.2s ease, box-shadow 0.2s ease; }
.voucher-card:hover { transform: translateY(-5px); box-shadow: 0 6px 16px rgba(0,0,0,0.15); }
.voucher-card i { font-size: 40px; color: #3b82f6; margin-bottom: 10px; }
.voucher-title { font-weight: 600; font-size: 18px; margin-bottom: 5px; color: #222; }
.voucher-desc { color: #555; font-size: 14px; margin-bottom: 10px; min-height: 40px; }
.voucher-info { font-size: 13px; color: #777; margin-bottom: 10px; }
.voucher-price { font-size: 16px; font-weight: bold; color: #111; margin-bottom: 15px; }
.buy-btn { display: inline-block; text-align: center; width: 100%; background-color: #22c55e; color: white; padding: 10px; border-radius: 8px; text-decoration: none; transition: background 0.2s ease; font-weight: 500; }
.buy-btn:hover { background-color: #16a34a; }
.no-vouchers { text-align: center; color: #777; font-size: 16px; margin-top: 40px; }

/* Color-coded card borders */
.voucher-card.Standard { border-left: 5px solid #22c55e; }
.voucher-card.Limited { border-left: 5px solid #f59e0b; }
.voucher-card.Promo { border-left: 5px solid #3b82f6; }
</style>
</head>

<body>
<div class="global-map-bg"></div>

<div id="sidebar" class="sidebar" aria-hidden="true">
  <span class="closebtn" onclick="closeNav()">&times;</span>
  <a href="../passenger_dashboard.php"><i class="fas fa-home"></i> Homepage</a>
  <a href="../vehiclePage/vehicle.php"><i class="fas fa-bus"></i> Vehicles</a>
  <a href="../ticketing/ticketing.php"><i class="fas fa-ticket-alt"></i> Buy Ticket</a>
  <a href="../redeem_voucher.php"><i class="fas fa-gift"></i> Redeem Voucher</a>
  <a href="../feedback.php"><i class="fas fa-comment-dots"></i> Feedback</a>
  <a href="../about.php"><i class="fas fa-info-circle"></i> About Us</a>
  <a href="../discountPage/discount_page.php"><i class="fas fa-percent"></i> Apply for a Discount</a>

  <div class="sidebar-power">
    <button id="power-toggle"><i class="fas fa-sign-out-alt"></i></button>
    <div id="power-menu" class="power-menu hidden">
      <a href="../login.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
      <a href="passenger_dashboard.php"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
  </div>
</div>

<header>
  <div class="menu" onclick="openNav()">☰</div>
  <div class="right-header">
    <a href="redeem_voucher.php" class="coin-balance">
      <svg width="22" height="22" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="10" fill="#F4C542"/>
        <circle cx="12" cy="12" r="8.2" fill="#F9D66B"/>
        <path d="M8 12c0-2 3-2 4-2s4 0 4 2-3 2-4 2-4 0-4-2z" fill="#D39C12" opacity="0.9"/>
      </svg>
      <span id="header-balance">₱0</span>
    </a>
    <div class="profile" onclick="window.location.href='user_prof.php'">👤</div>
  </div>
</header>

<div class="container">
  <h1><i class="fas fa-tags"></i> Available Vouchers</h1>
  <div class="voucher-grid">
    <?php if ($result->num_rows > 0): ?>
      <?php while($v = $result->fetch_assoc()): ?>
        <div class="voucher-card <?= htmlspecialchars($v['VoucherCategory']) ?>">
          <i class="fas fa-ticket-alt"></i>
          <div class="voucher-title"><?= htmlspecialchars($v['Code']) ?></div>
          <div class="voucher-desc"><?= htmlspecialchars($v['Description']) ?></div>
          <div class="voucher-info">
            <strong>Type:</strong> <?= htmlspecialchars($v['VoucherCategory']) ?><br>
            <?php if($v['VoucherCategory'] !== 'Standard'): ?>
              <strong>Valid:</strong> <?= htmlspecialchars($v['ValidFrom']) ?> to <?= htmlspecialchars($v['ValidTo']) ?>
            <?php else: ?>
              <strong>Valid:</strong> No expiry
            <?php endif; ?>
          </div>
          <div class="voucher-price">
            <?php if($v['VoucherCategory'] === 'Promo'): ?>
              Free!
            <?php else: ?>
              ₱<?= number_format($v['DiscountValue'], 2) ?>
            <?php endif; ?>
          </div>
          <?php if($v['VoucherCategory'] !== 'Promo'): ?>
            <a href="process_payment.php?voucher_id=<?= $v['VoucherID'] ?>" class="buy-btn">
              Buy Now
            </a>
          <?php else: ?>
            <a href="../redeem_voucher.php?voucher_id=<?= $v['VoucherID'] ?>" class="buy-btn">
              Claim
            </a>
          <?php endif; ?>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p class="no-vouchers">No vouchers are currently available.</p>
    <?php endif; ?>
  </div>
</div>

<script>
async function renderUserBalance() {
  const hb = document.getElementById('header-balance');
  hb.textContent = '...';
  try {
    const res = await fetch('../get_passenger_data.php');
    const data = await res.json();
    if (data.success) {
      const balance = parseFloat(data.user.balance || 0).toFixed(2);
      hb.textContent = '₱' + balance;
    } else hb.textContent = 'Err';
  } catch {
    hb.textContent = 'Err';
  }
}

function openNav() {
  const sidebar = document.getElementById("sidebar");
  sidebar.style.width = "280px";
  sidebar.setAttribute("aria-hidden", "false");
}

function closeNav() {
  const sidebar = document.getElementById("sidebar");
  sidebar.style.width = "0";
  sidebar.setAttribute("aria-hidden", "true");
}

document.getElementById('power-toggle').addEventListener('click', () => {
  document.getElementById('power-menu').classList.toggle('hidden');
});

renderUserBalance();
</script>
</body>
</html>
