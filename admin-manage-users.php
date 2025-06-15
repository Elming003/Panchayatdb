<?php
require_once('config/config.php');
session_start();

// Only admins allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: unauthorized.php");
    exit();
}

// Handle role update logic in same file
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['role'])) {
    $user_id = intval($_POST['user_id']);
    $new_role = $_POST['role'];

    $valid_roles = ['general', 'member'];
    if (!in_array($new_role, $valid_roles)) {
        $_SESSION['error_user'] = $user_id;
    } else {
        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE user_id = ?");
        $stmt->bind_param("si", $new_role, $user_id);

        if ($stmt->execute()) {
            $_SESSION['updated_user'] = $user_id;
        } else {
            $_SESSION['error_user'] = $user_id;
        }
        $stmt->close();
    }

    // Redirect to prevent form resubmission
    header("Location: admin-manage-users.php");
    exit();
}

// Fetch all non-admin users
$sql = "SELECT 
            u.user_id,
            u.email,
            u.role,
            ud.first_name,
            ud.middle_name,
            ud.last_name,
            ud.phone_no,
            ud.gender,
            ud.profile_picture
        FROM users u
        JOIN user_details ud ON u.user_id = ud.user_id
        WHERE u.role != 'admin'
        ORDER BY u.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Manage User Roles</title>
    <link href="res/css/member-complaints.css" rel="stylesheet">
    <style>
        .card {
            position: relative;
            padding-left: 80px;  /* Make space for the profile picture */
            text-align: left;
            display: inline-block;
            vertical-align: top;
            width: 31%;
            margin: 1%;
            min-width: 300px;  /* Ensure cards don't get too narrow */
        }
        .profile-image {
            position: absolute;
            left: 20px;
            top: 20px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            overflow: hidden;
            border: 1px solid var(--primary-color);
        }
        .profile-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        /* Container styles for better card alignment */
        .cards-container {
            text-align: center;  /* Center cards in container */
            margin: -1%;  /* Offset the card margins */
            font-size: 0;  /* Remove space between inline-block elements */
        }
        .cards-container .card {
            font-size: 1rem;  /* Restore font size for cards */
        }
        /* Responsive adjustments */
        @media (max-width: 1200px) {
            .card {
                width: 47%;  /* 2 cards per row on medium screens */
            }
        }
        @media (max-width: 768px) {
            .card {
                width: 98%;  /* 1 card per row on small screens */
            }
        }
        /* Success/Error messages */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            text-align: center;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>

<header>
    <div class="container">
        <div class="logo">
            <h1>Panchayat Dashboard</h1>
        </div>
        <nav>
            <ul>
                <li><a href="admin-dashboard.php">Dashboard</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </div>
</header>

<section class="hero">
    <div class="hero-content">
        <h2>Manage Users</h2>
        <p>Promote users to members or keep them as general users.</p>
    </div>
</section>

<section class="data-section">
    <div class="container">
        <?php if (isset($_SESSION['delete_success'])): ?>
            <div class="alert alert-success">
                <?php 
                    echo $_SESSION['delete_success'];
                    unset($_SESSION['delete_success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['delete_error'])): ?>
            <div class="alert alert-error">
                <?php 
                    echo $_SESSION['delete_error'];
                    unset($_SESSION['delete_error']);
                ?>
            </div>
        <?php endif; ?>

        <?php if ($result->num_rows > 0): ?>
            <div class="cards-container">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="card">
                        <div class="profile-image">
                            <?php if (!empty($row['profile_picture'])): ?>
                                <img src="<?= htmlspecialchars($row['profile_picture']); ?>" alt="Profile Picture">
                            <?php else: ?>
                                <img src="res/image/avatar.svg" alt="Default Avatar">
                            <?php endif; ?>
                        </div>
                        <h3><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></h3>
                        <p><strong>Email:</strong> <?= htmlspecialchars($row['email']) ?></p>
                        <p><strong>Phone:</strong> <?= htmlspecialchars($row['phone_no']) ?></p>
                        <p><strong>Gender:</strong> <?= ucfirst($row['gender']) ?></p>
                        <p><strong>Current Role:</strong> <?= ucfirst($row['role']) ?></p>

                        <!-- Success/Failure Message Below This Row -->
                        <?php if (isset($_SESSION['updated_user']) && $_SESSION['updated_user'] == $row['user_id']): ?>
                            <div style="background-color: #d4edda; color: #155724; padding: 10px; margin: 10px 0; border-radius: 5px; border: 1px solid #c3e6cb;">
                                ✅ Role updated successfully!
                            </div>
                        <?php elseif (isset($_SESSION['error_user']) && $_SESSION['error_user'] == $row['user_id']): ?>
                            <div style="background-color: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border-radius: 5px; border: 1px solid #f5c6cb;">
                                ❌ Failed to update role.
                            </div>
                        <?php endif; ?>

                        <div class="button-group" style="margin-top: 15px;">
                            <!-- Role Update Form -->
                            <form method="POST" action="admin-manage-users.php" style="display: inline-block; margin-right: 10px;">
                                <input type="hidden" name="user_id" value="<?= $row['user_id'] ?>">
                                <select name="role" required style="margin-right: 5px;">
                                    <option value="">--Select Role--</option>
                                    <option value="general" <?= $row['role'] === 'general' ? 'selected' : '' ?>>General</option>
                                    <option value="member" <?= $row['role'] === 'member' ? 'selected' : '' ?>>Member</option>
                                </select>
                                <button type="submit" class="btn btn-primary">Update Role</button>
                            </form>

                            <!-- Delete User Form -->
                            <form method="POST" action="admin-delete-user.php" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                <input type="hidden" name="user_id" value="<?= $row['user_id'] ?>">
                                <button type="submit" class="btn btn-primary">Delete User</button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>

                <!-- Clear the session flash messages -->
                <?php unset($_SESSION['updated_user'], $_SESSION['error_user']); ?>
            </div>
        <?php else: ?>
            <div class="card"><p>No users found.</p></div>
        <?php endif; ?>
    </div>
</section>

<footer>
    <div class="container">
        <p>© 2025 Panchayat Dashboard. All Rights Reserved.</p>
    </div>
</footer>

</body>
</html>
