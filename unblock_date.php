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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unblockDate'])) {
    $postedDate = $_POST['unblockDate'];

    // Convert the date to the format YYYY-MM-DD
    $formattedDate = date('Y-m-d', strtotime($postedDate));

    $startTime = "00:00:00"; // Remove Start time
    $endTime = "00:00:00";   // Remove End time

    // echo "Posted: $postedDate<br>Formatted: $formattedDate";


    // Check if the date already exists in the database
    $stmt = $conn->prepare("SELECT * FROM dates WHERE date_description = ?");
    $stmt->bind_param('s', $formattedDate);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows < 1) {
        // If the date doesn't exist, do nothing
        echo "Date not blocked, no need to unblock";
    }else {
        // If the date exists, you can handle it as needed
        $updateStmt = $conn->prepare("SELECT * FROM dates WHERE start_time = ? AND end_time = ? AND date_description = ?");
        $dayOfWeek = date('N', strtotime($formattedDate));
        $updateStmt->bind_param('sss', $startTime, $endTime, $formattedDate);
        $updateStmt->execute();
        $result = $updateStmt->get_result();

        if ($result->num_rows > 0) {
            // If the date exists, unblock it
            $deleteStmt = $conn->prepare("UPDATE dates SET start_time = NULL, end_time = NULL WHERE date_description = ?");
            $deleteStmt->bind_param('s', $formattedDate);
            $deleteStmt->execute();
            $deleteStmt->close();
            echo "Date unblocked successfully.";
        } else {
            echo "Date not blocked, no need to unblock";
        }

        $updateStmt->close();
    }

    $stmt->close();
} else {
    echo "No date provided.";
}

// Close the database connection
$conn->close();
?>