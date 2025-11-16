<?php
session_start();
include("../config.php");

if (!isset($_SESSION['reset_email']) || !isset($_SESSION['otp_verified'])) {
    header("Location: forgot_password.php");
    exit();
}

$email = $_SESSION['reset_email'];
$password = $_POST['password'];
$confirm = $_POST['confirm_password'];

if ($password !== $confirm) {
    echo "<script>alert('Passwords do not match'); window.location='reset_password.php';</script>";
    exit();
}

// Hash password
$hashed = password_hash($password, PASSWORD_DEFAULT);

// Update user table
$stmt = $conn->prepare("UPDATE users SET PasswordHash=? WHERE email=?");
$stmt->bind_param("ss", $hashed, $email);
$stmt->execute();

// Clean OTP
$conn->query("DELETE FROM password_resets WHERE email='$email'");

// Clear session
unset($_SESSION['reset_email']);
unset($_SESSION['otp_verified']);

echo "<script>alert('Password updated!'); window.location='../login.php';</script>";
exit();
