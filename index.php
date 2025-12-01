<!DOCTYPE html>
<html lang="en">
<?php require_once __DIR__ . '/database_load.php'; ?>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>IT 168 Tutoring – Welcome</title>
  <meta name="description" content="Illinois State University–style landing page for the IT 168 Tutoring Scheduler." />
  
  <link rel="stylesheet" href="assets/css/base.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <link rel="stylesheet" href="assets/css/pages.css">
</head>
<body>
  
  <header class="app-header">
    <div class="container header-inner">
      <div class="brand">
        <img src="imgs/isublack.png" alt="Illinois State University" style="height: 48px !important; width: auto !important;">
      </div>
      <nav>
        <a href="login.php" class="btn btn-primary">Login</a>
      </nav>
    </div>
  </header>

  <div class="hero-wrapper">
      <section class="hero-section container">
        <div class="hero-text">
          <h1>Master IT 168 with free peer tutoring</h1>
          <p>Get one‑on‑one help from experienced peer tutors. Simple scheduling. Zero hassle. Built specifically for Redbirds in the School of IT.</p>
          <a class="btn btn-primary btn-lg" href="login.php">Book an Appointment Now</a>
        </div>
        <div class="hero-carousel">
            <img src="imgs/Image1.jpg" class="carousel-img active" alt="Student studying">
            <img src="imgs/Image2.jpg" class="carousel-img" alt="Students collaborating">
            <img src="imgs/Image3.jpg" class="carousel-img" alt="Front Desk">
            <img src="imgs/Image4.jpg" class="carousel-img" alt="Building">
        </div>
      </section>
  </div>

  <main class="container">
    <section class="info-grid mb-4">
        <div class="card">
            <h2>Guidelines & What to Bring</h2>
            <p>To make the most of your session, please adhere to our guidelines:</p>
            <ul style="padding-left: 20px; color: var(--text-main); line-height: 1.7;">
                <li><strong>One Session Per Day:</strong> To ensure availability for all students, you may only book one appointment per day.</li>
                <li><strong>30-Minute Sessions:</strong> All appointments are scheduled for 30-minute blocks. Please arrive on time to make the most of your session.</li>
                <li><strong>Cancellation Policy:</strong> You cannot cancel online within 24 hours of your appointment. In such cases, please contact the IT Tutoring Center directly.</li>
                <li><strong>Come Prepared:</strong> Bring your laptop, your current code, and the specific assignment prompt. Tutors are here to guide you, not to do the work for you.</li>
            </ul>
        </div>

        <div class="card">
            <h2>Weekly Tutoring Hours</h2>
            <div id="tutoringHoursOutput" style="margin-top: 15px;">Loading tutoring hours...</div>
        </div>
    </section>
  </main>

  <footer style="background: white; border-top: 1px solid var(--border-color); padding: 40px 0; margin-top: auto;">
    <div class="container text-center">
      <p style="font-size: 0.9rem; color: #999;">&copy; <?php echo date("Y"); ?> Illinois State University – IT 168 Tutoring Center</p>
    </div>
  </footer>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      fetch('get_hours.php')
        .then(response => response.json())
        .then(data => {
          let hoursHTML = '<ul style="list-style: none; padding: 0; margin:0;">';
          data.forEach(row => {
            const days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
            const dayName = days[parseInt(row.day_of_week) % 7];
            const start = row.start_time === "00:00:00" && row.end_time === "00:00:00"
              ? "<span style='color:#ccc; font-weight:500;'>Closed</span>"
              : `<span>${row.start_time.slice(0,5)} - ${row.end_time.slice(0,5)}</span>`;
            hoursHTML += `<li style="padding: 10px 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items:center;">
                            <strong style="color: var(--secondary);">${dayName}</strong> 
                            ${start}
                          </li>`;
          });
          hoursHTML += '</ul>';
          document.getElementById('tutoringHoursOutput').innerHTML = hoursHTML;
        })
        .catch(error => { document.getElementById('tutoringHoursOutput').innerHTML = 'Error loading hours.'; });
    });

    document.addEventListener("DOMContentLoaded", function() {
        const slides = document.querySelectorAll('.hero-carousel .carousel-img');
        let currentSlide = 0;
        if (slides.length > 1) {
            setInterval(() => {
                slides[currentSlide].classList.remove('active');
                currentSlide = (currentSlide + 1) % slides.length;
                slides[currentSlide].classList.add('active');
            }, 4000);
        }
    });
  </script>
</body>
</html>