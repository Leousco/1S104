<?php 
// gcash_payment.php
include __DIR__ . '/config.php';

$ticket = isset($_GET['ticket']) ? intval($_GET['ticket']) : 0;
if (!$ticket) {
  http_response_code(400);
  echo "<div style='padding:30px;font-family:Arial,Helvetica,sans-serif;color:#b00;'>❌ Invalid ticket — no ticket ID provided.</div>";
  exit;
}

// fetch ticket + passenger
$stmt = $conn->prepare("
  SELECT t.TicketID, t.FareAmount, p.Name AS PassengerName, p.Email
  FROM ticket t
  LEFT JOIN passenger p ON t.PassengerID = p.PassengerID
  WHERE t.TicketID = ?
");
$stmt->bind_param("i", $ticket);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
  echo "<div style='padding:30px;font-family:Arial,Helvetica,sans-serif;color:#b00;'>❌ Invalid ticket ID.</div>";
  exit;
}
$row = $res->fetch_assoc();
$stmt->close();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>GCash — Pay</title>
<style>
/* keep same styling */
:root{--gcash-blue:#007bff;--muted:#6b7280;--card:#ffffff;}
html,body{height:100%;margin:0;font-family:Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;}
body{background:#eef2ff;display:flex;align-items:center;justify-content:center;padding:20px;}
.phone { width:360px; max-width:95vw; background:linear-gradient(180deg,#ffffff 0,#f7fafc 100%); border-radius:22px; box-shadow:0 12px 30px rgba(2,6,23,0.12); overflow:hidden; border: 1px solid rgba(0,0,0,0.06); }
.phone .header { background: var(--gcash-blue); color: #fff; padding:14px 16px; display:flex; align-items:center; justify-content:space-between; }
.header .left { font-weight:600; font-size:16px; }
.header .right { font-size:14px; opacity:0.95; }
.body { padding:16px; background: #fff; }
.merchant { display:flex; align-items:center; gap:12px; margin-bottom:14px; }
.merchant .logo { width:56px; height:56px; border-radius:12px; background:linear-gradient(135deg,#e6f0ff,#dbeafe); display:flex; align-items:center; justify-content:center; color:var(--gcash-blue); font-weight:700; font-size:20px; }
.merchant .meta { font-size:14px; color:var(--muted); }
.merchant .meta b { color:#111; display:block; font-size:15px; margin-bottom:4px; }
.amount { margin: 12px 0 6px; text-align:center; background:#f3f6ff; padding:16px;border-radius:12px; }
.amount .big { font-size:28px; font-weight:700; color:#0b1220; }
.amount .small { color:var(--muted); margin-top:6px; font-size:13px; }
.form-row { margin:10px 0; }
label{display:block;font-size:13px;color:var(--muted); margin-bottom:6px;}
input[type="text"]{ width:100%; padding:12px; border-radius:8px; border:1px solid #e6eefc; font-size:15px; outline:none; box-sizing:border-box; }
.pay-btn{ width:100%; margin-top:12px; background:var(--gcash-blue); color:#fff; padding:12px; border-radius:10px; font-size:16px; border:0; cursor:pointer; box-shadow: 0 6px 18px rgba(3,82,255,0.12); }
.pay-btn:active{transform:translateY(1px);}
.note{font-size:12px;color:var(--muted);text-align:center;margin-top:12px;}
.footer { padding:12px 16px; font-size:12px; color:var(--muted); text-align:center; }
.loading, .success { display:none; text-align:center; padding:20px; font-size:15px; }
.loading.active, .success.active { display:block; }
</style>
</head>
<body>
<div class="phone" role="main" aria-label="GCash demo payment">
  <div class="header">
    <div class="left">Payment</div>
    <div class="right">Done</div>
  </div>
  <div class="body">
    <div class="merchant">
      <div class="logo">GC</div>
      <div>
        <div class="meta"><b>ABC Transport Co.</b> Pay to transport</div>
        <div style="font-size:13px;color:var(--muted)">Ticket #: <strong><?php echo htmlspecialchars($row['TicketID']); ?></strong></div>
      </div>
    </div>
    <div class="amount">
      <div class="big">₱<?php echo number_format($row['FareAmount'],2); ?></div>
      <div class="small">Amount due</div>
    </div>

    <form id="gcashForm" method="POST" autocomplete="off">
      <input type="hidden" id="ticket_id" name="ticket_id" value="<?php echo (int)$row['TicketID']; ?>">
      <div class="form-row">
        <label for="gcash_no">GCash Mobile Number</label>
        <input id="gcash_no" name="gcash_no" type="text" placeholder="09XXXXXXXXX" required pattern="[0-9]{11}">
      </div>
      <button class="pay-btn" type="submit">Pay Now</button>
    </form>

    <div class="loading" id="loading">⏳ Processing payment...</div>
    <div class="success" id="successMsg">✅ Payment successful!</div>
    <div class="note">This is a demo page — no real money will be transferred.</div>
  </div>
  <div class="footer">GCash Demo • ABC Transport Co.</div>
</div>

<script>
const form = document.getElementById("gcashForm");
const loading = document.getElementById("loading");
const successMsg = document.getElementById("successMsg");

form.addEventListener("submit", function(e) {
  e.preventDefault();
  form.style.display = "none";
  loading.classList.add("active");

  const formData = new FormData(form);

  fetch("gcash_process.php", {
    method: "POST",
    body: formData
  })
  .then(response => response.text())
  .then(() => {
    setTimeout(() => {
      loading.classList.remove("active");
      successMsg.classList.add("active");
      localStorage.setItem("lastPaidTicket", formData.get("ticket_id"));
    }, 2000);
  })
  .catch(() => {
    loading.classList.remove("active");
    form.style.display = "block";
    alert("Error: Payment failed. Try again.");
  });
});
</script>
</body>
</html>
