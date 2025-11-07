<?php
session_start();

$cookieName = 'auth_token';

// Delete cookie
setcookie($cookieName, '', time() - 3600, '/');

// End session
session_unset();
session_destroy();

// Redirect to login page
header("Location: login.php?logged_out=1");
exit();
?>
