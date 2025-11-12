<?php
session_start();
include 'config.php';  // DB connection

// Check connection
if (!$conn) {
    die("Database connection failed.");
}

// Ensure user is logged in
if (!isset($_SESSION['UserID'])) {
    die("Unauthorized access.");
}

$userID = $_SESSION['UserID'];
$category = $_POST['category'] ?? '';
$fullName = $_POST['full_name'] ?? null;
$email = $_POST['email'] ?? null;
$school = $_POST['school'] ?? null;
$notes = $_POST['notes'] ?? null;

// Validate category
$validCategories = ['Student', 'PWD', 'Senior', 'Government'];
if (!in_array($category, $validCategories)) {
    die("Invalid category.");
}

// Handle file uploads
$uploadDir = 'uploads/';  // Ensure this folder exists and is writable
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$uploadedFiles = [];
$errors = [];

foreach (['id_front', 'id_back', 'proof'] as $field) {
    if (isset($_FILES[$field]) && $_FILES[$field]['error'] == 0) {
        $fileName = time() . '_' . basename($_FILES[$field]['name']);  // Unique filename
        $targetPath = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES[$field]['tmp_name'], $targetPath)) {
            $uploadedFiles[$field] = $targetPath;
        } else {
            $errors[] = "Failed to upload $field.";
        }
    } else {
        $errors[] = "$field is required.";
    }
}

if (!empty($errors)) {
    die("Upload errors: " . implode(', ', $errors));
}

// Insert into discount_applications
$sql = "INSERT INTO discount_applications (UserID, Category, ID_Front, ID_Back, ProofOfEnrollment, FullName, Email, School, Notes) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("issssssss", $userID, $category, $uploadedFiles['id_front'], $uploadedFiles['id_back'], $uploadedFiles['proof'], $fullName, $email, $school, $notes);

if ($stmt->execute()) {
    echo "Application submitted successfully! It will be reviewed soon.";
    // Optional: Redirect to a success page
    // header("Location: success.php");
} else {
    echo "Error submitting application: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>