<?php
require_once '../config.php';
$data = json_decode(file_get_contents("php://input"), true);
if (!$data || !isset($data['id'])) { echo json_encode(["error" => "Missing ID"]); exit; }

$id = intval($data['id']);
$plate = $data['plate'];
$capacity = intval($data['capacity']);
$type = intval($data['vtype']);
$status = $data['status'];

$stmt = $conn->prepare("UPDATE vehicle SET TypeID=?, PlateNo=?, Capacity=?, Status=? WHERE VehicleID=?");
$stmt->bind_param("isisi", $type, $plate, $capacity, $status, $id);
echo json_encode(["success" => $stmt->execute()]);
