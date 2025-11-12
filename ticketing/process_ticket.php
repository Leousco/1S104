<?php
require_once '../config.php';
session_start();

header('Content-Type: application/json');

// Check login
if (!isset($_SESSION['UserID'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['UserID'];

// Validate schedule_id
$schedule_id = intval($_POST['schedule_id'] ?? 0);
if ($schedule_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid schedule selected']);
    exit;
}

// Validate fare
$fare = floatval($_POST['fare'] ?? 0);
if ($fare <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid fare amount']);
    exit;
}

// Validate destination
$destination = trim($_POST['destination'] ?? '');
if (empty($destination)) {
    echo json_encode(['success' => false, 'error' => 'Missing destination information']);
    exit;
}

// Get user info
$userQuery = $conn->prepare("SELECT FirstName, LastName, balance FROM users WHERE UserID = ?");
$userQuery->bind_param("i", $user_id);
$userQuery->execute();
$userResult = $userQuery->get_result();
$user = $userResult->fetch_assoc();
$userQuery->close();

if (!$user) {
    echo json_encode(['success' => false, 'error' => 'User not found']);
    exit;
}

// Check balance
if ($user['balance'] < $fare) {
    echo json_encode([
        'success' => false, 
        'error' => 'Insufficient wallet balance. You have ₱' . number_format($user['balance'], 2) . ' but need ₱' . number_format($fare, 2)
    ]);
    exit;
}

// Get schedule details for additional info
$scheduleQuery = $conn->prepare("
    SELECT s.DepartureTime, s.ArrivalTime, s.Date, r.StartLocation, r.EndLocation, r.TypeID
    FROM schedule s
    JOIN route r ON s.RouteID = r.RouteID
    WHERE s.ScheduleID = ?
");
$scheduleQuery->bind_param("i", $schedule_id);
$scheduleQuery->execute();
$scheduleResult = $scheduleQuery->get_result();
$schedule = $scheduleResult->fetch_assoc();
$scheduleQuery->close();

if (!$schedule) {
    echo json_encode(['success' => false, 'error' => 'Schedule not found']);
    exit;
}

// Get vehicle type
$vehicleType = $schedule['TypeID'] == 1 ? 'Bus' : 'E-Jeep';

// Begin transaction
$conn->begin_transaction();

try {
    // Deduct fare from user balance
    $newBalance = $user['balance'] - $fare;
    $updateBalance = $conn->prepare("UPDATE users SET balance = ? WHERE UserID = ?");
    $updateBalance->bind_param("di", $newBalance, $user_id);
    
    if (!$updateBalance->execute()) {
        throw new Exception("Failed to update balance");
    }
    $updateBalance->close();

    // Insert ticket
    $insertTicket = $conn->prepare("INSERT INTO ticket (PassengerID, ScheduleID, FareAmount, PaymentStatus) VALUES (?, ?, ?, 'PAID')");
    $insertTicket->bind_param("iid", $user_id, $schedule_id, $fare);
    
    if (!$insertTicket->execute()) {
        throw new Exception("Failed to create ticket");
    }
    
    $ticket_id = $conn->insert_id;
    $insertTicket->close();

    // Generate QR code using online API (no GD library needed)
    $qrData = "TICKET #" . $ticket_id . "\n";
    $qrData .= "Passenger: {$user['FirstName']} {$user['LastName']}\n";
    $qrData .= "Route: {$destination}\n";
    $qrData .= "Date: {$schedule['Date']}\n";
    $qrData .= "Departure: {$schedule['DepartureTime']}\n";
    $qrData .= "Arrival: {$schedule['ArrivalTime']}\n";
    $qrData .= "Vehicle: {$vehicleType}\n";
    $qrData .= "Fare: ₱" . number_format($fare, 2) . "\n";
    $qrData .= "Status: PAID";
    
    // Use online QR code generator
    $encodedData = urlencode($qrData);
    $qrFile = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={$encodedData}";
    
    // Optionally, try to download and save the QR code locally
    $qrDir = "../qrcodes/";
    $localQrFile = "{$qrDir}ticket_{$ticket_id}.png";
    
    if (!is_dir($qrDir)) {
        @mkdir($qrDir, 0777, true);
    }
    
    // Try to save QR code locally
    $qrContent = @file_get_contents($qrFile);
    if ($qrContent !== false && !empty($qrContent)) {
        if (@file_put_contents($localQrFile, $qrContent)) {
            $qrFile = $localQrFile; // Use local file if saved successfully
        }
    }

    // Check if QR_Code column exists in ticket table
    $columnCheck = $conn->query("SHOW COLUMNS FROM ticket LIKE 'QR_Code'");
    $hasQRColumn = ($columnCheck->num_rows > 0);
    
    // Update ticket with QR code path only if column exists
    if ($hasQRColumn && $qrFile) {
        $updateQR = $conn->prepare("UPDATE ticket SET QR_Code = ? WHERE TicketID = ?");
        $updateQR->bind_param("si", $qrFile, $ticket_id);
        $updateQR->execute();
        $updateQR->close();
    }

    // Commit transaction
    $conn->commit();

    // Return success response
    echo json_encode([
        'success' => true,
        'ticket_id' => $ticket_id,
        'fare' => $fare,
        'balance' => $newBalance,
        'qr' => $qrFile,
        'name' => "{$user['FirstName']} {$user['LastName']}",
        'destination' => $destination,
        'departure_time' => $schedule['DepartureTime'],
        'arrival_time' => $schedule['ArrivalTime'],
        'date' => $schedule['Date'],
        'vehicle_type' => $vehicleType
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    error_log("Ticket booking error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'error' => 'Booking failed: ' . $e->getMessage()
    ]);
}
?>