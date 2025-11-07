<!--<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Mock Appointment Scheduler</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif;">
  <p><a href="debugging_home.html">Back to Main Page</a></p>
  <h1>Admin Page</h1>
    <p>Welcome, Admin! Use the buttons below to manage the database.</p>
    <h2>Upcoming Appointments</h2>
    <div id="upcomingAppointments"></div>
    <h2>Change Weekly Scheduling Hours</h2>
    <form id="changeHoursForm" method="POST" onsubmit="changeHoursHandler(event)">
      <label for="dayOfWeek">Day of the Week:</label>
      <select id="dayOfWeek" name="dayOfWeek" required>
        <option value="1">Monday</option>
        <option value="2">Tuesday</option>
        <option value="3">Wednesday</option>
        <option value="4">Thursday</option>
        <option value="5">Friday</option>
        <option value="6">Saturday</option>
        <option value="7">Sunday</option>
      </select><br>
      <label for="startTime">Start Time:</label>
      <input type="time" id="startTime" name="startTime" required><br><br>
      <label for="endTime">End Time:</label>
      <input type="time" id="endTime" name="endTime" required><br><br>
      <button type="submit">Submit</button>
    </form>
    <h2>Block Date:</h2>
    <form id="blockDateForm" method="POST" onsubmit="blockDateHandler(event)">
      <label for="blockDate">Date to Block:</label>
      <input type="date" id="blockDate" name="blockDate" required><br><br>
      <button type="submit">Submit</button>
    </form>

    <h2>Unblock Date:</h2>
    <form id="unblockDateForm" method="POST" onsubmit="unblockDateHandler(event)">
      <label for="unblockDate">Date to Unblock:</label>
      <input type="date" id="unblockDate" name="unblockDate" required><br><br>
      <button type="submit">Submit</button>
    </form>

    <h2>Change Daily Hours</h2>
    <form id="changeDailyHoursForm" method="POST" onsubmit="changeDailyHoursHandler(event)">
      <label for="changeHoursDate">Date to Change:</label>
      <input type="date" id="changeHoursDate" name="changeHoursDate" required><br><br>
      <label for="updatedStartTime">Start Time:</label>
      <input type="time" id="updatedStartTime" name="updatedStartTime" required><br><br>
      <label for="updatedEndTime">End Time:</label>
      <input type="time" id="updatedEndTime" name="updatedEndTime" required><br><br>
      <button type="submit">Submit</button>
    </form>

      <div id="changeHoursOutput" style="margin-top:20px;"></div>
    
      <h2>Run PHP Scripts</h2>
      <button onclick="callPhp('display_booked_appointments.php')">Display Booked Appointments</button>
      <button onclick="callPhp('display_hours.php')">Display Scheduling Hours</button>
      <div id="output" style="margin-top:20px;"></div>
</body>
<script>
  // Function to call PHP scripts
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
      // Instead of alert, inject result into #changeHoursOutput
      document.getElementById("changeHoursOutput").innerHTML = data;
    })
    .catch(error => {
      document.getElementById("changeHoursOutput").innerHTML = "Error: " + error;
    });
  }

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
      // Instead of alert, inject result into #changeHoursOutput
      document.getElementById("changeHoursOutput").innerHTML = data;
    })
    .catch(error => {
      document.getElementById("changeHoursOutput").innerHTML = "Error: " + error;
    });
  }

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
      // Instead of alert, inject result into #changeHoursOutput
      document.getElementById("changeHoursOutput").innerHTML = data;
    })
    .catch(error => {
      document.getElementById("changeHoursOutput").innerHTML = "Error: " + error;
    });
  }

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
      // Instead of alert, inject result into #changeHoursOutput
      document.getElementById("changeHoursOutput").innerHTML = data;
    })
    .catch(error => {
      document.getElementById("changeHoursOutput").innerHTML = "Error: " + error;
    });
  }

  function callPhp(scriptName) {
      fetch(scriptName)
        .then(response => response.text())
        .then(data => {
          // Instead of alert, inject result into #output
          document.getElementById("output").innerHTML = data;
        })
        .catch(error => {
          document.getElementById("output").innerHTML = "Error: " + error;
        });
  }

  // Function to load upcoming appointments on page load
  document.addEventListener("DOMContentLoaded", function() {
    // Generate an array of the next 14 days
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

    // Fetch appointment data and merge into the days array
    fetch("get_upcoming_appointments.php")
      .then(response => response.json())
      .then(data => {
        if (Array.isArray(data)) {
          data.forEach(app => {
            // Find matching date in the 14-day array
            const match = days.find(d => d.date === app.app_date);
            if (match) {
              match.appointments.push(app);
            }
          });
        }

        // Build the calendar display
        let calendar = '<div style="display: flex; flex-wrap: wrap; gap: 10px;">';
        days.forEach(day => {
          const dateObj = new Date(day.date);
          const dayLabel = dateObj.toLocaleDateString(undefined, { weekday: "short", month: "short", day: "numeric" });

          calendar += `<div style="border: 1px solid #ccc; padding: 10px; width: 160px; text-align: center; border-radius: 8px;">`;
          calendar += `<strong>${dayLabel}</strong><br><small>${day.date}</small><br>`;

          if (day.appointments.length > 0) {
            calendar += `<hr style="margin:6px 0;">`;
            day.appointments.forEach(app => {
              calendar += `
                <div style="margin-bottom: 5px;">
                  <strong>${app.app_time}</strong><br>
                  ${app.email}<br>
                  <small>${app.reason || ""}</small>
                </div>`;
            });
          } else {
            calendar += `<div style="color: #999;">No appointments</div>`;
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
</html>-->

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>IT 168 Tutoring – Admin</title>

  <style>
    :root {
      --isu-red: #cc0000;
      --isu-dark-red: #990000;
      --isu-gray: #f5f5f5;
      --isu-border: #dddddd;
      --isu-text: #222222;
    }

    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: Arial, Helvetica, sans-serif;
      background: linear-gradient(180deg, #ffffff 0%, #fafafa 40%, #f0f0f0 100%);
      color: var(--isu-text);
    }

    /* Header */
    .isu-header {
      display: flex;
      align-items: center;
      gap: 16px;
      padding: 12px 24px;
      background: var(--isu-red);
      border-bottom: 4px solid var(--isu-dark-red);
      color: #ffffff;
    }

    .isu-header img {
      height: 52px;
      width: auto;
    }

    .isu-header-title {
      font-size: 1.5rem;
      font-weight: bold;
      margin: 0;
    }

    .isu-header-subtitle {
      font-size: 0.9rem;
      margin: 2px 0 0;
      opacity: 0.9;
    }

    .isu-header-link a {
      color: #ffffff;
      text-decoration: none;
      font-size: 0.9rem;
      border: 1px solid rgba(255,255,255,0.85);
      padding: 5px 10px;
      border-radius: 4px;
    }

    .isu-header-link a:hover {
      background: rgba(0, 0, 0, 0.18);
    }

    /* Page layout */
    .page {
      max-width: 1100px;
      margin: 24px auto 40px;
      padding: 0 16px;
    }

    .hero {
      background: #ffffff;
      border-radius: 10px;
      padding: 18px 20px;
      margin-bottom: 18px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
      border-left: 5px solid var(--isu-red);
    }

    .hero h1 {
      margin: 0 0 8px;
      font-size: 1.8rem;
      color: var(--isu-red);
    }

    .hero p {
      margin: 4px 0;
      font-size: 0.96rem;
    }

    .grid {
      display: grid;
      grid-template-columns: minmax(0, 2fr) minmax(0, 1.3fr);
      gap: 18px;
    }

    @media (max-width: 900px) {
      .grid {
        grid-template-columns: 1fr;
      }
    }

    .card {
      background: #ffffff;
      border-radius: 10px;
      padding: 16px 18px;
      margin-bottom: 16px;
      border: 1px solid var(--isu-border);
      box-shadow: 0 1px 5px rgba(0, 0, 0, 0.04);
    }

    .card h2 {
      margin-top: 0;
      margin-bottom: 8px;
      font-size: 1.2rem;
      color: var(--isu-dark-red);
    }

    .card p {
      margin: 2px 0 8px;
      font-size: 0.94rem;
    }

    form {
      margin-top: 8px;
      font-size: 0.94rem;
    }

    label {
      display: block;
      font-weight: 600;
      margin-bottom: 4px;
      font-size: 0.9rem;
    }

    input[type="time"],
    input[type="date"],
    select {
      width: 100%;
      padding: 7px 9px;
      margin-bottom: 10px;
      border-radius: 4px;
      border: 1px solid #cccccc;
      font-size: 0.95rem;
    }

    input[type="time"]:focus,
    input[type="date"]:focus,
    select:focus {
      outline: none;
      border-color: var(--isu-red);
      box-shadow: 0 0 0 2px rgba(204,0,0,0.15);
    }

    button {
      background: var(--isu-red);
      color: #ffffff;
      border: none;
      border-radius: 4px;
      padding: 7px 14px;
      font-size: 0.94rem;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.15s ease, transform 0.05s ease;
      margin-top: 4px;
    }

    button:hover {
      background: var(--isu-dark-red);
    }

    button:active {
      transform: translateY(1px);
    }

    .button-secondary {
      background: #ffffff;
      color: var(--isu-red);
      border: 1px solid var(--isu-red);
      margin-right: 6px;
    }

    .button-secondary:hover {
      background: var(--isu-red);
      color: #ffffff;
    }

    #changeHoursOutput,
    #output {
      margin-top: 10px;
      padding: 8px 10px;
      background: var(--isu-gray);
      border-radius: 6px;
      border: 1px solid #e0e0e0;
      font-size: 0.9rem;
      white-space: pre-wrap;
    }

    /* Upcoming appointments mini calendar */
    #upcomingAppointments {
      margin-top: 8px;
    }

    .calendar-wrap {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
    }

    .calendar-day {
      border: 1px solid #cccccc;
      padding: 8px 8px;
      width: 170px;
      border-radius: 8px;
      text-align: left;
      background: #fafafa;
      font-size: 0.88rem;
    }

    .calendar-day strong {
      font-size: 0.95rem;
    }

    .calendar-day small {
      color: #777;
    }

    .calendar-appt {
      margin-top: 5px;
      padding-top: 4px;
      border-top: 1px dashed #dddddd;
      font-size: 0.86rem;
    }

    .calendar-none {
      margin-top: 6px;
      color: #999;
      font-size: 0.86rem;
    }
  </style>
</head>
<body>

  <!-- Header -->
  <header class="isu-header">
    <img src="ISU-Seal.png" alt="Illinois State University Seal">
    <div>
      <p class="isu-header-title">IT 168 Tutoring – Admin</p>
      <p class="isu-header-subtitle">Manage appointments and scheduling · Illinois State University</p>
    </div>
  </header>

  <main class="page">
    <section class="hero">
      <h1>Admin Dashboard</h1>
      <p>Welcome, Admin! Use this page to review upcoming appointments and adjust tutoring availability.</p>
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

      fetch("get_upcoming_appointments.php")
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
