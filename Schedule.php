<?php
session_start();

// ✅ Ensure only admin can access
if (!isset($_SESSION['Role']) || $_SESSION['Role'] !== 'ADMIN') {
    header("Location: login.php");
    exit();
}

// ✅ Database connection (use your existing database name)
$conn = new mysqli("localhost", "root", "", "transportation_management");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ✅ Handle adding new schedule
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_schedule"])) {
    $vehicleID = $_POST["vehicle"];
    $routeID = $_POST["route"];
    $departure = $_POST["departure"];
    $arrival = $_POST["arrival"];
    $date = $_POST["date"];
    $status = $_POST["status"];

    $stmt = $conn->prepare("INSERT INTO schedule (VehicleID, RouteID, DepartureTime, ArrivalTime, Date, Status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissss", $vehicleID, $routeID, $departure, $arrival, $date, $status);
    $stmt->execute();
    $stmt->close();

    echo "<script>alert('✅ Schedule added successfully!'); window.location='Schedule.php';</script>";
    exit();
}

// ✅ Fetch schedules
$schedules = $conn->query("SELECT * FROM schedule ORDER BY Date DESC");

// ✅ Fetch dropdown data
$vehicles = $conn->query("SELECT VehicleID, PlateNumber FROM vehicle");
$routes = $conn->query("SELECT RouteID, RouteName FROM route");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Schedule Management</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background: #f1f2f6;
        margin: 0;
        padding: 0;
    }
    h1 {
        background: #7bed9f;
        color: #2f3542;
        text-align: center;
        padding: 20px;
        margin: 0;
        font-size: 28px;
    }
    .container {
        width: 85%;
        max-width: 900px;
        margin: 30px auto;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.2);
        padding: 20px;
    }
    button {
        background: #2ed573;
        color: white;
        border: none;
        padding: 10px 15px;
        border-radius: 6px;
        cursor: pointer;
    }
    button:hover {
        background: #1eae60;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    th, td {
        border: 1px solid #ddd;
        padding: 10px;
        text-align: center;
    }
    th {
        background: #2f3542;
        color: white;
    }
    #addForm {
        display: none;
        margin-top: 20px;
        background: #f9f9f9;
        padding: 20px;
        border-radius: 8px;
    }
    label {
        display: block;
        margin-top: 10px;
    }
    input, select {
        width: 100%;
        padding: 8px;
        margin-top: 5px;
        border-radius: 4px;
        border: 1px solid #ccc;
    }
</style>
<script>
function toggleForm() {
    const form = document.getElementById('addForm');
    form.style.display = (form.style.display === 'none') ? 'block' : 'none';
}
</script>
</head>
<body>
<h1>Schedule Management</h1>

<div class="container">
    <button onclick="toggleForm()">+ Add New Schedule</button>

    <form id="addForm" method="POST" action="">
        <h3>Add Schedule</h3>
        <label>Vehicle:</label>
        <select name="vehicle" required>
            <option value="">Select Vehicle</option>
            <?php while($v = $vehicles->fetch_assoc()) { ?>
                <option value="<?= $v['VehicleID'] ?>"><?= $v['PlateNumber'] ?></option>
            <?php } ?>
        </select>

        <label>Route:</label>
        <select name="route" required>
            <option value="">Select Route</option>
            <?php while($r = $routes->fetch_assoc()) { ?>
                <option value="<?= $r['RouteID'] ?>"><?= $r['RouteName'] ?></option>
            <?php } ?>
        </select>

        <label>Departure Time:</label>
        <input type="time" name="departure" required>

        <label>Arrival Time:</label>
        <input type="time" name="arrival" required>

        <label>Date:</label>
        <input type="date" name="date" required>

        <label>Status:</label>
        <select name="status" required>
            <option value="Active">Active</option>
            <option value="Cancelled">Cancelled</option>
        </select>

        <br><br>
        <button type="submit" name="add_schedule">Save Schedule</button>
    </form>

    <h3 style="margin-top:30px;">Existing Schedules</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Vehicle</th>
            <th>Route</th>
            <th>Departure</th>
            <th>Arrival</th>
            <th>Date</th>
            <th>Status</th>
        </tr>
        <?php if ($schedules->num_rows > 0): ?>
            <?php while($row = $schedules->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['ScheduleID'] ?></td>
                    <td><?= $row['VehicleID'] ?></td>
                    <td><?= $row['RouteID'] ?></td>
                    <td><?= $row['DepartureTime'] ?></td>
                    <td><?= $row['ArrivalTime'] ?></td>
                    <td><?= $row['Date'] ?></td>
                    <td><?= $row['Status'] ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7">No schedules found</td></tr>
        <?php endif; ?>
    </table>
</div>

</body>
</html>
