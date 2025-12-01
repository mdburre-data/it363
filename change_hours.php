<?php
declare(strict_types=1);
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
require_once __DIR__ . '/Session_Cookie/auth.php';
requireAuthOrRedirect($COOKIE_NAME, $INACTIVITY, '/it363/login.php', true);
require __DIR__ . '/config.php';

$conn = new mysqli('localhost', DB_USER, DB_PASS, 'tutoring_center');
if ($conn->connect_error) { die("Connection failed"); }

$dayOfWeek = trim($_POST['dayOfWeek'] ?? '');
$startTime = trim($_POST['startTime'] ?? '');
$endTime = trim($_POST['endTime'] ?? '');

// Error Redirect back to Settings
if ($dayOfWeek === '' || $startTime === '' || $endTime === '') {
    header("Location: admin_page.php?view=settings&error=All fields are required"); exit;
}
if ($endTime <= $startTime) {
    header("Location: admin_page.php?view=settings&error=End time must be after start time"); exit;
}

$startTime = date("H:i:s", strtotime($startTime));
$endTime = date("H:i:s", strtotime($endTime));

$stmt = $conn->prepare("UPDATE scheduling_hours SET start_time = ?, end_time = ? WHERE day_of_week = ?");
$stmt->bind_param("sss", $startTime, $endTime, $dayOfWeek);

if ($stmt->execute()) {
    // Success Redirect back to Settings
    header("Location: admin_page.php?view=settings&msg=Hours updated successfully");
} else {
    header("Location: admin_page.php?view=settings&error=Database update failed");
}
$stmt->close(); $conn->close();
?>