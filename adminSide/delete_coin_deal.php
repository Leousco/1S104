<?php
session_start();
include("../config.php");

header('Content-Type: application/json');

if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== "ADMIN") {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$id = intval($data['id']);

$sql = "DELETE FROM coin_deals WHERE DealID = $id";

if ($conn->query($sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}
?>