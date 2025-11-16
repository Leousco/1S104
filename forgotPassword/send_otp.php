<?php
session_start();
include("../config.php");
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_POST['email'])) {
    header("Location: forgot_password.php");
    exit();
}

$email = $_POST['email'];

// Generate OTP
$otp = rand(100000, 999999);

// Expiry (5 minutes)
$expires_at = date("Y-m-d H:i:s", time() + 300);

// Delete old OTPs for this email
$conn->query("DELETE FROM password_resets WHERE email='$email'");

// Insert new OTP
$stmt = $conn->prepare("INSERT INTO password_resets (email, otp, expires_at) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $email, $otp, $expires_at);
$stmt->execute();

// ---------------------
// SEND OTP USING PHPMailer
// ---------------------
$mail = new PHPMailer(true);

try {
    //SMTP SETTINGS
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';    // or your SMTP
    $mail->SMTPAuth   = true;
    $mail->Username   = 'novacore.mailer@gmail.com'; // your email
    $mail->Password   = 'yjwc zsaa jltv vekq';    // gmail app password
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    //EMAIL SETTINGS
    $mail->setFrom('novacore.mailer@gmail.com', 'NovaCore Team');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = "Your Password Reset OTP";
    $mail->Body    = "
        <h3>Your OTP Code</h3>
        <p style='font-size: 20px; font-weight: bold;'>$otp</p>
        <p>This code will expire in 5 minutes.</p>
    ";

    $mail->send();

} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    exit();
}

// Save email in session for verification
$_SESSION['reset_email'] = $email;

header("Location: verify_otp.php");
exit();
?>
