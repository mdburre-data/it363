
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo Page</title>
</head>
<body>
    <?php
    $_SESSION['token'] = 'DemoUser'; // Example user data
    include 'auth.php'; // Include authentication check
  ?>
    <h1>Welcome to the Demo Page</h1>
    <p>You are successfully authenticated!</p>
    <button onclick="window.location.href='logout.php'">Logout</button>
</body>
</html>