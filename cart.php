<?php
session_start();
include 'config.php'; // Make sure this defines $conn = new SQLite3(...);

// Set CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Redirect if not logged in
if (!isset($_SESSION['customer'])) {
    header("Location: login.php");
    exit();
}

$customer_id = intval($_SESSION['customer']);

// Fetch cart items
$query = "SELECT cart.id AS cart_id, products.id AS product_id, products.name, products.price, cart.quantity 
          FROM cart 
          JOIN products ON cart.product_id = products.id 
          WHERE cart.user_id = :customer_id";

$stmt = $conn->prepare($query);
$stmt->bindValue(':customer_id', $customer_id, SQLITE3_INTEGER);
$result = $stmt->execute();
$cart_items = [];

while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $cart_items[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Cart - Barter Bay</title>
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
            width: 85%;
            margin: 50px auto;
            padding: 20px;
            background: rgba(0, 0, 0, 0.85);
            border-radius: 10px;
        }
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            border: 1px solid white;
        }
        th {
            background-color: red;
        }
        td {
            background-color: rgba(255, 255, 255, 0.1);
        }
        .btn {
            background-color: red;
            color: white;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .btn:hover {
            background-color: blue;
        }
        a {
            color: yellow;
            text-decoration: none;
        }
        marquee {
            font-size: 18px;
            margin: 10px;
            color: yellow;
        }
    </style>
</head>
<body>

<marquee behavior="scroll" direction="left">Welcome to Barter Bay</marquee>

<?php include 'navbar.php'; ?>

<div class="container">
    <h2>My Cart</h2>

    <?php if (empty($cart_items)): ?>
        <p>Your cart is empty. <a href="products.php">Browse products</a></p>
    <?php else: ?>
        <table>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total</th>
                <th>Action</th>
            </tr>
            <?php
                $total = 0;
                foreach ($cart_items as $item):
                    $item_total = $item['price'] * $item['quantity'];
                    $total += $item_total;
            ?>
                <tr>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td>₹<?= number_format($item['price'], 2) ?></td>
                    <td><?= intval($item['quantity']) ?></td>
                    <td>₹<?= number_format($item_total, 2) ?></td>
                    <td>
                        <form action="remove_from_cart.php" method="POST" style="margin: 0;">
                            <input type="hidden" name="cart_id" value="<?= intval($item['cart_id']) ?>">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <button type="submit" class="btn">Remove</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php
                $cgst = $total * 0.09;
                $sgst = $total * 0.09;
                $grand_total = $total + $cgst + $sgst;
            ?>

            <tr>
                <th colspan="3">Subtotal</th>
                <th colspan="2">₹<?= number_format($total, 2) ?></th>
            </tr>
            <tr>
                <th colspan="3">CGST (9%)</th>
                <th colspan="2">₹<?= number_format($cgst, 2) ?></th>
            </tr>
            <tr>
                <th colspan="3">SGST (9%)</th>
                <th colspan="2">₹<?= number_format($sgst, 2) ?></th>
            </tr>
            <tr>
                <th colspan="3">Grand Total</th>
                <th colspan="2">₹<?= number_format($grand_total, 2) ?></th>
            </tr>
        </table>

        <br>
        <form action="buy.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <button type="submit" class="btn">Proceed to Checkout</button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
