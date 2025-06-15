<?php
require_once('config/config.php');

// Check if the user is logged in and has the 'general' role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'general') {
    // Redirect to the unauthorized page if the user is not a 'general' user
    header("Location: unauthorized.php"); 
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = $_POST['category'];
    $description = trim($_POST['description']);

    if (!empty($category) && !empty($description)) {
        $sql = "INSERT INTO complaints (user_id, category, description) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $user_id, $category, $description);

        if ($stmt->execute()) {
            header("Location: general-my-complaints.php");
            exit();
        } else {
            $message = "Failed to submit complaint. Please try again.";
        }

        $stmt->close();
    } else {
        $message = "Please fill in all required fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Complaint</title>
    <link href="res/css/general-add-complaint.css" rel="stylesheet">
</head>
<body>

<header>
    <div class="container">
        <div class="logo">
            <h1>Panchayat Dashboard</h1>
        </div>
        <nav>
            <ul>
                <li><a href="general-my-complaints.php">Back to My Complaints</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </div>
</header>

<section class="hero">
    <div class="hero-content">
        <h2>Submit a Complaint</h2>
        <p>Let us know the issue you're facing in your locality.</p>
    </div>
</section>

<section class="data-section">
    <div class="container">
        <?php if (!empty($message)): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="card">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="category">Complaint Category:</label>
                    <select name="category" id="category" required>
                        <option value="">-- Select Category --</option>
                        <option value="road_repair">Road Repair</option>
                        <option value="sanitation">Sanitation</option>
                        <option value="electric_issue">Electric Issue</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="description">Complaint Description:</label>
                    <textarea name="description" id="description" rows="6" placeholder="Describe your issue in detail..." required></textarea>
                </div>

                <div class="form-group" style="text-align: right; margin-top: 20px;">
                    <button type="submit" class="btn btn-primary">🚀 Submit Complaint</button>
                </div>
            </form>
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
