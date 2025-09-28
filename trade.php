<?php
session_start();
if (!isset($_SESSION['customer'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';
$customer_id = $_SESSION['customer'];

// Search term logic
$search = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';

// Fetch products not owned by current user and match search
$query = "SELECT * FROM products WHERE user_id != :customer_id AND (name LIKE :search OR category LIKE :search)";
$stmt = $conn->prepare($query);
$stmt->bindValue(':customer_id', $customer_id, SQLITE3_INTEGER);
$stmt->bindValue(':search', $search, SQLITE3_TEXT);
$result = $stmt->execute();

$products = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $products[] = $row;
}
$stmt->close();

// Fetch user's own products for trade
$my_products = [];
$my_stmt = $conn->prepare("SELECT * FROM products WHERE user_id = :customer_id");
$my_stmt->bindValue(':customer_id', $customer_id, SQLITE3_INTEGER);
$my_result = $my_stmt->execute();
while ($row = $my_result->fetchArray(SQLITE3_ASSOC)) {
    $my_products[] = $row;
}
$my_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Trade Products</title>
    <style>
        body {
            background: linear-gradient(to right, red, blue);
            color: white;
            font-family: Arial;
            text-align: center;
        }
        .container {
            margin: 30px auto;
            width: 90%;
            padding: 20px;
            background: rgba(0,0,0,0.8);
            border-radius: 10px;
        }
        .search-box {
            margin-bottom: 20px;
        }
        .search-box input[type="text"] {
            padding: 10px;
            width: 300px;
            border-radius: 5px;
            border: none;
        }
        .search-box button {
            padding: 10px 20px;
            background-color: red;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .search-box button:hover {
            background-color: blue;
        }
        .product {
            background: rgba(255,255,255,0.1);
            padding: 20px;
            margin: 20px;
            border-radius: 10px;
            display: inline-block;
            width: 300px;
            vertical-align: top;
        }
        .product img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 10px;
        }
        .btn {
            background: red;
            color: white;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
            border-radius: 10px;
        }
        .btn:hover {
            background: blue;
        }
        select {
            padding: 5px;
            margin-top: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container">
    <h2>Trade Products</h2>

    <!-- 🔍 Search Form -->
    <div class="search-box">
        <form method="GET">
            <input type="text" name="search" placeholder="Search by name or category" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
            <button type="submit">Search</button>
        </form>
    </div>

    <?php if (empty($products)): ?>
        <p>No products found for trade.</p>
    <?php else: ?>
        <?php foreach ($products as $product): ?>
            <div class="product">
            <img src="images/<?= htmlspecialchars($product['image']) ?>" alt="Product">
            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                <p>Category: <?php echo htmlspecialchars($product['category']); ?></p>
                <p>Price: ₹<?php echo number_format($product['price'], 2); ?></p>

                <form method="POST" action="trade_process.php">
                    <input type="hidden" name="receiver_id" value="<?php echo $product['user_id']; ?>">
                    <input type="hidden" name="receiver_product_id" value="<?php echo $product['id']; ?>">

                    <label>Select Your Product to Trade:</label><br>
                    <select name="sender_product_id" required>
                        <option value="">-- Choose Your Product --</option>
                        <?php foreach ($my_products as $my): ?>
                            <option value="<?php echo $my['id']; ?>">
                                <?php echo htmlspecialchars($my['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <br><br>
                    <button type="submit" class="btn">Request Trade</button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>
