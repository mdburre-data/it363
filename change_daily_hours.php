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
$changeHoursDate = trim($_POST['changeHoursDate'] ?? '');
$startTime = trim($_POST['updatedStartTime'] ?? '');
$endTime = trim($_POST['updatedEndTime'] ?? '');

//Debug Output
echo "Received change hours date: " . htmlspecialchars($changeHoursDate) . "<br>";
echo "Received start time: " . htmlspecialchars($startTime) . "<br>";
echo "Received end time: " . htmlspecialchars($endTime) . "<br>";

//VALIDATION
// Basic validation
if ($changeHoursDate === '' || $startTime === '' || $endTime === '') {
    die("Error: All fields are required.");
}

if ($endTime <= $startTime) {
    die("Error: End time must be after start time.");
}

// Convert times to a format suitable for database storage
$startTime = date("H:i:s", strtotime($startTime));
$endTime = date("H:i:s", strtotime($endTime));

// Check if the date already exists in the database
    $stmt = $conn->prepare("SELECT * FROM dates WHERE date_description = ?");
    $stmt->bind_param('s', $changeHoursDate);
    $stmt->execute();
    $result = $stmt->get_result();


    if ($result->num_rows === 0) {
        // If the date doesn't exist, insert it
        $insertStmt = $conn->prepare("INSERT INTO dates (date_description, day_of_week, start_time, end_time) VALUES (?, ?, ?, ?)");
        $dayOfWeek = date('N', strtotime($changeHoursDate));
        $insertStmt->bind_param('siss', $changeHoursDate, $dayOfWeek, $startTime, $endTime);
        $insertStmt->execute();
        $insertStmt->close();
        echo "Date added successfully.";
    }else {
        // If the date exists, you can handle it as needed (e.g., notify the user)
       $stmt = $conn->prepare("UPDATE dates SET start_time = ?, end_time = ? WHERE date_description = ?");
       $stmt->bind_param("sss", $startTime, $endTime, $changeHoursDate);
       $stmt->execute();
       $stmt->close();
       echo "Date updated successfully.";
    }

// Close
$stmt->close();
$conn->close();
?>
