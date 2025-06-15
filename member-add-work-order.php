<?php
require_once('config/config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header("Location: unauthorized.php");
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_name = trim($_POST['project_name']);
    $description = trim($_POST['description']);
    $status = $_POST['status'];
    $completion_date = !empty($_POST['completion_date']) ? $_POST['completion_date'] : NULL;

    if (!empty($project_name) && !empty($description) && !empty($status)) {
        $stmt = $conn->prepare("INSERT INTO work_orders (project_name, description, status, assigned_date, completion_date) VALUES (?, ?, ?, NOW(), ?)");
        $stmt->bind_param("ssss", $project_name, $description, $status, $completion_date);
        $stmt->execute();

        header("Location: member-work-orders.php");
        exit();
    } else {
        $message = "Please fill in all required fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Work Order</title>
    <link href="res/css/member-add-work-order.css" rel="stylesheet">
</head>
<body>

<header>
    <div class="container">
        <div class="logo">
            <h1>Panchayat Dashboard</h1>
        </div>
        <nav>
            <ul>
                <li><a href="member-work-orders.php">Back to Work Orders</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </div>
</header>

<section class="hero">
    <div class="hero-content">
        <h2>Create New Work Order</h2>
        <p>Assign a new project for village maintenance and development.</p>
    </div>
</section>

<section class="data-section">
    <div class="container">
        <?php if (!empty($message)): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="card">
            <form method="POST">
                <div class="form-group">
                    <label for="project_name">Project Name</label>
                    <input type="text" id="project_name" name="project_name" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="6" placeholder="Enter project details..." required></textarea>
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        <option value="">-- Select Status --</option>
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="completion_date">Expected Completion Date (optional)</label>
                    <input type="date" id="completion_date" name="completion_date">
                </div>

                <div class="form-group" style="text-align: right;">
                    <button type="submit" class="btn btn-primary">🛠️ Create Work Order</button>
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
