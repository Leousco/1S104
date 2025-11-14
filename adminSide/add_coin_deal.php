<?php
session_start();
include("../config.php");

header('Content-Type: application/json');

if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== "ADMIN") {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

$dealName = $conn->real_escape_string($data['dealName']);
$coinAmount = intval($data['coinAmount']);
$price = floatval($data['price']);
$dealType = $data['dealType'];
$status = $data['status'];

$validFrom = null;
$validTo = null;

if ($dealType === 'LIMITED') {
    $validFrom = !empty($data['validFrom']) ? "'" . $conn->real_escape_string($data['validFrom']) . "'" : "NULL";
    $validTo = !empty($data['validTo']) ? "'" . $conn->real_escape_string($data['validTo']) . "'" : "NULL";
} else {
    $validFrom = "NULL";
    $validTo = "NULL";
}

$sql = "INSERT INTO coin_deals (DealName, CoinAmount, Price, DealType, ValidFrom, ValidTo, Status) 
        VALUES ('$dealName', $coinAmount, $price, '$dealType', $validFrom, $validTo, '$status')";

if ($conn->query($sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}
?>