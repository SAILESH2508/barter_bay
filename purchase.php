<?php
session_start();
include 'config.php';

if (!isset($_SESSION['customer'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['customer'];

// Fetch cart items
$cart_items = [];
$query = "SELECT cart.product_id, cart.quantity, products.price 
          FROM cart 
          JOIN products ON cart.product_id = products.id 
          WHERE cart.customer_id = :customer_id";
$stmt = $conn->prepare($query);
$stmt->bindValue(':customer_id', $customer_id, SQLITE3_INTEGER);
$res = $stmt->execute();

while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    $cart_items[] = $row;
}

if (empty($cart_items)) {
    header("Location: cart.php");
    exit();
}

// Process each cart item
foreach ($cart_items as $item) {
    $total_price = $item['price'] * $item['quantity'];
    $qr_code = "fixed_qr_code";

    $insert_query = "INSERT INTO transactions (user_id, product_id, amount, payment_status, qr_code) 
                     VALUES (:user_id, :product_id, :amount, 'Completed', :qr_code)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bindValue(':user_id', $customer_id, SQLITE3_INTEGER);
    $stmt->bindValue(':product_id', $item['product_id'], SQLITE3_INTEGER);
    $stmt->bindValue(':amount', $total_price, SQLITE3_TEXT);
    $stmt->bindValue(':qr_code', $qr_code, SQLITE3_TEXT);
    $stmt->execute();
}

// Clear cart
$clear_cart_query = "DELETE FROM cart WHERE customer_id = :customer_id";
$stmt = $conn->prepare($clear_cart_query);
$stmt->bindValue(':customer_id', $customer_id, SQLITE3_INTEGER);
$stmt->execute();

// Redirect to receipt
$last_transaction_id = $conn->lastInsertRowID();
header("Location: receipt_purchase.php?transaction_id=" . $last_transaction_id);
exit();
?>
