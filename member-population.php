<?php
require_once('config/config.php');
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header("Location: unauthorized.php");
    exit();
}

$result = $conn->query("SELECT * FROM population ORDER BY last_updated DESC LIMIT 1");
$population = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Village Population</title>
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
                <li><a href="home.php">Home</a></li>
            </ul>
        </nav>
    </div>
</header>

<section class="hero">
    <div class="hero-content">
        <h2>Population Statistics</h2>
        <p>Latest statistics about the village.</p>
    </div>
</section>

<section class="data-section">
    <div class="container" style="text-align:right; margin-bottom: 10px;">
        <a href="member-update-population.php" class="btn btn-primary">Update Population</a>
    </div>

    <div class="container">
        <?php if ($population): ?>
            <div class="card">
                <p><strong>Total Population:</strong> <?php echo $population['total_population']; ?></p>
                <p><strong>Above 18:</strong> <?php echo $population['population_above_18']; ?></p>
                <p><strong>Below 18:</strong> <?php echo $population['population_below_18']; ?></p>
                <p><strong>Number of Houses:</strong> <?php echo $population['number_of_houses']; ?></p>
                <p><strong>Last Updated:</strong> <?php echo date("d M Y", strtotime($population['last_updated'])); ?></p>
            </div>
        <?php else: ?>
            <div class="card"><p>No population data available.</p></div>
        <?php endif; ?>
    </div>
</section>

<footer><div class="container"><p>© 2025 Panchayat Dashboard. All Rights Reserved.</p></div></footer>
</body>
</html>
