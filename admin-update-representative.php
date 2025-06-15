<?php
require_once('config/config.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['position'], $_POST['user_id'])) {
    $position = strtolower(trim($_POST['position']));
    $user_id = intval($_POST['user_id']);

    // Validate position
    $valid = ['headman', 'secretary', 'treasurer', 'health coordinator', 'education officer'];
    if (!in_array($position, $valid)) {
        $_SESSION['rep_error'] = true;
        header("Location: admin-representatives.php");
        exit();
    }

    // Validate user is a member
    $check = $conn->prepare("SELECT user_id FROM users WHERE user_id = ? AND role = 'member'");
    $check->bind_param("i", $user_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        // Check if position already assigned
        $checkExisting = $conn->prepare("SELECT rep_id FROM village_representatives WHERE position = ? LIMIT 1");
        $checkExisting->bind_param("s", $position);
        $checkExisting->execute();
        $result = $checkExisting->get_result();

        if ($result->num_rows > 0) {
            // Update existing representative
            $rep_id = $result->fetch_assoc()['rep_id'];
            $stmt = $conn->prepare("UPDATE village_representatives SET user_id = ?, updated_at = NOW() WHERE rep_id = ?");
            $stmt->bind_param("ii", $user_id, $rep_id);
        } else {
            // Assign new representative
            $stmt = $conn->prepare("INSERT INTO village_representatives (user_id, position) VALUES (?, ?)");
            $stmt->bind_param("is", $user_id, $position);
        }

        if ($stmt->execute()) {
            $_SESSION['rep_success'] = true;
        } else {
            $_SESSION['rep_error'] = true;
        }
    } else {
        $_SESSION['rep_error'] = true;
    }

    header("Location: admin-representatives.php");
    exit();
}
