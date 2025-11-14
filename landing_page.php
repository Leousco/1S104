<?php
session_start();
if (!isset($_SESSION['Role']) || $_SESSION['Role'] !== "PASSENGER") {
    header("Location: login.php?error=unauthorized");
    exit();
}

$balance = isset($_SESSION['balance']) ? $_SESSION['balance'] : 0; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <title>Passenger Dashboard</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
     font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
     background: #f5f7fa;
     color: #333;
     overflow-x: hidden;
    -ms-overflow-style: none;
    }
    body::-webkit-scrollbar { display: none; width: 0; }

    .global-map-bg {
      position: fixed; top: 0; left: 0;
      width: 100%; height: 100%;
      background: url('https://upload.wikimedia.org/wikipedia/commons/thumb/8/80/World_map_-_low_resolution.svg/1280px-World_map_-_low_resolution.svg.png') center center no-repeat;
      background-size: cover; opacity: 0.1; z-index: -1;
    }

    header {
      background: linear-gradient(90deg, #2e7d32, #66bb6a);
      padding: 15px 20px;
      display: flex; align-items: center; justify-content: space-between;
      color: white;
      box-shadow: 0 3px 6px rgba(0,0,0,0.15);
      position: sticky; top: 0; z-index: 10;
    }

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
    .coin-balance:hover { background: #ffffff33; transform: scale(1.05); }

    .coin-balance img {
      width: 22px; height: 22px; margin-right: 8px;
    }

    .menu { font-size: 26px; cursor: pointer; transition: transform 0.2s; }
    .menu:hover { transform: scale(1.1); }

    .profile {
      width: 35px; height: 35px;
      background-color: #2e7d32;
      color: white;
      font-size: 22px;
      display: flex; justify-content: center; align-items: center;
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
        top: 0; left: 0;
        background-color: #1b1b1b;
        overflow: hidden;
        transition: width 0.3s ease;
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
        background: #2e7d32; color: #fff; padding-left: 35px;
    }
    .sidebar .closebtn {
        position: absolute; top: 10px; right: 20px;
        font-size: 30px; cursor: pointer; color: white;
    }

    .container { 
      display: flex; flex-direction: column;
      align-items: center; padding: 40px 20px; gap: 30px;
      position: relative; z-index: 1;
    }
      
    .slideshow-container {
      min-width: 320px; min-height: calc(100vh - 70px);
      display: flex; flex-direction: column;
      align-items: center; justify-content: center;
      padding: 30px 20px; gap: 30px;
    } 

    .destination { display: flex; width: 100%; }

    .destination-flex {
      display: flex; gap: 40px; justify-content: space-between;
      width: 100%; max-width: 1200px;
      margin: 0 auto; align-items: flex-start;
    }

    .description { flex: 1; font-size: 30px; color: #333; opacity: 0; transition: opacity 1s ease-in; }
    .description h2 { font-size: 50px; color: #2e7d32; margin-bottom: 15px; }

    .thumbnails-pattern { flex: 1; display: flex; flex-wrap: wrap; gap: 10px; justify-content: flex-end; }
    .thumb { border-radius: 12px; object-fit: cover; opacity: 0; transition: opacity 0.5s ease-in; }
    .thumb.show { opacity: 1; }

    .thumb-large { width: 500px; height: 400px; border-radius: 15px; object-fit: cover; box-shadow: 0 4px 12px rgba(0,0,0,0.3); transition: transform 0.3s ease; }
    .thumb-large:hover { transform: scale(1.05); }

    .fixed-booking-container {
        position: fixed;
        bottom: 150px;
        left: 80px;
        z-index: 1000;
    }

    .btn-book {
        background-color: #2e7d32;
        color: white;
        font-size: 1.2rem;
        font-weight: 700;
        border: none;
        padding: 10px 30px; 
        border-radius: 10px; 
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(46, 125, 50, 0.5);
        transition: all 0.3s ease;
    }
    .btn-book:hover {
        background-color: #388e3c;
        transform: scale(1.05);
        box-shadow: 0 6px 20px rgba(46, 125, 50, 0.7);
    }

    /* POWER MENU */
    .sidebar-power {
      position: absolute; bottom: 20px; left: 0;
      width: 100%; padding: 0 20px;
    }
    #power-toggle {
      background: none; border: none;
      color: #ddd; font-size: 20px;
      cursor: pointer; width: 100%; text-align: left;
    }
    #power-toggle:hover { color: #2e7d32; }

    .power-menu {
      margin-top: 10px;
      display: flex; flex-direction: column; gap: 8px;
    }
    .power-menu a {
      font-size: 18px; color: #ddd;
      display: flex; align-items: center;
      transition: 0.3s;
    }
    .power-menu a:hover {
      background: #2e7d32; color: #fff;
      padding-left: 7px; border-radius: 6px;
    }
    .hidden { display: none; }

    /* POP-UP DISCOUNT AD */
    .discount-ad {
      position: fixed;
      bottom: 30px;
      right: 30px;
      background: #2e7d32;
      color: white;
      width: 260px;
      padding: 20px;
      border-radius: 18px;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
      cursor: pointer;
      z-index: 3000;
      animation: popIn 0.9s ease-out;
      transition: 0.3s;
    }
    .discount-ad:hover {
      transform: scale(1.05);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.35);
    }
    .discount-ad-content { text-align: center; }
    .discount-ad i { font-size: 40px; margin-bottom: 10px; }
    .discount-ad h3 { font-size: 20px; margin-bottom: 8px; font-weight: bold; }
    .discount-ad p { font-size: 14px; margin-bottom: 12px; }
    .discount-ad button {
      background: white; color: #2e7d32;
      border: none; padding: 10px 18px;
      border-radius: 8px; font-size: 14px;
      font-weight: 700; cursor: pointer;
    }
    @keyframes popIn {
      from { opacity: 0; transform: translateY(30px) scale(0.9); }
      to { opacity: 1; transform: translateY(0) scale(1); }
    }
    @keyframes fadeOut {
      from { opacity: 1; }
      to { opacity: 0; transform: translateY(20px); }
    }
    .discount-ad.hide { animation: fadeOut 1s forwards; }

  </style>
</head>
<body>
  
<div class="global-map-bg"></div>

<div id="sidebar" class="sidebar" aria-hidden="true">
      <span class="closebtn" onclick="closeNav()">&times;</span>
      
      <a href="passenger_dashboard.php"><i class="fas fa-home"></i> Homepage</a>
      <a href="vehicle.php"><i class="fas fa-bus"></i> Vehicles</a>
      <a href="ticketing/ticketing.php"><i class="fas fa-ticket-alt"></i> Buy Ticket</a>
      <a href="redeem_voucher.php"><i class="fas fa-gift"></i> Redeem Voucher</a>
      <a href="feedback.php"><i class="fas fa-comment-dots"></i> Feedback</a>
      <a href="about.php"><i class="fas fa-info-circle"></i> About Us</a>
      <a href="discountPage/discount_page.php"><i class="fas fa-percent"></i> Apply for a Discount</a>

      <div class="sidebar-power">
        <button id="power-toggle"><i class="fas fa-sign-out-alt"></i></button>
        <div id="power-menu" class="power-menu hidden">
          <a href="login.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
          <a href="passenger_dashboard.php"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
      </div>
</div>

<header>
  <div class="menu" onclick="openNav()">☰</div>
  <div class="right-header">
      <a href="redeem_voucher.php" class="coin-balance">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
              <circle cx="12" cy="12" r="10" fill="#F4C542"/>
              <circle cx="12" cy="12" r="8.2" fill="#F9D66B"/>
              <path d="M8 12c0-2 3-2 4-2s4 0 4 2-3 2-4 2-4 0-4-2z" fill="#D39C12" opacity="0.9"/>
          </svg>
          <span id="header-balance">₱0</span>
      </a>
      <div class="profile" onclick="window.location.href='user_prof.php'">👤</div>
  </div>
</header>

<div class="slideshow-container" id="slideshow-container">

  <div class="destination">
    <div class="destination-flex">
      <div class="description">
        <h2>Start your journey with kindness</h2>
        <p>Offer your seat to the elderly, pregnant women, or persons with disabilities.</p>
      </div>
      <div class="thumbnails-pattern carousel-thumbnails">
        <img src="slideshowimg/1.jpg" class="thumb thumb-large">
        <img src="slideshowimg/2.jpg" class="thumb thumb-large">
        <img src="slideshowimg/3.jpg" class="thumb thumb-large">
        <img src="slideshowimg/4.jpg" class="thumb thumb-large">
      </div>
    </div>
  </div>

  <div class="destination">
    <div class="destination-flex">
      <div class="description">
        <h2>Keep your things safe</h2>
        <p>Always watch your bag, wallet, and phone in crowded places.</p>
      </div>
      <div class="thumbnails-pattern carousel-thumbnails">
        <img src="slideshowimg/5.jpg" class="thumb thumb-large">
        <img src="slideshowimg/6.jpg" class="thumb thumb-large">
        <img src="slideshowimg/7.jpg" class="thumb thumb-large">
        <img src="slideshowimg/8.jpg" class="thumb thumb-large">
      </div>
    </div>
  </div>

</div>

<div class="fixed-booking-container">
    <button class="btn-book">Book Now!</button>
</div>

<!-- ✅ DISCOUNT POP-UP INSERTED HERE -->
<div class="discount-ad" onclick="window.location.href='discountPage/discount_page.php'">
  <div class="discount-ad-content">
    <i class="fas fa-percent"></i>
    <h3>Get Special Discounts!</h3>
    <p>Students, seniors, and PWDs can apply for fare discounts.</p>
    <button>Apply Now</button>
  </div>
</div>

<script>
  async function renderUserBalance() {
    const hb = document.getElementById('header-balance');
    hb.textContent = '...';
    try {
        const res = await fetch('get_passenger_data.php');
        const data = await res.json();
        if (data.success) {
            hb.textContent = '₱' + parseFloat(data.user.balance || 0).toFixed(2);
        } else hb.textContent = 'Err';
    } catch {
        hb.textContent = 'Err';
    }
  }
renderUserBalance();

function openNav() {
  document.getElementById("sidebar").style.width = "280px";
}
function closeNav() {
  document.getElementById("sidebar").style.width = "0";
}

const slideshowContainer = document.getElementById('slideshow-container');
const destinations = document.querySelectorAll('.destination');
let currentIndex = 0;
let interval;
let imageIntervals = [];

function startImageCarousel(dest) {
  const thumbnails = dest.querySelectorAll('.thumb');
  let imgIndex = 0;

  function showImages() {
    thumbnails.forEach(t => t.style.opacity = 0);
    thumbnails[imgIndex].style.opacity = 1;
    imgIndex = (imgIndex + 1) % thumbnails.length;
  }

  showImages();
  const intv = setInterval(showImages, 2000);
  imageIntervals.push(intv);
}

function stopAllImageCarousels() {
  imageIntervals.forEach(clearInterval);
  imageIntervals = [];
}

function showDestination(index) {
  destinations.forEach((dest, i) => {
    dest.style.display = i === index ? 'flex' : 'none';
    const desc = dest.querySelector('.description');
    const thumbs = dest.querySelectorAll('.thumb');

    if (i === index) {
      desc.style.opacity = 0;
      thumbs.forEach(t => t.style.opacity = 0);
      setTimeout(() => {
        desc.style.opacity = 1;
        thumbs.forEach(t => t.style.opacity = 1);
      }, 500);
      startImageCarousel(dest);
    }
  });
}

document.addEventListener('DOMContentLoaded', () => {
  showDestination(currentIndex);

  interval = setInterval(() => {
    stopAllImageCarousels();
    currentIndex = (currentIndex + 1) % destinations.length;
    showDestination(currentIndex);
  }, 10000);
});

document.addEventListener('DOMContentLoaded', () => {
  document.querySelector('.btn-book').addEventListener('click', () => {
    window.location.href = 'vehicle.php';
  });
});

document.getElementById('power-toggle').addEventListener('click', () => {
  document.getElementById('power-menu').classList.toggle('hidden');
});

/* ✅ AUTO-HIDE THE DISCOUNT AD AFTER 7 SECONDS */
setTimeout(() => {
  const ad = document.querySelector('.discount-ad');
  if (ad) ad.classList.add('hide');
}, 7000);

</script>

</body>
</html>