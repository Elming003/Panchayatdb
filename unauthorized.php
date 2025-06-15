<?php
// Start session to check if the user is logged in
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unauthorized</title>
    <link rel="stylesheet" href="res/css/unauthorized.css"> <!-- Optional for styling -->
</head>
<body>
    <div class="container">
        <header>You are not authorized to view this page</header>
        <p>You do not have the necessary permissions to access this page. Please contact the administrator if you believe this is a mistake.</p>
        <div class="go-back">
            <a href="home.php" class="go-back-btn">← Back to Home</a>
        </div>
    </div>
</body>
</html>
