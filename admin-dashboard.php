<?php
require_once('config/config.php');

// Ensure only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: unauthorized.php");
    exit();
}

// Get admin profile
$user_id = $_SESSION['user_id'];
$sql = "SELECT u.email, ud.first_name, ud.middle_name, ud.last_name, ud.phone_no, ud.gender, ud.profile_picture
        FROM users u
        JOIN user_details ud ON u.user_id = ud.user_id
        WHERE u.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="res/css/member-dashboard.css" rel="stylesheet"> <!-- Reusing the same CSS -->
</head>
<body>

<header>
    <div class="container">
        <div class="logo">
            <h1>Panchayat Admin Dashboard</h1>
        </div>
        <nav>
            <ul>
                <li><a href="home.php">Home</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </div>
</header>

<section class="hero">
    <div class="hero-content">
        <h2>Welcome, <?= $admin ? $admin['first_name'] : 'Admin'; ?>!</h2>
        <p>You are logged in as the system administrator. Manage users, members, and committee roles here.</p>
    </div>
</section>

<section class="data-section">
    <div class="container dashboard-grid">

        <!-- Admin Profile Card -->
        <div class="card profile-card">
            <div class="card-icon">
                <?php if ($admin && $admin['profile_picture']): ?>
                    <img src="<?= $admin['profile_picture']; ?>" alt="Admin Avatar">
                <?php else: ?>
                    <img src="res/image/avatar.svg" alt="Default Avatar">
                <?php endif; ?>
            </div>
            <h3><?= $admin['first_name'] . ' ' . $admin['last_name']; ?></h3>
            <div class="user-info">
                <p><strong>Email:</strong> <?= $admin['email']; ?></p>
                <p><strong>Phone:</strong> <?= $admin['phone_no']; ?></p>
                <p><strong>Gender:</strong> <?= ucfirst($admin['gender']); ?></p>
            </div>
            <button class="btn btn-primary" onclick="window.location.href='admin-edit-profile.php'">Edit Profile</button>
        </div>

        <!-- Users Management -->
        <div class="card complaint-card">
            <h3>All Users</h3>
            <p>View and manage user roles.</p>
            <button class="btn btn-secondary" onclick="window.location.href='admin-manage-users.php'">Users</button>
        </div>

        <!-- Village Representatives -->
        <div class="card complaint-card">
            <h3>Village Representatives</h3>
            <p>View representatives and manage their assigned committee positions.</p>
            <button class="btn btn-secondary" onclick="window.location.href='admin-representatives.php'">Representatives</button>
        </div>

        <!-- Government Schemes -->
        <div class="card complaint-card">
            <h3>Government Schemes</h3>
            <p>Manage and update government schemes available for villagers.</p>
            <button class="btn btn-secondary" onclick="window.location.href='admin-schemes.php'">Manage Schemes</button>
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
