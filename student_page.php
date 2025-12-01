<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/Session_Cookie/auth.php';

// Ensure user is logged in, otherwise redirect
requireAuthOrRedirect(
    $COOKIE_NAME,
    $INACTIVITY,
    '/it363/login.php'
);

require __DIR__ . '/config.php';
$conn = new mysqli('localhost', DB_USER, DB_PASS, 'tutoring_center');

// Check for feedback messages in URL
$msg = $_GET['msg'] ?? null;
$error = $_GET['error'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Student Dashboard | IT 168 Tutoring</title>
  <link rel="stylesheet" href="assets/css/base.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <link rel="stylesheet" href="assets/css/pages.css">
</head>
<body>

  <header class="app-header">
    <div class="container header-inner header-grid-layout">
      <div class="header-left">
        <img src="imgs/isublack.png" alt="Illinois State University Seal">
      </div>
      <div class="header-center">
        <div class="brand-text-center">IT 168 Tutoring Center</div>
        <div class="portal-badge">Student Portal</div>
      </div>
      <div class="header-right">
        <a href="Session_Cookie/logout.php" class="btn btn-sm btn-primary">Logout</a>
      </div>
    </div>
  </header>

  <main class="container">

    <div class="status-bar">
      <div>
      <?php      
      // Fetch student name for personalization
      $stmt = $conn->prepare("SELECT fName, lName FROM user WHERE email = ?");
      $stmt->bind_param("s", $_SESSION['email']);
      $stmt->execute();
      $result = $stmt->get_result();
      $username = "Student";
      if ($row = $result->fetch_assoc()) {
          $username = $row['fName'] . " " . $row['lName'];
      }
      
      if (isset($_SESSION['token'])) {
        echo "<h3 style='margin:0'>Welcome back, " . htmlspecialchars($username) . "!</h3>";
      } else {
        echo "Please log in.";
       }
      ?>
      </div>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error mt-4">
            <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <section class="dashboard-grid">
      <div class="main-content">
        
        <section class="card">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
             <h2 style="margin:0; border:none;">Your Upcoming Appointments</h2>
          </div>
          <div id="upcomingAppointments">Loading appointments...</div>
        </section>
          
        <section class="card">
          <h2>Book an Appointment</h2>
          <p>Select a date to check availability.</p>
          
          <form id="dateForm" method="POST" onsubmit="timeHandler(event)" style="display:flex; gap:12px; align-items:flex-end;">
            <div style="flex-grow:1;">
                <label for="date">Select Date</label>
                <input type="date" id="date" name="date" required style="margin:0;">
            </div>
            <button type="submit" class="btn btn-primary">Check Availability</button>
          </form>
          
          <div id="buttonContainer" class="mt-4"></div>

          <hr style="margin: 1.5rem 0; border:0; border-top:1px solid #eee;">

          <form id="appointmentForm" method="POST" action="submit_appointment.php">
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($_SESSION['email']); ?>">
            <input type="hidden" name="date" id="hiddenDate">
            
            <label for="reason">Reason for Appointment</label>
            <input type="text" id="reason" name="reason" placeholder="Exam review, homework help, etc." required />
            
            <div class="text-right">
                <button type="submit" class="btn btn-primary">Confirm Booking</button>
            </div>
          </form>
        </section>
      </div>

      <div class="sidebar">
        <section class="card">
          <h2>Tutoring Hours</h2>
          <div id="tutoringHoursOutput" style="font-size:0.9rem; line-height:1.6;">Loading hours...</div>
        </section>

        <section class="card" style="background:#fff5f5; border-color:#fed7d7;">
          <h2 style="border-bottom-color:#e53e3e; color:#c53030;">Cancel Appointment</h2>
          <form method="POST" action="cancel_appointment.php">
            <label for="appointmentId">Appointment ID to Cancel</label>
            <input type="number" id="appointmentId" name="appointmentId" required>
            <button type="submit" class="btn btn-danger" style="width:100%">Cancel Appointment</button>
          </form>
        </section>
      </div>
    </section>
  </main>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      loadHours();
      loadAppointments();
    });

    // Fetch general hours
    function loadHours() {
     function to12Hour(timeStr) {
    if (!timeStr) return "";

    // Split the time string into hours and minutes
    let [hour, minute] = timeStr.split(":"); 
    hour = parseInt(hour, 10);

    // Determine AM/PM
    const ampm = hour >= 12 ? "PM" : "AM";
    hour = hour % 12 || 12;

    return `${hour}:${minute} ${ampm}`;
  }

  fetch('get_hours.php')
    .then(response => response.json())
    .then(data => {
      let hoursHTML = '<ul style="list-style: none; padding: 0; margin:0;">';

      data.forEach(row => {

        const days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
        const dayName = days[(parseInt(row.day_of_week) % 7)];

        const isClosed = row.start_time === "00:00:00" && row.end_time === "00:00:00";

        const start = isClosed
          ? "<span style='color:#ccc; font-weight:500;'>Closed</span>"
          : `<span>${to12Hour(row.start_time)} - ${to12Hour(row.end_time)}</span>`;

        hoursHTML += `
          <li style="padding: 10px 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items:center;">
            <strong style="color: var(--secondary);">${dayName}</strong>
            ${start}
          </li>`;
      });

      hoursHTML += '</ul>';
      document.getElementById('tutoringHoursOutput').innerHTML = hoursHTML;
    })
    .catch(error => {
      document.getElementById('tutoringHoursOutput').innerHTML = 'Error loading hours.';
    });
    }

    // AJAX Fetch for Time Slots
    function timeHandler(event) {
      event.preventDefault();
      const form = event.target;
      const formData = new FormData(form);
      
      // Update hidden date input
      document.getElementById('hiddenDate').value = formData.get('date');

      fetch("get_available.php", { method: "POST", body: formData })
        .then(r => r.json())
        .then(data => {
          let html = '';
          if (data.available_slots && data.available_slots.length > 0) {
             html += '<label>Available Slots:</label><div style="display:flex; flex-wrap:wrap; gap:8px;">';
             data.available_slots.forEach(slot => {
                 html += `<label style="background:var(--bg-app); padding:8px 12px; border-radius:4px; cursor:pointer; border:1px solid var(--border-color); font-weight:normal; font-size:0.9rem;">
                            <input type="radio" name="time_slot" value="${slot}" form="appointmentForm" required> ${slot}
                          </label>`;
             });
             html += '</div>';
          } else {
             html = '<div style="padding:10px; background:#e2e8f0; border-radius:4px; color:#4a5568;">No slots available for this date.</div>';
          }
          document.getElementById('buttonContainer').innerHTML = html;
        });
    }

    // Load Appointments (Hides empty days)
    function loadAppointments() {
      fetch("get_student_upcoming.php")
        .then(r => r.json())
        .then(data => {
          if (!Array.isArray(data) || data.length === 0) {
             document.getElementById("upcomingAppointments").innerHTML = "<div class='calendar-none'>No upcoming appointments scheduled.</div>";
             return;
          }
          let html = '<div class="calendar-wrap">';
          data.forEach(app => {
             const dateObj = new Date(app.app_date + "T00:00:00");
             const dayLabel = dateObj.toLocaleDateString(undefined, { weekday: "short", month: "short", day: "numeric" });

            html += `
              <div class="calendar-appt">
                <strong style="color:var(--primary); display:block; margin-bottom:4px; font-size:1.1rem;">${dayLabel}</strong>
                <div style="font-size:1.2rem; font-weight:bold; margin-bottom:4px;">${app.app_time.slice(0,5)}</div>
                <div style="font-size:0.9rem; color:var(--text-main); margin-bottom:4px;">${app.reason || "Tutoring Session"}</div>
                <small style="color:var(--text-muted); font-family:monospace;">ID: ${app.id}</small>
              </div>
            `;
          });
          document.getElementById("upcomingAppointments").innerHTML = html + '</div>';
        });
    }
  </script>
</body>
</html>