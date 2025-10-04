<?php

//
// Returns HTML for all inputs (For debugging, will be removed)
//

// Database connection settings
$host = "localhost:3306";
$user = "root";
$pass = "";
$dbname = "tutoring_center";

// Connect
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
    echo "No Database Found. Load Database to continue";
}

// Collect POST data
$email = trim($_POST['email'] ?? '');
$fName = trim($_POST['fName'] ?? '');
$lName = trim($_POST['lName'] ?? '');
$section = trim($_POST['section'] ?? '');

//Debug Output
echo "Received email: " . htmlspecialchars($email) . "<br>";
echo "Received first name: " . htmlspecialchars($fName) . "<br>";
echo "Received last name: " . htmlspecialchars($lName) . "<br>";
echo "Received section: " . htmlspecialchars($section) . "<br>";

// Bind parameters and prepare statement
$stmt = $conn->prepare("INSERT IGNORE INTO user (email, fName, lName, section) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $email, $fName, $lName, $section);

// Execute query
if ($stmt->execute()) {
    echo "User added successfully!";
} else {
    echo "Error: " . $stmt->error;
}

// Close
$stmt->close();
$conn->close();

?>