<?php
require_once('config/config.php');
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header("Location: unauthorized.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['house_id'])) {
    $house_id = (int)$_POST['house_id'];

    $stmt = $conn->prepare("DELETE FROM houses WHERE house_id = ?");
    $stmt->bind_param("i", $house_id);
    if ($stmt->execute()) {
        header("Location: member-houses.php");
        exit();
    } else {
        echo "Failed to delete house.";
    }
    $stmt->close();
} else {
    echo "Invalid request.";
}
?>
