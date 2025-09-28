<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // SQLite query
    $query = "INSERT INTO users (name, email, password) VALUES (:name, :email, :password)";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':name', $name, SQLITE3_TEXT);
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $stmt->bindValue(':password', $password, SQLITE3_TEXT);

    if ($stmt->execute()) {
        header("Location: login.php");
        exit();
    } else {
        $error = "Signup failed!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Barter Bay - Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, red, blue);
            margin: 0;
            padding: 0;
            color: white;
        }

        .navbar-placeholder {
            height: 60px;
            background: rgba(0,0,0,0.5);
            text-align: center;
            line-height: 60px;
            font-size: 24px;
            font-weight: bold;
        }

        .welcome-banner {
            background-color: rgba(255,255,255,0.1);
            padding: 30px;
            text-align: center;
        }

        .welcome-banner h1 {
            font-size: 36px;
            margin: 0;
        }

        .container {
            width: 340px;
            margin: 40px auto;
            padding: 25px;
            background: rgba(0, 0, 0, 0.85);
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.4);
        }

        input[type="email"], input[type="password"],input[type="text"] {
            width: 90%;
            padding: 10px;
            margin: 12px 0;
            border: none;
            border-radius: 5px;
        }

        .btn {
            background: red;
            color: white;
            padding: 12px;
            width: 100%;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn:hover {
            background: blue;
        }

        a {
            color: lightblue;
            text-decoration: none;
        }

        .error {
            color: yellow;
        }

        .info {
            margin: 30px auto;
            width: 80%;
            padding: 20px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 10px;
        }

        ul {
            list-style: none;
            padding-left: 0;
        }

        ul li::before {
            content: "✔ ";
            color: yellow;
            margin-right: 6px;
        }

        footer {
            text-align: center;
            margin-top: 40px;
            padding: 10px;
            font-size: 14px;
            background: rgba(0,0,0,0.3);
        }

        .support {
            background-color: rgba(0, 0, 0, 0.6);
            padding: 20px;
            margin-top: 10px;
            border-radius: 8px;
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="welcome-banner">
    <h1>Welcome to Barter Bay</h1>
    <p>Signup and Login to trade, shop, and manage your profile!</p>
</div>

<div class="container">
    <h2>Sign Up</h2>
    <?php if (isset($error)) echo "<p style='color: red;'>$error</p>"; ?>
    <form method="POST">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" class="btn">Sign Up</button>
    </form>
    <p>Already have an account? <a href="login.php">Login</a></p>
</div>

<div class="info">
    <h2>Why Choose Barter Bay?</h2>
    <ul>
        <li>Trade goods with other users safely</li>
        <li>Secure QR-based payments</li>
        <li>Live product filtering and search</li>
        <li>Ratings and reviews on all trades</li>
        <li>PDF receipts and trade tracking</li>
    </ul>
</div>

<div class="info">
    <h2>Security You Can Trust</h2>
    <p>We value your privacy. Your passwords are encrypted, and no sensitive data is ever shared.</p>
</div>

<div class="info support">
    <h2>Need Help?</h2>
    <p>Problems logging in? <a href="contact.php">Contact support</a> or read our <a href="faq.php">FAQs</a>.</p>
</div>

<footer>
    &copy; <?= date("Y") ?> Barter Bay. All rights reserved.
</footer>

</body>
</html>
