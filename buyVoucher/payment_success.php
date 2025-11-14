<?php
session_start();
include("../config.php");

// MUST be logged in
if (!isset($_SESSION['UserID'])) {
    die("Unauthorized.");
}

// MUST have voucher_id from redirect
if (!isset($_GET['voucher_id'])) {
    die("Invalid voucher.");
}

$voucher_id = intval($_GET['voucher_id']);
$user_email = $_SESSION['Email'];


$stmt = $conn->prepare("SELECT * FROM voucher WHERE VoucherID=? LIMIT 1");
$stmt->bind_param("i", $voucher_id);
$stmt->execute();
$voucher = $stmt->get_result()->fetch_assoc();

if (!$voucher) {
    die("Voucher not found.");
}

// Update transaction status
$stmt = $conn->prepare("
    UPDATE transactions 
    SET Status='COMPLETED' 
    WHERE UserID=? AND VoucherID=? 
    LIMIT 1
");

$stmt->bind_param("ii", $_SESSION['UserID'], $voucher_id);
$stmt->execute();

// ============================
// SEND EMAIL WITH PHPMailer
// ============================
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Require Composer autoloader
require '../vendor/autoload.php';

$mail = new PHPMailer(true);

try {
    // SMTP CONFIG
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;

    // CHANGE THESE ↓↓↓
    $mail->Username = 'novacore.mailer@gmail.com';
    $mail->Password = 'yjwc zsaa jltv vekq';  // Gmail App Password
    // CHANGE THESE ↑↑↑

    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    // Recipients
    $mail->setFrom('novacore.mailer@gmail.com', 'NovaCore Team');
    $mail->addAddress($user_email);

    // Email Body
    $mail->isHTML(true);
    $mail->Subject = "Your Voucher Code";

    $mail->Body = "
        <h2>Thank you for your purchase!</h2>
        <p>Your voucher code is:</p>
        <h1><b>{$voucher['Code']}</b></h1>
        <p>You can redeem this code inside your account to get coins.</p>
    ";

    $mail->send();

} catch (Exception $e) {
    // If email fails, show error but continue
    echo "<p style='color:red;'>Email Error: {$mail->ErrorInfo}</p>";
}

?>

<h1>Payment Successful!</h1>
<p>Your voucher has been emailed to you.</p>

<a href="../passenger_dashboard.php">
    <button style="padding:10px 20px; font-size:16px; cursor:pointer;">
        Go to Dashboard
    </button>
</a>
