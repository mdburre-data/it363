<?php

//
// Returns HTML for all inputs (For debugging, will be removed) and confirmation
//

// Database connection settings
$host = "localhost:3306";
$user = "root";
$pass = "";
$dbname = "tutoring_center";

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Collect POST data
$email = trim($_POST['email'] ?? '');
$reason = trim($_POST['reason'] ?? '');
$date = trim($_POST['date'] ?? '');
$time_slot = trim($_POST['time_slot'] ?? '');

//Debug Output
echo "Received email: " . htmlspecialchars($email) . "<br>";
echo "Received reason: " . htmlspecialchars($reason) . "<br>";
echo "Received date: " . htmlspecialchars($date) . "<br>";
echo "Received time slot: " . htmlspecialchars($time_slot) . "<br>";

//VALIDATION
// Basic validation
if ($email === '' || $date === '' || $reason === '' || $time_slot === '') {
    die("Error: All fields are required.");
}

// Check if email exists in user table
$result = $conn->query("SELECT * FROM user WHERE email = '$email'");
if ($result->num_rows === 0) {
    die("Error: Email not found in user database. Please register first.");
}
// Check if user is activated
$user = $result->fetch_assoc();
if (!$user['activated']) {
    die("Error: User is not activated. Please contact the administrator.");
}

// Check if the time slot is already booked (Like if someone else booked it since we loaded available times)
$result = $conn->query("SELECT is_scheduled FROM appointments WHERE appointment_date = '$date' AND appointment_time = '$time_slot'");
if ($result && $row = $result->fetch_assoc()) {
    if ($row['appointment_is_scheduled'] == 1) {
        die("Error: Time slot already booked. Please choose another.");
    }
}


// Bind parameters and prepare update statement
$stmt = $conn->prepare("UPDATE appointments SET email = ?, reason = ?, is_scheduled = TRUE WHERE date = ? AND time = ?");
$stmt->bind_param("ssss", $email, $reason, $date, $time_slot);

// Execute query
if ($stmt->execute()) {
    echo "Appointment created successfully!";
} else {
    echo "Error: " . $stmt->error;
}

// Close
$stmt->close();
$conn->close();
?>
