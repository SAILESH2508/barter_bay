<?php
session_start();
include 'config.php';

// Check for admin login
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Fetch products with owner's name (user_id instead of owner_id)
$query = "
    SELECT p.*, u.name AS owner_name
    FROM products p
    JOIN users u ON p.user_id = u.id
";
$stmt = $conn->prepare($query);
$result = $stmt->execute();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - View Products</title>
    <style>
        body { font-family: Arial, sans-serif; background: linear-gradient(to right, red, blue); color: white; }
        table { width: 90%; margin: 30px auto; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid white; text-align: center; }
        th { background: red; }
        td { background: rgba(255, 255, 255, 0.1); }
        .btn { padding: 5px 10px; background: red; color: white; border: none; cursor: pointer;border-radius: 5px;text-decoration-line: none;}
        .btn:hover { background: blue; }
        h2 { text-align: center; margin-top: 30px; }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<h2>Admin - Product List</h2>

<table>
    <tr>
        <th>ID</th>
        <th>Product</th>
        <th>Description</th>
        <th>Category</th>
        <th>Price</th>
        <th>Owner</th>
        <th>Actions
            <div style="text-align: center; margin-top: 20px;">
            <a href="add_product.php" class="btn" style="background-color: purple;">Add New Product</a>
           </div>
        </th>
    </tr>

    <?php
    // Fetch and display the products
    while ($row = $result->fetchArray(SQLITE3_ASSOC)): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['description']) ?></td>
            <td><?= htmlspecialchars($row['category']) ?></td>
            <td>₹<?= number_format($row['price'], 2) ?></td>
            <td><?= htmlspecialchars($row['owner_name']) ?></td>
            <td>
                <a href="edit_product.php?id=<?= $row['id'] ?>" class="btn">Edit</a>
                <a href="delete_product.php?id=<?= $row['id'] ?>" class="btn" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
            </td>
        </tr>
    <?php endwhile; ?>
</table>
</body>
</html>
