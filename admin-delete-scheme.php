<?php
require_once('config/config.php');

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: unauthorized.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['scheme_id'])) {
    $scheme_id = (int)$_POST['scheme_id'];

    $stmt = $conn->prepare("DELETE FROM schemes WHERE scheme_id = ?");
    $stmt->bind_param("i", $scheme_id);
    if ($stmt->execute()) {
        header("Location: admin-schemes.php");
        exit();
    } else {
        echo "Failed to delete scheme.";
    }
    $stmt->close();
} else {
    echo "Invalid request.";
}
?> 