<?php
session_start();
include 'config.php';

// Basic admin authentication (assuming fixed credentials)
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Define the SQL query for fetching trade data
$sql = "
    SELECT trades.*, 
           u1.name AS sender_name, 
           u2.name AS receiver_name,
           p1.name AS sender_product,
           p2.name AS receiver_product
    FROM trades
    JOIN users u1 ON trades.sender_id = u1.id
    JOIN users u2 ON trades.receiver_id = u2.id
    JOIN products p1 ON trades.sender_product_id = p1.id
    JOIN products p2 ON trades.receiver_product_id = p2.id
    ORDER BY trades.trade_date DESC
";

// Try to execute the query and fetch data
try {
    // Prepare and execute the query
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute();

    if (!$result) {
        throw new Exception("Query failed: " . $conn->lastErrorMsg());
    }
} catch (Exception $e) {
    // Catch exceptions (like query failures) and display error message
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Manage Trades</title>
    <style>
        body { background: linear-gradient(to right, red, blue); color: white; font-family: Arial, sans-serif; text-align: center; }
        table { width: 90%; margin: 20px auto; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid white; }
        .btn { padding: 5px 10px; margin: 5px; background: red; color: white; border: none; cursor: pointer; }
        .btn:hover { background: blue; }
    </style>
</head>
<body>

<h2>Pending Trade Requests</h2>

<table>
    <tr>
        <th>Sender</th>
        <th>Receiver</th>
        <th>Sender Product</th>
        <th>Receiver Product</th>
        <th>Status</th>
        <th>Action</th>
    </tr>
    <?php 
    // Fetch and display each row from the result set
    while ($row = $result->fetchArray(SQLITE3_ASSOC)): ?>
        <tr>
            <td><?= htmlspecialchars($row['sender_name']) ?></td>
            <td><?= htmlspecialchars($row['receiver_name']) ?></td>
            <td><?= htmlspecialchars($row['sender_product']) ?></td>
            <td><?= htmlspecialchars($row['receiver_product']) ?></td>
            <td><?= htmlspecialchars($row['status']) ?></td>
            <td>
                <?php if ($row['status'] === 'Pending'): ?>
                    <form method="POST" action="trade_action.php" style="display:inline;">
                        <input type="hidden" name="trade_id" value="<?= $row['id'] ?>">
                        <button class="btn" name="action" value="Approve">Approve</button>
                        <button class="btn" name="action" value="Reject">Reject</button>
                    </form>
                <?php else: ?>
                    <?= htmlspecialchars($row['status']) ?>
                <?php endif; ?>
            </td>
        </tr>
    <?php endwhile; ?>
</table>

</body>
</html>
