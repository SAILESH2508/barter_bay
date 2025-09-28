<?php
session_start();
include 'config.php';

// Ensure the user is logged in before proceeding
if (!isset($_SESSION['customer'])) {
    die("User not logged in!");  // Redirect to login page if not logged in
}

// Debugging - Check if user is logged in and session ID exists
echo "Logged in user ID: " . $_SESSION['customer'];  // Debugging output

// Getting user details from the session
$sender_id = $_SESSION['customer'];  // User's ID from session
$receiver_id = $_POST['receiver_id'] ?? null;  // Receiver ID from form
$sender_product_id = $_POST['sender_product_id'] ?? null;  // Sender product ID
$receiver_product_id = $_POST['receiver_product_id'] ?? null;  // Receiver product ID

// Debugging - Check if any form data is missing
if ($receiver_id === null || $sender_product_id === null || $receiver_product_id === null) {
    echo "Missing required form data! <br />";
    echo "Receiver ID: $receiver_id, Sender Product ID: $sender_product_id, Receiver Product ID: $receiver_product_id";
    exit();  // Prevent further execution if any data is missing
}

// Prepare SQL query to insert into trades table
$stmt = $conn->prepare("INSERT INTO trades (sender_id, receiver_id, sender_product_id, receiver_product_id) 
                        VALUES (:sender_id, :receiver_id, :sender_product_id, :receiver_product_id)");

if ($stmt) {
    // Bind values to SQL parameters
    $stmt->bindValue(':sender_id', $sender_id, SQLITE3_INTEGER);
    $stmt->bindValue(':receiver_id', $receiver_id, SQLITE3_INTEGER);
    $stmt->bindValue(':sender_product_id', $sender_product_id, SQLITE3_INTEGER);
    $stmt->bindValue(':receiver_product_id', $receiver_product_id, SQLITE3_INTEGER);

    // Execute the query
    if ($stmt->execute()) {
        echo "<script>alert('Trade request sent successfully!'); window.location.href='my_trades.php';</script>";
    } else {
        // Error handling using SQLite's lastErrorMsg() method
        $errorMsg = $conn->lastErrorMsg();
        echo "Trade failed: " . $errorMsg; // Show error message if the query failed
    }
} else {
    // Handle error if the statement preparation fails
    $errorMsg = $conn->lastErrorMsg();
    echo "Database error: " . $errorMsg;
}
?>
