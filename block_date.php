<?php
declare(strict_types=1);
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
require_once __DIR__ . '/Session_Cookie/auth.php';
requireAuthOrRedirect($COOKIE_NAME, $INACTIVITY, '/it363/login.php', true);
require __DIR__ . '/config.php';

$conn = new mysqli('localhost', DB_USER, DB_PASS, 'tutoring_center');
if ($conn->connect_error) { die("Connection failed"); }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['blockDate'])) {
    $postedDate = $_POST['blockDate'];
    $formattedDate = date('Y-m-d 00:00:00', strtotime($postedDate));
    $startTime = "00:00:00"; $endTime = "00:00:00";

    $stmt = $conn->prepare("SELECT * FROM dates WHERE date_description = ?");
    $stmt->bind_param('s', $formattedDate);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $insertStmt = $conn->prepare("INSERT INTO dates (date_description, day_of_week, start_time, end_time) VALUES (?, ?, ?, ?)");
        $dayOfWeek = date('N', strtotime($formattedDate));
        $insertStmt->bind_param('siss', $formattedDate, $dayOfWeek, $startTime, $endTime);
        $insertStmt->execute();
        $insertStmt->close();
    } else {
        $updateStmt = $conn->prepare("UPDATE dates SET start_time = ?, end_time = ? WHERE date_description = ?");
        $updateStmt->bind_param('sss', $startTime, $endTime, $formattedDate);
        $updateStmt->execute();
        $updateStmt->close();
    }
    
    $cancelStmt = $conn->prepare("DELETE FROM appointments WHERE app_date = ? AND is_scheduled = FALSE");
    $cancelStmt->bind_param('s', $formattedDate);
    $cancelStmt->execute();
    $cancelStmt->close();
    
    header("Location: admin_page.php?view=settings&msg=Date blocked successfully");
} else {
    header("Location: admin_page.php?view=settings&error=No date provided");
}
$conn->close();
?>