<?php
require_once('config/config.php');

// Check if the user is logged in and has the 'general' role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'general') {
    // Redirect to the unauthorized page if the user is not a 'general' user
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

// Close the prepared statement
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>General Dashboard</title>
    <link href="res/css/general-dashboard.css" rel="stylesheet">
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
                    <li><a href="logout.php">Logout</a></li>
                    <li><a href="home.php">Home</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h2>Welcome, <?php echo $user_details ? $user_details['first_name'] : 'User'; ?>!</h2>
            <p>Manage your profile and complaints easily here.</p>
        </div>
    </section>

    <!-- Dashboard Content -->
    <section class="data-section">
        <div class="container dashboard-grid">

            <!-- Profile Overview -->
            <div class="card profile-card">
                <div class="card-icon">
                    <?php if ($user_details && $user_details['profile_picture']): ?>
                        <img src="<?php echo $user_details['profile_picture']; ?>" alt="User Avatar" width="60">
                    <?php else: ?>
                        <img src="res/image/avatar.svg" alt="User Avatar" width="60">
                    <?php endif; ?>
                </div>

                <h3><?php echo $user_details ? $user_details['first_name'] . ' ' . $user_details['last_name'] : 'John Doe'; ?></h3>

                <div class="user-info">
                    <p><strong>Email:</strong> <?php echo $user_details['email'] ?? 'Not available'; ?></p>
                    <p><strong>Phone Number:</strong> <?php echo $user_details['phone_no'] ?? 'Not available'; ?></p>
                    <p><strong>Address:</strong> <?php echo $user_details['address'] ?? 'Not available'; ?></p>
                    <p><strong>Date of Birth:</strong> <?php echo $user_details['dob'] ?? 'Not available'; ?></p>
                    <p><strong>Gender:</strong> <?php echo ucfirst($user_details['gender'] ?? 'Not available'); ?></p>
                </div>

                <button class="btn btn-primary" onclick="window.location.href='general-edit-profile.php'">Edit Profile</button>
            </div>

            <!-- Complaints Section -->
            <div class="card complaint-card">
                <h3>Your Complaints</h3>
                <p>Review and submit your complaints here.</p>
                <button class="btn btn-secondary" onclick="window.location.href='general-my-complaints.php'">View Complaints</button>
            </div>

            <!-- Work Orders Section -->
            <div class="card complaint -card">
                <h3>Work Orders</h3>
                <p>Track the progress and status of your submitted work orders.</p>
                <button class="btn btn-secondary" onclick="window.location.href='general-work-orders.php'">View Work Orders</button>
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
