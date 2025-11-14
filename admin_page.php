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
    '/it363/index.php'
);

require __DIR__ . '/config.php';
// Database connection settings 
      // Create connection
      $conn = new mysqli('localhost', DB_USER, DB_PASS, 'tutoring_center');
      echo $_SESSION['isAdmin'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>IT 168 Tutoring – Admin</title>
  <link rel="stylesheet" href="admin_page.css">

 
</head>
<body>
 
  <!-- Header -->
  <header class="isu-header">
    <img src="imgs/ISU-Seal.png" alt="Illinois State University Seal">
    <div>
      <p class="isu-header-title">IT 168 Tutoring – Admin</p>
      <p class="isu-header-subtitle">Manage appointments and scheduling · Illinois State University</p>
      <button onclick="location.href='Session_Cookie/logout.php'">Logout</button>
    </div>
  </header>

  <main class="page">
    <section class="hero">
      <h1>Admin Dashboard</h1>
      <p>
      <?php      
      $stmt = $conn->prepare("SELECT fName, lName FROM user WHERE email = ?");
      $stmt->bind_param("s", $_SESSION['email']);
      $stmt->execute();
      $result = $stmt->get_result();

      $username = "";

      if ($row = $result->fetch_assoc()) {
          $username = $row['fName'] . " " . $row['lName'];
      }
      // $username = rtrim($username, " "); 

      
      if (isset($_SESSION['token'])) {
        $email = $_SESSION['email'];
        echo "Welcome back, " . $username . "! Use this page to review upcoming appointments and adjust tutoring availability.";
      } else {
        echo "Please log in.";
       }
      ?>
      </p>
    </section>

    <section class="grid">
      <!-- Left side: upcoming appointments + display scripts -->
      <div>
        <section class="card">
          <h2>Upcoming Appointments (Next 14 Days)</h2>
          <div id="upcomingAppointments">Loading appointments...</div>
        </section>

<!-------------------------------------------------------------------------------------------->
<!-------------------------------------------------------------------------------------------->
<!-------------------------------------------------------------------------------------------->
<!-------------------------------------------------------------------------------------------->
<!-------------------------------------------------------------------------------------------->
<!-------------------------------------------------------------------------------------------->
<!-------------------------------------------------------------------------------------------->
<!-------------------------------------------------------------------------------------------->
<!-------------------------------------------------------------------------------------------->
<!-------------------------------------------------------------------------------------------->
<!-------------------------------------------------------------------------------------------->
      </div>

      <!-- Right side: controls -->
      <div>
        <section class="card">
          <h2>Change Weekly Scheduling Hours</h2>
          <form id="changeHoursForm" method="POST" onsubmit="changeHoursHandler(event)">
            <label for="dayOfWeek">Day of the Week</label>
            <select id="dayOfWeek" name="dayOfWeek" required>
              <option value="">Select a day</option>
              <option value="1">Monday</option>
              <option value="2">Tuesday</option>
              <option value="3">Wednesday</option>
              <option value="4">Thursday</option>
              <option value="5">Friday</option>
              <option value="6">Saturday</option>
              <option value="7">Sunday</option>
            </select>

            <label for="startTime">Start Time</label>
            <input type="time" id="startTime" name="startTime" required>

            <label for="endTime">End Time</label>
            <input type="time" id="endTime" name="endTime" required>

            <button type="submit">Update Weekly Hours</button>
          </form>
        </section>

        <section class="card">
          <h2>Block / Unblock Dates</h2>

          <form id="blockDateForm" method="POST" onsubmit="blockDateHandler(event)">
            <label for="blockDate">Date to Block</label>
            <input type="date" id="blockDate" name="blockDate" required>
            <button type="submit">Block Date</button>
          </form>

          <form id="unblockDateForm" method="POST" onsubmit="unblockDateHandler(event)" style="margin-top:10px;">
            <label for="unblockDate">Date to Unblock</label>
            <input type="date" id="unblockDate" name="unblockDate" required>
            <button type="submit">Unblock Date</button>
          </form>
        </section>

        <section class="card">
          <h2>Change Daily Hours</h2>
          <form id="changeDailyHoursForm" method="POST" onsubmit="changeDailyHoursHandler(event)">
            <label for="changeHoursDate">Specific Date</label>
            <input type="date" id="changeHoursDate" name="changeHoursDate" required>

            <label for="updatedStartTime">Start Time</label>
            <input type="time" id="updatedStartTime" name="updatedStartTime" required>

            <label for="updatedEndTime">End Time</label>
            <input type="time" id="updatedEndTime" name="updatedEndTime" required>

            <button type="submit">Update Daily Hours</button>
          </form>

          <div id="changeHoursOutput"></div>
        </section>
      </div>
    </section>
  </main>

  <script>
    // Change weekly hours
    function changeHoursHandler(event) {
      event.preventDefault();
      const form = event.target;
      const formData = new FormData(form);
      fetch('change_hours.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.text())
      .then(data => {
        document.getElementById("changeHoursOutput").innerHTML = data;
      })
      .catch(error => {
        document.getElementById("changeHoursOutput").innerHTML = "Error: " + error;
      });
    }

    // Change daily hours
    function changeDailyHoursHandler(event) {
      event.preventDefault();
      const form = event.target;
      const formData = new FormData(form);
      fetch('change_daily_hours.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.text())
      .then(data => {
        document.getElementById("changeHoursOutput").innerHTML = data;
      })
      .catch(error => {
        document.getElementById("changeHoursOutput").innerHTML = "Error: " + error;
      });
    }

    // Block date
    function blockDateHandler(event) {
      event.preventDefault();
      const form = event.target;
      const formData = new FormData(form);
      fetch('block_date.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.text())
      .then(data => {
        document.getElementById("changeHoursOutput").innerHTML = data;
      })
      .catch(error => {
        document.getElementById("changeHoursOutput").innerHTML = "Error: " + error;
      });
    }

    // Unblock date
    function unblockDateHandler(event) {
      event.preventDefault();
      const form = event.target;
      const formData = new FormData(form);
      fetch('unblock_date.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.text())
      .then(data => {
        document.getElementById("changeHoursOutput").innerHTML = data;
      })
      .catch(error => {
        document.getElementById("changeHoursOutput").innerHTML = "Error: " + error;
      });
    }

    // Generic output viewer
    function callPhp(scriptName) {
      fetch(scriptName)
        .then(response => response.text())
        .then(data => {
          document.getElementById("output").innerHTML = data;
        })
        .catch(error => {
          document.getElementById("output").innerHTML = "Error: " + error;
        });
    }

    // Load upcoming appointments on page load
    document.addEventListener("DOMContentLoaded", function() {
      const today = new Date();
      const days = [];
      for (let i = 0; i < 14; i++) {
        const date = new Date(today);
        date.setDate(today.getDate() + i);
        const formattedDate = date.toISOString().split("T")[0]; // YYYY-MM-DD
        days.push({
          date: formattedDate,
          appointments: []
        });
      }

      fetch("get_all_upcoming.php")
        .then(response => response.json())
        .then(data => {
          if (Array.isArray(data)) {
            data.forEach(app => {
              const match = days.find(d => d.date === app.app_date);
              if (match) {
                match.appointments.push(app);
              }
            });
          }

          let calendar = '<div class="calendar-wrap">';
          days.forEach(day => {
            const dateObj = new Date(day.date);
            const dayLabel = dateObj.toLocaleDateString(undefined, {
              weekday: "short",
              month: "short",
              day: "numeric"
            });

            calendar += `
              <div class="calendar-day">
                <strong>${dayLabel}</strong><br>
                <small>${day.date}</small>
            `;

            if (day.appointments.length > 0) {
              day.appointments.forEach(app => {
                calendar += `
                  <div class="calendar-appt">
                    <strong>${app.app_time}</strong><br>
                    ${app.email}<br>
                    <small>${app.reason || ""}</small>
                  </div>
                `;
              });
            } else {
              calendar += `<div class="calendar-none">No appointments</div>`;
            }

            calendar += `</div>`;
          });
          calendar += `</div>`;

          document.getElementById("upcomingAppointments").innerHTML = calendar;
        })
        .catch(error => {
          document.getElementById("upcomingAppointments").innerHTML = "Error: " + error;
        });
    });
  </script>
</body>
</html>
