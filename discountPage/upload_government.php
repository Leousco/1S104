<?php
session_start();
require_once "../config.php";

if (!isset($_SESSION['UserID'])) {
    die("Error: You must be logged in to submit this form.");
}

$user_id = $_SESSION['UserID'];

// ✅ Get form fields (must match your form inputs in Government_Verification.php)
$full_name = $_POST['fullName'] ?? '';
$email = $_POST['email'] ?? '';
$agency = $_POST['agency'] ?? ''; // stored in column 'School' to match DB schema
$notes = $_POST['notes'] ?? '';

// ✅ Basic validation
if (trim($full_name) === '' || trim($email) === '' || trim($agency) === '') {
    die("Error: Please fill out all required fields.");
}

// ✅ Validate required file uploads
if (
    !isset($_FILES['idFront']) || $_FILES['idFront']['error'] !== UPLOAD_ERR_OK ||
    !isset($_FILES['idBack']) || $_FILES['idBack']['error'] !== UPLOAD_ERR_OK ||
    !isset($_FILES['proofOfEmployment']) || $_FILES['proofOfEmployment']['error'] !== UPLOAD_ERR_OK
) {
    die("Error: Please upload all required documents (Front ID, Back ID, Proof of Employment).");
}

// ✅ Ensure upload folder exists
$upload_dir = "../uploads/";
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// ✅ File upload helper
function uploadFile($fileArr, $upload_dir) {
    $file_name = time() . "_" . preg_replace('/[^A-Za-z0-9._-]/', '_', basename($fileArr['name']));
    $target = $upload_dir . $file_name;
    if (move_uploaded_file($fileArr['tmp_name'], $target)) {
        return "uploads/" . $file_name; // store relative path in DB
    } else {
        return false;
    }
}

// ✅ Upload files
$id_front = uploadFile($_FILES['idFront'], $upload_dir);
$id_back = uploadFile($_FILES['idBack'], $upload_dir);
$proof = uploadFile($_FILES['proofOfEmployment'], $upload_dir);

if (!$id_front || !$id_back || !$proof) {
    die("Error: Failed to save one or more uploaded files.");
}

$query = "INSERT INTO discount_applications 
(UserID, FullName, Email, School, Notes, ID_Front, ID_Back, ProofOfEnrollment, Category, Status, SubmittedAt)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Government', 'Pending', NOW())";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Database prepare failed: " . $conn->error);
}

$stmt->bind_param(
    "isssssss",
    $user_id,
    $full_name,
    $email,
    $agency,
    $notes,
    $id_front,
    $id_back,
    $proof   
);

if ($stmt->execute()) {
    echo "<script>
        alert('✅ Your government employee verification has been submitted successfully!');
        window.location.href = 'Government_Verification.php';
    </script>";
} else {
    echo "Database Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
