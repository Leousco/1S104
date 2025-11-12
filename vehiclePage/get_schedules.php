<?php
include("../config.php");
header('Content-Type: application/json');

// Get filters
$routeId = isset($_GET['route_id']) ? intval($_GET['route_id']) : 0;
$date = isset($_GET['date']) ? $_GET['date'] : null;
$typeId = isset($_GET['typeID']) ? intval($_GET['typeID']) : 0; // 👈 Add this line

// Build query
$sql = "SELECT 
            s.ScheduleID, 
            s.VehicleID, 
            s.RouteID, 
            s.DepartureTime, 
            s.ArrivalTime, 
            s.Date, 
            s.Status,
            v.PlateNo, 
            vt.TypeName, 
            r.StartLocation, 
            r.EndLocation
        FROM schedule s
        JOIN vehicle v ON s.VehicleID = v.VehicleID
        JOIN vehicletype vt ON v.TypeID = vt.TypeID
        JOIN route r ON s.RouteID = r.RouteID
        WHERE 1=1";

// ✅ Filter by TypeID (e.g., 2 = E-Jeep)
if ($typeId > 0) {
    $sql .= " AND vt.TypeID = $typeId";
}

// ✅ Filter by route if provided
if ($routeId > 0) {
    $sql .= " AND s.RouteID = $routeId";
}

// ✅ Filter by date if provided
if (!empty($date)) {
    $sql .= " AND s.Date = '" . $conn->real_escape_string($date) . "'";
}

$sql .= " ORDER BY s.Date ASC, s.DepartureTime ASC";

$result = $conn->query($sql);

$schedules = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Normalize times
        $dep = $row['DepartureTime'];
        $arr = $row['ArrivalTime'];

        $row['DepartureTime'] = ($dep && $dep !== '0000-00-00 00:00:00') ? date('H:i:s', strtotime($dep)) : null;
        $row['ArrivalTime']   = ($arr && $arr !== '0000-00-00 00:00:00') ? date('H:i:s', strtotime($arr)) : null;

        $schedules[] = $row;
    }
}

echo json_encode(['success' => true, 'schedules' => $schedules], JSON_UNESCAPED_UNICODE);
?>
