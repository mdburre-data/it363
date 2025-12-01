<?php
declare(strict_types=1);
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
require_once __DIR__ . '/Session_Cookie/auth.php';
requireAuthOrRedirect($COOKIE_NAME, $INACTIVITY, '/it363/login.php', true);
require __DIR__ . '/config.php';

$conn = new mysqli('localhost', DB_USER, DB_PASS, 'tutoring_center');
if ($conn->connect_error) { die("Connection failed"); }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unblockDate'])) {
    $postedDate = $_POST['unblockDate'];
    $formattedDate = date('Y-m-d', strtotime($postedDate));

    $stmt = $conn->prepare("SELECT * FROM dates WHERE date_description = ?");
    $stmt->bind_param('s', $formattedDate);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $deleteStmt = $conn->prepare("UPDATE dates SET start_time = NULL, end_time = NULL WHERE date_description = ?");
        $deleteStmt->bind_param('s', $formattedDate);
        $deleteStmt->execute();
        $deleteStmt->close();
        header("Location: admin_page.php?view=settings&msg=Date unblocked successfully");
    } else {
        header("Location: admin_page.php?view=settings&error=Date was not blocked");
    }
    $stmt->close();
} else {
    header("Location: admin_page.php?view=settings&error=No date provided");
}
$conn->close();
?>