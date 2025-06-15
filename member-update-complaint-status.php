<?php
require_once('config/config.php');

// Check if the user is logged in and a member
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header("Location: unauthorized.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['complaint_id'], $_POST['status'])) {
    $complaint_id = intval($_POST['complaint_id']);
    $status = $_POST['status'];

    if (in_array($status, ['pending', 'resolved'])) {
        $sql = "UPDATE complaints SET status = ? WHERE complaint_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status, $complaint_id);
        
        if ($stmt->execute()) {
            header("Location: member-complaints.php?success=1");
            exit();
        } else {
            header("Location: member-complaints.php?error=1");
            exit();
        }
    }
}

header("Location: member-complaints.php");
exit();
