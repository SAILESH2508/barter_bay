<?php
session_start();
include 'config.php';

// Ensure only admin access
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Fetch users for the owner dropdown
$users = $conn->query("SELECT id, name FROM users");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $category = $_POST['category'];
    $price = floatval($_POST['price']);
    $user_id = intval($_POST['user_id']);

    $stmt = $conn->prepare("INSERT INTO products (name, description, category, price, user_id) 
                            VALUES (:name, :desc, :category, :price, :user_id)");
    $stmt->bindValue(':name', $name, SQLITE3_TEXT);
    $stmt->bindValue(':desc', $desc, SQLITE3_TEXT);
    $stmt->bindValue(':category', $category, SQLITE3_TEXT);
    $stmt->bindValue(':price', $price, SQLITE3_FLOAT);
    $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);

    $result = $stmt->execute();

    if ($result) {
        header("Location: view_products.php");
        exit();
    } else {
        $error = "Failed to add product: " . $conn->lastErrorMsg();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Product</title>
    <style>
        body { font-family: Arial, sans-serif; background: linear-gradient(to right, red, blue); color: white; }
        form { width: 50%; margin: 40px auto; padding: 20px; background: rgba(0,0,0,0.3); border-radius: 10px; }
        input, select, textarea { width: 90%; padding: 10px; margin: 10px 0; border: none; border-radius: 5px;align-content: center; }
        .btn { background: red; color: white; padding: 10px 20px; cursor: pointer; border: none; border-radius: 10px; }
        .btn:hover { background: blue; }
        h2 { text-align: center; }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<h2>Add New Product</h2>

<?php if (isset($error)) echo "<p style='color: yellow; text-align: center;'>$error</p>"; ?>

<form method="post">
    <label>Product Name:</label>
    <input type="text" name="name" required>

    <label>Description:</label>
    <textarea name="description" required></textarea>

    <label>Category:</label>
    <input type="text" name="category" required><br>
    <label>Price (₹):</label>
    <input type="number" step="0.01" name="price" required><br>
    <label>Owner (User):</label>
    <select name="user_id" required>
        <option value="">Select User</option>
        <?php while ($user = $users->fetchArray(SQLITE3_ASSOC)): ?>
            <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['name']) ?></option>
        <?php endwhile; ?>
    </select>

    <button type="submit" class="btn">Add Product</button>
</form>

</body>
</html>
