<?php

// NEEDS TO REQUIRE STUDENT
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/Session_Cookie/auth.php';

// If not authenticated, send them back to homepage
requireAuthOrRedirect(
    $COOKIE_NAME,
    $INACTIVITY,
    '/it363/index.php'
);

// Database connection settings
require __DIR__ . '/config.php';
// Create connection
$conn = new mysqli('localhost', DB_USER, DB_PASS, 'tutoring_center');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Collect POST data
$email = trim($_POST['email'] ?? '');
$reason = trim($_POST['reason'] ?? '');
$date = trim($_POST['date'] ?? '');
$time_slot = trim($_POST['time_slot'] ?? '');

//Debug Output
// echo "Received email: " . htmlspecialchars($email) . "<br>";
// echo "Received reason: " . htmlspecialchars($reason) . "<br>";
// echo "Received date: " . htmlspecialchars($date) . "<br>";
// echo "Received time slot: " . htmlspecialchars($time_slot) . "<br>";

//VALIDATION
// Basic validation
if ($email === '' || $date === '' || $reason === '' || $time_slot === '') {
    die("Error: All fields are required.");
}

// Check if the time slot is already booked (Like if someone else booked it since we loaded available times)
$result = $conn->query("SELECT is_scheduled FROM appointments WHERE app_date = '$date' AND app_time = '$time_slot'");
if ($result && $row = $result->fetch_assoc()) {
    if ($row['is_scheduled'] == 1) {
        die("Time slot already booked. Please choose another.");
    }
}

//Check if the student already has an appointment at that day
$result = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE app_date = '$date' AND email = '$email'");
if ($result && $row = $result->fetch_assoc()) {
    if ($row['count'] > 0) {
        die("You already have an appointment on this date. Please choose another date.");
    }
}


// Bind parameters and prepare update statement
$stmt = $conn->prepare("UPDATE appointments SET email = ?, reason = ?, is_scheduled = TRUE WHERE app_date = ? AND app_time = ?");
$stmt->bind_param("ssss", $email, $reason, $date, $time_slot);

// Execute query
if ($stmt->execute()) {
    echo "Appointment successfully booked for " . htmlspecialchars($date) . " at " . htmlspecialchars($time_slot) . ".";
} else {
    echo "Error: " . $stmt->error;
}

// Close
$stmt->close();
$conn->close();
?>
