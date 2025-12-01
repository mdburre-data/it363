<?php
declare(strict_types=1);
ob_start();

// ensuring session is active
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', '1');

// load libraries
require __DIR__ . '/Email_Login_Feature/vendor/autoload.php';
require __DIR__ . '/Email_Login_Feature/config.php';
require_once __DIR__ . '/Session_Cookie/auth.php';

use App\DB;
use App\Mailer;

// database connection
$pdo = DB::conn();

// sanitize html output
function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

// get current user email from sesion
function userEmail(): ?string { return $_SESSION['user_email'] ?? null; }

// check if user has admin privileges
function userIsAdmin(PDO $pdo, string $email): ?bool {
    if (empty($email)) return false;
    $st = $pdo->prepare("SELECT isAdmin FROM user WHERE email = :e");
    $st->execute([':e' => $email]);
    $u = $st->fetch();
    return $u ? (bool)$u['isAdmin'] : false;
}

// fetch user details (ie. name, section) from database
function fetchUser(PDO $pdo, string $email): ?array {
    $st = $pdo->prepare("SELECT email, fname, lname, section FROM user WHERE email = :e");
    $st->execute([':e' => $email]);
    $u = $st->fetch();
    return $u ?: null;
}

// check if user is incomplete (missing fname, lname, section)
function profileIncomplete(?array $u): bool {
    return !$u || empty($u['fname']) || empty($u['lname']) || empty($u['section']);
}

$mode = $_GET['mode'] ?? ($_POST['mode'] ?? 'login');

// ==dashboard, then route user to correct portal based on role
if (isset($mode) && $mode === 'dashboard') {
    $email = userEmail();
    
    // if not logged in, kick out to login
    if (!$email) {
        header('Location: login.php');
        exit;
    }

    // check role and redirect to correct portal
    if(userIsAdmin($pdo, $email)){
        ensureAuthCookie($COOKIE_NAME, $INACTIVITY, $email, TRUE);
        header('Location: admin_page.php');
        exit;
    } else {
        ensureAuthCookie($COOKIE_NAME, $INACTIVITY, $email);
        header('Location: student_page.php');
        exit;
    }
}

// main logic switch
$action = $_GET['action'] ?? 'home';
$errors = [];
$messages = [];

// request code
if ($action === 'request_code' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // case-sensitivity fix
    $email = strtolower(trim($_POST['email'] ?? ''));
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } else {
        // create user if they don't exist
        $pdo->prepare("INSERT IGNORE INTO user (email) VALUES (:email)")->execute([':email' => $email]);
        
        // create 6-digit code
        $code = (string)random_int(100000, 999999);
        $hash = password_hash($code, PASSWORD_DEFAULT);
        $expires = (new DateTime('+10 minutes'))->format('Y-m-d H:i:s');
        
        // store code's hash in database
        $pdo->prepare("INSERT INTO login_codes (email, code_hash, expires_at) VALUES (:email, :hash, :exp)")
            ->execute([':email' => $email, ':hash' => $hash, ':exp' => $expires]);
        
        $html = "<p>Your verification code:</p><h2 style='margin:0;font-size:28px;letter-spacing:2px;'>$code</h2><p>This code expires in 10 minutes.</p>";
        
        // email the code to user
        if (Mailer::send($email, 'Your sign-in code', $html)) {
            $_SESSION['pending_email'] = $email;
            $messages[] = 'We sent a 6-digit code to ' . h($email) . '.';
            $action = 'verify';
        } else {
            $errors[] = 'Failed to send email. Please check SMTP settings.';
        }
    }
}

// verify code
if ($action === 'verify_code' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_SESSION['pending_email'] ?? '';
    $code = trim($_POST['code'] ?? '');
    if (!preg_match('/^\d{6}$/', $code)) {
        $errors[] = 'Code must be 6 digits.';
    } else {
        // finds unsued, non-expired codes for this email
        $stmt = $pdo->prepare("SELECT id, code_hash, expires_at, used_at FROM login_codes WHERE email = :email AND used_at IS NULL ORDER BY id DESC LIMIT 5");
        $stmt->execute([':email' => $email]);
        $rows = $stmt->fetchAll();
        $now = new DateTime();
        $matchId = null;

        // checks matches
        foreach ($rows as $row) {
            if (new DateTime($row['expires_at']) < $now) continue;
            if (password_verify($code, $row['code_hash'])) {
                $matchId = (int)$row['id'];
                break;
            }
        }
        if ($matchId) {
            // mark code as used
            $pdo->prepare("UPDATE login_codes SET used_at = :now WHERE id = :id")->execute([':now' => $now->format('Y-m-d H:i:s'), ':id' => $matchId]);
            $pdo->prepare("UPDATE login_codes SET used_at = :now WHERE email = :email AND used_at IS NULL")->execute([':now' => $now->format('Y-m-d H:i:s'), ':email' => $email]);
            
            // log user in
            $_SESSION['user_email'] = $email;
            unset($_SESSION['pending_email']);
            
            // if profile missing info, force setup, else go to portal
            $u = fetchUser($pdo, $email);
            if (profileIncomplete($u)) {
                header('Location: login.php?action=profile');
                exit;
            }
            header('Location: login.php?mode=dashboard');
            exit;
        } else {
            $errors[] = 'Invalid or expired code.';
            $action = 'verify';
        }
    }
}

// save profile
if ($action === 'save_profile' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!userEmail()) { header('Location: login.php'); exit; }
    $fname = trim($_POST['fname'] ?? '');
    $lname = trim($_POST['lname'] ?? '');
    $section = trim($_POST['section'] ?? '');
    if ($fname === '') $errors[] = 'First name is required.';
    if ($lname === '') $errors[] = 'Last name is required.';
    if ($section === '') $errors[] = 'IT168 Section is required.';
    if (!$errors) {
        $pdo->prepare("UPDATE user SET fname = :fn, lname = :ln, section = :sec WHERE email = :e")
            ->execute([':fn' => $fname, ':ln' => $lname, ':sec' => $section, ':e' => userEmail()]);
        header('Location: login.php?mode=dashboard');
        exit;
    } else {
        $action = 'profile';
    }
}

// Logout
if ($action === 'logout') {
    session_destroy();
    header('Location: index.php');
    exit;
}

// determine which view to render
$view_mode = 'login';
if ($action === 'verify') $view_mode = 'verify';
if ($action === 'profile') $view_mode = 'profile';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sign In - IT168 Tutoring</title>
<link rel="stylesheet" href="assets/css/base.css">
<link rel="stylesheet" href="assets/css/components.css">
<link rel="stylesheet" href="assets/css/pages.css">
</head>
<body class="login-body">

<div class="login-content">
    <div class="login-card">
        <div style="text-align: center;">
            <img src="imgs/isublack.png" alt="Illinois State University" class="login-logo">
        </div>

        <?php if ($view_mode === 'login'): ?>
            <form method="post" action="?action=request_code" class="text-left">
              <label>Illinois State Email</label>
              <input type="email" name="email" placeholder="ulid@ilstu.edu" required autofocus>
              
              <button type="submit" class="btn btn-primary" style="width:100%; margin-top: 0rem;">Send Login Code</button>
              
              <p style="font-size:0.85rem; margin-top:1.0rem; text-align: center; color: var(--text-muted);">We will send a one-time verification code to your email.</p>
              
              <div class="mt-4 text-center">
                <a href="index.php" style="font-size:0.9rem; color: var(--text-muted); text-decoration:none;">&larr; Back to Homepage</a>
              </div>
            </form>

        <?php elseif ($view_mode === 'verify'): ?>
            <h2 style="font-size:1.0rem; border:none; margin-bottom:1rem; text-align:center; color: var(--secondary);">Verify Your Identity</h2>
            
            <?php if (!empty($_SESSION['pending_email'])): ?>
                <p style="text-align:center; color: var(--text-muted); margin-bottom: 2rem; font-size: 0.95rem;">
                    Enter the code sent to <br>
                    <strong style="color: var(--text-main); font-size: 1rem;"><?= h($_SESSION['pending_email']) ?></strong>
                </p>
            <?php endif; ?>
            
            <?php foreach ($errors as $e): ?>
                <div class="alert alert-error" style="margin-bottom:1.5rem; text-align:center;"><?= h($e) ?></div>
            <?php endforeach; ?>

            <form method="post" action="?action=verify_code" class="text-left">
              <label style="text-align:left;">6-Digit Code</label>
              <input type="text" name="code" placeholder="• • • • • •" pattern="\d{6}" maxlength="6" required autofocus 
                     style="letter-spacing: 0.5rem; font-size: 1.5rem; text-align:center; padding: 12px;">
              
              <button type="submit" class="btn btn-primary" style="width:100%; margin-top: 0rem;">Verify</button>
              
              <div class="mt-4 text-center">
                <a href="?action=home" style="font-size:0.9rem; color: var(--text-muted); text-decoration:none;">&larr; Back to Login</a>
              </div>
            </form>

        <?php elseif ($view_mode === 'profile'): ?>
            <h2 style="font-size:1.2rem; border:none;">Complete Profile</h2>
            <?php foreach ($errors as $e): ?>
                <div class="alert alert-error"><?= h($e) ?></div>
            <?php endforeach; ?>
            <form method="post" action="?action=save_profile" class="text-left">
              <label>First Name</label>
              <input type="text" name="fname" required>
              <label>Last Name</label>
              <input type="text" name="lname" required>
              <label>IT168 Section</label>
              <input type="text" name="section" placeholder="e.g. 004" required>
              <button type="submit" class="btn btn-primary" style="width:100%; margin-top: 1.0rem;">Save & Continue</button>
            </form>

        <?php endif; ?>
    </div>
</div>

</body>
</html>