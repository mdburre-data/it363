<?php
session_start();

// Cookie/session config
$cookieName = 'auth_token';
$inactivityLimit = 3600; // 1 hour in seconds

// Function to end session and delete cookie
function endSession($cookieName) {
    // Delete cookie
    setcookie($cookieName, '', time() - 3600, '/');
    // Clear session data
    session_unset();
    session_destroy();
}

// Check if cookie exists
if (!isset($_COOKIE[$cookieName]) || !isset($_SESSION['token'])) {
    endSession($cookieName);
    header("Location: login.php"); // redirect to login page
    exit();
}

// Validate that cookie matches session token
if ($_COOKIE[$cookieName] !== $_SESSION['token']) {
    endSession($cookieName);
    header("Location: login.php");
    exit();
}

// Check inactivity timeout
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $inactivityLimit) {
    // Inactive for over 1 hour
    endSession($cookieName);
    header("Location: login.php?expired=1");
    exit();
}

// Update last activity timestamp and extend cookie expiry
$_SESSION['last_activity'] = time();
setcookie($cookieName, $_SESSION['token'], time() + $inactivityLimit, '/', '', isset($_SERVER['HTTPS']), true);
?>
