<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header("Location: unauthorized.php");
    exit();
}

require_once 'config/config.php';

// Check if person_id is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: member-people.php");
    exit();
}

$person_id = (int)$_GET['id'];

// Get person data before deletion to update population counts
$person_query = "SELECT gender, age FROM people WHERE person_id = $person_id";
$person_result = mysqli_query($conn, $person_query);

if (mysqli_num_rows($person_result) == 0) {
    header("Location: member-people.php?error=Person not found");
    exit();
}

$person = mysqli_fetch_assoc($person_result);
$gender = $person['gender'];
$age = $person['age'];

// Delete the person
$delete_query = "DELETE FROM people WHERE person_id = $person_id";

if (mysqli_query($conn, $delete_query)) {
    // Update population count
    // $update_population = "UPDATE population SET total = total - 1";
    
    // // Update gender counts
    // if ($gender == 'male') {
    //     $update_population .= ", males = males - 1";
    // } else if ($gender == 'female') {
    //     $update_population .= ", females = females - 1";
    // }
    
    // // Update age group counts
    // if ($age < 18) {
    //     $update_population .= ", children = children - 1";
    // } else if ($age >= 60) {
    //     $update_population .= ", seniors = seniors - 1";
    // } else {
    //     $update_population .= ", adults = adults - 1";
    // }
    
    // mysqli_query($conn, $update_population);
    
    header("Location: member-people.php?success=Person deleted successfully");
} else {
    header("Location: member-people.php?error=" . urlencode("Error deleting person: " . mysqli_error($conn)));
}
?>
