<?php
session_start();
include("../config.php"); 

// ✅ Admin-only access & Content Type Check
if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== "ADMIN") {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized access.']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields including ID
if (empty($input['id'])) {
    echo json_encode(['success' => false, 'error' => 'Voucher ID is required for deletion.']);
    exit();
}

$id = (int)$input['id'];

try {
    // Prepare the DELETE statement
    $stmt = $conn->prepare("DELETE FROM voucher WHERE VoucherID = ?");

    // Bind parameter and execute
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Voucher not found.']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $stmt->error]);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}

$conn->close();
?>