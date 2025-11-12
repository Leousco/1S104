<?php
include("../config.php");
header('Content-Type: application/json');

$data=json_decode(file_get_contents("php://input"),true);
$start=trim($data['start']??'');
$end=trim($data['end']??'');
$lat=$data['lat']??null;
$lon=$data['lon']??null;
$status=$data['status']??'LIGHT';
$typeID=intval($data['typeID']??1);

if($start===''||$end===''){
  echo json_encode(['success'=>false,'error'=>'Missing fields']);exit;
}

$stmt=$conn->prepare("INSERT INTO route (StartLocation,EndLocation,Latitude,Longitude,traffic_status,TypeID)
VALUES (?,?,?,?,?,?)");
$stmt->bind_param("ssddsi",$start,$end,$lat,$lon,$status,$typeID);
echo json_encode(['success'=>$stmt->execute()]);
$stmt->close();$conn->close();
?>
