<?php

//
// Returns HTML for all inputs (For debugging, will be removed) and confirmation
//

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
$dayOfWeek = trim($_POST['dayOfWeek'] ?? '');
$startTime = trim($_POST['startTime'] ?? '');
$endTime = trim($_POST['endTime'] ?? '');

//Debug Output
echo "Received day of week: " . htmlspecialchars($dayOfWeek) . "<br>";
echo "Received start time: " . htmlspecialchars($startTime) . "<br>";
echo "Received end time: " . htmlspecialchars($endTime) . "<br>";

//VALIDATION
// Basic validation
if ($dayOfWeek === '' || $startTime === '' || $endTime === '') {
    die("Error: All fields are required.");
}

if ($dayOfWeek < 1 || $dayOfWeek > 7) {
    die("Error: Day of week must be between 1 (Monday) and 7 (Sunday).");
}

if ($endTime <= $startTime) {
    die("Error: End time must be after start time.");
}

// Convert times to a format suitable for database storage
$startTime = date("H:i:s", strtotime($startTime));
$endTime = date("H:i:s", strtotime($endTime));

// Bind parameters and prepare update statement
$stmt = $conn->prepare("UPDATE scheduling_hours SET start_time = ?, end_time = ? WHERE day_of_week = ?");
$stmt->bind_param("sss", $startTime, $endTime, $dayOfWeek);

// Execute query
if ($stmt->execute()) {
    echo "Time updated successfully!";
} else {
    echo "Error: " . $stmt->error;
}

// Close
$stmt->close();
$conn->close();
?>
