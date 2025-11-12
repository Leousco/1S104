<?php
include("../config.php");
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$id = intval($data['id']);
$start = trim($data['start'] ?? '');
$end = trim($data['end'] ?? '');
$lat = $data['lat'] ?? null;
$lon = $data['lon'] ?? null;
$status = $data['status'] ?? 'LIGHT';

if ($id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid ID']);
    exit;
}

$stmt = $conn->prepare("UPDATE route SET StartLocation=?, EndLocation=?, Latitude=?, Longitude=?, traffic_status=? WHERE RouteID=?");
$stmt->bind_param("ssddsi", $start, $end, $lat, $lon, $status, $id);

echo json_encode(['success' => $stmt->execute()]);
$stmt->close();
$conn->close();
?>
