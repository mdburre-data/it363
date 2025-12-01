<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require __DIR__ . '/Email_Login_Feature/vendor/autoload.php';
require_once __DIR__ . '/Session_Cookie/auth.php';

// Auth Check
if (!isset($_COOKIE[$COOKIE_NAME])) {
    header("Location: login.php");
    exit;
}

require __DIR__ . '/config.php';
use App\Mailer;

$conn = new mysqli('localhost', DB_USER, DB_PASS, 'tutoring_center');
if ($conn->connect_error) {
    die("Connection failed");
}

$appointmentId = $_POST['appointmentId'] ?? null;
$isAdmin = $_SESSION['isAdmin'] ?? false;
$userEmail = $_SESSION['email'] ?? null;

// Determine where to send errors back to
$redirectPage = $isAdmin ? 'admin_page.php' : 'student_page.php';

function redirectError($page, $msg) {
    header("Location: $page?error=" . urlencode($msg));
    exit;
}

if (!$appointmentId) {
    redirectError($redirectPage, "Appointment ID is required.");
}

// Fetch Appointment Details
$fetchSql = "SELECT email, app_date, app_time, is_scheduled FROM appointments WHERE id = ?";
$stmt = $conn->prepare($fetchSql);
$stmt->bind_param('i', $appointmentId);
$stmt->execute();
$result = $stmt->get_result();
$appt = $result->fetch_assoc();
$stmt->close();

if (!$appt) {
    redirectError($redirectPage, "Appointment not found.");
}

// Permission Check
if (!$isAdmin && strtolower($appt['email']) !== strtolower($userEmail)) {
    redirectError($redirectPage, "You do not have permission to cancel this appointment.");
}

if (!$appt['is_scheduled']) {
    redirectError($redirectPage, "This slot is not currently booked.");
}

// 24hr Cancellation Rule (Students Only)
if (!$isAdmin) {
    try {
        $apptDateTimeStr = $appt['app_date'] . ' ' . $appt['app_time'];
        $apptDateTime = new DateTime($apptDateTimeStr);
        $now = new DateTime();

        $cutoffTime = clone $apptDateTime;
        $cutoffTime->modify('-24 hours');

        if ($now > $cutoffTime) {
            redirectError($redirectPage, "Cancellations within 24 hours are not allowed. Please contact the School of IT Tutoring Center directly.");
        }
    } catch (Exception $e) {
        redirectError($redirectPage, "Error verifying cancellation policy.");
    }
}

// Cancel
$updateSql = "UPDATE appointments SET reason = NULL, email = NULL, is_scheduled = FALSE WHERE id = ?";
$stmt = $conn->prepare($updateSql);
$stmt->bind_param('i', $appointmentId);

if ($stmt->execute()) {
    
    // Send Email
    $toEmail = $appt['email'];
    $date = $appt['app_date'];
    $time = $appt['app_time'];
    
    $subject = "Appointment Cancelled - IT 168 Tutoring";
    
    // --- CANCEL EMAIL ---
    $html = "
    <div style='background-color: #f4f6f8; padding: 40px 0; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif;'>
        <div style='background: #fff; max-width: 600px; margin: 0 auto; border-radius: 8px; overflow: hidden; border: 1px solid #e1e1e1; box-shadow: 0 2px 4px rgba(0,0,0,0.05);'>
            
            <div style='background: #fff; padding: 20px; text-align: center; border-bottom: 4px solid #CE1126;'>
                <img src='cid:isulogo' alt='Illinois State University' style='height: 60px; width: auto; display: block; margin: 0 auto;'>
            </div>
            
            <div style='padding: 30px;'>
                <h2 style='color: #D32F2F; margin-top: 0; font-size: 22px; text-align: center; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px; margin-bottom: 20px;'>Appointment Cancelled</h2>
                
                <p style='color: #555; font-size: 16px; line-height: 1.5; margin-bottom: 20px; text-align: center;'>
                    The following appointment has been cancelled.
                </p>
                
                <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px;'>
                    <tr style='border-bottom: 1px solid #eee;'>
                        <td style='padding: 12px 0; color: #777; width: 40%;'>Date</td>
                        <td style='padding: 12px 0; color: #333; font-weight: bold;'>$date</td>
                    </tr>
                    <tr style='border-bottom: 1px solid #eee;'>
                        <td style='padding: 12px 0; color: #777;'>Time</td>
                        <td style='padding: 12px 0; color: #333; font-weight: bold;'>$time</td>
                    </tr>
                </table>

                <div style='background: #fff5f5; padding: 15px; border-radius: 4px; font-size: 14px; color: #c53030; text-align: center; border: 1px solid #fed7d7;'>
                    If you did not request this cancellation, please contact the tutoring center immediately.
                </div>
            </div>
            
            <div style='background: #fdfdfd; padding: 15px; text-align: center; border-top: 1px solid #eee; font-size: 12px; color: #999;'>
                Illinois State University
            </div>
        </div>
    </div>";
    
    // Attach image for embedding
    $images = [
        __DIR__ . '/imgs/isublack.png' => 'isulogo'
    ];
    
    if ($toEmail) {
        Mailer::send($toEmail, $subject, $html, $images);
    }

    // Redirect to Confirmation Page
    header("Location: confirmation.php?type=cancel&date=$date&time=$time&email=$toEmail");
    exit;

} else {
    redirectError($redirectPage, "Database error: " . $stmt->error);
}

$stmt->close();
$conn->close();
?>