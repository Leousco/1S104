<?php
session_start();
include("../config.php");

if (!isset($_POST['otp']) || !isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit();
}

$email = $_SESSION['reset_email'];
$otp = $_POST['otp'];

$stmt = $conn->prepare("SELECT * FROM password_resets WHERE email=? AND otp=?");
$stmt->bind_param("ss", $email, $otp);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if(!$row || strtotime($row['expires_at']) < time()){
    echo "<script>alert('Invalid or expired OTP'); window.location='verify_otp.php';</script>";
    exit();
}


// OTP valid → allow password reset
$_SESSION['otp_verified'] = true;
header("Location: reset_password.php");
exit();
