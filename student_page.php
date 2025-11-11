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
?>

<!--<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>IT 168 Tutoring center – Welcome</title>
  <img src="ISU-Seal.png" alt="student seal">
  <meta name="description" content="Illinois State University–style landing page for the IT 168 Tutoring Scheduler." />
</head>
<body style="font-family: Arial, Helvetica, sans-serif;">

  <h2>Tutoring Hours (Home page):<br></h2>
  <div id="tutoringHoursOutput"></div>

  <h1>Appointment Registration</h1>
  </form>
    <h2>Check Appointment Date</h2>
    <form id="dateForm" method="POST" onsubmit="timeHandler(event)">
    <label for="date">Date:</label>
    <input type="date" id="date" name="date" required><br><br>
    <button type="submit">Submit</button>
    </form>
    <div id="buttonContainer" style="margin-top:20px;"></div>

    <h2>Appointment Details</h2>
    <form id="appointmentForm" method="POST" onsubmit="handleSubmit(event)">
    <label for="reason">Reason for Appointment:</label>
    <input type="text" id="reason" name="reason" required><br><br>
      
    <button type="submit">Submit</button>
  </form>

  
<div id="output" style="margin-top:20px;"></div>

  <div id="output" style="margin-top:20px;"></div>

</body>

<script>

// Fetch and display tutoring hours on page load
document.addEventListener('DOMContentLoaded', function() {
  fetch('get_hours.php')
    .then(response => response.json())
    .then(data => {
      console.log("Server returned:", data);

      let hoursHTML = '<ul>';

      // `data` is already an array
      data.forEach(row => {
        // Convert numeric day to weekday name
        const days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
        const dayName = days[parseInt(row.day_of_week) % 7]; // `% 7` ensures it wraps correctly

        // Format hours nicely
        const start = row.start_time === "00:00:00" && row.end_time === "00:00:00"
          ? "Closed"
          : `${row.start_time.slice(0,5)} - ${row.end_time.slice(0,5)}`;

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



  // Create user submission handler
  function userCreationHandler(event) {
   event.preventDefault(); 

    const form = event.target;
    const formData = new FormData(form);

    fetch("addUser.php", {  
    method: "POST",
    body: formData
    })
    .then(response => response.text())
    .then(data => {
      //Using the JSON data returned from addUser.php, show radio buttons for available time slots
      userOutputHTML = data;
      document.getElementById('userOutput').innerHTML = userOutputHTML;
    })
    .catch(error => {
      document.getElementById('userOutput').innerHTML = 'Error: ' + error;
    });
  }

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
      //Using the JSON data returned from get_available.php, show radio buttons for available time slots
      console.log("Available time slots:", data.available_slots); 
      buttonContainerHTML = '';
      for (let i = 0; i < data.available_slots.length; i++) {
        buttonContainerHTML += `<input type=radio name="time_slot" value="${data.available_slots[i]}">${data.available_slots[i]}</input>`;
      }
      document.getElementById('buttonContainer').innerHTML = buttonContainerHTML;
    })
    .catch(error => {
      document.getElementById('buttonContainer').innerHTML = 'Error: ' + error;
    });
  }

  // Handle primary form submission
  function handleSubmit(event) {
      event.preventDefault();

    // Combine data from both forms into one FormData object
    const appointmentForm = document.getElementById('appointmentForm');
    const formData = new FormData(appointmentForm);
    const dateForm = document.getElementById('dateForm');
    const dateFormData = new FormData(dateForm);
    
    // Append date and time to appointment form data
    formData.append('date', dateFormData.get('date'));
    formData.append('time_slot', document.querySelector('input[name="time_slot"]:checked').value);
    const form = appointmentForm;

    // Submit the form via fetch so the page doesn't navigate away
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

// Function to call PHP scripts
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
      
</script>
</html>-->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>IT 168 Tutoring Center – Welcome</title>
  <meta name="description" content="Illinois State University–style landing page for the IT 168 Tutoring Scheduler." />

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

    /* Page container */
    .page {
      max-width: 900px;
      margin: 24px auto 40px;
      padding: 0 16px;
    }

    .hero {
      background: #ffffff;
      border-radius: 10px;
      padding: 20px 20px 16px;
      margin-bottom: 20px;
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
      font-size: 0.95rem;
      line-height: 1.5;
    }

    .card {
      background: #ffffff;
      border-radius: 10px;
      padding: 18px 20px;
      margin-bottom: 16px;
      border: 1px solid var(--isu-border);
      box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
    }

    .card h2 {
      margin-top: 0;
      margin-bottom: 10px;
      font-size: 1.2rem;
      color: var(--isu-dark-red);
    }

    label {
      display: block;
      font-size: 0.9rem;
      margin-bottom: 4px;
      font-weight: 600;
    }

    input[type="date"],
    input[type="text"] {
      width: 100%;
      padding: 8px 10px;
      margin-bottom: 12px;
      border-radius: 4px;
      border: 1px solid #cccccc;
      font-size: 0.95rem;
    }

    input[type="date"]:focus,
    input[type="text"]:focus {
      outline: none;
      border-color: var(--isu-red);
      box-shadow: 0 0 0 2px rgba(204, 0, 0, 0.15);
    }

    button {
      background: var(--isu-red);
      color: #ffffff;
      border: none;
      border-radius: 4px;
      padding: 8px 16px;
      font-size: 0.95rem;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.16s ease, transform 0.05s ease;
    }

    button:hover {
      background: var(--isu-dark-red);
    }

    button:active {
      transform: translateY(1px);
    }

    #tutoringHoursOutput ul {
      list-style: none;
      padding-left: 0;
      margin: 6px 0 0;
    }

    #tutoringHoursOutput li {
      padding: 4px 0;
      font-size: 0.95rem;
      border-bottom: 1px dotted #e0e0e0;
    }

    #tutoringHoursOutput li:last-child {
      border-bottom: none;
    }

    #buttonContainer {
      margin-top: 10px;
    }

    #buttonContainer label {
      font-weight: normal;
      margin-bottom: 4px;
    }

    #output {
      margin-top: 10px;
      font-size: 0.95rem;
      background: var(--isu-gray);
      border-radius: 6px;
      padding: 10px 12px;
      border: 1px solid #e0e0e0;
      white-space: pre-wrap;
    }
  </style>
</head>
<body>

  <!-- Header with ISU seal -->
  <header class="isu-header">
    <img src="ISU-Seal.png" alt="Illinois State University Seal">
    <div>
      <p class="isu-header-title">IT 168 Tutoring Center</p>
      <p class="isu-header-subtitle">School of Information Technology · Illinois State University</p>
      <button onclick="location.href='Session_Cookie/logout.php'">Logout</button>
    </div>
  </header>

  <main class="page">
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

    // Optional helper to call PHP scripts into output (if you want it later)
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
  </script>
</body>
</html>

