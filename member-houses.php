<?php
require_once('config/config.php');
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header("Location: unauthorized.php");
    exit();
}

$result = $conn->query("SELECT * FROM houses ORDER BY house_no ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Manage Houses</title>
    <link href="res/css/general-dashboard.css" rel="stylesheet">
</head>
<body>

<header>
    <div class="container">
        <div class="logo"><h1>Panchayat Dashboard</h1></div>
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
        <h2>Village Houses</h2>
        <p>Track and manage household information.</p>
    </div>
</section>

<section class="data-section">
    <div class="container" style="text-align: right; margin-bottom: 10px;">
        <a href="member-add-house.php" class="btn btn-primary">+ Add House</a>
    </div>
    <div class="container">
    <?php if ($result->num_rows > 0): ?>
        <?php while ($house = $result->fetch_assoc()): ?>
            <div class="card">
                <h3>House No: <?php echo htmlspecialchars($house['house_no']); ?></h3>
                <p><strong>Owner:</strong> <?php echo htmlspecialchars($house['owner_name']); ?></p>
                <p><strong>Members:</strong> <?php echo $house['number_of_members']; ?></p>

                <div style="margin-top: 10px;">
                    <a href="member-edit-house.php?id=<?php echo $house['house_id']; ?>" class="btn btn-primary">✏️ Edit</a>
                    <form method="POST" action="member-delete-house.php" onsubmit="return confirm('Are you sure you want to delete this house?');" style="display:inline;">
                        <input type="hidden" name="house_id" value="<?php echo $house['house_id']; ?>">
                        <button type="submit" class="btn btn-secondary">🗑️ Delete</button>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="card"><p>No houses available.</p></div>
    <?php endif; ?>
</div>

</section>

<footer>
    <div class="container"><p>© 2025 Panchayat Dashboard. All Rights Reserved.</p></div>
</footer>
</body>
</html>
