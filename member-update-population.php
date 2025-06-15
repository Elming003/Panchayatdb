<?php
require_once('config/config.php');
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header("Location: unauthorized.php");
    exit();
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $total = (int)$_POST['total_population'];
    $above = (int)$_POST['population_above_18'];
    $below = (int)$_POST['population_below_18'];
    $houses = (int)$_POST['number_of_houses'];

    if ($total && $above && $below && $houses) {
        $stmt = $conn->prepare("INSERT INTO population (total_population, population_above_18, population_below_18, number_of_houses) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiii", $total, $above, $below, $houses);
        if ($stmt->execute()) {
            header("Location: member-population.php");
            exit();
        } else {
            $message = "Failed to update population.";
        }
    } else {
        $message = "Please fill all fields correctly.";
    }
}

// Calculate population from user_details table
$population_sql = "SELECT 
    COUNT(*) as total_count,
    SUM(CASE WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) >= 18 THEN 1 ELSE 0 END) as above_18,
    SUM(CASE WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) < 18 THEN 1 ELSE 0 END) as below_18
FROM user_details";
$population_result = $conn->query($population_sql);
$population_counts = $population_result->fetch_assoc();

// Get latest population record
$latest_sql = "SELECT * FROM population ORDER BY last_updated DESC LIMIT 1";
$latest_result = $conn->query($latest_sql);
$latest_population = $latest_result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Update Population</title>
    <link href="res/css/member-edit-work-order.css" rel="stylesheet">
</head>
<body>

<header>
    <div class="container">
        <div class="logo"><h1>Panchayat Dashboard</h1></div>
        <nav>
            <ul>
                <li><a href="member-population.php">Back to Population</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </div>
</header>

<section class="hero">
    <div class="hero-content">
        <h2>Update Village Population</h2>
        <p>Current registered population: <?= number_format($population_counts['total_count']) ?> people</p>
    </div>
</section>

<section class="data-section">
    <div class="container">
        <?php if ($message): ?><div class="message"><?php echo $message; ?></div><?php endif; ?>
        <form method="POST" class="card">
            <div class="form-group">
                <label>Total Population</label>
                <input type="number" name="total_population" value="<?= $population_counts['total_count'] ?>" required>
                <small>Suggested value based on registered users</small>
            </div>
            <div class="form-group">
                <label>Population Above 18</label>
                <input type="number" name="population_above_18" value="<?= $population_counts['above_18'] ?>" required>
                <small>Suggested value based on registered users</small>
            </div>
            <div class="form-group">
                <label>Population Below 18</label>
                <input type="number" name="population_below_18" value="<?= $population_counts['below_18'] ?>" required>
                <small>Suggested value based on registered users</small>
            </div>
            <div class="form-group">
                <label>Number of Houses</label>
                <input type="number" name="number_of_houses" value="<?= $latest_population ? $latest_population['number_of_houses'] : 0 ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
</section>

<footer><div class="container"><p>© 2025 Panchayat Dashboard. All Rights Reserved.</p></div></footer>
</body>
</html>
