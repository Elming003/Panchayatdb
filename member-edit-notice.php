<?php
require_once('config/config.php');
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header("Location: unauthorized.php");
    exit();
}

$id = $_GET['id'];
$message = '';
$stmt = $conn->prepare("SELECT * FROM notices WHERE notice_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$notice = $result->fetch_assoc();
$stmt->close();

if (!$notice) {
    echo "Notice not found.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $category = $_POST['category'];
    $content = trim($_POST['content']);

    $stmt = $conn->prepare("UPDATE notices SET title=?, category=?, content=? WHERE notice_id=?");
    $stmt->bind_param("sssi", $title, $category, $content, $id);
    if ($stmt->execute()) {
        header("Location: member-notices.php");
        exit();
    } else {
        $message = "Failed to update notice.";
    }
}
?>

<!-- HTML form to edit -->
<form method="POST" class="container card">
    <h3>Edit Notice</h3>
    <?php if ($message): ?><p class="message"><?php echo $message; ?></p><?php endif; ?>
    <div class="form-group">
        <label for="title">Title</label>
        <input type="text" name="title" value="<?php echo htmlspecialchars($notice['title']); ?>" required>
    </div>
    <div class="form-group">
        <label for="category">Category</label>
        <select name="category" required>
            <?php
            $categories = ['meeting', 'announcement', 'safety', 'health'];
            foreach ($categories as $cat) {
                echo "<option value=\"$cat\" " . ($cat == $notice['category'] ? 'selected' : '') . ">" . ucfirst($cat) . "</option>";
            }
            ?>
        </select>
    </div>
    <div class="form-group">
        <label for="content">Content</label>
        <textarea name="content" rows="6"><?php echo htmlspecialchars($notice['content']); ?></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Update Notice</button>
</form>
