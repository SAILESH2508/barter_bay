<?php
session_start();
include 'config.php';

// Check for admin login
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Fetch product details
if (isset($_GET['id'])) {
    $product_id = $_GET['id'];

    // Prepare the query to fetch the product details
    $query = "SELECT * FROM products WHERE id = :product_id";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':product_id', $product_id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $product = $result->fetchArray(SQLITE3_ASSOC);
}

// Update product details
if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $price = $_POST['price'];

    // Prepare the query to update the product
    $update_query = "UPDATE products SET name = :name, description = :description, category = :category, price = :price WHERE id = :product_id";
    $stmt = $conn->prepare($update_query);

    // Bind the values
    $stmt->bindValue(':name', $name, SQLITE3_TEXT);
    $stmt->bindValue(':description', $description, SQLITE3_TEXT);
    $stmt->bindValue(':category', $category, SQLITE3_TEXT);
    $stmt->bindValue(':price', $price, SQLITE3_FLOAT);
    $stmt->bindValue(':product_id', $product_id, SQLITE3_INTEGER);

    // Execute the update query
    if ($stmt->execute()) {
        header("Location: view_products.php");
        exit();
    } else {
        echo "Error updating product: " . $conn->lastErrorMsg();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Product</title>
    <style>
        body { font-family: Arial, sans-serif; background: linear-gradient(to right, red, blue); color: white; }
        .form-container { width: 50%; margin: 30px auto; padding: 20px; background: rgba(0, 0, 0, 0.7); border-radius: 10px; }
        label { font-weight: bold; }
        input, textarea { width: 90%; padding: 10px; margin-bottom: 15px; background: rgba(255, 255, 255, 0.1); border: 1px solid white; color: white; }
        button { padding: 10px 20px; background: red; color: white; border: none; cursor: pointer; }
        button:hover { background: blue; }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="form-container">
    <h2>Edit Product</h2>
    <form method="POST">
        <label for="name">Product Name:</label>
        <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>

        <label for="description">Description:</label>
        <textarea name="description" required><?= htmlspecialchars($product['description']) ?></textarea>

        <label for="category">Category:</label>
        <input type="text" name="category" value="<?= htmlspecialchars($product['category']) ?>" required><br>

        <label for="price">Price:</label><br>
        <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($product['price']) ?>" required>

        <button type="submit" name="submit">Update Product</button>
    </form>
</div>

</body>
</html>
