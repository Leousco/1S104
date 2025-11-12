```php
<?php
// payment_success.php
include 'config.php'; // must define $conn (mysqli)

$ticket_id = $_GET['ticket'] ?? null;

if (!$ticket_id) {
    echo "❌ Invalid request — no ticket ID provided.";
    exit;
}

// Update ticket status to PAID
$stmt = $conn->prepare("UPDATE ticket SET PaymentStatus = 'PAID' WHERE TicketID = ?");
$stmt->bind_param("i", $ticket_id);

if ($stmt->execute()) {
    echo "<h2 style='text-align:center; color:green; margin-top:50px;'>✅ Payment Successful!</h2>";
    echo "<p style='text-align:center;'>Your ticket (ID: <strong>$ticket_id</strong>) is now confirmed and marked as <strong>PAID</strong>.</p>";
    echo "<div style='text-align:center; margin-top:30px;'>
            <a href='buyticket.php' style='background:#4CAF50; color:white; padding:10px 20px; border-radius:8px; text-decoration:none;'>Book Another Ticket</a>
            <a href='index.php' style='background:#2196F3; color:white; padding:10px 20px; border-radius:8px; text-decoration:none; margin-left:10px;'>Go Home</a>
          </div>";
} else {
    echo "❌ Database error: " . htmlspecialchars($conn->error);
}

$stmt->close();
$conn->close();
?>
```
