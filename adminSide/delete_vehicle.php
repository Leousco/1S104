<?php
require_once '../config.php';
$data = json_decode(file_get_contents("php://input"), true);
if (!$data || !isset($data['id'])) { echo json_encode(["error" => "Missing ID"]); exit; }

$id = intval($data['id']);
$stmt = $conn->prepare("DELETE FROM vehicle WHERE VehicleID=?");
$stmt->bind_param("i", $id);
echo json_encode(["success" => $stmt->execute()]);
