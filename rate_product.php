<?php
session_start();
include 'config.php'; // Make sure your SQLite connection is set up here

// Check if user is logged in
if (!isset($_SESSION['customer'])) {
    header("Location: login.php");
    exit();
}

// Support both formats: either just ID or an array
$customer_id = is_array($_SESSION['customer']) ? $_SESSION['customer']['id'] : intval($_SESSION['customer']);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = intval($_POST['product_id']);
    $rating = intval($_POST['rating']);
    $review = trim($_POST['review']);

    // Validate input
    if ($rating < 1 || $rating > 5) {
        echo "<script>alert('Invalid rating. Please enter a value between 1 and 5.');</script>";
    } elseif (empty($review)) {
        echo "<script>alert('Review cannot be empty.');</script>";
    } else {
        // Insert review into SQLite
        $stmt = $conn->prepare("INSERT INTO reviews (product_id, user_id, rating, review) VALUES (?, ?, ?, ?)");
        $stmt->bindValue(1, $product_id, SQLITE3_INTEGER);
        $stmt->bindValue(2, $customer_id, SQLITE3_INTEGER);
        $stmt->bindValue(3, $rating, SQLITE3_INTEGER);
        $stmt->bindValue(4, htmlspecialchars($review), SQLITE3_TEXT);

        if ($stmt->execute()) {
            echo "<script>alert('Review Submitted!'); window.location='rate_product.php';</script>";
        } else {
            echo "<script>alert('Failed to submit review. Try again.');</script>";
        }
    }
}

// Fetch products from SQLite
$query = "SELECT * FROM products";
$result = $conn->query($query);
$products = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $products[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rate Products</title>
    <style>
        body { background: linear-gradient(to right, red, blue); color: white; text-align: center; }
        .container { margin: 50px auto; width: 50%; padding: 20px; background: rgba(0, 0, 0, 0.8); border-radius: 10px; }
        select, textarea, input { width: 90%; padding: 10px; margin: 10px 0; border-radius: 5px; border: none; }
        .btn { background: red; color: white; padding: 10px; border: none; cursor: pointer; transition: 0.3s; border-radius: 5px; }
        .btn:hover { background: blue; }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container">
    <h2>Rate a Product</h2>
    <form method="POST">
        <select name="product_id" required>
            <option value="">Select Product</option>
            <?php foreach ($products as $product): ?>
                <option value="<?= htmlspecialchars($product['id']) ?>"><?= htmlspecialchars($product['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="number" name="rating" min="1" max="5" placeholder="Rating (1-5)" required>
        <textarea name="review" rows="5" placeholder="Your Review" required></textarea>
        <button type="submit" class="btn">Submit</button>
    </form>
</div>

</body>
</html>
