<?php
declare(strict_types=1);
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
require_once __DIR__ . '/Session_Cookie/auth.php';
requireAuthOrRedirect($COOKIE_NAME, $INACTIVITY, '/it363/login.php', true);
require __DIR__ . '/config.php';

$conn = new mysqli('localhost', DB_USER, DB_PASS, 'tutoring_center');
if ($conn->connect_error) { die("Connection failed"); }

$email = trim($_POST['email'] ?? '');
$fName = trim($_POST['fName'] ?? '');
$lName = trim($_POST['lName'] ?? '');

if ($email === '' || $fName === '' || $lName === '') {
    header("Location: admin_page.php?view=settings&error=All fields are required"); exit;
}

$result = $conn->query("SELECT * FROM user WHERE email = '$email'");
if ($result->num_rows === 0) {
    $stmt = $conn->prepare("INSERT INTO user (email, fName, lName, isAdmin) VALUES (?, ?, ?, TRUE)");
    $stmt->bind_param("sss", $email, $fName, $lName);
    if ($stmt->execute()) {
        header("Location: admin_page.php?view=settings&msg=New admin added successfully");
    } else {
        header("Location: admin_page.php?view=settings&error=Database error");
    }
} else {
    $conn->query("UPDATE user SET isAdmin = TRUE WHERE email = '$email'");
    header("Location: admin_page.php?view=settings&msg=User promoted to Admin");
}

$conn->close();
?>