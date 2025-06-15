<?php
require_once('config/config.php');

// Check session for member
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header("Location: unauthorized.php");
    exit();
}

// Fetch all notices
$sql = "SELECT * FROM notices ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notices - Member</title>
    <link href="res/css/general-dashboard.css" rel="stylesheet">
</head>
<body>

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

<section class="hero">
    <div class="hero-content">
        <h2>Village Notices</h2>
        <p>Stay updated with important announcements and events.</p>
    </div>
</section>

<section class="data-section">
    <!-- Add this inside your <section class="data-section"> -->
    <div class="container" style="margin-top: 20px; text-align: right;">
        <a href="member-add-notice.php" class="btn btn-primary">+ Add Notice</a>
    </div>

    <div class="container">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="card">
                    <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                    <p><strong>Category:</strong> <?php echo ucfirst(str_replace('_', ' ', $row['category'])); ?></p>
                    <p><strong>Date:</strong> <?php echo date('d M Y', strtotime($row['created_at'])); ?></p>
                    <p><?php echo nl2br(htmlspecialchars($row['content'])); ?></p>
                    <div style="margin-top: 10px;">
                        <a href="member-edit-notice.php?id=<?php echo $row['notice_id']; ?>" class="btn btn-primary">✏️ Edit</a>
                        <form method="POST" action="member-delete-notice.php" onsubmit="return confirm('Are you sure?');" style="display:inline;">
                            <input type="hidden" name="notice_id" value="<?php echo $row['notice_id']; ?>">
                            <button type="submit" class="btn btn-secondary">🗑️ Delete</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="card"><p>No notices available.</p></div>
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
