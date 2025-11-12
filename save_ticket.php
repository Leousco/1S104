<?php
// save_ticket.php
header('Content-Type: application/json');
include 'config.php'; // must set $conn (mysqli)

$data = $_POST; // form submitted via fetch form data

$first = $data['firstName'] ?? '';
$last  = $data['lastName'] ?? '';
$email = $data['email'] ?? '';
$vehicle = $data['vehicle'] ?? '';
$route = $data['route'] ?? '';
$date = $data['date'] ?? null;
$schedule = $data['schedule'] ?? null;
$tickets = intval($data['tickets'] ?? 1);
$fare = floatval($data['fare'] ?? 0);
$payment = $data['payment'] ?? '';
$lat = $data['latitude'] ?? null;
$lon = $data['longitude'] ?? null;

// You should create/lookup a passenger record. For simplicity we'll insert a passenger if not exists by email:
$passengerId = null;
$stmt = $conn->prepare("SELECT PassengerID FROM passenger WHERE Email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->bind_result($pid);
    $stmt->fetch();
    $passengerId = $pid;
}
$stmt->close();

if (!$passengerId) {
    $stmt = $conn->prepare("INSERT INTO passenger (Name, PhoneNumber, Email) VALUES (?, ?, ?)");
    $name = trim($first . ' ' . $last);
    $phone = ''; // we don't collect phone here
    $stmt->bind_param("sss", $name, $phone, $email);
    $stmt->execute();
    $passengerId = $stmt->insert_id;
    $stmt->close();
}

// For simplicity we won't map schedule/vehicle to real IDs here. You can adapt to your schema.
// We'll insert a ticket record with minimal data (PassengerID, ScheduleID placeholder, FareAmount, PaymentStatus)
$scheduleID = 5;
$stmt = $conn->prepare("INSERT INTO ticket (PassengerID, ScheduleID, QRCode_RFID, FareAmount, PaymentStatus) VALUES (?, ?, ?, ?, 'PENDING')");
$qrplaceholder = NULL;
$stmt->bind_param("iids", $passengerId, $scheduleID, $qrplaceholder, $fare);
if ($stmt->execute()) {
    $ticket_id = $stmt->insert_id;
    $stmt->close();
    echo json_encode(['success' => true, 'ticket_id' => $ticket_id]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}
$conn->close();
