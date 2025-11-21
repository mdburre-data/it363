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
    '/it363/index.php',
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

// Get all scheduling hours
$sql = "
    SELECT *
    FROM appointments
    WHERE is_scheduled = TRUE
      AND app_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 14 DAY)
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