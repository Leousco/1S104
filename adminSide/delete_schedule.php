<?php
session_start();
include("../config.php");

if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== "ADMIN") {
    http_response_code(403);
    echo json_encode(["success" => false, "error" => "Unauthorized"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

$id = intval($data['id'] ?? 0);
if (!$id) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Missing schedule ID"]);
    exit();
}

$stmt = $conn->prepare("DELETE FROM schedules WHERE ScheduleID=?");
$stmt->bind_param("i", $id);
$ok = $stmt->execute();

echo json_encode(["success" => $ok]);
