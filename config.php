<?php
    // Start session to manage user state
    session_start();

    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    // Database connection details
    $servername = "localhost";
    $username = "root";
    $password = "";
    $database = "panchayat_db";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $database);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }