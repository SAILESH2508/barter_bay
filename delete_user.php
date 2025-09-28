<?php
session_start();
include 'config.php';

// Check if the admin is logged in
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Check if the user ID is set in the URL
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Prepare the DELETE query using a parameterized statement
    $delete_query = "DELETE FROM users WHERE id = :user_id";

    // Prepare the statement
    $stmt = $conn->prepare($delete_query);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->lastErrorMsg());
    }

    // Bind the user_id parameter
    $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);

    // Execute the statement
    $result = $stmt->execute();

    if ($result) {
        header("Location: manage_users.php");
        exit();
    } else {
        echo "Error deleting user: " . $conn->lastErrorMsg();
    }
} else {
    echo "No user selected for deletion!";
    exit();
}
?>
