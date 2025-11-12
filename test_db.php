<?php
include 'config.php';
if ($conn) {
    echo "Connection successful!";
} else {
    echo "Connection failed. Check logs.";
}
?>
