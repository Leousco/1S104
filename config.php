<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "transportation_management";
$port = 3306;  
$conn = new mysqli($servername, $username, $password, $database, $port);

if ($conn->connect_error) {
  
    error_log("Database connection failed: " . $conn->connect_error);
    
    $conn = null;
    
} else {

}

?>