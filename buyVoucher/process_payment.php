<?php
session_start();
include("../config.php");

// Make sure user is logged in
if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== "PASSENGER") {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['voucher_id'])) {
    die("Voucher not specified.");
}

$voucher_id = intval($_GET['voucher_id']);
$user_email = $_SESSION['Email']; // assuming you store email in session

// Fetch voucher details
$stmt = $conn->prepare("SELECT * FROM voucher WHERE VoucherID=? AND Status='Active' LIMIT 1");
$stmt->bind_param("i", $voucher_id);
$stmt->execute();
$voucher = $stmt->get_result()->fetch_assoc();

if (!$voucher) {
    die("Voucher not available.");
}

// Free vouchers (Promo) should go to redeem directly
if ($voucher['VoucherCategory'] === 'Promo') {
    header("Location: ../redeem_voucher.php?voucher_id=$voucher_id");
    exit();
}

$amount = $voucher['DiscountValue'];
$description = "Purchase Voucher: " . $voucher['Code'];

// Create Xendit invoice
$url = "https://api.xendit.co/v2/invoices";
$data = [
    "external_id" => "voucher_" . uniqid(),
    "payer_email" => $user_email,
    "description" => $description,
    "amount" => $amount,
    "success_redirect_url" => "http://localhost/SADPROJ/buyVoucher/payment_success.php?voucher_id=$voucher_id",
    "failure_redirect_url" => "http://localhost/SADPROJ/buyVoucher/payment_failed.php?voucher_id=$voucher_id"
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_USERPWD, $XENDIT_SECRET_KEY . ":");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

if (isset($result['invoice_url'])) {
    // Save transaction in database
    $stmt = $conn->prepare("INSERT INTO transactions (UserID, VoucherID, Status, PaymentURL) VALUES (?, ?, 'PENDING', ?)");
    $stmt->bind_param("iis", $_SESSION['UserID'], $voucher_id, $result['invoice_url']);
    $stmt->execute();

    // Redirect user to Xendit payment page
    header("Location: " . $result['invoice_url']);
    exit();
} else {
    echo "<pre>";
    print_r($result);
    echo "</pre>";
}
?>
