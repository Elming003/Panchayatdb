<?php
require_once('config/config.php');

// Check if the user is logged in and has the 'general' role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'general') {
    header("Location: unauthorized.php"); 
    exit();
}

// Fetch all work orders (visible to general users)
$sql = "SELECT order_id, project_name, description, status, assigned_date, completion_date FROM work_orders ORDER BY assigned_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Work Orders</title>
    <link href="res/css/general-my-complaints.css" rel="stylesheet">
    <style>
        .work-orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .work-orders-table th,
        .work-orders-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .work-orders-table th {
            background-color: #f5f5f5;
        }

        .card {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
        }

    </style>
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
        <h2>Village Project Work Orders</h2>
        <p>Browse all current and past work orders.</p>
    </div>
</section>

<section class="data-section">
    <div class="container">
        <?php if ($result && $result->num_rows > 0): ?>
            <div class="card">
                <table class="work-orders-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Project Name</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Assigned Date</th>
                            <th>Completion Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['order_id']; ?></td>
                                <td><?php echo htmlspecialchars($row['project_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['description']); ?></td>
                                <td><?php echo ucfirst($row['status']); ?></td>
                                <td><?php echo date("d M Y", strtotime($row['assigned_date'])); ?></td>
                                <td><?php echo $row['completion_date'] ? date("d M Y", strtotime($row['completion_date'])) : 'N/A'; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>No work orders available at the moment.</p>
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
