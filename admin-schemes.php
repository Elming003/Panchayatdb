<?php
require_once('config/config.php');

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: unauthorized.php");
    exit();
}

// Fetch all schemes
$sql = "SELECT * FROM schemes ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Schemes - Admin</title>
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
                <li><a href="admin-dashboard.php">Dashboard</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </div>
</header>

<section class="hero">
    <div class="hero-content">
        <h2>Government Schemes</h2>
        <p>Manage and update government schemes available for villagers</p>
    </div>
</section>

<section class="data-section">
    <div class="container" style="margin-top: 20px; text-align: right;">
        <a href="admin-add-scheme.php" class="btn btn-primary">+ Add New Scheme</a>
    </div>

    <div class="container">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="card">
                    <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                    <p><strong>Department:</strong> <?php echo htmlspecialchars($row['department']); ?></p>
                    <p><strong>Status:</strong> 
                        <span style="color: <?php echo $row['status'] === 'active' ? '#4caf50' : '#f44336'; ?>;">
                            <?php echo ucfirst($row['status']); ?>
                        </span>
                    </p>
                    <p><strong>Start Date:</strong> <?php echo date('d M Y', strtotime($row['start_date'])); ?></p>
                    <?php if ($row['end_date']): ?>
                        <p><strong>End Date:</strong> <?php echo date('d M Y', strtotime($row['end_date'])); ?></p>
                    <?php endif; ?>
                    <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars(substr($row['description'], 0, 150))) . '...'; ?></p>
                    
                    <div style="margin-top: 15px;">
                        <a href="admin-edit-scheme.php?id=<?php echo $row['scheme_id']; ?>" class="btn btn-primary">✏️ Edit</a>
                        <form method="POST" action="admin-delete-scheme.php" onsubmit="return confirm('Are you sure you want to delete this scheme?');" style="display:inline;">
                            <input type="hidden" name="scheme_id" value="<?php echo $row['scheme_id']; ?>">
                            <button type="submit" class="btn btn-secondary">🗑️ Delete</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="card"><p>No schemes available.</p></div>
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