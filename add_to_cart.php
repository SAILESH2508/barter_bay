<?php
session_start();
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['customer'])) {
    header("Location: login.php");
    exit();
}

$customer_id = intval($_SESSION['customer']);

// Ensure the request method is POST and product_id is set
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);

    // Validate the product_id
    if ($product_id <= 0) {
        header("Location: products.php?error=invalid_product");
        exit();
    }

    // Check if product already exists in cart
    $stmt = $conn->prepare("SELECT id FROM cart WHERE user_id = :user_id AND product_id = :product_id");
    $stmt->bindValue(':user_id', $customer_id, SQLITE3_INTEGER);
    $stmt->bindValue(':product_id', $product_id, SQLITE3_INTEGER);
    $result = $stmt->execute();

    if ($result === false) {
        die("Error checking cart: " . $conn->lastErrorMsg());
    }

    $row = $result->fetchArray(SQLITE3_ASSOC);

    if ($row) {
        // Already in cart, increase quantity
        $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = :user_id AND product_id = :product_id");
    } else {
        // Not in cart, insert new entry
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, 1)");
    }

    $stmt->bindValue(':user_id', $customer_id, SQLITE3_INTEGER);
    $stmt->bindValue(':product_id', $product_id, SQLITE3_INTEGER);

    if (!$stmt->execute()) {
        die("Error updating cart: " . $conn->lastErrorMsg());
    }

    header("Location: products.php?success=added_to_cart");
    exit();
} else {
    // Invalid access
    header("Location: products.php?error=invalid_request");
    exit();
}
