<?php
session_start();
include 'config.php';

$conn = new SQLite3('barter_bay.db');

// Handle Add to Cart (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['customer'])) {
        header("Location: login.php");
        exit();
    }

    $product_id = intval($_POST['add_to_cart']);
    $customer_id = intval($_SESSION['customer']);

    // Check if product already in cart
    $stmt = $conn->prepare("SELECT id FROM cart WHERE user_id = :user_id AND product_id = :product_id");
    $stmt->bindValue(':user_id', $customer_id, SQLITE3_INTEGER);
    $stmt->bindValue(':product_id', $product_id, SQLITE3_INTEGER);
    $result = $stmt->execute();

    if ($result && $result->fetchArray(SQLITE3_ASSOC)) {
        // Product already in cart, increase quantity
        $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = :user_id AND product_id = :product_id");
    } else {
        // Add product to cart
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, 1)");
    }

    $stmt->bindValue(':user_id', $customer_id, SQLITE3_INTEGER);
    $stmt->bindValue(':product_id', $product_id, SQLITE3_INTEGER);
    $stmt->execute();

    header("Location: products.php?success=added");
    exit();
}

// Handle AJAX search
if (isset($_GET['search'])) {
    header('Content-Type: application/json; charset=utf-8');

    $search = "%" . str_replace(['%', '_'], '', $_GET['search']) . "%";
    $category = $_GET['category'] ?? '';
    $price_range = $_GET['price_range'] ?? '';

    $sql = "SELECT * FROM products WHERE name LIKE ?";
    $params = [$search];
    $types = [SQLITE3_TEXT];

    if (!empty($category)) {
        $sql .= " AND category = ?";
        $params[] = $category;
        $types[] = SQLITE3_TEXT;
    }

    if (!empty($price_range)) {
        $range = explode("-", $price_range);
        if (count($range) === 2) {
            $sql .= " AND price BETWEEN ? AND ?";
            $params[] = (int)$range[0];
            $params[] = (int)$range[1];
            $types[] = SQLITE3_INTEGER;
            $types[] = SQLITE3_INTEGER;
        }
    }

    $stmt = $conn->prepare($sql);
    foreach ($params as $i => $val) {
        $stmt->bindValue($i + 1, $val, $types[$i]);
    }

    $result = $stmt->execute();
    $filtered_products = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $filtered_products[] = $row;
    }

    echo json_encode($filtered_products);
    exit();
}

// Normal page load logic
$result = $conn->query("SELECT * FROM products");

$products = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $products[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Barter Bay - Products</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, red, blue);
            color: white;
            text-align: center;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1000px;
            margin: 50px auto;
            padding: 20px;
            background: rgba(0, 0, 0, 0.8);
            border-radius: 10px;
        }
        h2 {
            margin-bottom: 20px;
        }
        .search-bar {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        input, select {
            padding: 10px;
            border-radius: 5px;
            border: none;
        }
        .btn {
            background: red;
            color: white;
            padding: 10px;
            border: none;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn:hover {
            background: blue;
        }
        .product-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            justify-content: center;
            gap: 15px;
        }
        .product {
            padding: 15px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            max-width: 250px;
            margin: auto;
        }
        .product img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
        }
        .product p {
            margin: 10px 0;
        }
        .trade-btn, .cart-btn {
            width: 48%;
            display: inline-block;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container">
    <h2>Available Products</h2>
    <?php if (isset($_GET['success']) && $_GET['success'] === 'added'): ?>
        <p style="color: lightgreen;">✅ Product added to cart successfully!</p>
    <?php endif; ?>

    <div class="search-bar">
        <input type="text" id="search" placeholder="Search products...">
        <select id="category">
            <option value="">All Categories</option>
            <option value="Electronics">Electronics</option>
            <option value="Clothing">Clothing</option>
            <option value="Books">Books</option>
        </select>
        <select id="price_range">
            <option value="">All Prices</option>
            <option value="0-50">₹0 - ₹50</option>
            <option value="51-100">₹51 - ₹100</option>
            <option value="101-500">₹101 - ₹500</option>
        </select>
        <button class="btn" onclick="searchProducts()">Search</button>
    </div>

    <div class="product-list" id="product-list">
        <?php foreach ($products as $product): ?>
            <div class="product">
                <img src="images/<?= htmlspecialchars($product['image']) ?>" alt="Product">
                <h3><?= htmlspecialchars($product['name']) ?></h3>
                <p>Category: <?= htmlspecialchars($product['category']) ?></p>
                <p>Price: ₹<?= htmlspecialchars($product['price']) ?></p>
                <button class="btn trade-btn" onclick="window.location.href='trade.php?product_id=<?= $product['id'] ?>'">Trade</button>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="add_to_cart" value="<?= $product['id'] ?>">
                    <button class="btn cart-btn" type="submit">Add to Cart</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    function escapeHtml(text) {
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function searchProducts() {
        let search = document.getElementById("search").value;
        let category = document.getElementById("category").value;
        let price_range = document.getElementById("price_range").value;

        fetch(`products.php?search=${encodeURIComponent(search)}&category=${encodeURIComponent(category)}&price_range=${encodeURIComponent(price_range)}`)
        .then(response => {
            if (!response.ok) throw new Error("Network response was not ok.");
            return response.json();
        })
        .then(data => {
            let productHTML = "";
            if (data.length === 0) {
                productHTML = "<p>No products found.</p>";
            } else {
                data.forEach(product => {
                    productHTML += `
                        <div class="product">
                            <img src="images/${escapeHtml(product.image)}" alt="Product">
                            <h3>${escapeHtml(product.name)}</h3>
                            <p>Category: ${escapeHtml(product.category)}</p>
                            <p>Price: ₹${product.price}</p>
                            <button class="btn trade-btn" onclick="window.location.href='trade.php?product_id=${product.id}'">Trade</button>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="add_to_cart" value="${product.id}">
                                <button class="btn cart-btn" type="submit">Add to Cart</button>
                            </form>
                        </div>
                    `;
                });
            }
            document.getElementById("product-list").innerHTML = productHTML;
        })
        .catch(error => {
            console.error("Fetch error:", error);
            document.getElementById("product-list").innerHTML = "<p>Error loading products.</p>";
        });
    }

    document.getElementById("search").addEventListener("input", searchProducts);
    document.getElementById("category").addEventListener("change", searchProducts);
    document.getElementById("price_range").addEventListener("change", searchProducts);
</script>

</body>
</html>
