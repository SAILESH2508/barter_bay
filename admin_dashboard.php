<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

$admin_name = htmlspecialchars($_SESSION['admin']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Barter Bay</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, red, blue);
            color: white;
            text-align: center;
            margin: 0;
            padding: 0;
        }
        h1 {
            margin-top: 30px;
        }
        .container {
            width: 80%;
            margin: 50px auto;
            padding: 20px;
            background: rgba(0, 0, 0, 0.85);
            border-radius: 10px;
        }
        .btn {
            background: red;
            color: white;
            padding: 15px 25px;
            margin: 10px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: 0.3s;
        }
        .btn:hover {
            background: blue;
        }
        .logout-btn {
            background: white;
            color: red;
            font-weight: bold;
            margin-top: 30px;
        }
        .logout-btn:hover {
            color: white;
            background: black;
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<h1>Welcome, <?= $admin_name ?> 👋</h1>

<div class="container">
    <h2>Admin Dashboard - Barter Bay</h2>

    <button class="btn" onclick="location.href='view_products.php'">📦 View Products</button>
    <button class="btn" onclick="location.href='manage_users.php'">👤 Manage Users</button>
    <button class="btn" onclick="location.href='view_trades.php'">🔁 View Trades</button>
    <button class="btn logout-btn" onclick="location.href='logout.php'">🚪 Logout</button>
</div>

</body>
</html>
