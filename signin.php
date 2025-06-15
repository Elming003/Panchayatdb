<?php
  require_once('config/config.php'); // Include database configuration


  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate user inputs
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // Use a prepared statement to prevent SQL injection
    $sql = "SELECT user_id, email, password, role FROM users WHERE email = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);  // "s" means the email is a string
    $stmt->execute();
    $result = $stmt->get_result();

    if (mysqli_num_rows($result) > 0) {
        // User found, now check password
        $row = mysqli_fetch_assoc($result);
        $hash = $row["password"];

        if (password_verify($password, $hash)) {
            // Password is correct, store user information in session
            session_start(); // Ensure session is started

            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['email'] = $row['email'];

            // Redirect based on user role
            switch ($row['role']) {
                case 'admin':
                    header("Location: admin-dashboard.php");
                    break;
                case 'member':
                    header("Location: member-dashboard.php");
                    break;
                case 'general':
                    header("Location: general-dashboard.php");
                    break;
                default:
                    // If role is not recognized, log the user out and ask to try again
                    session_destroy();
                    header("Location: login.php");
                    break;
            }
            exit(); // Stop further script execution after redirect
        } else {
            // Incorrect password
            $error = "Wrong credentials, please try again.";
        }
    } else {
        // No user found with that email
        $error = "Account does not exist.";
    }

    // Close the prepared statement
    $stmt->close();
  }
?>

<!-- HTML Form for login -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="res/css/signin.css">
</head>
<body>
    
    <div class="container">
        <header>Login</header>
        <form id="loginForm" action="" method="POST">
            <div class="form">
                <div class="fields">
                    <div class="input-field">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email" required />
                    </div>
                    <div class="input-field">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required />
                    </div>
                    <button type="submit" class="submitBtn">
                        <span class="btnText">Login</span>
                    </button>
                    <!-- Display error message if any -->
                    <?php if (isset($error)) { echo "<p class='error-message'>$error</p>"; } ?>
                </div>
            </div>
        </form>
        <div class="go-back">
            <a href="home.php" class="go-back-btn">← Back to Home</a>
        </div>
        <div class="signup-link">
            <p>Don't have an account? <a href="signup.php">Sign Up</a></p>
        </div>
    </div>
    <script src="res/js/signin.js"></script>
</body>
</html>
