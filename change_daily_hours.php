<?php
declare(strict_types=1);
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
require_once __DIR__ . '/Session_Cookie/auth.php';
requireAuthOrRedirect($COOKIE_NAME, $INACTIVITY, '/it363/login.php', true);
require __DIR__ . '/config.php';

$conn = new mysqli('localhost', DB_USER, DB_PASS, 'tutoring_center');
if ($conn->connect_error) { die("Connection failed"); }

$changeHoursDate = trim($_POST['changeHoursDate'] ?? '');
$startTime = trim($_POST['updatedStartTime'] ?? '');
$endTime = trim($_POST['updatedEndTime'] ?? '');

if ($changeHoursDate === '' || $startTime === '' || $endTime === '') {
    header("Location: admin_page.php?view=settings&error=All fields are required"); exit;
}

$startTime = date("H:i:s", strtotime($startTime));
$endTime = date("H:i:s", strtotime($endTime));

$stmt = $conn->prepare("SELECT * FROM dates WHERE date_description = ?");
$stmt->bind_param('s', $changeHoursDate);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $insertStmt = $conn->prepare("INSERT INTO dates (date_description, day_of_week, start_time, end_time) VALUES (?, ?, ?, ?)");
    $dayOfWeek = date('N', strtotime($changeHoursDate));
    $insertStmt->bind_param('siss', $changeHoursDate, $dayOfWeek, $startTime, $endTime);
    $insertStmt->execute();
    $insertStmt->close();
} else {
    $updateStmt = $conn->prepare("UPDATE dates SET start_time = ?, end_time = ? WHERE date_description = ?");
    $updateStmt->bind_param('sss', $startTime, $endTime, $changeHoursDate);
    $updateStmt->execute();
    $updateStmt->close();
}

header("Location: admin_page.php?view=settings&msg=Daily override set successfully");
$stmt->close(); $conn->close();
?>