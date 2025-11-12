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

// Validate required fields
// 'code' is now expected from the frontend, along with 'discountValue'
if (empty($input['code']) || !isset($input['discountValue'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields (Code or Value).']);
    exit();
}

$code = $input['code'];
// $description is removed
$discountType = 'FIXED'; // ✅ Hardcoded to FIXED
$discountValue = (float)$input['discountValue'];
$validFrom = !empty($input['validFrom']) ? $input['validFrom'] : null;
$validTo = !empty($input['validTo']) ? $input['validTo'] : null;
$status = $input['status'] ?? 'ACTIVE';

try {
    // Prepare the INSERT statement
    // Removed 'Description' column, 'DiscountType' is fixed
    $stmt = $conn->prepare("
        INSERT INTO voucher (Code, DiscountType, DiscountValue, ValidFrom, ValidTo, Status)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    // Bind parameters and execute
    // Bind types: s (Code), s (DiscountType), d (DiscountValue), s (ValidFrom), s (ValidTo), s (Status)
    $stmt->bind_param("ssdsss", $code, $discountType, $discountValue, $validFrom, $validTo, $status);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        // Handle unique constraint violation for Code (if set in DB)
        if ($conn->errno == 1062) { // MySQL error code for Duplicate entry
            echo json_encode(['success' => false, 'error' => 'Voucher code already exists. Try generating a new one.']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database error: ' . $stmt->error]);
        }
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}

$conn->close();
?>