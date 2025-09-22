<?php
// Database connection settings 
$host = "localhost:3306";
$user = "root";
$pass = "";

// Create connection
$conn = new mysqli($host, $user, $pass);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS tutoring_center";
if ($conn->query($sql) === TRUE) {
    echo "Creating Database 'tutoring_center'<br>";
} else {
    die("Error creating database: " . $conn->error);
}

// Select database
$conn->select_db("tutoring_center");

// Create appointments table if not exists
$sql = "CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_name VARCHAR(100) NOT NULL,
    appointment_date DATE NOT NULL,
    section VARCHAR(100) NOT NULL
)";
if ($conn->query($sql) === TRUE) {
    echo "Creating Table 'appointments'<br>";
} else {
    die("Error creating table: " . $conn->error);
}

// Insert 5 example rows only if table is empty
$check = $conn->query("SELECT COUNT(*) AS count FROM appointments");
$row = $check->fetch_assoc();
if ($row['count'] == 0) {
    $sql = "INSERT INTO appointments (student_name, appointment_date, section) VALUES
        ('Alice Johnson', '2025-09-25', '1'),
        ('Bob Smith', '2025-09-26', '2'),
        ('Charlie Brown', '2025-09-27', '2a'),
        ('Dana White', '2025-09-28', '0'),
        ('Eli Carter', '2025-09-29', '-5')";
    if ($conn->query($sql) === TRUE) {
        echo "Inserted 5 example appointments.<br>";
    } else {
        echo "Error inserting data: " . $conn->error;
    }
} else {
    echo "Appointments table already has data, skipping insert.<br>";
}

$conn->close();
echo "Setup complete!";
?>