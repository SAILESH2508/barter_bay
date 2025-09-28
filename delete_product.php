<?php
session_start();
include 'config.php';

// Check for admin login
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Delete product
if (isset($_GET['id'])) {
    $product_id = $_GET['id'];
    
    // Use prepared statements to prevent SQL injection
    $delete_query = "DELETE FROM products WHERE id = :product_id";
    
    // Prepare the statement
    $stmt = $conn->prepare($delete_query);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->lastErrorMsg());
    }

    // Bind the product_id parameter to the statement
    $stmt->bindValue(':product_id', $product_id, SQLITE3_INTEGER);

    // Execute the statement
    $result = $stmt->execute();
    if ($result) {
        header("Location: view_products.php");
        exit();
    } else {
        echo "Error deleting product: " . $conn->lastErrorMsg();
    }
}
?>
