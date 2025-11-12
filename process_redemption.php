<?php
session_start();
// Include the database connection file.
include("config.php"); 

header('Content-Type: application/json');

// 1. Validate User Session and Role (PASSENGER)
if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== "PASSENGER") {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized access.']);
    exit();
}

$userID = $_SESSION['UserID'];
$input = json_decode(file_get_contents('php://input'), true);

if (empty($input['code'])) {
    echo json_encode(['success' => false, 'error' => 'Voucher code is missing.']);
    exit();
}

$voucherCode = strtoupper(trim($input['code']));
$currentDate = date('Y-m-d');

try {
    // Start Transaction (Using SQL command for compatibility)
    $conn->query("START TRANSACTION"); 

    // 2. Find and Validate Voucher
    $stmt = $conn->prepare("
        SELECT VoucherID, DiscountValue
        FROM voucher
        WHERE Code = ? 
        AND Status = 'ACTIVE' 
        AND (ValidFrom IS NULL OR ValidFrom <= ?)
        AND (ValidTo IS NULL OR ValidTo >= ?)
    ");
    $stmt->bind_param("sss", $voucherCode, $currentDate, $currentDate);
    $stmt->execute();
    $result = $stmt->get_result();
    $voucher = $result->fetch_assoc();
    $stmt->close();

    if (!$voucher) {
        $conn->query("ROLLBACK");
        echo json_encode(['success' => false, 'error' => 'Invalid, expired, or already used voucher code.']);
        exit();
    }
    
    $voucherID = $voucher['VoucherID'];
    $discountValue = $voucher['DiscountValue'];

    // 3. Find the PassengerID associated with the UserID
    $stmt = $conn->prepare("SELECT PassengerID, u.Email FROM passenger p JOIN users u ON p.Email = u.Email WHERE u.UserID = ?");
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $passengerResult = $stmt->get_result();
    $passenger = $passengerResult->fetch_assoc();
    $stmt->close();

    // *** START AUTOMATIC PASSENGER RECORD CREATION (FIX) ***
    if (!$passenger) {
        // Fetch the user's email first
        $stmt = $conn->prepare("SELECT Email FROM users WHERE UserID = ?");
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $userResult = $stmt->get_result();
        $user = $userResult->fetch_assoc();
        $stmt->close();

        if (!$user) {
            $conn->query("ROLLBACK");
            echo json_encode(['success' => false, 'error' => 'User record not found in users table.']);
            exit();
        }

        // Auto-create the passenger record using the minimal required column: Email
        $stmt = $conn->prepare("INSERT INTO passenger (Email) VALUES (?)");
        $stmt->bind_param("s", $user['Email']);
        if (!$stmt->execute()) {
            $conn->query("ROLLBACK");
            echo json_encode(['success' => false, 'error' => 'Failed to automatically create passenger record.']);
            exit();
        }
        $stmt->close();

        // Refetch the PassengerID using the same join query (it should now succeed)
        $stmt = $conn->prepare("SELECT PassengerID FROM passenger p JOIN users u ON p.Email = u.Email WHERE u.UserID = ?");
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $passengerResult = $stmt->get_result();
        $passenger = $passengerResult->fetch_assoc();
        $stmt->close();
        
        if (!$passenger) {
             $conn->query("ROLLBACK");
             echo json_encode(['success' => false, 'error' => 'Failed to find PassengerID even after auto-creation.']);
             exit();
        }
    }
    // *** END AUTOMATIC FIX ***

    $passengerID = $passenger['PassengerID'];

    // 4. Update Voucher Status to 'USED' (Voucher can't be redeemed again)
    $stmt = $conn->prepare("UPDATE voucher SET Status = 'USED' WHERE VoucherID = ?");
    $stmt->bind_param("i", $voucherID);
    if (!$stmt->execute()) {
        $conn->query("ROLLBACK");
        echo json_encode(['success' => false, 'error' => 'Failed to update voucher status: ' . $stmt->error]);
        exit();
    }
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM voucher WHERE VoucherID = ?");
    $stmt->bind_param("i", $voucherID);
    if (!$stmt->execute()) {
        $conn->query("ROLLBACK");
        echo json_encode(['success' => false, 'error' => 'Failed to delete voucher: ' . $stmt->error]);
        exit();
    }
    $stmt->close();
    
    // 6. Update User Balance (Using the correct column name 'Balance')
    $stmt = $conn->prepare("UPDATE users SET Balance = Balance + ? WHERE UserID = ?");
    $stmt->bind_param("di", $discountValue, $userID);
    if (!$stmt->execute()) {
        $conn->query("ROLLBACK");
        echo json_encode(['success' => false, 'error' => 'Failed to update user balance: ' . $stmt->error]);
        exit();
    }
    $stmt->close();
    
    // Commit Transaction
    $conn->query("COMMIT");
    echo json_encode([
        'success' => true, 
        'message' => 'Voucher redeemed successfully! Balance updated.', 
        'discount' => $discountValue
    ]);

} catch (Exception $e) {
    // Rollback
    $conn->query("ROLLBACK");
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}

$conn->close();
?>