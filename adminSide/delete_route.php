<?php
include("../config.php");
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$id = intval($data['id']);
if ($id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid ID']);
    exit;
}

$stmt = $conn->prepare("DELETE FROM route WHERE RouteID=?");
$stmt->bind_param("i", $id);

echo json_encode(['success' => $stmt->execute()]);
$stmt->close();
$conn->close();
?>
