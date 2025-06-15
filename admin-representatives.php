<?php
require_once('config/config.php');
session_start();

// Only admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: unauthorized.php");
    exit();
}

// Positions to display
$positions = ['headman', 'secretary', 'treasurer', 'health coordinator', 'education officer'];

// Fetch all representatives
$repsSql = "
    SELECT vr.rep_id, vr.user_id, vr.position, vr.created_at,
           ud.first_name, ud.middle_name, ud.last_name, ud.phone_no, u.email
    FROM village_representatives vr
    JOIN users u ON vr.user_id = u.user_id
    JOIN user_details ud ON vr.user_id = ud.user_id
";
$repRes = $conn->query($repsSql);

$reps = [];
while ($r = $repRes->fetch_assoc()) {
    $reps[$r['position']] = $r;
}

// Get all eligible members for dropdown
$membersSql = "
    SELECT u.user_id, ud.first_name, ud.middle_name, ud.last_name
    FROM users u
    JOIN user_details ud ON u.user_id = ud.user_id
    WHERE u.role = 'member'
    ORDER BY ud.first_name
";
$memberRes = $conn->query($membersSql);
$memberOptions = [];
while ($m = $memberRes->fetch_assoc()) {
    $memberOptions[] = [
        'id' => $m['user_id'],
        'name' => $m['first_name'] . ' ' . ($m['middle_name'] ? $m['middle_name'] . ' ' : '') . $m['last_name']
    ];
}

// Flash message
$flash = '';
if (isset($_SESSION['rep_success'])) {
    $flash = "<div style='background: #d4edda; padding: 10px; color: #155724; margin-bottom: 15px;'>✅ Representative updated successfully.</div>";
    unset($_SESSION['rep_success']);
} elseif (isset($_SESSION['rep_error'])) {
    $flash = "<div style='background: #f8d7da; padding: 10px; color: #721c24; margin-bottom: 15px;'>❌ Failed to update representative.</div>";
    unset($_SESSION['rep_error']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Representatives</title>
    <link href="res/css/member-complaints.css" rel="stylesheet">
</head>
<body>

<header>
    <div class="container">
        <div class="logo"><h1>Panchayat Dashboard</h1></div>
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
        <h2>Village Representatives</h2>
        <p>View and update committee representatives</p>
    </div>
</section>

<section class="data-section">
    <div class="container">
        <?= $flash ?>

        <?php foreach ($positions as $pos): ?>
            <div class="card" style="margin-bottom: 25px;">
                <h3><?= ucwords($pos) ?></h3>
                <?php if (isset($reps[$pos])): ?>
                    <p><strong>Name:</strong> <?= htmlspecialchars($reps[$pos]['first_name'] . ' ' . $reps[$pos]['last_name']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($reps[$pos]['email']) ?></p>
                    <p><strong>Phone:</strong> <?= htmlspecialchars($reps[$pos]['phone_no']) ?></p>
                    <p><strong>Serving Since:</strong> <?= date('F Y', strtotime($reps[$pos]['created_at'])) ?></p>
                <?php else: ?>
                    <p><em>No <?= ucwords($pos) ?> assigned yet.</em></p>
                <?php endif; ?>

                <!-- Update Form -->
                <form method="POST" action="admin-update-representative.php" style="margin-top: 10px;">
                    <input type="hidden" name="position" value="<?= $pos ?>">
                    <label for="user_id">Assign New <?= ucwords($pos) ?>:</label>
                    <select name="user_id" required>
                        <option value="">-- Select Member --</option>
                        <?php foreach ($memberOptions as $m): ?>
                            <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary" style="margin-left: 10px;">Update</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<footer>
    <div class="container">
        <p>© 2025 Panchayat Dashboard. All Rights Reserved.</p>
    </div>
</footer>

</body>
</html>
