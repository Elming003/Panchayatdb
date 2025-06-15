<?php
require_once('config/config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header("Location: unauthorized.php");
    exit();
}

$order_id = $_GET['id'] ?? 0;
$message = '';

$stmt = $conn->prepare("SELECT * FROM work_orders WHERE order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$work_order = $result->fetch_assoc();
$stmt->close();

if (!$work_order) {
    echo "Work order not found.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_name = trim($_POST['project_name']);
    $description = trim($_POST['description']);
    $status = $_POST['status'];
    $completion_date = !empty($_POST['completion_date']) ? $_POST['completion_date'] : NULL;

    if (!empty($project_name) && !empty($description) && !empty($status)) {
        $stmt = $conn->prepare("UPDATE work_orders SET project_name=?, description=?, status=?, completion_date=? WHERE order_id=?");
        $stmt->bind_param("ssssi", $project_name, $description, $status, $completion_date, $order_id);
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
    <title>Edit Work Order</title>
    <link href="res/css/member-edit-work-order.css" rel="stylesheet">
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
        <h2>Edit Work Order</h2>
        <p>Update project details and monitor progress.</p>
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
                    <input type="text" id="project_name" name="project_name" value="<?php echo htmlspecialchars($work_order['project_name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="6" required><?php echo htmlspecialchars($work_order['description']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        <option value="pending" <?php if ($work_order['status'] == 'pending') echo 'selected'; ?>>Pending</option>
                        <option value="in_progress" <?php if ($work_order['status'] == 'in_progress') echo 'selected'; ?>>In Progress</option>
                        <option value="completed" <?php if ($work_order['status'] == 'completed') echo 'selected'; ?>>Completed</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="completion_date">Completion Date (optional)</label>
                    <input type="date" id="completion_date" name="completion_date" value="<?php echo $work_order['completion_date']; ?>">
                </div>

                <div class="form-group" style="text-align: right;">
                    <button type="submit" class="btn btn-primary">💾 Update Work Order</button>
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
