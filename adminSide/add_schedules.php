<?php
session_start();
include("../config.php");

if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== "ADMIN") {
    http_response_code(403);
    echo json_encode(["success" => false, "error" => "Unauthorized"]);
    exit();
}

// ✅ Use $_POST instead of JSON
if (
    !isset($_POST['vehicle_id'], $_POST['route_id'], $_POST['date'], 
            $_POST['departure_time'], $_POST['arrival_time'], $_POST['status'])
) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Invalid data"]);
    exit();
}

// Ensure proper format
$departure = $_POST['departure_time'];
$arrival = $_POST['arrival_time'];

if (strlen($departure) === 5) $departure .= ":00"; // "HH:MM" → "HH:MM:00"
if (strlen($arrival) === 5) $arrival .= ":00";

$stmt = $conn->prepare("
    INSERT INTO schedules (VehicleID, RouteID, DayOfWeek, DepartureTime, ArrivalTime, Status)
    VALUES (?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "iissss",
    $_POST['vehicle_id'],
    $_POST['route_id'],
    $_POST['date'],
    $departure,
    $arrival,
    $_POST['status']
);

$ok = $stmt->execute();

echo json_encode([
    "success" => $ok,
    "id" => $ok ? $conn->insert_id : null,
    "error" => $ok ? null : $stmt->error
]);
