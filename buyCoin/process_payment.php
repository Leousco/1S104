<?php
session_start();
include("../config.php");

// Make sure user is logged in
if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== "PASSENGER") {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['deal_id'])) {
    die("Deal not specified.");
}

$deal_id = intval($_GET['deal_id']);
$user_id = $_SESSION['UserID'];
$user_email = $_SESSION['Email'];

// Fetch deal details and check if it's valid
$stmt = $conn->prepare("
    SELECT * FROM coin_deals 
    WHERE DealID=? 
    AND Status='ACTIVE'
    AND (
        DealType = 'STANDARD' 
        OR (DealType = 'LIMITED' AND NOW() BETWEEN ValidFrom AND ValidTo)
    )
    LIMIT 1
");
$stmt->bind_param("i", $deal_id);
$stmt->execute();
$deal = $stmt->get_result()->fetch_assoc();

if (!$deal) {
    die("Coin deal not available or has expired.");
}

$amount = $deal['Price'];
$description = "Purchase Coin Deal: " . $deal['DealName'] . " (" . number_format($deal['CoinAmount']) . " coins)";

// Get Xendit API key from config
$XENDIT_SECRET_KEY = "xnd_development_5aKcd6t1aUkFF5o8pvSdJTeZ2NJu9Jm5nvhh2cIZYHMBYnsvOrfV6Kycc5gk8r"; // Xendit API key

// Create Xendit invoice
$url = "https://api.xendit.co/v2/invoices";
$external_id = "coin_deal_" . $user_id . "_" . time();

$data = [
    "external_id" => $external_id,
    "payer_email" => $user_email,
    "description" => $description,
    "amount" => $amount,
    "success_redirect_url" => "http://localhost/SADPROJ/buyCoin/payment_success.php?deal_id=$deal_id&external_id=$external_id",
    "failure_redirect_url" => "http://localhost/SADPROJ/buyCoin/payment_failed.php?deal_id=$deal_id"
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_USERPWD, $XENDIT_SECRET_KEY . ":");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$result = json_decode($response, true);

if ($http_code == 200 && isset($result['invoice_url'])) {
    // Save transaction in database
    $stmt = $conn->prepare("
        INSERT INTO coin_transactions 
        (UserID, DealID, CoinAmount, AmountPaid, Status, PaymentMethod) 
        VALUES (?, ?, ?, ?, 'PENDING', 'Xendit')
    ");
    $stmt->bind_param("iiid", $user_id, $deal_id, $deal['CoinAmount'], $amount);
    $stmt->execute();
    $transaction_id = $conn->insert_id;

    // Store external_id for webhook matching
    $stmt = $conn->prepare("UPDATE coin_transactions SET PaymentMethod = ? WHERE TransactionID = ?");
    $payment_ref = "Xendit-" . $external_id;
    $stmt->bind_param("si", $payment_ref, $transaction_id);
    $stmt->execute();

    // Redirect user to Xendit payment page
    header("Location: " . $result['invoice_url']);
    exit();
} else {
    echo "<h2>Payment Error</h2>";
    echo "<p>Unable to create payment invoice. Please try again later.</p>";
    echo "<pre>";
    print_r($result);
    echo "</pre>";
    echo '<a href="buy_coins.php"><button>Go Back</button></a>';
}
?>