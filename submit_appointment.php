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

// Connect to DB
$conn = new mysqli('localhost', DB_USER, DB_PASS, 'tutoring_center');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// collect inputs
$email = trim($_POST['email'] ?? '');
$reason = trim($_POST['reason'] ?? '');
$date = trim($_POST['date'] ?? '');
$time_slot = trim($_POST['time_slot'] ?? '');

// Helper to redirect with error
function redirectError($msg) {
    header("Location: student_page.php?error=" . urlencode($msg));
    exit;
}

//Debug Output
// echo "Received email: " . htmlspecialchars($email) . "<br>";
// echo "Received reason: " . htmlspecialchars($reason) . "<br>";
// echo "Received date: " . htmlspecialchars($date) . "<br>";
// echo "Received time slot: " . htmlspecialchars($time_slot) . "<br>";

// Validation
if ($email === '' || $date === '' || $reason === '' || $time_slot === '') {
    redirectError("All fields are required.");
}

// 1. Check availability (Time Slot Taken?)
$result = $conn->query("SELECT is_scheduled FROM appointments WHERE app_date = '$date' AND app_time = '$time_slot'");
if ($result && $row = $result->fetch_assoc()) {
    if ($row['is_scheduled'] == 1) {
        redirectError("That time slot is already booked. Please choose another.");
    }
}

// 2. Check duplicate bookings (Already has appt today?)
$result = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE app_date = '$date' AND email = '$email'");
if ($result && $row = $result->fetch_assoc()) {
    if ($row['count'] > 0) {
        redirectError("You already have an appointment on this date.");
    }
}

// 3. Book Appointment
$stmt = $conn->prepare("UPDATE appointments SET email = ?, reason = ?, is_scheduled = TRUE WHERE app_date = ? AND app_time = ?");
$stmt->bind_param("ssss", $email, $reason, $date, $time_slot);

if ($stmt->execute()) {
    // --- SEND EMAIL CONFIRMATION ---
    $subject = "Appointment Booked - IT 168 Tutoring";
    
    $html = "
    <div style='background-color: #f4f6f8; padding: 40px 0; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif;'>
        <div style='background: #fff; max-width: 600px; margin: 0 auto; border-radius: 8px; overflow: hidden; border: 1px solid #e1e1e1; box-shadow: 0 2px 4px rgba(0,0,0,0.05);'>
            
            <div style='background: #fff; padding: 20px; text-align: center; border-bottom: 4px solid #CE1126;'>
                <img src='cid:isulogo' alt='Illinois State University' style='height: 60px; width: auto; display: block; margin: 0 auto;'>
            </div>
            
            <div style='padding: 30px;'>
                <h2 style='color: #333; margin-top: 0; font-size: 22px; text-align: center; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px; margin-bottom: 20px;'>Appointment Confirmed</h2>
                
                <p style='color: #555; font-size: 16px; line-height: 1.5; margin-bottom: 20px; text-align: center;'>
                    Your tutoring session has been successfully scheduled.
                </p>
                
                <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px;'>
                    <tr style='border-bottom: 1px solid #eee;'>
                        <td style='padding: 12px 0; color: #777; width: 40%;'>Date</td>
                        <td style='padding: 12px 0; color: #333; font-weight: bold;'>$date</td>
                    </tr>
                    <tr style='border-bottom: 1px solid #eee;'>
                        <td style='padding: 12px 0; color: #777;'>Time</td>
                        <td style='padding: 12px 0; color: #333; font-weight: bold;'>$time_slot</td>
                    </tr>
                    <tr style='border-bottom: 1px solid #eee;'>
                        <td style='padding: 12px 0; color: #777;'>Reason</td>
                        <td style='padding: 12px 0; color: #333;'>$reason</td>
                    </tr>
                </table>

                <div style='background: #f9f9f9; padding: 15px; border-radius: 4px; font-size: 14px; color: #666; text-align: center;'>
                    Location: Old Union - School of IT Learning Center
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

    Mailer::send($email, $subject, $html, $images);

    // Redirect to Confirmation Page
    header("Location: confirmation.php?type=book&date=$date&time=$time_slot&email=$email");
    exit;

} else {
    redirectError("Database error: " . $stmt->error);
}

$stmt->close();
$conn->close();
?>