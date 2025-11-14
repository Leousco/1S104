<?php
include 'config.php';

if (!isset($_GET['token'])) {
    die("Invalid verification link.");
}

$token = $_GET['token'];

$stmt = $conn->prepare("SELECT * FROM users WHERE verification_token=? LIMIT 1");
$stmt->bind_param("s", $token);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($user) {
    $stmt = $conn->prepare("UPDATE users SET is_verified=1, verification_token=NULL WHERE UserID=?");
    $stmt->bind_param("i", $user['UserID']);
    $stmt->execute();
    echo "Email verified! You can now log in.";
} else {
    echo "Invalid or expired token.";
}
