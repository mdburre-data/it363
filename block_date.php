<?php
// Database connection credentials
$host = "localhost:3306";
$user = "root";
$pass = "";
$dbname = "tutoring_center";

// Connect to the database
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the posted date
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['blockDate'])) {
    $postedDate = $_POST['blockDate'];

    // Convert the date to the format YYYY-MM-DD 00:00:00
    $formattedDate = date('Y-m-d 00:00:00', strtotime($postedDate));

    $startTime = "00:00:00"; // Remove Start time
    $endTime = "00:00:00";   // Remove End time

    // Check if the date already exists in the database
    $stmt = $conn->prepare("SELECT * FROM dates WHERE date_description = ?");
    $stmt->bind_param('s', $formattedDate);
    $stmt->execute();
    $result = $stmt->get_result();


    if ($result->num_rows === 0) {
        // If the date doesn't exist, insert it
        $insertStmt = $conn->prepare("INSERT INTO dates (date_description, day_of_week, start_time, end_time) VALUES (?, ?, ?, ?)");
        $dayOfWeek = date('N', strtotime($formattedDate));
        $insertStmt->bind_param('siss', $formattedDate, $dayOfWeek, $startTime, $endTime);
        $insertStmt->execute();
        $insertStmt->close();
        echo "Date added successfully.";
    }else {
        // If the date exists, you can handle it as needed (e.g., notify the user)
        $updateStmt = $conn->prepare("UPDATE dates SET start_time = ?, end_time = ? WHERE date_description = ?");
        $dayOfWeek = date('N', strtotime($formattedDate));
        $updateStmt->bind_param('sss', $startTime, $endTime, $formattedDate);
        $updateStmt->execute();
        $updateStmt->close();

        $updateStmt = $conn->prepare("DELETE FROM appointments WHERE app_date = ? AND is_scheduled = FALSE");
        $dayOfWeek = date('N', strtotime($formattedDate));
        $updateStmt->bind_param('s', $formattedDate);
        $updateStmt->execute();
        $updateStmt->close();

        echo "Date Blocked successfully.";
        $stmt = $conn->prepare("SELECT * FROM appointments WHERE app_date = ?");
        $stmt->bind_param('s', $formattedDate);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            echo"<br>There are remaining scheduled appointments on this date. Please contact the students to reschedule.";
        }
        
    }

    $stmt->close();
} else {
    echo "No date provided.";
}

// Close the database connection
$conn->close();
?>