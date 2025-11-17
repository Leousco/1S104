<?php
require_once '../config.php';
require_once 'email_config.php';

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer (adjust path if needed)
require '../vendor/autoload.php'; // If installed via Composer
// OR if you downloaded PHPMailer manually:
// require '../PHPMailer/src/Exception.php';
// require '../PHPMailer/src/PHPMailer.php';
// require '../PHPMailer/src/SMTP.php';

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

// Get user info including email
$userQuery = $conn->prepare("SELECT FirstName, LastName, Email, balance FROM users WHERE UserID = ?");
$userQuery->bind_param("i", $user_id);
$userQuery->execute();
$userResult = $userQuery->get_result();
$user = $userResult->fetch_assoc();
$userQuery->close();

if (!$user) {
    echo json_encode(['success' => false, 'error' => 'User not found']);
    exit;
}

// Validate email
if (empty($user['Email']) || !filter_var($user['Email'], FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Invalid email address in your account']);
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

// Get schedule details
$scheduleQuery = $conn->prepare("
    SELECT s.DepartureTime, s.ArrivalTime, s.DayOfWeek, r.StartLocation, r.EndLocation, v.TypeID
    FROM schedules s
    JOIN route r ON s.RouteID = r.RouteID
    JOIN vehicle v ON s.VehicleID = v.VehicleID
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

    // Generate QR code data
    $qrData = "TICKET #" . $ticket_id . "\n";
    $qrData .= "Passenger: {$user['FirstName']} {$user['LastName']}\n";
    $qrData .= "Route: {$destination}\n";
    $qrData .= "Date: {$schedule['DayOfWeek']}\n";
    $qrData .= "Departure: {$schedule['DepartureTime']}\n";
    $qrData .= "Arrival: {$schedule['ArrivalTime']}\n";
    $qrData .= "Vehicle: {$vehicleType}\n";
    $qrData .= "Fare: ₱" . number_format($fare, 2) . "\n";
    $qrData .= "Status: PAID";
    
    // Use online QR code generator
    $encodedData = urlencode($qrData);
    $qrFile = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={$encodedData}";
    
    // Try to save QR code locally
    $qrDir = "../qrcodes/";
    $localQrFile = "{$qrDir}ticket_{$ticket_id}.png";
    
    if (!is_dir($qrDir)) {
        @mkdir($qrDir, 0777, true);
    }
    
    // Download QR code
    $qrContent = @file_get_contents($qrFile);
    $qrSaved = false;
    if ($qrContent !== false && !empty($qrContent)) {
        if (@file_put_contents($localQrFile, $qrContent)) {
            $qrFile = $localQrFile;
            $qrSaved = true;
        }
    }

    // Update ticket with QR code path
    $columnCheck = $conn->query("SHOW COLUMNS FROM ticket LIKE 'QR_Code'");
    $hasQRColumn = ($columnCheck->num_rows > 0);
    
    if ($hasQRColumn && $qrFile) {
        $updateQR = $conn->prepare("UPDATE ticket SET QR_Code = ? WHERE TicketID = ?");
        $updateQR->bind_param("si", $qrFile, $ticket_id);
        $updateQR->execute();
        $updateQR->close();
    }

    // Commit transaction
    $conn->commit();

    // === SEND EMAIL ===
    $emailSent = false;
    $emailError = '';
    
    try {
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_ENCRYPTION;
        $mail->Port = SMTP_PORT;
        
        // Recipients
        $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        $mail->addAddress($user['Email'], "{$user['FirstName']} {$user['LastName']}");
        $mail->addReplyTo(MAIL_REPLY_TO, MAIL_REPLY_TO_NAME);
        
        // Attach QR code if saved locally
        if ($qrSaved && file_exists($localQrFile)) {
            $mail->addAttachment($localQrFile, "ticket_{$ticket_id}.png");
        }
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = "Your Ticket Confirmation - Ticket #{$ticket_id}";
        
        // Create email body
        $emailBody = getTicketEmailTemplate(
            $ticket_id,
            $user['FirstName'] . ' ' . $user['LastName'],
            $destination,
            $schedule['DayOfWeek'],
            $schedule['DepartureTime'],
            $schedule['ArrivalTime'],
            $vehicleType,
            $fare,
            $qrSaved ? "cid:qrcode" : $qrFile // Use embedded image or URL
        );
        
        $mail->Body = $emailBody;
        $mail->AltBody = strip_tags($emailBody); // Plain text version
        
        // Embed QR code image if saved locally
        if ($qrSaved && file_exists($localQrFile)) {
            $mail->addEmbeddedImage($localQrFile, 'qrcode', "ticket_{$ticket_id}.png");
        }
        
        $mail->send();
        $emailSent = true;
        
    } catch (Exception $e) {
        $emailError = $mail->ErrorInfo;
        error_log("Email sending failed: " . $emailError);
        // Don't fail the whole transaction if email fails
    }

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
        'date' => $schedule['DayOfWeek'],
        'vehicle_type' => $vehicleType,
        'email_sent' => $emailSent,
        'email_error' => $emailError
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



function getTicketEmailTemplate(
    $ticketId, 
    $passengerName, 
    $destination, 
    $date, 
    $departureTime, 
    $arrivalTime, 
    $vehicleType, 
    $fare, 
    $qrCodeSrc
) { 
    
    $fareFormatted = "₱" . number_format($fare, 2);

    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Confirmation</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f5f7fa;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f5f7fa; padding: 20px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(90deg, #2e7d32, #66bb6a); padding: 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 28px;">🎫 Ticket Confirmed!</h1>
                            <p style="color: #ffffff; margin: 10px 0 0 0; opacity: 0.95;">Your booking is successful</p>
                        </td>
                    </tr>
                    
                    <!-- Ticket ID Banner -->
                    <tr>
                        <td style="background-color: #e6f5e6; padding: 20px; text-align: center; border-bottom: 2px solid #2e7d32;">
                            <p style="margin: 0; color: #1b5e20; font-size: 16px; font-weight: bold;">Ticket ID</p>
                            <h2 style="margin: 5px 0 0 0; color: #2e7d32; font-size: 32px;">#{$ticketId}</h2>
                        </td>
                    </tr>
                    
                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 30px;">
                            <p style="color: #333; font-size: 16px; margin: 0 0 20px 0;">Dear <strong>{$passengerName}</strong>,</p>
                            <p style="color: #666; font-size: 14px; line-height: 1.6; margin: 0 0 25px 0;">
                                Thank you for booking with us! Your ticket has been successfully generated and payment has been processed. Below are your travel details:
                            </p>
                            
                            <!-- Ticket Details Table -->
                            <table width="100%" cellpadding="12" cellspacing="0" style="border: 2px solid #e9e9e9; border-radius: 8px; margin-bottom: 25px;">
                                <tr style="background-color: #f9f9f9;">
                                    <td style="color: #666; font-size: 14px; border-bottom: 1px solid #e9e9e9;"><strong>Passenger Name</strong></td>
                                    <td style="color: #333; font-size: 14px; border-bottom: 1px solid #e9e9e9; text-align: right;">{$passengerName}</td>
                                </tr>
                                <tr>
                                    <td style="color: #666; font-size: 14px; border-bottom: 1px solid #e9e9e9;"><strong>Route</strong></td>
                                    <td style="color: #333; font-size: 14px; border-bottom: 1px solid #e9e9e9; text-align: right;">{$destination}</td>
                                </tr>
                                <tr style="background-color: #f9f9f9;">
                                    <td style="color: #666; font-size: 14px; border-bottom: 1px solid #e9e9e9;"><strong>Travel Day</strong></td>
                                    <td style="color: #333; font-size: 14px; border-bottom: 1px solid #e9e9e9; text-align: right;">{$date}</td>
                                </tr>
                                <tr>
                                    <td style="color: #666; font-size: 14px; border-bottom: 1px solid #e9e9e9;"><strong>Departure Time</strong></td>
                                    <td style="color: #333; font-size: 14px; border-bottom: 1px solid #e9e9e9; text-align: right;">{$departureTime}</td>
                                </tr>
                                <tr style="background-color: #f9f9f9;">
                                    <td style="color: #666; font-size: 14px; border-bottom: 1px solid #e9e9e9;"><strong>Arrival Time</strong></td>
                                    <td style="color: #333; font-size: 14px; border-bottom: 1px solid #e9e9e9; text-align: right;">{$arrivalTime}</td>
                                </tr>
                                <tr>
                                    <td style="color: #666; font-size: 14px; border-bottom: 1px solid #e9e9e9;"><strong>Vehicle Type</strong></td>
                                    <td style="color: #333; font-size: 14px; border-bottom: 1px solid #e9e9e9; text-align: right;">{$vehicleType}</td>
                                </tr>
                                <tr style="background-color: #e6f5e6;">
                                    <td style="color: #1b5e20; font-size: 16px; font-weight: bold;"><strong>Total Fare</strong></td>
                                    <td style="color: #2e7d32; font-size: 18px; font-weight: bold; text-align: right;">{$fareFormatted}</td>
                                </tr>
                            </table>
                            
                            <!-- QR Code Section -->
                            <div style="background-color: #f0fdf0; border: 2px solid #2e7d32; border-radius: 12px; padding: 25px; text-align: center; margin-bottom: 25px;">
                                <p style="color: #1b5e20; font-size: 16px; font-weight: bold; margin: 0 0 15px 0;">📱 Your Ticket QR Code</p>
                                <img src="{$qrCodeSrc}" alt="Ticket QR Code" style="max-width: 250px; height: auto; border: 3px solid #2e7d32; border-radius: 8px; padding: 10px; background: white;" />
                                <p style="color: #666; font-size: 13px; margin: 15px 0 0 0; line-height: 1.5;">
                                    Show this QR code to the conductor when boarding.<br>
                                    You can also find this ticket in your dashboard.
                                </p>
                            </div>
                            
                            <!-- Important Notice -->
                            <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                                <p style="color: #856404; font-size: 14px; margin: 0; line-height: 1.6;">
                                    <strong>⚠️ Important:</strong> Please arrive at the boarding location at least 10 minutes before departure time. Your ticket is non-refundable once the journey has started.
                                </p>
                            </div>
                            
                            <p style="color: #666; font-size: 14px; line-height: 1.6; margin: 0;">
                                If you have any questions or need assistance, please contact our support team.
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f9f9f9; padding: 25px; text-align: center; border-top: 2px solid #e9e9e9;">
                            <p style="color: #999; font-size: 13px; margin: 0 0 10px 0;">
                                This is an automated confirmation email. Please do not reply to this message.
                            </p>
                            <p style="color: #999; font-size: 12px; margin: 0;">
                                © 2024 Ticketing System. All rights reserved.
                            </p>
                        </td>
                    </tr>
                    
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
}
?>