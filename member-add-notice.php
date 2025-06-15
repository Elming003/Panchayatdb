<?php
require_once('config/config.php');
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header("Location: unauthorized.php");
    exit();
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $category = $_POST['category'];
    $content = trim($_POST['content']);

    if ($title && $category && $content) {
        $stmt = $conn->prepare("INSERT INTO notices (title, content, category) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $title, $content, $category);
        if ($stmt->execute()) {
            header("Location: member-notices.php");
            exit();
        } else {
            $message = "Failed to add notice.";
        }
        $stmt->close();
    } else {
        $message = "Please fill all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Notice</title>
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
                <li><a href="member-notices.php">Back to Notices</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </div>
</header>

<section class="hero">
    <div class="hero-content">
        <h2>Add Notice</h2>
        <p>Add public notice.</p>
    </div>
</section>

<section class="data-section">
    <div class="container">
        <?php if (!empty($message)): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="card">
            <form method="POST" class="container card">
                <h3>Add Notice</h3>
                <?php if ($message): ?><p class="message"><?php echo $message; ?></p><?php endif; ?>
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" name="title" id="title" required>
                </div>
                <div class="form-group">
                    <label for="category">Category</label>
                    <select name="category" required>
                        <option value="">--Select--</option>
                        <option value="meeting">Meeting</option>
                        <option value="announcement">Announcement</option>
                        <option value="safety">Safety</option>
                        <option value="health">Health</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="content">Content</label>
                    <textarea name="content" rows="6" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Submit Notice</button>
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
