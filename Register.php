<?php
include 'config.php'; // database connection

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize and validate inputs
    $firstname = trim($_POST['firstname']);
    $lastname  = trim($_POST['lastname']);
    $email     = trim($_POST['email']);
    $phone     = trim($_POST['phone']);
    $password  = $_POST['password'];
    $confirm   = $_POST['confirm_password'];
    $error     = "";

    // Validation
    if (empty($firstname) || empty($lastname) || empty($email) || empty($phone) || empty($password) || empty($confirm)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,14}$/', $password)) {
        $error = "Password must be 8–14 chars, include uppercase, lowercase, number, and special character.";
    } else {
        // Set role and hash password
        $role = "PASSENGER";
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert user
        $stmt = $conn->prepare("
            INSERT INTO users (FirstName, LastName, Email, Phone, PasswordHash, Role)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssssss", $firstname, $lastname, $email, $phone, $hashed_password, $role);

        if ($stmt->execute()) {
            header("Location: login.php?success=1");
            exit;
        } else {
            if ($conn->errno === 1062) {
                $error = "Email already registered.";
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
        $stmt->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Passenger Registration</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="flex items-center justify-center min-h-screen bg-gradient-to-br from-blue-500 to-blue-800">

  <div class="bg-white w-[550px] h-[630px] rounded-2xl shadow-lg p-8 animate-fadeIn">
    <h2 class="text-2xl font-semibold text-center text-blue-700 mb-6">Create an Account</h2>

    <?php if (!empty($error)): ?>
      <p class="text-red-600 text-center text-sm mb-4"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" action="" class="space-y-4">
      <input type="text" name="firstname" placeholder="First Name" required
        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-400 outline-none" />

      <input type="text" name="lastname" placeholder="Last Name" required
        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-400 outline-none" />

      <div>
        <input type="tel" name="phone" placeholder="+639XXXXXXXXX" pattern="^\+?\d{9,15}$" required
          class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-400 outline-none" />
        <p class="text-[11px] text-gray-500 mt-1 text-center">Include country code (optional). Digits only.</p>
      </div>

      <input type="email" name="email" placeholder="Email" required
        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-400 outline-none" />

      <div>
        <input type="password" name="password" placeholder="Password" required
          class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-400 outline-none" />
        <p class="text-[11px] text-gray-500 mt-1 text-center">
          8–14 chars, at least 1 uppercase, 1 lowercase, 1 number, and 1 special character.
        </p>
      </div>

      <input type="password" name="confirm_password" placeholder="Confirm Password" required
        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-400 outline-none" />

      <div class="flex items-center gap-2 text-sm mt-2">
        <input id="agree" type="checkbox" required class="w-4 h-4 accent-blue-500 cursor-pointer" />
        <label for="agree" class="text-gray-700">I agree to the
          <a href="#" class="text-blue-600 hover:underline">terms</a> and privacy policy.
        </label>
      </div>

      <button type="submit"
        class="w-full bg-blue-600 text-white py-3 rounded-lg font-medium hover:bg-blue-700 transition">
        Register
      </button>

      <p class="text-center text-sm text-gray-600 mt-4">
        Already have an account?
        <a href="login.php" class="text-blue-600 hover:underline">Login here</a>.
      </p>
    </form>
  </div>

  <style>
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeIn {
      animation: fadeIn 0.4s ease-in-out;
    }
  </style>

</body>
</html>
