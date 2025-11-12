<?php
// gcash_process.php
include 'config.php';

$ticket = isset($_POST['ticket_id']) ? intval($_POST['ticket_id']) : 0;
$mobile = $_POST['gcash_no'] ?? '';

if (!$ticket) {
    die("Invalid ticket ID");
}

// Check if ticket exists
$check = $conn->prepare("SELECT TicketID FROM ticket WHERE TicketID = ?");
$check->bind_param("i", $ticket);
$check->execute();
$result = $check->get_result();
if ($result->num_rows === 0) {
    die("Ticket not found");
}
$check->close();

// ✅ Update payment status to PAID
$update = $conn->prepare("UPDATE ticket SET PaymentStatus = 'PAID' WHERE TicketID = ?");
$update->bind_param("i", $ticket);
$update->execute();

if ($update->affected_rows > 0) {
    // Optional: Generate QR text for receipt
    $qrText = "TICKET#$ticket|PAID VIA GCASH";
    $qr = $conn->prepare("UPDATE ticket SET QRCode_RFID = ? WHERE TicketID = ?");
    $qr->bind_param("si", $qrText, $ticket);
    $qr->execute();
    $qr->close();

    // Redirect to success page
    header("Location: payment_success.php?ticket=" . $ticket);
    exit();
} else {
    die("⚠️ Failed to update payment status. Check database permissions or TicketID.");
}

$update->close();
$conn->close();
?>
