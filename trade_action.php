<?php
session_start();
include 'config.php';

if (!isset($_SESSION['admin'])) {
    die("Access denied.");
}

if (isset($_POST['trade_id'], $_POST['action'])) {
    $trade_id = intval($_POST['trade_id']);
    $action = $_POST['action'];

    if (in_array($action, ['Approve', 'Reject'])) {
        // SQLite query with placeholders
        $stmt = $conn->prepare("UPDATE trades SET status = :status WHERE id = :trade_id");
        $stmt->bindValue(':status', $action, SQLITE3_TEXT);
        $stmt->bindValue(':trade_id', $trade_id, SQLITE3_INTEGER);

        // Execute and check if update is successful
        if ($stmt->execute()) {
            header("Location: admin_trades.php");
            exit();
        } else {
            echo "Failed to update trade status.";
        }
    } else {
        echo "Invalid action.";
    }
} else {
    echo "Missing data.";
}
