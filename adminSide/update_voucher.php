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
// We only need the ID and the discount value
if (empty($input['id']) || !isset($input['discountValue'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields (ID or Value).']);
    exit();
}

$id = (int)$input['id'];
$discountValue = (float)$input['discountValue'];
$validFrom = !empty($input['validFrom']) ? $input['validFrom'] : null;
$validTo = !empty($input['validTo']) ? $input['validTo'] : null;
$status = $input['status'] ?? 'ACTIVE';

// Note: We do NOT update the 'Code' field, as it should be permanent.
// 'Description' has been removed.
// 'DiscountType' is fixed and does not need updating.

try {
    // Prepare the UPDATE statement
    // Only update the fields that are editable in the form.
    $stmt = $conn->prepare("
        UPDATE voucher 
        SET DiscountValue = ?, ValidFrom = ?, ValidTo = ?, Status = ?
        WHERE VoucherID = ?
    ");

    // Bind parameters and execute
    // Bind types: d (DiscountValue), s (ValidFrom), s (ValidTo), s (Status), i (VoucherID)
    $stmt->bind_param("dsssi", $discountValue, $validFrom, $validTo, $status, $id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true]);
        } else {
            // This can happen if the user clicks "Save" without changing anything
            echo json_encode(['success' => true, 'message' => 'No changes detected.']);
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