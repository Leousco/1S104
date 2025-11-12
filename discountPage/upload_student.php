
<?php
session_start();
require_once "../config.php"; 

// Ensure the user is logged in
if (!isset($_SESSION['UserID'])) {
    die("Error: You must be logged in to submit a verification.");
}

$user_id = $_SESSION['UserID'];

// File upload paths
$uploadDir = "../uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Helper to move and rename files safely
function uploadFile($fileInputName, $uploadDir) {
    if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES[$fileInputName]['tmp_name'];
        $originalName = basename($_FILES[$fileInputName]['name']);
        $uniqueName = time() . "_" . preg_replace("/[^A-Za-z0-9_.-]/", "_", $originalName);
        $targetPath = $uploadDir . $uniqueName;
        if (move_uploaded_file($tmpName, $targetPath)) {
            return "uploads/" . $uniqueName; 
        }
    }
    return null;
}

// Upload files to uploads folder
$id_front  = uploadFile("idFront",  $uploadDir);
$id_back   = uploadFile("idBack",   $uploadDir);
$proof_doc = uploadFile("proofOfEnrollment", $uploadDir);

// Gather text fields
$full_name = $_POST['fullName'] ?? '';
$email     = $_POST['email'] ?? '';
$school    = $_POST['school'] ?? '';
$notes     = $_POST['notes'] ?? '';

// Validate required fields
if (!$id_front || !$id_back || !$proof_doc) {
    die("Error: All required documents must be uploaded.");
}

// Insert into database
$stmt = $conn->prepare("INSERT INTO discount_applications 
    (UserID, ID_Front, ID_Back, ProofOfEnrollment, FullName, Email, School, Notes)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isssssss", $user_id, $id_front, $id_back, $proof_doc, $full_name, $email, $school, $notes);

if ($stmt->execute()) {
    echo "<script>alert('Your verification has been submitted successfully!'); 
          window.location.href='student.php';</script>";
} else {
    echo "Database Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
