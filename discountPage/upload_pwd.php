<?php
session_start();
require_once "../config.php";

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['UserID'])) {
    die("Error: You must be logged in to upload documents.");
}

$user_id = (int) $_SESSION['UserID'];

// ✅ Check if all required files are uploaded
if (
    !isset($_FILES['idFront']) || $_FILES['idFront']['error'] !== UPLOAD_ERR_OK ||
    !isset($_FILES['idBack'])  || $_FILES['idBack']['error'] !== UPLOAD_ERR_OK ||
    !isset($_FILES['medicalCert']) || $_FILES['medicalCert']['error'] !== UPLOAD_ERR_OK
) {
    die("Error: All required documents must be uploaded.");
}

// ✅ Define upload folder
$uploadDir = "../uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// ✅ Function to safely save uploaded files
function saveFile($file, $prefix) {
    global $uploadDir;
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = $prefix . "_" . time() . "_" . uniqid() . "." . $ext;
    $filePath = $uploadDir . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        die("Error: Failed to upload " . htmlspecialchars($file['name']));
    }

    return "uploads/" . $fileName; // Relative path for database
}

// ✅ Save uploaded files
$idFrontPath = saveFile($_FILES['idFront'], "pwd_front");
$idBackPath  = saveFile($_FILES['idBack'], "pwd_back");
$medicalCertPath = saveFile($_FILES['medicalCert'], "pwd_medical");

// ✅ Get form inputs
$fullName = trim($_POST['fullName'] ?? '');
$email = trim($_POST['email'] ?? '');
$disabilityType = trim($_POST['disabilityType'] ?? '');
$notes = trim($_POST['notes'] ?? '');

if (empty($fullName) || empty($email) || empty($disabilityType)) {
    die("Error: Please fill in all required fields.");
}

// ✅ Insert into discount_applications table
$query = "INSERT INTO discount_applications 
    (UserID, Category, FullName, Email, ID_Front, ID_Back, ProofOfEnrollment, Notes, Status, SubmittedAt) 
    VALUES (?, 'PWD', ?, ?, ?, ?, ?, ?, 'Pending', NOW())";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die("SQL prepare() failed: " . $conn->error);
}

$stmt->bind_param(
    "issssss",
    $user_id,
    $fullName,
    $email,
    $idFrontPath,
    $idBackPath,
    $medicalCertPath,
    $notes
);

if (!$stmt->execute()) {
    die("Error: Could not save application. " . $stmt->error);
}

$stmt->close();
$conn->close();

// ✅ Instant redirect after successful upload
header("Location: PWD_Verification.php");
exit;
?>
