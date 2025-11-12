<?php
session_start();
require_once "../config.php";

if (!isset($_SESSION['Role']) || $_SESSION['Role'] !== "ADMIN") {
    die("unauthorized");
}

if (isset($_POST['application_id'], $_POST['status'])) {
    $app_id = intval($_POST['application_id']);
    $status = $_POST['status'];

    // Sanity check
    if (!in_array($status, ['Approved', 'Rejected'])) {
        die("invalid_status");
    }

    $stmt = $conn->prepare("UPDATE discount_applications SET Status = ?, ReviewedAt = NOW() WHERE ApplicationID = ?");
    $stmt->bind_param("si", $status, $app_id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "db_error";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "missing_data";
}
?>
