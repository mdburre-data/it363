<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$COOKIE_NAME = 'auth_token';
$INACTIVITY  = 3600; // 1 hour

function endSession(string $cookieName): void {
    setcookie($cookieName, '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    $_SESSION = [];
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_unset();
        session_destroy();
    }
}

// Create/refresh cookie after successful auth
function ensureAuthCookie(string $cookieName, int $inactivity, string $email,bool $isAdmin = false): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (empty($_SESSION['token'])) {
        $_SESSION['token'] = bin2hex(random_bytes(32));
        $_SESSION['email'] = $email;
        if (empty($_SESSION['isAdmin']) && $isAdmin !== null){
            $_SESSION['isAdmin'] = $isAdmin;
        }else if (empty($_SESSION['isAdmin'])){
            $_SESSION['isAdmin'] = false;
        }
    }
    $_SESSION['last_activity'] = time();
    setcookie($cookieName, $_SESSION['token'], [
        'expires'  => time() + $inactivity,
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

// Gate pages that require an existing, valid cookie
// Optional $isAdminRequired parameter to restrict access to admin users only
function requireAuthOrRedirect(string $cookieName, int $inactivity, string $loginUrl, bool $isAdminRequired = false): void {
    if (!isset($_COOKIE[$cookieName]) || !isset($_SESSION['token'])) {
        endSession($cookieName);
        header("Location: {$loginUrl}");
        exit;
    }
    if (hash_equals($_SESSION['token'], (string)$_COOKIE[$cookieName]) === false) {
        endSession($cookieName);
        header("Location: {$loginUrl}");
        exit;
    }
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $inactivity) {
        endSession($cookieName);
        header("Location: {$loginUrl}?expired=1");
        exit;
    }
    if ($isAdminRequired && (empty($_SESSION['isAdmin']) || $_SESSION['isAdmin'] !== true)) {
        endSession($cookieName);
        header("Location: {$loginUrl}");
        exit;
    }
    $_SESSION['last_activity'] = time();
    setcookie($cookieName, $_SESSION['token'], [
        'expires'  => time() + $inactivity,
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}