<?php
require_once('config/config.php');

$error = null;
$success = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Retrieve form data safely
    $first_name = mysqli_real_escape_string($conn, $_POST['firstName']);
    $middle_name = mysqli_real_escape_string($conn, $_POST['middleName']);
    $last_name = mysqli_real_escape_string($conn, $_POST['lastName']);
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone_no = mysqli_real_escape_string($conn, $_POST['phoneNumber']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirmPassword'];
    $role = 'general';

    // Basic validation
    if (empty($first_name) || empty($last_name) || empty($dob) || empty($gender) || empty($email) || empty($phone_no) || empty($password) || empty($confirm_password) || empty($role)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "An account with this email already exists.";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Start transaction
            mysqli_begin_transaction($conn);
            try {
                // Insert user (for login purposes)
                $stmt = $conn->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $email, $hashed_password, $role);
                if (!$stmt->execute()) {
                    throw new Exception("Error inserting into users table.");
                }
                $user_id = $stmt->insert_id;

                // Insert user details
                $stmt = $conn->prepare("INSERT INTO user_details (user_id, first_name, middle_name, last_name, dob, phone_no, address, gender) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("isssssss", $user_id, $first_name, $middle_name, $last_name, $dob, $phone_no, $address, $gender);
                if (!$stmt->execute()) {
                    throw new Exception("Error inserting into user_details table.");
                }

                mysqli_commit($conn);
                $success = "Registration successful. Redirecting to login...";
                header("refresh:2;url=signin.php");
            } catch (Exception $e) {
                mysqli_rollback($conn);
                $error = $e->getMessage();
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Registration</title>
    <link rel="stylesheet" href="res/css/signup.css" />
    <link
      rel="stylesheet"
      href="https://unicons.iconscout.com/release/v4.0.0/css/line.css"
    />
  </head>
  <body>
    <div class="container">
      <header>Registration</header>
      <form id="registrationForm" action="" method="POST">
        <div class="form">
          <div class="details">
            <span class="title">User Details</span>
            <div class="fields">

              <div class="input-field">
                <label for="firstName">First Name</label>
                <input
                  type="text"
                  id="firstName"
                  name="firstName"
                  placeholder="Enter first name"
                  required
                />
                <span class="error-message"></span>
              </div>

              <div class="input-field">
                <label for="middleName">Middle Name</label>
                <input
                  type="text"
                  id="middleName"
                  name="middleName"
                  placeholder="Enter middle name"
                />
                <span class="error-message"></span>
              </div>

              <div class="input-field">
                <label for="lastName">Last Name</label>
                <input
                  type="text"
                  id="lastName"
                  name="lastName"
                  placeholder="Enter last name"
                  required
                />
                <span class="error-message"></span>
              </div>

              <div class="input-field">
                <label for="dob">Date of Birth</label>
                <input
                  type="date"
                  id="dob"
                  name="dob"
                  required
                />
                <span class="error-message"></span>
              </div>

              <div class="input-field">
                <label for="email">Email</label>
                <input
                  type="email"
                  id="email"
                  name="email"
                  placeholder="Enter your email"
                  required
                />
                <span class="error-message"></span>
              </div>

              <div class="input-field">
                <label for="gender">Gender</label>
                <select id="gender" name="gender" required>
                  <option value="male">Male</option>
                  <option value="female">Female</option>
                  <option value="other">Other</option>
                </select>
                <span class="error-message"></span>
              </div>

              <div class="input-field">
                <label for="phoneNumber">Phone Number</label>
                <input
                  type="text"
                  id="phoneNumber"
                  name="phoneNumber"
                  placeholder="Enter phone number"
                  required
                />
                <span class="error-message"></span>
              </div>

              <div class="input-field">
                <label for="password">Password</label>
                <input
                  type="password"
                  id="password"
                  name="password"
                  placeholder="Enter password"
                  required
                />
                <span class="error-message"></span>
              </div>

              <div class="input-field">
                <label for="confirmPassword">Confirm Password</label>
                <input
                  type="password"
                  id="confirmPassword"
                  name="confirmPassword"
                  placeholder="Confirm password"
                  required
                />
                <span class="error-message"></span>
              </div>

              <div class="input-field">
                <label for="address">Address</label>
                <textarea
                  id="address"
                  name="address"
                  placeholder="Enter your address"
                  required
                ></textarea>
                <span class="error-message"></span>
              </div>

            </div>

            <button type="submit" class="submitBtn">
              <span class="btnText">Register</span>
              <i class="uil uil-user-check"></i>
            </button>

            <span class="error-message"><?php echo $error; ?></span>
            <?php if ($success) { echo "<p class='success-message'>$success</p>"; } ?>

            <div class="go-back">
              <a href="home.php" class="go-back-btn">← Back to Home</a>
            </div>

            <div class="login-link">
              <p>Already have an account? <a href="signin.php">Sign In</a></p>
            </div>

          </div>
        </div>
      </form>
    </div>
    <script src="res/js/signup.js"></script>
  </body>
</html>
