<?php
declare(strict_types=1);
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

$COOKIE_NAME = 'auth_token';

$_SESSION = [];
session_unset();
session_destroy();

setcookie($COOKIE_NAME, '', [
    'expires'  => time() - 3600,
    'path'     => '/',
    'secure'   => !empty($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax',
]);

header('Location: ../index.php');
exit;
