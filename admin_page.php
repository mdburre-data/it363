<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/Session_Cookie/auth.php';

// If not authenticated, send them back to homepage
requireAuthOrRedirect(
    $COOKIE_NAME,
    $INACTIVITY,
    '/it363/login.php',
    true
);

require __DIR__ . '/config.php';
$conn = new mysqli('localhost', DB_USER, DB_PASS, 'tutoring_center');

// Handle View Logic (Default to dashboard)
$view = $_GET['view'] ?? 'dashboard';

// Check for messages
$msg = $_GET['msg'] ?? null;
$error = $_GET['error'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard | IT 168 Tutoring</title>
  <link rel="stylesheet" href="assets/css/base.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <link rel="stylesheet" href="assets/css/pages.css">
  <style>
      /* Local style for the settings grid layout */
      .settings-grid {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
          gap: 24px;
          margin-top: 24px;
      }
  </style>
</head>
<body>
 
  <header class="app-header">
    <div class="container header-inner header-grid-layout">
      <div class="header-left">
        <img src="imgs/isublack.png" alt="Illinois State University Seal">
      </div>
      <div class="header-center">
        <div class="brand-text-center">IT 168 Tutoring Center</div>
        <div class="portal-badge">Admin Portal</div>
      </div>
      <div class="header-right">
        <a href="Session_Cookie/logout.php" class="btn btn-sm btn-primary">Logout</a>
      </div>
    </div>
  </header>

  <main class="container">
    
    <div class="status-bar">
      <?php if ($view === 'settings'): ?>
          <div style="display:flex; align-items:center; gap:15px; width:100%;">
              <a href="admin_page.php?view=dashboard" class="btn btn-sm btn-secondary">&larr; Back</a>
              <div>
                  <h3 style="margin:0">System Settings</h3>
                  <p style="margin:0; font-size:0.9rem">Manage hours, blocked dates, and admin accounts.</p>
              </div>
          </div>
      <?php else: ?>
          <div>
              <?php      
              $stmt = $conn->prepare("SELECT fName, lName FROM user WHERE email = ?");
              $stmt->bind_param("s", $_SESSION['email']);
              $stmt->execute();
              $result = $stmt->get_result();
              $username = "Admin";
              if ($row = $result->fetch_assoc()) {
                  $username = $row['fName'] . " " . $row['lName'];
              }
              echo "<h3 style='margin:0'>Admin Dashboard: " . htmlspecialchars($username) . "</h3>";
              ?>
          </div>
      <?php endif; ?>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-info mt-4" style="background:#e6fffa; color:#2c7a7b; border-color:#b2f5ea;">
            <strong>Success:</strong> <?php echo htmlspecialchars($msg); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error mt-4">
            <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>


    <?php if ($view === 'settings'): ?>
    
    <section class="settings-grid">
        
        <section class="card">
          <h2>Weekly Hours</h2>
          <p>Set the standard recurring schedule for each day.</p>
          <form method="POST" action="change_hours.php">
            <label>Day of the Week</label>
            <select name="dayOfWeek" required>
              <option value="">Select a day</option>
              <option value="1">Monday</option>
              <option value="2">Tuesday</option>
              <option value="3">Wednesday</option>
              <option value="4">Thursday</option>
              <option value="5">Friday</option>
              <option value="6">Saturday</option>
              <option value="7">Sunday</option>
            </select>

            <div style="display:flex; gap:15px; margin-bottom:1rem;">
                <div style="flex:1"><label>Open</label><input type="time" name="startTime" required></div>
                <div style="flex:1"><label>Close</label><input type="time" name="endTime" required></div>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%">Update Weekly Schedule</button>
          </form>
        </section>

        <section class="card">
          <h2>Block / Unblock Dates</h2>
          <p>Close the center for holidays or specific events.</p>
          <form method="POST" action="block_date.php" style="margin-bottom:2rem;">
            <label style="color:#D32F2F;">Block a Date (Cancels Appointments)</label>
            <div style="display:flex; gap:8px;">
                <input type="date" name="blockDate" required style="margin:0; flex:1;">
                <button type="submit" class="btn btn-danger">Block</button>
            </div>
          </form>
          <form method="POST" action="unblock_date.php">
            <label>Unblock a Date</label>
            <div style="display:flex; gap:8px;">
                <input type="date" name="unblockDate" required style="margin:0; flex:1;">
                <button type="submit" class="btn btn-secondary">Unblock</button>
            </div>
          </form>
        </section>

        <section class="card">
          <h2>Daily Override</h2>
          <p>Change hours for a specific single date.</p>
          <form method="POST" action="change_daily_hours.php">
            <label>Date</label>
            <input type="date" name="changeHoursDate" required>
            <div style="display:flex; gap:15px; margin-bottom:1rem;">
                <div style="flex:1"><label>New Open</label><input type="time" name="updatedStartTime" required></div>
                <div style="flex:1"><label>New Close</label><input type="time" name="updatedEndTime" required></div>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%">Set Daily Override</button>
          </form>
        </section>

        <section class="card">
          <h2>Admin Access</h2>
          <p>Grant administrative privileges to a user.</p>
          <form method="POST" action="add_admin.php">
            <label>Email</label>
            <input type="email" name="email" required>
            <div style="display:flex; gap:10px;">
                <div style="flex:1"><label>First Name</label><input type="text" name="fName" required></div>
                <div style="flex:1"><label>Last Name</label><input type="text" name="lName" required></div>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%">Add New Admin</button>
          </form>
        </section>

    </section>


    <?php else: ?>

    <section class="dashboard-grid">
      <div class="main-content">
        <section class="card">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
             <h2 style="margin:0; border:none;">Upcoming Appointments Overview</h2>
             <span style="font-size:0.8rem; color:#666; background:#eee; padding:2px 8px; border-radius:4px;">Next 14 Days</span>
          </div>
          <div id="upcomingAppointments">Loading appointments...</div>
        </section>

        <section class="card" style="background:#fff5f5; border-color:#fed7d7;">
          <h2 style="border-bottom-color:#e53e3e; color:#c53030;">Cancel Any Appointment</h2>
          <form method="POST" action="cancel_appointment.php" style="display:flex; gap:12px; align-items:flex-end;">
            <div style="flex-grow:1;">
                <label for="appointmentId">Appointment ID to Cancel</label>
                <input type="number" id="appointmentId" name="appointmentId" placeholder="Enter ID" required style="margin:0;">
            </div>
            <button type="submit" class="btn btn-danger">Cancel Appointment</button>
          </form>
        </section>
      </div>

      <div class="sidebar">
        <section class="card">
          <h2>Settings & Schedule</h2>
          <p>Manage weekly hours, block specific dates, handle daily overrides, and add new admins.</p>
          
          <a href="admin_page.php?view=settings" class="btn btn-primary" style="width:100%; text-align:center; justify-content: center;">
              Manage Settings &rarr;
          </a>
        </section>
      </div>
    </section>
    
    <script>
    // Only load appointments if we are on the dashboard view
    document.addEventListener("DOMContentLoaded", loadAppointments);
    function loadAppointments() {
      // If the element doesn't exist (because we are in settings view), stop.
      if (!document.getElementById("upcomingAppointments")) return;

      fetch("get_all_upcoming.php")
        .then(r => r.json())
        .then(data => {
          if (!Array.isArray(data) || data.length === 0) {
             document.getElementById("upcomingAppointments").innerHTML = "<p>No upcoming appointments found.</p>";
             return;
          }

          let html = '<div class="calendar-wrap">';
          data.forEach(app => {
            const dateObj = new Date(app.app_date + "T00:00:00");
            const dayLabel = dateObj.toLocaleDateString(undefined, { weekday: "short", month: "short", day: "numeric" });
            const sectionBadge = app.section ? `<span style="background:#eee; padding:2px 6px; border-radius:4px; font-size:0.7rem; font-weight:700; color:#333; float:right;">Sec ${app.section}</span>` : '';

            html += `
              <div class="calendar-appt" style="padding:12px;">
                <div style="font-size:0.85rem; color:#666; margin-bottom:4px; text-transform:uppercase; letter-spacing:0.05em; font-weight:600;">
                    ${dayLabel}
                </div>
                <div style="margin-bottom:6px; display:flex; justify-content:space-between;">
                    <strong style="font-size:0.9rem; color:var(--primary);">${app.app_time.slice(0,5)}</strong>
                    ${sectionBadge}
                </div>
                <div style="font-weight:600; font-size:0.9rem; margin-bottom:2px;">${app.fName || ''} ${app.lName || ''}</div>
                <div style="font-size:0.8rem; color:#666; margin-bottom:6px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${app.email}</div>
                <div style="background:#f4f6f8; padding:6px; border-radius:4px; font-size:0.85rem; color:#333; margin-bottom:6px; font-style:italic; border:1px solid #eee;">"${app.reason || 'No reason'}"</div>
                <small style="color:#999; font-size:0.75rem;">Appt ID: ${app.id}</small>
              </div>
            `;
          });
          document.getElementById("upcomingAppointments").innerHTML = html + '</div>';
        })
        .catch(error => { document.getElementById("upcomingAppointments").innerHTML = "Error loading data."; });
    }
    </script>

    <?php endif; ?>
  </main>
</body>
</html>