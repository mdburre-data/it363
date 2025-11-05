<?php

//
// Returns JSON object of all scheduling hours
//

header('Content-Type: application/json');

// Database connection settings
$host = "localhost:3306";
$user = "root";
$pass = "";
$dbname = "tutoring_center";

// Connect
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Connection failed: ' . $conn->connect_error]);
    exit;
}

// Get all scheduling hours
$sql = "SELECT day_of_week, start_time, end_time FROM scheduling_hours";
$res = $conn->query($sql);
if (!$res) {
    http_response_code(500);
    echo json_encode(['error' => 'Query failed: ' . $conn->error]);
    exit;
}

$hours = [];
while ($row = $res->fetch_assoc()) {
    $hours[] = $row;
}
echo json_encode($hours);

// Close
$conn->close();
?>