<?php
header('Content-Type: application/json');
// Database connection settings
$host = "localhost:3306";
$user = "root";
$pass = "";
$dbname = "tutoring_center";

// Connect
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => 'Connection failed: ' . $conn->connect_error]);
    exit;
}

// Get the date from the request (form sends POST)
$date = $_POST['date'] ?? '';

// Validate the date format (HTML date input uses Y-m-d)
if (!DateTime::createFromFormat('Y-m-d', $date) || DateTime::createFromFormat('Y-m-d', $date)->format('Y-m-d') !== $date) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['error' => 'Invalid date format. Use Y-m-d']);
    exit;
}

// Determine day of week for scheduling_hours lookup
$dayOfWeek = (int) date('N', strtotime($date)); // 1 (Monday) to 7 (Sunday)

// Fetch scheduling center hours using mysqli (minimal: start_time and end_time)
$stmt = $conn->prepare("SELECT start_time, end_time FROM scheduling_hours WHERE day_of_week = ? LIMIT 1");
$stmt->bind_param('i', $dayOfWeek);
$stmt->execute();

$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['error' => 'No scheduling hours found for this date']);
    exit;
}
$row = $result->fetch_assoc();
$start_time = $row['start_time'];
$end_time   = $row['end_time'];

$stmt->close();

// Divide Hours into 30-minute slots
$startDateTime = new DateTime($date . ' ' . $start_time);
$endDateTime   = new DateTime($date . ' ' . $end_time);

$dateTimes = new DatePeriod(
    $startDateTime,
    new DateInterval('PT30M'),
    $endDateTime
);


$appointmentTimes = [];

foreach ($dateTimes as $dt) {
    $slotTime = $dt->format("H:i:s");
   $result = $conn->query("SELECT COUNT(*) AS count 
                        FROM appointments 
                        WHERE appointment_date = '$date' 
                        AND appointment_time = '$slotTime'");
        if (!$result) {
         header('Content-Type: application/json');
         echo json_encode(['error' => 'Query failed: ' . $conn->error]);
         exit;
        }
    $row = $result->fetch_assoc();
    if ((int)$row['count'] === 0) {
        $appointmentTimes[] = $dt->format("H:i");
    }
}

$conn->close();

// Return the start and end time for the requested date
header('Content-Type: application/json');
echo json_encode(['start_time' => $start_time, 'end_time' => $end_time, 'available_slots' => $appointmentTimes]);
?>