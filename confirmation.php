<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/Session_Cookie/auth.php';

// Ensure user is logged in
if (empty($_SESSION['token'])) {
    header('Location: login.php');
    exit;
}

// Get details from URL parameters
$type = $_GET['type'] ?? 'unknown';
$email = htmlspecialchars($_GET['email'] ?? 'the user');
$date = htmlspecialchars($_GET['date'] ?? '');
$time = htmlspecialchars($_GET['time'] ?? '');

// Determine Dashboard Link based on role
$dashboardLink = 'student_page.php';
if (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] === true) {
    $dashboardLink = 'admin_page.php';
}

// Content Logic
$title = "Action Confirmed";
$message = "The action has been completed successfully.";

if ($type === 'book') {
    $title = "Appointment Booked";
    $message = "We have confirmed your appointment for <strong>$date</strong> at <strong>$time</strong>.<br>A confirmation email has been sent to <strong>$email</strong>.";
} elseif ($type === 'cancel') {
    $title = "Appointment Cancelled";
    $message = "The appointment on <strong>$date</strong> at <strong>$time</strong> has been cancelled.<br>A confirmation email has been sent to <strong>$email</strong>.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Confirmation | IT 168 Tutoring</title>
  <link rel="stylesheet" href="assets/css/base.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <link rel="stylesheet" href="assets/css/pages.css">
  <style>
      .conf-wrapper {
          display: flex; justify-content: center; align-items: center; min-height: 80vh;
      }
      .conf-card {
          max-width: 500px; width: 100%; padding: 50px 30px; text-align: center;
      }
      .conf-logo { 
          height: 80px; width: auto; margin-bottom: 2rem; 
      }
  </style>
</head>
<body>

  <header class="app-header">
    <div class="container header-inner header-flex">
      <div class="brand">
        <img src="imgs/isublack.png" alt="Illinois State University Seal" style="height: 48px !important; width: auto !important;">
      </div>
    </div>
  </header>

  <main class="container conf-wrapper">
      <div class="card conf-card">
          <img src="imgs/ISU-Seal.png" alt="ISU" class="conf-logo">
          
          <h1 style="font-size: 1.75rem; margin-bottom: 1.5rem; color: var(--secondary);"><?php echo $title; ?></h1>
          
          <div style="font-size: 1rem; color: #555; margin-bottom: 2.5rem; line-height: 1.6;">
              <?php echo $message; ?>
          </div>

          <div style="display: flex; flex-direction: column; gap: 12px;">
              <a href="<?php echo $dashboardLink; ?>" class="btn btn-primary btn-lg">Return to Dashboard</a>
              <a href="Session_Cookie/logout.php" class="btn btn-secondary">Logout</a>
          </div>
      </div>
  </main>

</body>
</html>