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

//
// Create tables and insert example data
//

// Create SCHEDULING_HOURS table if not exists
$sql = "CREATE TABLE IF NOT EXISTS scheduling_hours (
    day_of_week INT NOT NULL PRIMARY KEY,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL
)";
if ($conn->query($sql) === TRUE) {
    echo "Creating Table 'scheduling_hours'<br>";
} else {
    die("Error creating table: " . $conn->error);
}

// Insert 5 example rows only if table is empty
$check = $conn->query("SELECT COUNT(*) AS count FROM scheduling_hours");
$row = $check->fetch_assoc();
if ($row['count'] == 0) {
    $sql = "INSERT INTO scheduling_hours (day_of_week, start_time, end_time) VALUES
        (1, '09:00:00', '12:00:00'),
        (2, '09:00:00', '12:00:00'),
        (3, '09:00:00', '12:00:00'),
        (4, '09:00:00', '12:00:00'),
        (5, '09:00:00', '12:00:00'),
        (6, '00:00:00','00:00:00'),
        (7, '00:00:00','00:00:00')";
    if ($conn->query($sql) === TRUE) {
        echo "Inserted 5 example scheduling hours.<br>";
    } else {
        echo "Error inserting data: " . $conn->error;
    }
} else {
    echo "Scheduling hours table already has data, skipping insert.<br>";
}

// Create DATES table if not exists
$sql = "CREATE TABLE IF NOT EXISTS dates (
    date_description DATE NOT NULL PRIMARY KEY,
    day_of_week INT NOT NULL,
    start_time TIME,
    end_time TIME,
    foreign key (day_of_week) references scheduling_hours(day_of_week)
)";
if ($conn->query($sql) === TRUE) {
    echo "Creating Table 'dates'<br>";
} else {
    die("Error creating table: " . $conn->error);
}


// Insert 5 example rows only if table is empty
$check = $conn->query("SELECT COUNT(*) AS count FROM dates");
$row = $check->fetch_assoc();
if ($row['count'] == 0) {
    $sql = "INSERT INTO dates (date_description, day_of_week) VALUES
        ('2025-10-25', 4),
        ('2025-10-26', 5),
        ('2025-10-27', 6),
        ('2025-10-28', 7),
        ('2025-10-29', 1)";

    if ($conn->query($sql) === TRUE) {
        echo "Inserted 5 example dates.<br>";
    } else {
        echo "Error inserting data: " . $conn->error;
    }
} else {
    echo "Dates table already has data, skipping insert.<br>";
}

// Create USER table if not exists
// TODO: activated should not be default true
$sql = "CREATE TABLE IF NOT EXISTS user (
    email VARCHAR(100) NOT NULL PRIMARY KEY,
    fName VARCHAR(100) NOT NULL,
    lName VARCHAR(100) NOT NULL,
    section VARCHAR(100) NOT NULL,
    activated BOOLEAN DEFAULT TRUE
)";
if ($conn->query($sql) === TRUE) {
    echo "Creating Table 'user'<br>";
} else {
    die("Error creating table: " . $conn->error);
}


// Insert 5 example rows only if table is empty
$check = $conn->query("SELECT COUNT(*) AS count FROM user");
$row = $check->fetch_assoc();
if ($row['count'] == 0) {
    $sql = "INSERT INTO user (email, fName, lName, section) VALUES
        ('witcher@ilstu.com', 'Geralt', 'Rivia', '1'),
        ('tombraider@ilstu.com', 'Lara', 'Croft', '2'),
        ('halflife@ilstu.com', 'Gordon', 'Freeman', '2a'),
        ('doom@ilstu.com', 'Doom', 'Guy', '0'),
        ('halo@ilstu.com', 'Master', 'Chief', '-5')";
    if ($conn->query($sql) === TRUE) {
        echo "Inserted 5 example users.<br>";
    } else {
        echo "Error inserting data: " . $conn->error;
    }
} else {
    echo "User table already has data, skipping insert.<br>";
}

// Create APPOINTMENTS table if not exists
$sql = "CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100),
    app_date DATE NOT NULL,
    app_time TIME NOT NULL,
    is_scheduled BOOLEAN DEFAULT FALSE,
    reason TEXT,
    FOREIGN KEY (email) REFERENCES user(email),
    FOREIGN KEY (app_date) REFERENCES dates(date_description)
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
    $sql = "INSERT INTO appointments (email, app_date, app_time, is_scheduled, reason) VALUES
        ('witcher@ilstu.com', '2025-10-25', '09:00:00', TRUE, 'Potion ingredient gathering tool'),
        ('tombraider@ilstu.com', '2025-10-26', '09:30:00', TRUE, 'Expedition planning'),
        ('halflife@ilstu.com', '2025-10-27', '10:00:00', TRUE, 'Help with resonance cascades'),
        ('doom@ilstu.com', '2025-10-28', '10:30:00', TRUE, 'Demon containment advice'),
        ('halo@ilstu.com', '2025-10-29', '09:00:00', TRUE, 'Strategy session')";
    if ($conn->query($sql) === TRUE) {
        echo "Inserted 5 example appointments.<br>";
    } else {
        echo "Error inserting data: " . $conn->error;
    }
} else {
    echo "Appointments table already has data, skipping insert.<br>";
}

// Close connection
$conn->close();
echo "Setup complete!";
?>