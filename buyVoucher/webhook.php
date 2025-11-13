<?php
include("../config.php");

$payload = file_get_contents("php://input");
$data = json_decode($payload, true);

if (!empty($data['status']) && $data['status'] === 'PAID') {
    $external_id = $data['external_id'];

    // Update transaction
    $stmt = $conn->prepare("UPDATE transactions SET Status='PAID' WHERE PaymentURL LIKE ?");
    $like = "%$external_id%";
    $stmt->bind_param("s", $like);
    $stmt->execute();

    // Mark voucher as used
    $stmt = $conn->prepare("UPDATE voucher SET Status='Used' WHERE VoucherID=(SELECT VoucherID FROM transactions WHERE PaymentURL LIKE ? LIMIT 1)");
    $stmt->bind_param("s", $like);
    $stmt->execute();

    // Optionally send voucher code by email
    $stmt = $conn->prepare("SELECT UserID, VoucherID FROM transactions WHERE PaymentURL LIKE ? LIMIT 1");
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    $voucher = $conn->query("SELECT Code FROM voucher WHERE VoucherID=" . $res['VoucherID'])->fetch_assoc();
    $user = $conn->query("SELECT Email FROM users WHERE UserID=" . $res['UserID'])->fetch_assoc();
    
    $to = $user['Email'];
    $subject = "Your Voucher Code";
    $message = "Thank you for your purchase! Your voucher code: " . $voucher['Code'];
    mail($to, $subject, $message);
}

http_response_code(200);
?>
