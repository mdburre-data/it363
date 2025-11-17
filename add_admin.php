<?php

// NEEDS TO REQUIRE ADMIN
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/Session_Cookie/auth.php';

// If not authenticated, send them back to homepage
requireAuthOrRedirect(
    $COOKIE_NAME,
    $INACTIVITY,
    '/it363/index.php',
    true
);

// Database connection settings
require __DIR__ . '/config.php';
// Create connection
$conn = new mysqli('localhost', DB_USER, DB_PASS, 'tutoring_center');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Collect POST data
$email = trim($_POST['email'] ?? '');
$fName = trim($_POST['fName'] ?? '');
$lName = trim($_POST['lName'] ?? '');

//Debug Output
echo "Received email: " . htmlspecialchars($email) . "<br>";
echo "Received fName: " . htmlspecialchars($fName) . "<br>";
echo "Received lName: " . htmlspecialchars($lName) . "<br>";


//VALIDATION
// Basic validation
if ($email === '' || $fName === '' || $lName === '') {
    die("Error: All fields are required.");
}

// Check if email exists in user table
$result = $conn->query("SELECT * FROM user WHERE email = '$email'");
if ($result->num_rows === 0) {
    $stmt = $conn->prepare("INSERT INTO user (email, fName, lName, isAdmin) VALUES (?, ?, ?, TRUE)");
    $stmt->bind_param("sss", $email, $fName, $lName);
    echo "Adding new admin user...<br>";
    if ($stmt->execute()) {
        echo "New admin user created successfully!";
    } else {
        die("Error creating new admin user: " . $stmt->error);
    }
}else{
    // Promote user to admin
    $updateResult = $conn->query("UPDATE user SET isAdmin = TRUE WHERE email = '$email' AND fName = '$fName' AND lName = '$lName'");
    if ($updateResult) {
        echo "User promoted to admin successfully!";
    } else {
        die("Error promoting user to admin: " . $conn->error);
    }
}

// Close
$stmt->close();
$conn->close();
?>
