<?php
require_once '../config.php';
$data = json_decode(file_get_contents("php://input"), true);
if (!$data) { echo json_encode(["error" => "No data received"]); exit; }

$plate = $data['plate'];
$capacity = intval($data['capacity']);
$type = intval($data['vtype']);
$status = $data['status'];

$stmt = $conn->prepare("INSERT INTO vehicle (TypeID, PlateNo, Capacity, Status) VALUES (?, ?, ?, ?)");
$stmt->bind_param("isis", $type, $plate, $capacity, $status);
echo json_encode(["success" => $stmt->execute()]);
