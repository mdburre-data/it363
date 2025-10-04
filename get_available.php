<?php

//
// Returns JSON object of available appointment slots for a given date
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

// Get and validate date
$date = $_POST['date'] ?? '';
$inputDate = DateTime::createFromFormat('Y-m-d', $date);
error_log("Requested date: $date");
if (!$inputDate || $inputDate->format('Y-m-d') !== $date) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid date format. Use Y-m-d']);
    exit;
}

// Check within two weeks
$today = new DateTime();
$twoWeeks = (new DateTime())->modify('+14 days');
if ($inputDate < $today || $inputDate > $twoWeeks) {
    http_response_code(400);
    echo json_encode(['error' => 'Date must be within the next two weeks.']);
    exit;
}

// Determine day of week
$dayOfWeek = (int)$inputDate->format('N');
$dateStr = $inputDate->format('Y-m-d');


// If date not in DATES table, add it without specific hours. If already there, ignore
$conn->query("INSERT IGNORE INTO dates (date_description, day_of_week) VALUES ('$dateStr', $dayOfWeek)");

// Get scheduling hours for this date and day of week
// THIS USES COALESCE TO GET DEFAULT HOURS IF NONE SET FOR THE DATE
$sql = <<<SQL
SELECT dates.day_of_week,
       COALESCE(dates.start_time, scheduling_hours.start_time) AS start_time,
       COALESCE(dates.end_time, scheduling_hours.end_time) AS end_time
FROM dates
LEFT JOIN scheduling_hours ON dates.day_of_week = scheduling_hours.day_of_week
WHERE dates.date_description = '$dateStr'
SQL;
$res = $conn->query($sql);
if ($res->num_rows === 0) {
    echo json_encode(['error' => 'No scheduling hours for this date']);
    exit;
}
$row = $res->fetch_assoc();
$start_time = $row['start_time'];
$end_time = $row['end_time'];

// Check if slots already exist for this date
$check = $conn->query("SELECT COUNT(*) AS count FROM appointments WHERE app_date = '$date'");
$row = $check->fetch_assoc();
if ($row['count'] == 0) {
    $conn->query("INSERT INTO dates (date_description, day_of_week) VALUES ('$date', $dayOfWeek)");
    // Divide hours into 30 min slots
    $startDateTime = new DateTime($date . ' ' . $start_time);
    $endDateTime   = new DateTime($date . ' ' . $end_time);

    $dateTimes = new DatePeriod(
        $startDateTime,
        new DateInterval('PT30M'),
        $endDateTime
    );

    foreach ($dateTimes as $dt) {
        $slotTime = $dt->format("H:i:s");
        $sql = "INSERT INTO appointments (app_date, app_time) VALUES ('$date', '$slotTime')";
        if (!$conn->query($sql)) {
            error_log("Insert failed: " . $conn->error . " | SQL: $sql");
        } else {
            error_log("Inserted slot: $slotTime");
        }
    }
}

// Now fetch them back to return JSON
$result = $conn->query("SELECT app_time FROM appointments WHERE app_date = '$date' ORDER BY app_time");
$slots = [];
while ($r = $result->fetch_assoc()) {
    $slots[] = $r['app_time'];
}

echo json_encode(['available_slots' => $slots]);


$conn->close();
?>
