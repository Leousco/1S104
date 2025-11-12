<?php
session_start();
include 'config.php';  // Loads your DB connection

// Check if config.php set up $conn successfully
if (!$conn) {
    die("Database connection failed. Please check your config.php and try again.");
}

// Ensure user is logged in (basic check; enhance with proper auth)
if (!isset($_SESSION['UserID']) || empty($_SESSION['UserID'])) {
    die("You must be logged in to access this page.");
}

$userID = $_SESSION['UserID'];

// Prepare and execute the query safely
$sql = "SELECT HasDiscount FROM users WHERE UserID = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Query preparation failed: " . $conn->error);
}

$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$hasDiscount = $user ? $user['HasDiscount'] : 0;

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discount Application</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
<div class="container mt-5">
    <h2 class="text-center mb-4">Discount Application</h2>

    <?php if ($hasDiscount == 1): ?>
        <div class="alert alert-info text-center">
            <strong>Notice:</strong> You already have a discount and cannot apply for another.
        </div>
    <?php else: ?>
        <div class="card shadow-sm p-4">
            <form action="submit_discount_application.php" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="category" class="form-label">Discount Category</label>
                    <select name="category" id="category" class="form-select" required>
                        <option value="" disabled selected>Select Category</option>
                        <option value="Student">Student</option>
                        <option value="PWD">PWD</option>
                        <option value="Senior">Senior Citizen</option>  <!-- Value changed to 'Senior' to match DB -->
                        <option value="Government">Government Employee</option>  <!-- Value is 'Government' to match DB -->
                    </select>
                </div>

                <!-- Additional fields for Student category (shown/hidden via JS if needed) -->
                <div id="student-fields" style="display: none;">
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" name="full_name" id="full_name" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="school" class="form-label">School</label>
                        <input type="text" name="school" id="school" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea name="notes" id="notes" class="form-control"></textarea>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="id_front" class="form-label">Upload ID (Front)</label>
                    <input type="file" name="id_front" id="id_front" class="form-control" accept="image/*" required>
                </div>
                <div class="mb-3">
                    <label for="id_back" class="form-label">Upload ID (Back)</label>
                    <input type="file" name="id_back" id="id_back" class="form-control" accept="image/*" required>
                </div>
                <div class="mb-3">
                    <label for="proof" class="form-label">Proof Document</label>
                    <input type="file" name="proof" id="proof" class="form-control" accept="image/*" required>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary px-4">Submit Application</button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Show/hide student fields based on category
document.getElementById('category').addEventListener('change', function() {
    const studentFields = document.getElementById('student-fields');
    if (this.value === 'Student') {
        studentFields.style.display = 'block';
    } else {
        studentFields.style.display = 'none';
    }
});
</script>
</body>
</html>

<?php
// Close the connection
if ($conn) {
    $conn->close();
}
?>