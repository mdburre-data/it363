<?php
// Database connection settings
$host = "localhost:3306";
$user = "root";
$pass = "";
$dbname = "tutoring_center";

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Collect POST data
$name = $_POST['name'] ?? '';
$date = $_POST['date'] ?? '';
$class = $_POST['classPrompt'] ?? '';

// Basic validation
if (empty($name) || empty($date) || empty($class)) {
    die("Error: All fields are required.");
}

// Prepare and bind (to prevent SQL injection)
$stmt = $conn->prepare("INSERT INTO appointments (name, date, section) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $name, $date, $class);

// Execute query
if ($stmt->execute()) {
    echo "Data saved successfully!";
} else {
    echo "Error: " . $stmt->error;
}

// Close
$stmt->close();
$conn->close();
?>
