<?php
require_once('config/config.php');

// Check if the user is logged in and has the 'general' role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'general') {
    // Redirect to the unauthorized page if the user is not a 'general' user
    header("Location: unauthorized.php"); 
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch complaints for the logged-in user
$sql = "SELECT complaint_id, category, description, status, submitted_at 
        FROM complaints 
        WHERE user_id = ? 
        ORDER BY submitted_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$complaints = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $complaints[] = $row;
    }
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Complaints</title>
    <link href="res/css/general-my-complaints.css" rel="stylesheet">
</head>
<body>

<header>
    <div class="container">
        <div class="logo">
            <h1>Panchayat Dashboard</h1>
        </div>
        <nav>
            <ul>
                <li><a href="general-dashboard.php">Dashboard</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </div>
</header>

<section class="hero">
    <div class="hero-content">
        <h2>Your Complaints</h2>
        <p>Track the status of your submitted complaints here.</p>
    </div>
</section>

<section class="data-section">
    <!-- Add Complaint Button -->
    <div class="container" style="margin-top: 20px; text-align: right;">
        <a href="general-add-complaint.php" class="btn btn-primary">+ Add New Complaint</a>
    </div>

    <div class="container">
        <?php if (empty($complaints)): ?>
            <div class="card">
                <p>You haven't submitted any complaints yet.</p>
            </div>
        <?php else: ?>
            <?php foreach ($complaints as $complaint): ?>
                <div class="card">
                    <h3>Complaint #<?php echo $complaint['complaint_id']; ?></h3>
                    <p><strong>Category:</strong> <?php echo ucfirst(str_replace('_', ' ', $complaint['category'])); ?></p>
                    <p><strong>Description:</strong> <?php echo $complaint['description']; ?></p>
                    <p><strong>Status:</strong> 
                        <span class="<?php echo $complaint['status'] === 'resolved' ? 'status-resolved' : 'status-pending'; ?>">
                            <?php echo ucfirst($complaint['status']); ?>
                        </span>
                    </p>
                    <p><strong>Submitted:</strong> <?php echo date("F j, Y, g:i a", strtotime($complaint['submitted_at'])); ?></p>
                </div>
            <?php endforeach; ?>
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
