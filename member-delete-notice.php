<?php
require_once('config/config.php');
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header("Location: unauthorized.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notice_id'])) {
    $id = $_POST['notice_id'];
    $stmt = $conn->prepare("DELETE FROM notices WHERE notice_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: member-notices.php");
exit();
?>
