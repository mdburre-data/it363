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
echo "Received name: " . htmlspecialchars($name) . "<br>";
$date = $_POST['date'] ?? '';
echo "Received date: " . htmlspecialchars($date) . "<br>";
$section = $_POST['section'] ?? '';
echo "Received section: " . htmlspecialchars($section) . "<br>";

// Basic validation
if ($name == '' || $date == '' || $section = '') {
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
