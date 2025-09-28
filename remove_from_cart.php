<?php
session_start();
include 'config.php';

// Check if the user is logged in and the request method is POST
if (!isset($_SESSION['customer']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: cart.php");
    exit();
}

$customer_id = intval($_SESSION['customer']);
$cart_id = intval($_POST['cart_id']);
$csrf_token = $_POST['csrf_token'] ?? '';

// Validate CSRF token
if ($csrf_token !== $_SESSION['csrf_token']) {
    die("Invalid CSRF token");
}

// Prepare the SQL statement to delete the item from the cart
$stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
$stmt->bindValue(1, $cart_id, SQLITE3_INTEGER);
$stmt->bindValue(2, $customer_id, SQLITE3_INTEGER);
$stmt->execute();
$stmt->close();

// Redirect back to the cart page
header("Location: cart.php");
exit();
?>
