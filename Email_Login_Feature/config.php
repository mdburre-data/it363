
<?php
// Copy this to config.php and fill in your values.
date_default_timezone_set('America/Chicago');

// --- App URL ---
// Used in emails for links, e.g., https://yourdomain.com
define('APP_URL', 'http://localhost:8080');

// --- SMTP Settings (PHPMailer) ---
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'ILSTU.TutoringCenter@gmail.com');
define('SMTP_PASS', 'zcta qrol naou uale'); // <— your App Password here (no spaces is fine)
define('SMTP_FROM', 'ILSTU.TutoringCenter@gmail.com');
define('SMTP_FROM_NAME', 'Tutoring Center');
define('SMTP_SECURE', 'tls');

// --- Database DSN ---
// Default: SQLite file in /data/app.db
define('DB_DSN', 'sqlite:' . __DIR__ . '/data/app.db');
define('DB_USER', null);   // unused for SQLite
define('DB_PASS', null);   // unused for SQLite

// If you prefer MySQL, comment the SQLite line above and use something like:
// define('DB_DSN', 'mysql:host=127.0.0.1;dbname=app;charset=utf8mb4');
// define('DB_USER', 'appuser');
// define('DB_PASS', 'apppass');
