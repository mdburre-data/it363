<?php

//
// Returns JSON object of all upcoming appointments
//

// NEEDS TO REQUIRE ADMIN  
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/Session_Cookie/auth.php';

// If not authenticated, send them back to homepage
requireAuthOrRedirect(
    $COOKIE_NAME,
    $INACTIVITY,
    '/it363/login.php',
    true
);


header('Content-Type: application/json');

// Database connection settings
require __DIR__ . '/config.php';
// Create connection
$conn = new mysqli('localhost', DB_USER, DB_PASS, 'tutoring_center');

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Connection failed: ' . $conn->connect_error]);
    exit;
}

// Get all appointments joined with user data to show Name/Section
$sql = "
    SELECT a.*, u.fName, u.lName, u.section
    FROM appointments a
    JOIN user u ON a.email = u.email
    WHERE a.is_scheduled = TRUE
      AND a.app_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 14 DAY)
    ORDER BY a.app_date ASC, a.app_time ASC
";

$res = $conn->query($sql);
if (!$res) {
    http_response_code(500);
    echo json_encode(['error' => 'Query failed: ' . $conn->error]);
    exit;
}

$appointments = [];
while ($row = $res->fetch_assoc()) {
    $appointments[] = $row;
}
echo json_encode($appointments);

// Close
$conn->close();
?>