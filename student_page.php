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
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>IT 168 Tutoring Center – Welcome</title>
  <meta name="description" content="Illinois State University–style landing page for the IT 168 Tutoring Scheduler." />
  <link rel="stylesheet" href="student_page.css">

</head>
<body>
  <!-- Header with ISU seal -->
  <header class="isu-header">
    <img src="imgs/ISU-Seal.png" alt="Illinois State University Seal">
    <div>
      <p class="isu-header-title">IT 168 Tutoring Center</p>
      <p class="isu-header-subtitle">School of Information Technology · Illinois State University</p>
      <button onclick="location.href='Session_Cookie/logout.php'">Logout</button>
    </div>
  </header>

  <main class="page">

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
        echo "Welcome back, " . $username . "!";
      } else {
        echo "Please log in.";
       }
      ?>

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

    <section class="hero">
      <h1>Welcome</h1>
      <p>Use this page to check tutoring hours and register an appointment.</p>
      <p>Step 1: Pick a date, Step 2: Choose a time, Step 3: Tell us what you need help with.</p>
    </section>

    <section class="card">
      <h2>Tutoring Hours</h2>
      <p>Weekly schedule:</p>
      <div id="tutoringHoursOutput">Loading hours...</div>
    </section>

    <section class="card">
      <h2>Check Appointment Date</h2>
      <form id="dateForm" method="POST" onsubmit="timeHandler(event)">
        <label for="date">Date</label>
        <input type="date" id="date" name="date" required />
        <button type="submit">See Available Times</button>
      </form>
      <div id="buttonContainer"></div>
    </section>

    <section class="card">
      <h2>Appointment Details</h2>
      <form id="appointmentForm" method="POST" onsubmit="handleSubmit(event)">
        <label for="reason">Reason for Appointment</label>
        <input type="text" id="reason" name="reason" placeholder="Exam review, homework help, etc." required />
        <button type="submit">Submit Appointment</button>
      </form>

      <div id="output"></div>
    </section>
  </main>

  <script>
    // Fetch and display tutoring hours on page load
    document.addEventListener('DOMContentLoaded', function () {
      fetch('get_hours.php')
        .then(response => response.json())
        .then(data => {
          console.log("Server returned:", data);

          let hoursHTML = '<ul>';

          data.forEach(row => {
            const days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
            const dayName = days[parseInt(row.day_of_week) % 7];

            const start = row.start_time === "00:00:00" && row.end_time === "00:00:00"
              ? "Closed"
              : `${row.start_time.slice(0, 5)} - ${row.end_time.slice(0, 5)}`;

            hoursHTML += `<li>${dayName}: ${start}</li>`;
          });

          hoursHTML += '</ul>';
          document.getElementById('tutoringHoursOutput').innerHTML = hoursHTML;
        })
        .catch(error => {
          document.getElementById('tutoringHoursOutput').innerHTML =
            'Error loading hours: ' + error;
        });
    });

    // Handle date selection submission
    function timeHandler(event) {
      event.preventDefault();

      const form = event.target;
      const formData = new FormData(form);

      console.log("Date submitted:", formData.get('date'));

      fetch("get_available.php", {
        method: "POST",
        body: formData
      })
        .then(response => response.json())
        .then(data => {
          console.log("Available time slots:", data.available_slots);
          let buttonContainerHTML = '';

                try {
         for (let i = 0; i < data.available_slots.length; i++) {
            const slot = data.available_slots[i];
            buttonContainerHTML += `
              <label>
                <input type="radio" name="time_slot" value="${slot}"> ${slot}
              </label>`;
          }
      } catch (error) {
        console.error("Error processing available slots:", error);
        buttonContainerHTML = "Can't load available time slots, please ensure a valid date is selected.";
      }  

          if (data.available_slots.length === 0) {
            buttonContainerHTML = "No available time slots for this date.";
          }

          document.getElementById('buttonContainer').innerHTML = buttonContainerHTML;
        })
        .catch(error => {
          document.getElementById('buttonContainer').innerHTML = 'Error: ' + error;
        });
    }

    // Handle appointment submission
    function handleSubmit(event) {
      event.preventDefault();

      const appointmentForm = document.getElementById('appointmentForm');
      const formData = new FormData(appointmentForm);

      const dateForm = document.getElementById('dateForm');
      const dateFormData = new FormData(dateForm);

      // Append date
      const chosenDate = dateFormData.get('date');
      if (!chosenDate) {
        document.getElementById('output').innerHTML = "Please choose a date first.";
        return;
      }
      formData.append('date', chosenDate);

      // Append time slot
      const timeRadio = document.querySelector('input[name="time_slot"]:checked');
      if (!timeRadio) {
        document.getElementById('output').innerHTML = "Please select a time slot.";
        return;
      }
      formData.append('time_slot', timeRadio.value);

      fetch("submit_appointment.php", {
        method: 'POST',
        body: formData
      })
        .then(response => response.text())
        .then(data => {
          document.getElementById('output').innerHTML = data;
        })
        .catch(error => {
          document.getElementById('output').innerHTML = 'Error: ' + error;
        });
    }


    // Load upcoming events
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

      fetch("get_student_upcoming.php")
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

