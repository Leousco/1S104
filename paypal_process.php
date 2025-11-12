<?php
include 'config.php';

$ticket = intval($_POST['ticket_id'] ?? 0);
$email = $_POST['paypal_email'] ?? '';

if (!$ticket) {
  http_response_code(400);
  die("Invalid ticket");
}

// Update payment status to PAID
$stmt = $conn->prepare("UPDATE ticket SET PaymentStatus = 'PAID' WHERE TicketID = ?");
$stmt->bind_param("i", $ticket);
$stmt->execute();
$stmt->close();

echo "Payment updated";
