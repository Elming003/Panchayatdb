<?php
require_once('config/config.php');

// Check session and role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header("Location: unauthorized.php");
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = intval($_POST['order_id']);

    $stmt = $conn->prepare("DELETE FROM work_orders WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);

    if ($stmt->execute()) {
        // Redirect back with success (optional: flash messages)
        header("Location: member-work-orders.php");
    } else {
        echo "Error deleting work order. Please try again.";
    }

    $stmt->close();
} else {
    // Invalid access
    header("Location: member-work-orders.php");
    exit();
}
?>
