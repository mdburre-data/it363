<?php
declare(strict_types=1);
ob_start();
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config.php';

// Determine mode early (before rendering the page)
$mode = $_GET['mode'] ?? ($_POST['mode'] ?? 'login');

// Bring in cookie helpers
require_once __DIR__ . '/../../Session_Cookie/auth.php';

// If authenticated and landing on dashboard, set cookie then redirect
if ($mode === 'dashboard') {
    ensureAuthCookie($COOKIE_NAME, $INACTIVITY);

    // Redirect to the student page (absolute path from web root is safest)
    header('Location: /it363/student_page.php');
    exit;
}

use App\DB;
use App\Mailer;

$pdo = DB::conn();

// Escape HTML safely
function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

// Get logged in user email
function userEmail(): ?string { return $_SESSION['user_email'] ?? null; }

// Get user info from database
function fetchUser(PDO $pdo, string $email): ?array {
    $st = $pdo->prepare("SELECT email, fname, lname, section FROM user WHERE email = :e");
    $st->execute([':e' => $email]);
    $u = $st->fetch();
    return $u ?: null;
}

// Check if user profile is missing fields
function profileIncomplete(?array $u): bool {
    return !$u || empty($u['fname']) || empty($u['lname']) || empty($u['section']);
}

$action = $_GET['action'] ?? 'home';
$errors = [];
$messages = [];

// Send 6-digit code to user email
if ($action === 'request_code' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } else {
        $pdo->prepare("INSERT IGNORE INTO user (email) VALUES (:email)")
            ->execute([':email' => $email]);

        $code = (string)random_int(100000, 999999);
        $hash = password_hash($code, PASSWORD_DEFAULT);
        $expires = (new DateTime('+10 minutes'))->format('Y-m-d H:i:s');

        $pdo->prepare("INSERT INTO login_codes (email, code_hash, expires_at) VALUES (:email, :hash, :exp)")
            ->execute([':email' => $email, ':hash' => $hash, ':exp' => $expires]);

        $html = "<p>Your verification code:</p>
                 <h2 style='margin:0;font-size:28px;letter-spacing:2px;'>$code</h2>
                 <p>This code expires in 10 minutes.</p>";

        if (Mailer::send($email, 'Your sign-in code', $html)) {
            $_SESSION['pending_email'] = $email;
            $messages[] = 'We sent a 6-digit code to ' . h($email) . '.';
            $action = 'verify';
        } else {
            $errors[] = 'Failed to send email. Please check SMTP settings.';
        }
    }
}

// Verify the 6-digit code
if ($action === 'verify_code' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_SESSION['pending_email'] ?? '';
    $code = trim($_POST['code'] ?? '');

    if (!preg_match('/^\d{6}$/', $code)) {
        $errors[] = 'Code must be 6 digits.';
    } else {
        $stmt = $pdo->prepare("SELECT id, code_hash, expires_at, used_at
                               FROM login_codes
                               WHERE email = :email AND used_at IS NULL
                               ORDER BY id DESC LIMIT 5");
        $stmt->execute([':email' => $email]);
        $rows = $stmt->fetchAll();

        $now = new DateTime();
        $matchId = null;

        foreach ($rows as $row) {
            if (new DateTime($row['expires_at']) < $now) continue;
            if (password_verify($code, $row['code_hash'])) {
                $matchId = (int)$row['id'];
                break;
            }
        }

        if ($matchId) {
            $pdo->prepare("UPDATE login_codes SET used_at = :now WHERE id = :id")
                ->execute([':now' => $now->format('Y-m-d H:i:s'), ':id' => $matchId]);
            $pdo->prepare("UPDATE login_codes SET used_at = :now WHERE email = :email AND used_at IS NULL")
                ->execute([':now' => $now->format('Y-m-d H:i:s'), ':email' => $email]);

            $_SESSION['user_email'] = $email;
            unset($_SESSION['pending_email']);

            $u = fetchUser($pdo, $email);
            if (profileIncomplete($u)) {
                header('Location: ' . APP_URL . '/?action=profile');
                exit;
            }

            header('Location: ' . APP_URL . '/');
            exit;
        } else {
            $errors[] = 'Invalid or expired code.';
            $action = 'verify';
        }
    }
}

// Save profile info
if ($action === 'save_profile' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!userEmail()) { header('Location: ' . APP_URL . '/'); exit; }

    $fname = trim($_POST['fname'] ?? '');
    $lname = trim($_POST['lname'] ?? '');
    $section = trim($_POST['section'] ?? '');

    if ($fname === '') $errors[] = 'First name is required.';
    if ($lname === '') $errors[] = 'Last name is required.';
    if ($section === '') $errors[] = 'IT168 Section is required.';

    if (!$errors) {
        $pdo->prepare("UPDATE user SET fname = :fn, lname = :ln, section = :sec WHERE email = :e")
            ->execute([':fn' => $fname, ':ln' => $lname, ':sec' => $section, ':e' => userEmail()]);
        header('Location: ' . APP_URL . '/');
        exit;
    } else {
        $action = 'profile';
    }
}

// Logout
if ($action === 'logout') {
    session_destroy();
    header('Location: ' . APP_URL . '/');
    exit;
}

// Determine which page to show
$mode = 'login';
if ($action === 'verify') $mode = 'verify';
if ($action === 'profile') $mode = 'profile';
if (userEmail() && !profileIncomplete(fetchUser($pdo, userEmail()))) $mode = 'dashboard';
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Passwordless Sign-In</title>
<style>
:root { --red:#d72626; --black:#0b0b0b; --gray:#2f2f2f; --soft:#f5f5f5; --white:#ffffff; }
* { box-sizing:border-box; }

body {
  margin:0;
  font-family: Arial, Helvetica, sans-serif;
  background: linear-gradient(135deg, #fff, #fff);
  color: var(--white);
  min-height:100vh;
  display:flex; align-items:center; justify-content:center;
  padding:24px;
}

.shell {
  width: min(960px, 96vw);
  background: var(--white);
  color: var(--black);
  overflow: hidden;
  display: grid;
  grid-template-columns: 1fr 1fr;
  border-radius: <?= ($mode === 'dashboard') ? '0' : '0 24px 24px 0'; ?>;
  box-shadow: 0 20px 60px rgba(0,0,0,.35);
}
.single { grid-template-columns: 1fr; max-width:700px; border-radius:24px; }

.shell.square { border-radius: 0 !important; }
.shell.square.single { border-radius: 0 !important; }

.left {
  background: var(--black);
  color: var(--white);
  padding: 48px 25px;
  border-radius: 0;
  position: relative;
}
.left .welcome { font-size: 34px; font-weight: 800; margin: 0 0 8px; }
.left p { opacity:.9; margin:0; }
.isu-logo { position:absolute; bottom:16px; left:16px; width:190px; height:auto; opacity:.95; }

.right { padding:48px 40px; background: var(--soft); }
.right h2 { margin:0 0 20px; font-size:28px; }

.card {
  background:#fff;
  border:1px solid #e9e9e9;
  border-radius:0;
  padding:20px;
  margin:14px 0;
}

.notice {
  background:#f2f2f2;
  border:1px solid #dddddd;
  color:#222;
  padding:12px;
  border-radius:0;
  text-align:center;
  margin-bottom:16px;
}

input {
  width:100%;
  padding:12px 14px;
  border:1px solid #dadada;
  border-radius:0;
  font-size:16px;
  outline:none;
  transition:border .15s ease;
  background:#fff;
}
input:focus { border-color: var(--red); }

button {
  appearance:none;
  border:0;
  background: var(--red);
  color:#fff;
  font-weight:800;
  padding:10px 14px;
  border-radius:8px;
  cursor:pointer;
  letter-spacing:.4px;
  width:100%;
}

a.link { color:#4b3fbf; text-decoration:none; font-weight:700; }
a.link:hover { text-decoration:underline; }

@media (max-width:860px) {
  .shell { grid-template-columns:1fr; border-radius:0; }
  .left { display: <?= ($mode === 'login' || $mode === 'verify') ? 'block' : 'none' ?>; }
}

</style>
</head>
<body>

<?php
require_once __DIR__ . '/../../Session_Cookie/auth.php';

// If we’ve landed on the dashboard view, set/refresh the cookie, then go to student_page.php
if (isset($mode) && $mode === 'dashboard') {
    ensureAuthCookie($COOKIE_NAME, $INACTIVITY);

    if (!headers_sent()) {
        header('Location: /it363/student_page.php');
        exit;
    } else {
        // Fallback in case something already printed output
        echo '<script>location.href="/it363/student_page.php";</script>';
        exit;
    }
}
?>

<div class="shell <?= ($mode === 'profile' || $mode === 'dashboard') ? 'single' : '' ?> <?= ($mode === 'dashboard') ? 'square' : '' ?>">
  <?php if ($mode === 'login' || $mode === 'verify'): ?>
    <section class="left">
      <h1 class="welcome">Welcome!</h1>
      <p>Sign in with a one-time code delivered to your email</p>
      <img class="isu-logo" src="assets/isu.png" alt="Illinois State University">
    </section>
  <?php endif; ?>

  <section class="right">
    <?php
      $title = 'Sign In';
      if ($mode === 'verify') $title = 'Enter Your Code';
      if ($mode === 'profile') $title = 'Complete Your Profile';
      if ($mode === 'dashboard') {
          $u = fetchUser($pdo, userEmail());
          $fname = $u && !empty($u['fname']) ? $u['fname'] : 'there';
          $title = 'Hello, ' . h($fname) . '!';
      }
    ?>
    <h2><?= $title ?></h2>

    <?php foreach ($errors as $e): ?><div class="card" style="background:#ffe8e8;border-color:#f3b6b9;color:#7a1014;"><?= h($e) ?></div><?php endforeach; ?>
    <?php if (!empty($messages)): ?>
      <?php foreach ($messages as $m): ?><div class="notice"><?= h($m) ?></div><?php endforeach; ?>
    <?php elseif (!empty($_SESSION['pending_email'])): ?>
      <div class="notice">We sent a 6-digit code to <?= h($_SESSION['pending_email']) ?>.</div>
    <?php endif; ?>

    <?php if ($mode === 'profile' && userEmail()): ?>
      <div class="card">
        <form method="post" action="?action=save_profile">
          <div style="display:flex; gap:12px; margin-bottom:12px; flex-wrap:wrap">
            <input type="text" name="fname" placeholder="First name" required>
            <input type="text" name="lname" placeholder="Last name" required>
          </div>
          <div style="margin-bottom:16px">
            <input type="text" name="section" placeholder="IT168 Section Number (e.g., 004)" required>
          </div>
          <button type="submit">Save Profile</button>
        </form>
      </div>

    <?php elseif ($mode === 'verify'): ?>
      <div class="card">
        <form method="post" action="?action=verify_code">
          <div style="margin-bottom:16px">
            <input type="text" name="code" placeholder="6-digit code" pattern="\d{6}" maxlength="6" required>
          </div>
          <div style="text-align:center;">
            <button type="submit">Verify & Continue</button>
            <div style="margin-top:10px;">
              <a class="link" href="/">Back</a>
            </div>
          </div>
        </form>
      </div>

    <?php elseif ($mode === 'login'): ?>
      <div class="card">
        <form method="post" action="?action=request_code">
          <div style="margin-bottom:6px; font-size:13px; font-weight:500; text-align:left; color:#666;">Email</div>
          <div style="margin-bottom:16px">
            <input type="email" name="email" placeholder="email@ilstu.edu" required>
          </div>
          <button type="submit">LOGIN</button>
        </form>
      </div>

    <?php elseif ($mode === 'dashboard'): ?>
      <div class="card" style="display:flex; justify-content:space-between; align-items:center;">
        <?php $u = fetchUser($pdo, userEmail()); ?>
        <div>
          <strong>Signed in as</strong><br><?= h($u['email'] ?? userEmail() ?? '') ?>
        </div>
        <div><a class="link" href="/../IT363">Log out</a></div>
      </div>
    <?php endif; ?>
  </section>
</div>
</body>
</html>
