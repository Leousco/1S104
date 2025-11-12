<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = $_POST['firstname'];
    $lastname  = $_POST['lastname'];
    $email     = $_POST['email'];
    $password  = $_POST['password'];

 $role = $_POST['role'];


    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check duplicate email
    $check = $conn->prepare("SELECT UserID FROM users WHERE Email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo "Error: Email already registered!";
        exit();
    }
    $check->close();

    // Insert new admin
    $stmt = $conn->prepare("INSERT INTO users (FirstName, LastName, Email, PasswordHash, Role) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $firstname, $lastname, $email, $hashed_password, $role);

    if ($stmt->execute()) {
        header("Location: login.php?success=1");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
$conn->close();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin & Driver Registration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #2196F3, #1565C0);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .form-container {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            width: 350px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.2);
            animation: fadeIn 0.6s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-15px); }
            to { opacity: 1; transform: translateY(0); }
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #1565C0;
        }
        input {
            display: block;
            width: 92%;
            padding: 12px;
            margin: 10px auto;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-size: 14px;
            outline: none;
            transition: all 0.3s ease;
        }
        input:focus {
            border-color: #2196F3;
            box-shadow: 0 0 6px rgba(33, 150, 243, 0.4);
        }

        .btn-container{
            display: flex;
            justify-content: center;
            gap: 10px
        }

        button {
            flex: 1;
            width: 100%;
            background: #2196F3;
            color: #fff;
            padding: 14px;
            border: 1px solid #2196F3;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
            transition: background 0.3s;
        }
        button:hover {
            background: #1976D2;
        }

        .back{
            background: white;
            border: 1px solid #2196F3;
            border-radius: 8px;
            color: black;
        }
        .back:hover{
            background: #c0d5ed;
        }

        .message {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
            color: #333;
        }
        .success { color: green; }
        .error { color: red; }

        select{
            display: block;
            width: 100%;
            padding: 12px;
            margin: 10px auto;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-size: 14px;
            outline: none;
            transition: all 0.3s ease;
        }
        
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Register Admin / Driver</h2>
        <?php if (!empty($message)): ?>
            <div class="message <?php echo (strpos($message, '✅') !== false) ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="text" name="firstname" placeholder="First Name" required>
            <input type="text" name="lastname" placeholder="Last Name" required>
            <input type="text" name="phone" placeholder="Phone Number">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>

           
            <select name="role" required>
                <option value="DRIVER">Driver</option>
                <option value="ADMIN">Admin</option>
            </select>

            <div class="btn-container">
                <button type="button" class="back" onclick="window.location.href='login.php'"> « Back to Login </button>
                <button type="submit">Register</button>
            </div>
        </form>
    </div>
</body>
</html>
