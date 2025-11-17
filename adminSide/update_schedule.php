<?php
session_start();
include("../config.php");

if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== "ADMIN") {
    http_response_code(403);
    echo json_encode(["success" => false, "error" => "Unauthorized"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['id'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Missing schedule ID"]);
    exit();
}


$departure = $data['departure'];
$arrival = $data['arrival'];

if (strlen($departure) === 5) $departure .= ":00"; 
if (strlen($arrival) === 5) $arrival .= ":00";     

$stmt = $conn->prepare("
    UPDATE schedules 
    SET VehicleID=?, RouteID=?, DayOfWeek=?, DepartureTime=?, ArrivalTime=?, Status=? 
    WHERE ScheduleID=?
");

$stmt->bind_param(
    "iissssi",
    $data['vehicle'],
    $data['route'],
    $data['date'],
    $departure,
    $arrival,
    $data['status'],
    $data['id']
);

$ok = $stmt->execute();

echo json_encode([
    "success" => $ok,
    "error" => $ok ? null : $stmt->error
]);
