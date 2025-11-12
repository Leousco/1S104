<?php
session_start();
if (!isset($_SESSION['Role']) || $_SESSION['Role'] !== "DRIVER") {
    header("Location: login.php?error=unauthorized");
    exit();
}

// DB Connection
$host = "localhost";
$user = "root";
$pass = "";
$db   = "transportation_management";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ✅ FIXED: Use LEFT JOINs to include tickets even without linked data
$sql = "
    SELECT 
        t.TicketID,
        p.Name AS PassengerName,
        r.StartLocation,
        r.EndLocation,
        s.Date,
        s.DepartureTime,
        v.PlateNo,
        vt.TypeName,
        t.FareAmount,
        t.PaymentStatus
    FROM ticket t
    LEFT JOIN passenger p ON t.PassengerID = p.PassengerID
    LEFT JOIN schedule s ON t.ScheduleID = s.ScheduleID
    LEFT JOIN route r ON s.RouteID = r.RouteID
    LEFT JOIN vehicle v ON s.VehicleID = v.VehicleID
    LEFT JOIN vehicletype vt ON v.TypeID = vt.TypeID
    ORDER BY t.TicketID DESC
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Driver Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8f9f8;
            margin: 0;
        }
        .header {
            background: #4a7c59;
            color: white;
            padding: 15px;
            text-align: center;
            font-size: 24px;
        }
        .container {
            margin: 20px auto;
            width: 95%;
            background: white;
            border: 2px solid #4a7c59;
            border-radius: 8px;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
        }
        th {
            background: #4a7c59;
            color: white;
        }
        tr:nth-child(even) { background: #f2f2f2; }
        .PAID { color: green; font-weight: bold; }
        .PENDING { color: red; font-weight: bold; }
    </style>
    <meta http-equiv="refresh" content="10">
</head>
<body>
    <div class="header">Driver Dashboard</div>
    <div class="container">
        <h2>Ticket Status</h2>
        <table>
            <tr>
                <th>Ticket ID</th>
                <th>Passenger</th>
                <th>Vehicle</th>
                <th>Route</th>
                <th>Date</th>
                <th>Departure</th>
                <th>Fare</th>
                <th>Status</th>
            </tr>
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['TicketID']}</td>
                            <td>" . ($row['PassengerName'] ?? '-') . "</td>
                            <td>" . ($row['TypeName'] ?? '-') . " (" . ($row['PlateNo'] ?? '-') . ")</td>
                            <td>" . ($row['StartLocation'] ?? '-') . " → " . ($row['EndLocation'] ?? '-') . "</td>
                            <td>" . ($row['Date'] ?? '-') . "</td>
                            <td>" . ($row['DepartureTime'] ?? '-') . "</td>
                            <td>₱" . number_format($row['FareAmount'], 2) . "</td>
                            <td class='{$row['PaymentStatus']}'>{$row['PaymentStatus']}</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='8'>No tickets found</td></tr>";
            }
            ?>
        </table>
    </div>
</body>
</html>
<?php
$conn->close();
?>
