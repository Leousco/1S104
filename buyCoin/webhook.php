<?php
include("../config.php");

// Get the webhook payload
$payload = file_get_contents("php://input");
$data = json_decode($payload, true);

// Log the webhook for debugging (optional)
file_put_contents('webhook_log.txt', date('Y-m-d H:i:s') . " - " . $payload . "\n", FILE_APPEND);

if (!empty($data['status']) && ($data['status'] === 'PAID' || $data['status'] === 'SETTLED')) {
    $external_id = $data['external_id'];

    // Find the transaction
    $stmt = $conn->prepare("
        SELECT * FROM coin_transactions 
        WHERE PaymentMethod LIKE ? 
        AND Status='PENDING'
        LIMIT 1
    ");
    $like = "%$external_id%";
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $transaction = $stmt->get_result()->fetch_assoc();

    if ($transaction) {
        // Update transaction status
        $stmt = $conn->prepare("UPDATE coin_transactions SET Status='COMPLETED' WHERE TransactionID=?");
        $stmt->bind_param("i", $transaction['TransactionID']);
        $stmt->execute();

        // Add coins to user balance
        $stmt = $conn->prepare("
            UPDATE users 
            SET balance = balance + ? 
            WHERE UserID=?
        ");
        $stmt->bind_param("ii", $transaction['CoinAmount'], $transaction['UserID']);
        $stmt->execute();

        // Optionally send email notification here
    }
}

http_response_code(200);
echo json_encode(['success' => true]);
?>