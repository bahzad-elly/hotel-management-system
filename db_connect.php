<?php
// db_connect.php

$servername = "localhost";
$username = "root";
$password = ""; 
$dbname = "hotel_management_system";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Optional: Un-comment the line below to test the connection initially
// echo "Connected successfully"; 
?>