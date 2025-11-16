<!DOCTYPE html>
<html lang="en">
<?php require_once __DIR__ . '/database_load.php'; ?>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>IT 168 Tutoring – Welcome</title>
  <meta name="description" content="Illinois State University–style landing page for the IT 168 Tutoring Scheduler." />
  <link rel="stylesheet" href="index.css">
  
</head>
<body>
  <header class="topbar" role="banner">
    <div class="brand" aria-label="Illinois State University – IT 168 Tutoring">
      <img class="isu-logo" src="imgs/isublack.png" alt="Illinois State University" style="height: 60px;">
    </div>
    <nav class="nav" aria-label="Primary">
      <!--<a href="#about">About</a>
      <a href="#how">How it works</a>-->
      <a href="Email_Login_Feature/public/index.php" class="cta" aria-label="Log in and go to admin/student">Login</a>
    </nav>
  </header>

  <main>
    <section class="hero" aria-labelledby="heroTitle">
      <div>
        <h1 id="heroTitle" class="headline">Master IT 168 with free tutoring</h1>
        <p class="subhead">Get one‑on‑one help from peer tutors. Simple scheduling. Zero hassle. Built for Redbirds.</p>
        <a class="cta" href="Email_Login_Feature/public/index.php" role="button">Book Now</a>
      </div>
      <div class="gallery" aria-label="Campus and tutoring photos">
        <figure class="card">
          <img src="imgs/Image1.jpg" alt="Illinois State campus photo" loading="lazy" />
        </figure>
        <figure class="card">
          <img src="imgs/Image2.jpg" alt="Student tutoring session" loading="lazy" />
        </figure>
      </div>
    </section>

    <!--<section id="about" class="info" aria-label="About tutoring">
      <article class="tile">
        <div class="eyebrow">Focus</div>
        <h3>IT 168 Success</h3>
        <p>Targeted sessions to help you nail fundamentals and labs without stress.</p>
      </article>-->
      <article class="tile">
        <div class="eyebrow">Flexible</div>
        <h3>Pick your time</h3>
        <p>Choose an open slot that fits your week. Cancel up to 24 hours before.</p>
        <div class="tile" id="tutoringHoursOutput">Loading tutoring hours...</div>
      </article>
      <!--<article class="tile">
        <div class="eyebrow">Fast</div>
        <h3>2‑click booking</h3>
        <p>See availability instantly and confirm in seconds—no emails back‑and‑forth.</p>-->
      </article>
    </section>
  </main>

  <footer>
    <div> Illinois State University – IT 168 Tutoring Center</div>
  </footer>

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
</script>
</html>
