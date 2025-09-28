<?php
session_start();
include 'config.php';

// Redirect if already logged in
if (isset($_SESSION['customer'])) {
    header("Location: dashboard.php");
    exit();
} elseif (isset($_SESSION['admin'])) {
    header("Location: admin_dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Define admin users
    $admins = [
        'admin1@barterbay.com' => 'admin123',
        'admin2@barterbay.com' => 'admin456'
    ];

    // Check if the user is an admin
    if (array_key_exists($email, $admins) && $password === $admins[$email]) {
        $_SESSION['admin'] = $email;
        header("Location: admin_dashboard.php");
        exit();
    }

    if (!empty($email) && !empty($password)) {
        $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = :email");
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $result = $stmt->execute();

        $row = $result->fetchArray(SQLITE3_ASSOC);

        if ($row && password_verify($password, $row['password'])) {
            $_SESSION['customer'] = $row['id'];
            $_SESSION['customer_name'] = $row['name'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "Please enter both email and password.";
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

        input[type="email"], input[type="password"] {
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
    <p>Login to trade, shop, and manage your profile!</p>
</div>

<div class="container">
    <h2>Login</h2>
    <?php if (isset($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="POST" action="login.php">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" class="btn">Login</button>
    </form>
    <p style="margin-top: 10px;">Not registered? <a href="signup.php">Sign up here</a></p>
    <p style="font-size: 13px; color: #ccc;">Admins use: admin1@barterbay.com or admin2@barterbay.com</p>
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
