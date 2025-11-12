<?php
include("../config.php");
header('Content-Type: application/json');

// ✅ Accept both ?typeID=2 or ?type=2
$typeID = isset($_GET['typeID']) ? intval($_GET['typeID']) :
          (isset($_GET['type']) ? intval($_GET['type']) : 1);

// ✅ Optional search filter
$q = isset($_GET['q']) ? trim($_GET['q']) : "";

// ✅ Build base SQL
$sql = "
    SELECT 
        r.RouteID,
        r.StartLocation,
        r.EndLocation,
        r.Latitude,
        r.Longitude,
        COALESCE(r.traffic_status, 'UNKNOWN') AS traffic_status,
        COALESCE(r.TypeID, 1) AS TypeID
    FROM route r
    WHERE 1=1
";

// ✅ Filter by TypeID (e.g., 2 = E-Jeep)
if ($typeID > 0) {
    $sql .= " AND r.TypeID = $typeID";
}

// ✅ Optional search filter
if ($q !== "") {
    $qEsc = $conn->real_escape_string($q);
    $sql .= " AND (
        r.RouteID LIKE '%$qEsc%' OR
        r.StartLocation LIKE '%$qEsc%' OR
        r.EndLocation LIKE '%$qEsc%'
    )";
}

$sql .= " ORDER BY r.RouteID ASC";

$result = $conn->query($sql);

$routes = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $routes[] = $row;
    }
}

echo json_encode([
    'success' => true,
    'routes' => $routes
], JSON_UNESCAPED_UNICODE);

$conn->close();
?>
