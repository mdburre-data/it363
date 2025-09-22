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

$name = trim($_POST['name'] ?? '');
$date = trim($_POST['date'] ?? '');
$section = trim($_POST['section'] ?? '');

//Debug Output
echo "Received name: " . htmlspecialchars($name) . "<br>";
echo "Received date: " . htmlspecialchars($date) . "<br>";
echo "Received section: " . htmlspecialchars($section) . "<br>";

// Basic validation
if ($name === '' || $date === '' || $section === '') {
    die("Error: All fields are required.");
}

// Bind parameters and prepare statement
$stmt = $conn->prepare("INSERT INTO appointments (student_name, appointment_date, section) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $name, $date, $section);

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
