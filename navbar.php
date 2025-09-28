<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!-- Modern Marquee Bar -->
<div class="marquee-bar">
    <div class="marquee-text">
        <span style="font-size:xx-large;">WELCOME TO BARTER BAY 💖</span><br>
        <span class="extra-msg">Where every trade is a treasure 🪙</span>
        <span class="extra-msg">| Explore. Exchange. Enjoy. ✨ |</span>
        <span class="extra-msg">Your one-stop hub for smart bartering! 💱 |</span>
        <span class="extra-msg">Trade smarter, not harder! 🚀</span>
    </div>
</div>

<!-- Navbar -->
<nav class="navbar" role="navigation" aria-label="Main Navigation">
    <div class="logo">
        <a href="index.php" class="logo-link">
            <img src="images/seal.png" alt="Barter Bay Seal" class="seal-img">    
            <h1>BARTER BAY</h1>
        </a>
    </div>

    <ul class="nav-links">
        <?php if (isset($_SESSION['customer'])): ?>
            <!-- Customer Navbar -->
            <li><a href="dashboard.php" class="<?= $currentPage == 'dashboard.php' ? 'active' : '' ?>">Dashboard</a></li>
            <li><a href="products.php" class="<?= $currentPage == 'products.php' ? 'active' : '' ?>">Products</a></li>
            <li><a href="trade.php" class="<?= $currentPage == 'trade.php' ? 'active' : '' ?>">Trade</a></li>
            <li><a href="contact.php" class="<?= $currentPage == 'contact.php' ? 'active' : '' ?>">Contact</a></li>
            <li><a href="cart.php" class="<?= $currentPage == 'cart.php' ? 'active' : '' ?>">Cart</a></li>

            <li class="dropdown">
                <a href="#" class="dropbtn">Account ▼</a>
                <div class="dropdown-content">
                    <a href="profile.php">Profile</a>
                    <a href="my_trades.php">My Trades</a>
                    <a href="logout.php">Logout</a>
                </div>
            </li>
            <li class="dropdown">
                <a href="#" class="dropbtn">More ▼</a>
                <div class="dropdown-content">
                    <a href="rate_product.php">Rate Products</a>
                    <a href="faq.php">FAQ</a>
                </div>
            </li>

        <?php elseif (isset($_SESSION['admin'])): ?>
            <!-- Admin Navbar -->
            <li><a href="admin_dashboard.php" class="<?= $currentPage == 'admin_dashboard.php' ? 'active' : '' ?>">Dashboard</a></li>
            
            <li class="dropdown">
                <a href="#" class="dropbtn">Manage ▼</a>
                <div class="dropdown-content">
                    <a href="manage_users.php" class="<?= $currentPage == 'manage_users.php' ? 'active' : '' ?>">Users</a>
                    <a href="view_products.php" class="<?= $currentPage == 'manage_products.php' ? 'active' : '' ?>">Products</a>
                    <a href="view_trades.php" class="<?= $currentPage == 'view_trades.php' ? 'active' : '' ?>">Trades</a>
                </div>
            </li>
            <li class="dropdown">
                <a href="#" class="dropbtn">Account ▼</a>
                <div class="dropdown-content">
                    <a href="logout.php">Logout</a>
                </div>
            </li>

        <?php else: ?>
            <!-- Guest Navbar -->
            <li><a href="login.php" class="<?= $currentPage == 'login.php' ? 'active' : '' ?>">Login</a></li>
            <li><a href="signup.php" class="<?= $currentPage == 'signup.php' ? 'active' : '' ?>">Sign Up</a></li>
            <li><a href="contact.php" class="<?= $currentPage == 'contact.php' ? 'active' : '' ?>">Contact</a></li>
            <li><a href="faq.php" class="<?= $currentPage == 'faq.php' ? 'active' : '' ?>">FAQ</a></li>
            <?php endif; ?>
    </ul>

    <div class="hamburger" onclick="toggleMenu()">☰</div>
</nav>

<!-- Internal CSS -->
<style>
    .marquee-bar {
        width: 100%;
        background-color: blueviolet;
        padding: 10px 0;
        border-bottom: 2px solid #ccc;
        overflow: hidden;
        white-space: nowrap;
        box-sizing: border-box;
        z-index: 1000;
        border-radius: 10px;
    }

    .marquee-text {
        display: inline-block;
        white-space: nowrap;
        animation: marquee 20s linear infinite;
        font-size: 20px;
        font-weight: bold;
        color: #fff;
        width: 100%;
        text-align: center;
    }

    @keyframes marquee {
        0% { transform: translateX(100%); }
        100% { transform: translateX(-100%); }
    }

    .extra-msg {
        color: yellow;
        font-family: Georgia, 'Times New Roman', Times, serif;
        font-size: smaller;
    }

    .navbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: linear-gradient(to right, red, blue);
        padding: 10px 20px;
        flex-wrap: wrap;
    }

    .logo {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .logo-link {
        display: flex;
        align-items: center;
        text-decoration: none;
        gap: 15px;
    }

    .logo h1 {
        color: white;
        font-size: 24px;
        font-weight: bold;
        margin: 0;
    }

    .seal-img {
        width: 60px;
        height: auto;
    }

    .nav-links {
        list-style: none;
        display: flex;
        margin: 0;
        padding: 0;
    }

    .nav-links li {
        margin: 0 12px;
        position: relative;
    }

    .nav-links a {
        text-decoration: none;
        color: white;
        font-weight: bold;
        padding: 10px;
        transition: 0.3s ease-in-out;
    }

    .nav-links a:hover,
    .nav-links a.active {
        color: white;
        transform: scale(1.05);
    }

    .dropdown-content {
        color: #fff;
        display: none;
        position: absolute;
        background: purple;
        min-width: 160px;
        box-shadow: 0px 8px 16px rgba(0,0,0,0.1);
        z-index: 999;
        border-radius: 5px;
    }

    .dropdown:hover .dropdown-content {
        display: block;
    }

    .dropdown-content a {
        color: #333;
        padding: 10px;
        display: block;
        text-align: left;
        text-decoration: none;
        transition: background 0.2s ease;
    }

    .dropdown-content a:hover {
        background: #eee;
        color: #000;
    }

    .hamburger {
        display: none;
        font-size: 28px;
        color: white;
        cursor: pointer;
    }

    @media screen and (max-width: 768px) {
        .nav-links {
            display: none;
            flex-direction: column;
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            background: #f9f9f9;
            text-align: center;
            border-top: 2px solid #ccc;
        }

        .nav-links.show {
            display: flex;
        }

        .nav-links a {
            color: #333;
        }

        .hamburger {
            display: block;
        }
    }
</style>

<!-- JS for Hamburger ----->
<script>
    function toggleMenu() {
        document.querySelector(".nav-links").classList.toggle("show");
    }
</script>
