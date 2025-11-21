<?php
declare(strict_types=1);

// NEEDS TO REQUIRE STUDENT

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/Session_Cookie/auth.php';

// If not authenticated, send them back to homepage
requireAuthOrRedirect(
    $COOKIE_NAME,
    $INACTIVITY,
    '/it363/index.php'
);

// Database connection settings
require __DIR__ . '/config.php';
// Create connection
$conn = new mysqli('localhost', DB_USER, DB_PASS, 'tutoring_center');
if ($conn->connect_error) {
    http_response_code(500);
    echo 'Connection failed: ' . $conn->connect_error;
    exit;
}

// Get appointment ID from POST
$appointmentId = $_POST['appointmentId'] ?? null;
if (!$appointmentId) {
    http_response_code(400);
    echo 'Appointment ID is required';
    exit;
}

$isAdmin = $_SESSION['isAdmin'] ?? false;

if ($isAdmin) {
    // Admin can cancel any appointment
    $sql = "UPDATE appointments SET reason = NULL, email = NULL, is_scheduled = FALSE WHERE id = ?";
} else {
    // Non-admin can only cancel their own appointment
    $email = $_SESSION['email'] ?? null;
    $sql = "UPDATE appointments SET reason = NULL, email = NULL, is_scheduled = FALSE WHERE id = ? AND email = ?";
}

$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo 'Prepare failed: ' . $conn->error;
    exit;
}

if ($isAdmin) {
    $stmt->bind_param('i', $appointmentId);
} else {
    $stmt->bind_param('is', $appointmentId, $email);
}

if (!$stmt->execute()) {
    http_response_code(500);
    echo 'Execution failed: ' . $stmt->error;
    exit;
}

if ($stmt->affected_rows === 0) {
    http_response_code(403);
    echo 'Appointment not found or you do not have permission to cancel it';
    exit;
}

echo 'Appointment cancelled.';

$stmt->close();
$conn->close();
?>