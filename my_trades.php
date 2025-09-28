<?php
session_start();
include 'config.php';

if (!isset($_SESSION['customer'])) {
    header("Location: login.php");
    exit();
}

$customer_id = is_array($_SESSION['customer']) ? $_SESSION['customer']['id'] : intval($_SESSION['customer']);

// Fetch trades where the user is the sender or receiver
$query = "
    SELECT t.*, 
           sp.name AS sender_product_name, sp.image AS sender_product_image,
           rp.name AS receiver_product_name, rp.image AS receiver_product_image,
           t.status
    FROM trades t
    JOIN products sp ON t.sender_product_id = sp.id
    JOIN products rp ON t.receiver_product_id = rp.id
    WHERE t.sender_id = :customer_id OR t.receiver_id = :customer_id
    ORDER BY t.id DESC
";

$stmt = $conn->prepare($query);
$stmt->bindValue(':customer_id', $customer_id, SQLITE3_INTEGER);
$result = $stmt->execute();

$trades = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $trades[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Trades - Barter Bay</title>
    <style>
        body {
            background: linear-gradient(to right, red, blue);
            font-family: Arial, sans-serif;
            color: white;
            text-align: center;
        }
        .container {
            width: 90%;
            margin: 30px auto;
        }
        .trade {
            background-color: rgba(0, 0, 0, 0.7);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
        img {
            max-width: 120px;
            height: auto;
            border-radius: 5px;
        }
        .status {
            padding: 6px 12px;
            border-radius: 5px;
            background: yellow;
            color: black;
            font-weight: bold;
            display: inline-block;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container">
    <h2>My Trade Requests</h2>

    <?php if (empty($trades)): ?>
        <p>No trades yet.</p>
    <?php else: ?>
        <?php foreach ($trades as $trade): ?>
            <div class="trade">
                <h3>Trade #<?php echo $trade['id']; ?></h3>
                <div>
                    <strong>Your Product:</strong><br>
                    <img src="uploads/<?php echo htmlspecialchars($trade['sender_product_image']); ?>" alt="">
                    <p><?php echo htmlspecialchars($trade['sender_product_name']); ?></p>
                </div>
                <div>
                    <strong>Offered For:</strong><br>
                    <img src="uploads/<?php echo htmlspecialchars($trade['receiver_product_image']); ?>" alt="">
                    <p><?php echo htmlspecialchars($trade['receiver_product_name']); ?></p>
                </div>
                <div class="status">
                    Status: <?php echo ucfirst($trade['status']); ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>
