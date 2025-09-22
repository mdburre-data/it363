<?php
// Database connection settings
$host = "localhost:3306";
$user = "root";
$pass = "";

// Create connection
$conn = new mysqli($host, $user, $pass);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Drop database if exists
$sql = "DROP DATABASE tutoring_center;";
if ($conn->query($sql) === TRUE) {
    echo "Database Dropped Successful";
} else {
    die("Failed to drop database: " . $conn->error);
}

$conn->close();
?>
