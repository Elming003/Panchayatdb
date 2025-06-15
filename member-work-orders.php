<?php
require_once('config/config.php');

// Session check for member role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header("Location: unauthorized.php");
    exit();
}

// Fetch all work orders
$sql = "SELECT * FROM work_orders ORDER BY assigned_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Work Orders - Member</title>
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
        <h2>Work Orders</h2>
        <p>List of all village projects and sanitation work.</p>
    </div>
</section>

<section class="data-section">
    <!-- Add Complaint Button -->
    <div class="container" style="margin-top: 20px; text-align: right;">
        <a href="member-add-work-order.php" class="btn btn-primary">+ Add Work Order</a>
    </div>
    <div class="container">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="card">
                    <h3><?php echo htmlspecialchars($row['project_name']); ?></h3>
                    <p><strong>Status:</strong> <?php echo ucfirst($row['status']); ?></p>
                    <p><strong>Assigned:</strong> <?php echo date('d M Y', strtotime($row['assigned_date'])); ?></p>
                    <?php if ($row['completion_date']): ?>
                        <p><strong>Completed:</strong> <?php echo date('d M Y', strtotime($row['completion_date'])); ?></p>
                    <?php endif; ?>
                    <p><?php echo substr($row['description'], 0, 100) . '...'; ?></p>
                    <div style="margin-top: 10px;">
                        <button class="btn btn-primary" onclick="window.location.href='member-edit-work-order.php?id=<?php echo $row['order_id']; ?>'">Edit</button>
                        <form method="POST" action="member-delete-work-order.php" onsubmit="return confirm('Are you sure you want to delete this work order?');" style="display: inline;">
                            <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                            <button type="submit" class="btn btn-secondary">🗑️ Delete</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="card">
                <p>No work orders found.</p>
            </div>
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
