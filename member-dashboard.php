<?php
require_once('config/config.php');

// Check if the user is logged in and has the 'member' role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header("Location: unauthorized.php");
    exit();
}

// Fetch user details
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

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Dashboard</title>
    <link href="res/css/member-dashboard.css" rel="stylesheet"> <!-- Reusing same CSS -->
</head>

<body>

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

<section class="hero">
    <div class="hero-content">
        <h2>Welcome, <?php echo $user_details ? $user_details['first_name'] : 'Member'; ?>!</h2>
        <p>You are logged in as a Panchayat Member. Access your tools and data below.</p>
    </div>
</section>

<section class="data-section">
    <div class="container dashboard-grid">

        <!-- Profile Card -->
        <div class="card profile-card">
            <div class="card-icon">
                <?php if ($user_details && $user_details['profile_picture']): ?>
                    <img src="<?php echo $user_details['profile_picture']; ?>" alt="User Avatar">
                <?php else: ?>
                    <img src="res/image/avatar.svg" alt="User Avatar">
                <?php endif; ?>
            </div>

            <h3><?php echo $user_details ? $user_details['first_name'] . ' ' . $user_details['last_name'] : 'Member'; ?></h3>

            <div class="user-info">
                <p><strong>Email:</strong> <?php echo $user_details['email'] ?? 'Not available'; ?></p>
                <p><strong>Phone Number:</strong> <?php echo $user_details['phone_no'] ?? 'Not available'; ?></p>
                <p><strong>Address:</strong> <?php echo $user_details['address'] ?? 'Not available'; ?></p>
                <p><strong>Date of Birth:</strong> <?php echo $user_details['dob'] ?? 'Not available'; ?></p>
                <p><strong>Gender:</strong> <?php echo ucfirst($user_details['gender'] ?? 'Not available'); ?></p>
            </div>

            <button class="btn btn-primary" onclick="window.location.href='member-edit-profile.php'">Edit Profile</button>
        </div>

        <!-- Work Orders Section -->
        <div class="card complaint-card">
            <h3>Village Work Orders</h3>
            <p>View and track ongoing or completed village work projects.</p>
            <button class="btn btn-secondary" onclick="window.location.href='member-work-orders.php'">Work Orders</button>
        </div>

        <!-- Complaints Overview -->
        <div class="card complaint-card">
            <h3>Complaints Submitted</h3>
            <p>Monitor complaints submitted by villagers.</p>
            <button class="btn btn-secondary" onclick="window.location.href='member-complaints.php'">Complaints</button>
        </div>

        <!-- Notices -->
        <div class="card complaint-card">
            <h3>Village Notices</h3>
            <p>View or post announcements to the village board.</p>
            <button class="btn btn-secondary" onclick="window.location.href='member-notices.php'">Notices</button>
        </div>

    </div>
</section>

<section class="data-section">
    <div class="container dashboard-grid">

        <!-- Schemes -->
        <div class="card complaint-card">
            <h3>Government Shemes</h3>
            <p>View or add schemes to the village record.</p>
            <button class="btn btn-secondary" onclick="window.location.href='member-schemes.php'">Schemes</button>
        </div>

        <!-- People -->
        <div class="card complaint-card">
            <h3>Village People</h3>
            <p>View or manage individual resident records.</p>
            <button class="btn btn-secondary" onclick="window.location.href='member-people.php'">People</button>
        </div>

    </div>
</section>

<footer>
    <div class="container">
        <p>© 2025 Panchayat Dashboard. All Rights Reserved.</p>
    </div>
</footer>

</body>
</html>
