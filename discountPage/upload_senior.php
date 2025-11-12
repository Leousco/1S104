<?php
session_start();
require_once "../config.php";

if (!isset($_SESSION['UserID'])) {
    die("Error: You must be logged in to upload files.");
}

$user_id = $_SESSION['UserID'];

// ✅ Match your form field names
$required = ['idFront', 'idBack', 'proofDocument'];
$missing = [];

foreach ($required as $file) {
    if (!isset($_FILES[$file]) || $_FILES[$file]['error'] != 0) {
        $missing[] = $file;
    }
}

if (!empty($missing)) {
    die("Error: All required documents must be uploaded. Missing: " . implode(", ", $missing));
}

// ✅ Prepare upload directory
$uploadDir = "../uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// ✅ Generate unique file paths
$idFrontPath = $uploadDir . uniqid("front_") . "_" . basename($_FILES['idFront']['name']);
$idBackPath = $uploadDir . uniqid("back_") . "_" . basename($_FILES['idBack']['name']);
$proofDocPath = $uploadDir . uniqid("proof_") . "_" . basename($_FILES['proofDocument']['name']);

// ✅ Move uploaded files
move_uploaded_file($_FILES['idFront']['tmp_name'], $idFrontPath);
move_uploaded_file($_FILES['idBack']['tmp_name'], $idBackPath);
move_uploaded_file($_FILES['proofDocument']['tmp_name'], $proofDocPath);

// ✅ Collect user inputs safely
$fullName = trim($_POST['fullName'] ?? '');
$email = trim($_POST['email'] ?? '');
$notes = trim($_POST['notes'] ?? '');

// ✅ Validate minimal input
if (empty($fullName) || empty($email)) {
    die("Error: Full name and email are required.");
}

// ✅ Insert into main table as Senior category
$stmt = $conn->prepare("
    INSERT INTO discount_applications 
    (UserID, Category, FullName, Email, ID_Front, ID_Back, ProofOfEnrollment, Notes, Status, SubmittedAt)
    VALUES (?, 'Senior', ?, ?, ?, ?, ?, ?, 'Pending', NOW())
");

if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}

$stmt->bind_param(
    "issssss",
    $user_id,
    $fullName,
    $email,
    $idFrontPath,
    $idBackPath,
    $proofDocPath,
    $notes
);

if ($stmt->execute()) {
    header("Location: Senior_Citizen_Verification.php?success=1");
    exit();
} else {
    die("Database Error: " . $stmt->error);
}

$stmt->close();
$conn->close();
?>
