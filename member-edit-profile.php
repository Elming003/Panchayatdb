<?php
require_once('config/config.php');

// Check if the user is logged in and has the 'member' role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    // Redirect to the unauthorized page if the user is not a 'member' user
    header("Location: unauthorized.php"); 
    exit();
}

// Fetch user details from the database
$user_id = $_SESSION['user_id'];
$sql = "SELECT u.email, ud.first_name, ud.middle_name, ud.last_name, ud.dob, ud.phone_no, ud.gender, ud.address, ud.profile_picture
        FROM users u
        JOIN user_details ud ON u.user_id = ud.user_id
        WHERE u.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user_details = $result->fetch_assoc();
} else {
    $user_details = null;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the form data
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $dob = $_POST['dob'];
    $phone_no = $_POST['phone_no'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    
    // Profile picture upload
    $profile_picture = $user_details['profile_picture'];
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $file_name = $_FILES['profile_picture']['name'];
        $file_tmp = $_FILES['profile_picture']['tmp_name'];
        $target_dir = "res/image/uploads";
        $target_file = $target_dir . basename($file_name);
        
        // Check if the file is an image
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        if (in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) {
            if (move_uploaded_file($file_tmp, $target_file)) {
                $profile_picture = $target_file;
            }
        }
    }

    // Update user details in the database
    $update_sql = "UPDATE user_details SET first_name = ?, middle_name = ?, last_name = ?, dob = ?, phone_no = ?, gender = ?, address = ?, profile_picture = ? WHERE user_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssssssssi", $first_name, $middle_name, $last_name, $dob, $phone_no, $gender, $address, $profile_picture, $user_id);
    
    if ($update_stmt->execute()) {
        header("Location: member-dashboard.php");
    } else {
        $message = "Failed to update profile. Please try again.";
    }
    
    // Close the prepared statement
    $update_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link href="res/css/member-edit-profile.css" rel="stylesheet">
</head>

<body>

    <!-- Header -->
    <header>
        <div class="container">
            <div class="logo">
                <h1>Panchayat Dashboard</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="member-dashboard.php">Dashboard</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h2>Edit Your Profile</h2>
            <p>Update your personal details below.</p>
        </div>
    </section>

    <!-- Dashboard Content -->
    <section class="data-section">
        <div class="container">
            <?php if (isset($message)): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <!-- Profile Edit Form -->
            <div class="card">
                <form action="member-edit-profile.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="first_name">First Name:</label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo $user_details ? $user_details['first_name'] : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="middle_name">Middle Name:</label>
                        <input type="text" id="middle_name" name="middle_name" value="<?php echo $user_details ? $user_details['middle_name'] : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name:</label>
                        <input type="text" id="last_name" name="last_name" value="<?php echo $user_details ? $user_details['last_name'] : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="dob">Date of Birth:</label>
                        <input type="date" id="dob" name="dob" value="<?php echo $user_details ? $user_details['dob'] : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="phone_no">Phone Number:</label>
                        <input type="text" id="phone_no" name="phone_no" value="<?php echo $user_details ? $user_details['phone_no'] : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="gender">Gender:</label>
                        <select id="gender" name="gender" required>
                            <option value="male" <?php echo $user_details['gender'] == 'male' ? 'selected' : ''; ?>>Male</option>
                            <option value="female" <?php echo $user_details['gender'] == 'female' ? 'selected' : ''; ?>>Female</option>
                            <option value="other" <?php echo $user_details['gender'] == 'other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="address">Address:</label>
                        <textarea id="address" name="address" required><?php echo $user_details ? $user_details['address'] : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="profile_picture">Profile Picture:</label>
                        <input type="file" id="profile_picture" name="profile_picture">
                    </div>

                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>© 2025 Panchayat Dashboard. All Rights Reserved.</p>
        </div>
    </footer>

</body>

</html>
