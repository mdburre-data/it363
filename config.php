<?php
date_default_timezone_set('America/Chicago');

// --- App URL ---
define('APP_URL', 'http://localhost/IT363');

// --- SMTP Settings (PHPMailer) ---
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'ILSTU.TutoringCenter@gmail.com');
define('SMTP_PASS', 'zcta qrol naou uale'); 
define('SMTP_FROM', 'ILSTU.TutoringCenter@gmail.com');
define('SMTP_FROM_NAME', 'Tutoring Center');
define('SMTP_SECURE', 'tls');

// --- MySQL Database Settings ---
define('DB_DSN', 'mysql:host=localhost:3306;dbname=tutoring_center;charset=utf8mb4');
define('DB_USER', 'root');
define('DB_PASS', '');