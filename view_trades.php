<?php
session_start();
include 'config.php';

// Handle Approve/Reject POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['trade_id'], $_POST['action'])) {
    $trade_id = intval($_POST['trade_id']);
    $action = $_POST['action'];

    // Fetch trade details
    $stmt = $conn->prepare("SELECT * FROM trades WHERE id = ?");
    $stmt->bindValue(1, $trade_id, SQLITE3_INTEGER);
    $trade = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    if (!$trade) {
        echo "Trade not found.";
        exit();
    }

    $sender_id = $trade['sender_id'];
    $receiver_id = $trade['receiver_id'];
    $sender_product = $trade['sender_product_id'];
    $receiver_product = $trade['receiver_product_id'];

    if ($action === 'Approve') {
        $conn->exec('BEGIN TRANSACTION'); // Start transaction

        try {
            // Swap products
            $swap1 = $conn->prepare("UPDATE products SET user_id = ? WHERE id = ?");
            $swap1->bindValue(1, $receiver_id, SQLITE3_INTEGER);
            $swap1->bindValue(2, $sender_product, SQLITE3_INTEGER);
            $swap1->execute();

            $swap2 = $conn->prepare("UPDATE products SET user_id = ? WHERE id = ?");
            $swap2->bindValue(1, $sender_id, SQLITE3_INTEGER);
            $swap2->bindValue(2, $receiver_product, SQLITE3_INTEGER);
            $swap2->execute();

            // Update trade status to 'Approved'
            $update = $conn->prepare("UPDATE trades SET status = 'Approved' WHERE id = ?");
            $update->bindValue(1, $trade_id, SQLITE3_INTEGER);
            $update->execute();

            $conn->exec('COMMIT'); // Commit transaction

            // Send emails to both users
            sendTradeEmail($conn, $sender_id, "Trade Approved", "Your trade #$trade_id has been approved.");
            sendTradeEmail($conn, $receiver_id, "Trade Approved", "Your trade #$trade_id has been approved.");
        } catch (Exception $e) {
            $conn->exec('ROLLBACK'); // Rollback transaction in case of error
            echo "Error approving trade: " . $e->getMessage();
            exit();
        }
    } elseif ($action === 'Reject') {
        $update = $conn->prepare("UPDATE trades SET status = 'Rejected' WHERE id = ?");
        $update->bindValue(1, $trade_id, SQLITE3_INTEGER);
        $update->execute();

        // Send emails to both users
        sendTradeEmail($conn, $sender_id, "Trade Rejected", "Your trade #$trade_id has been rejected.");
        sendTradeEmail($conn, $receiver_id, "Trade Rejected", "Your trade #$trade_id has been rejected.");
    }

    header("Location: view_trades.php");
    exit();
}

// Send email
function sendTradeEmail($conn, $user_id, $subject, $message) {
    $stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->bindValue(1, $user_id, SQLITE3_INTEGER);
    $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    if ($result) {
        $to = $result['email'];
        $headers = "From: no-reply@barterbay.com\r\nContent-Type: text/plain; charset=UTF-8\r\n";
        mail($to, $subject, $message, $headers);
    }
}

// Get trades
$trades = $conn->query("SELECT t.*, 
    u1.name AS sender_name, u2.name AS receiver_name,
    p1.name AS sender_product, p2.name AS receiver_product
    FROM trades t
    JOIN users u1 ON t.sender_id = u1.id
    JOIN users u2 ON t.receiver_id = u2.id
    JOIN products p1 ON t.sender_product_id = p1.id
    JOIN products p2 ON t.receiver_product_id = p2.id
    ORDER BY t.trade_date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Trades</title>
    <style>
        body { font-family: Arial; background: linear-gradient(to right, red, blue); color: white; }
        .container { width: 90%; margin: 40px auto; background: rgba(0,0,0,0.8); padding: 20px; border-radius: 10px; }
        table { width: 100%; border-collapse: collapse; color: white; }
        th, td { padding: 10px; border: 1px solid white; text-align: center; }
        th { background: darkred; }
        td { background: rgba(255,255,255,0.1); }
        .btn { padding: 6px 10px; margin: 2px; background: red; border: none; color: white; cursor: pointer; }
        .btn:hover { background: blue; }
        a { color: yellow; text-decoration: none; }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container">
    <h2>Trade Requests</h2>
    <table>
        <tr>
            <th>Trade ID</th>
            <th>Sender</th>
            <th>Sender Product</th>
            <th>Receiver</th>
            <th>Receiver Product</th>
            <th>Status</th>
            <th>Date</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $trades->fetchArray(SQLITE3_ASSOC)): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['sender_name']) ?></td>
                <td><?= htmlspecialchars($row['sender_product']) ?></td>
                <td><?= htmlspecialchars($row['receiver_name']) ?></td>
                <td><?= htmlspecialchars($row['receiver_product']) ?></td>
                <td><?= $row['status'] ?></td>
                <td><?= $row['trade_date'] ?></td>
                <td>
                    <?php if ($row['status'] === 'Pending'): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="trade_id" value="<?= $row['id'] ?>">
                            <button type="submit" name="action" value="Approve" class="btn">Approve</button>
                            <button type="submit" name="action" value="Reject" class="btn">Reject</button>
                        </form>
                    <?php else: ?>
                        <em><?= $row['status'] ?></em>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

</body>
</html>
