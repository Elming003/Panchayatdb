<?php
require_once('config/config.php');

// Session check for member role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header("Location: unauthorized.php");
    exit();
}

// Fetch all complaints and who submitted them
$sql = "SELECT 
            c.*,
            u.email,
            d.first_name,
            d.middle_name,
            d.last_name,
            d.phone_no
        FROM complaints c
        JOIN users u ON c.user_id = u.user_id
        JOIN user_details d ON c.user_id = d.user_id
        ORDER BY c.submitted_at DESC;
        ";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Complaints - Member</title>
    <link href="res/css/member-complaints.css" rel="stylesheet">
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
                <li><a href="Home.php">Home</a></li>
            </ul>
        </nav>
    </div>
</header>

<section class="hero">
    <div class="hero-content">
        <h2>All Complaints</h2>
        <p>Review all complaints raised by members in your village.</p>
    </div>
</section>



    <div class="container">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="card">
                <h3><?php echo ucfirst(str_replace("_", " ", $row['category'])); ?></h3>
                    <p><strong>Raised by:</strong> 
                        <?php echo htmlspecialchars($row['first_name'] . ' ' . ($row['middle_name'] ? $row['middle_name'] . ' ' : '') . $row['last_name']); ?>
                    </p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($row['email']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($row['phone_no']); ?></p>

                    <p><strong>Status:</strong> <?php echo ucfirst($row['status']); ?></p>
                    <p><strong>Submitted:</strong> <?php echo date('d M Y, h:i A', strtotime($row['submitted_at'])); ?></p>
                    <p><?php echo nl2br(htmlspecialchars(substr($row['description'], 0, 100))) . '...'; ?></p>
                    <div style="margin-top: 10px;">
                    <form method="POST" action="member-update-complaint-status.php" style="display: inline-flex; align-items: center; gap: 10px;">
                        <input type="hidden" name="complaint_id" value="<?php echo $row['complaint_id']; ?>">
                        <select name="status" required>
                            <option value="pending" <?php if ($row['status'] === 'pending') echo 'selected'; ?>>Pending</option>
                            <option value="resolved" <?php if ($row['status'] === 'resolved') echo 'selected'; ?>>Resolved</option>
                        </select>
                        <button type="submit" class="btn btn-primary">✅ Update</button>
                    </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="card">
                <p>No complaints found.</p>
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
