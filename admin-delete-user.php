<?php
require_once('config/config.php');

// Ensure only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: unauthorized.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Delete from user_details first (it has foreign key constraint)
        $stmt = $conn->prepare("DELETE FROM user_details WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
        
        // Then delete from users table
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
        
        // If we got here, commit the transaction
        mysqli_commit($conn);
        $_SESSION['delete_success'] = "User successfully deleted.";
    } catch (Exception $e) {
        // If there was an error, rollback the transaction
        mysqli_rollback($conn);
        $_SESSION['delete_error'] = "Failed to delete user. Please try again.";
    }
    
    // Redirect back to manage users page
    header("Location: admin-manage-users.php");
    exit();
} else {
    // Invalid request
    header("Location: admin-manage-users.php");
    exit();
} 