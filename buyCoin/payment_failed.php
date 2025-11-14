<?php
session_start();

if (!isset($_SESSION['UserID'])) {
    header("Location: ../login.php");
    exit();
}

$deal_id = $_GET['deal_id'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .failure-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
        }
        .failure-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        .failure-icon i {
            font-size: 40px;
            color: white;
        }
        h1 {
            color: #dc2626;
            margin-bottom: 10px;
            font-size: 32px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 16px;
        }
        .info-box {
            background: #fef2f2;
            border-left: 4px solid #ef4444;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
            text-align: left;
            color: #991b1b;
        }
        .btn-group {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-block;
            padding: 14px 30px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: transform 0.2s;
        }
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }
        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="failure-container">
        <div class="failure-icon">
            <i class="fas fa-times"></i>
        </div>
        <h1>Payment Failed</h1>
        <p class="subtitle">Your transaction could not be completed</p>

        <div class="info-box">
            <strong>What happened?</strong><br>
            The payment was cancelled or failed to process. No charges have been made to your account.
        </div>

        <div class="btn-group">
            <?php if ($deal_id): ?>
                <a href="process_payment.php?deal_id=<?= $deal_id ?>" class="btn btn-primary">
                    <i class="fas fa-redo"></i> Try Again
                </a>
            <?php endif; ?>
            <a href="buy_coins.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Store
            </a>
            <a href="../passenger_dashboard.php" class="btn btn-secondary">
                <i class="fas fa-home"></i> Dashboard
            </a>
        </div>
    </div>
</body>
</html>