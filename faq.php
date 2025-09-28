<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - Barter Bay</title>
    <style>
        body {
            background: linear-gradient(to right, red, blue);
            color: white;
            text-align: center;
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            margin: 50px auto;
            width: 80%;
            max-width: 900px;
            padding: 30px;
            background: rgba(0, 0, 0, 0.8);
            border-radius: 10px;
        }
        .faq-item {
            margin-bottom: 20px;
            text-align: left;
        }
        .question {
            font-weight: bold;
            color: orange;
            cursor: pointer;
            margin-bottom: 5px;
            font-size: 18px;
        }
        .answer {
            display: none;
            padding: 10px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 5px;
            color: #fff;
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container">
    <h2>Frequently Asked Questions</h2>

    <?php
    $faqs = [
        "What is Barter Bay?" => "Barter Bay is a platform that allows users to trade and buy products securely.",
        "How do I trade products?" => "Go to the Trade page, select a product, and send a trade request.",
        "How does rating work?" => "After a trade, you can rate the product from 1 to 5 stars and leave a review.",
        "Is my personal information safe?" => "Yes, we use industry-standard encryption to protect your data.",
        "How do I contact support?" => "You can use the Contact page to send us your queries.",
        "Can I sell products directly?" => "Currently, we support bartering and purchases, not direct selling.",
        "What is the value matching system?" => "We ensure fair trades by matching product value and category.",
        "How long does trade approval take?" => "Admins usually review trades within 24 hours.",
        "Can I cancel a trade request?" => "Yes, if the trade hasn’t been approved yet.",
        "Can I trade the same product twice?" => "No, once a product is traded, ownership is transferred.",
        "What happens after a trade is approved?" => "Products are swapped in the system and both users are notified.",
        "Can I update my profile?" => "Yes, go to the Profile page to update your name, email, password, or picture.",
        "Is there a mobile app for Barter Bay?" => "A mobile app is in development. Stay tuned!",
        "Do I need to pay anything to use Barter Bay?" => "No, Barter Bay is free to use for all registered users.",
        "How do I use the cart and checkout?" => "Add products to your cart and proceed to checkout using QR code payment.",
        "What happens if I receive a damaged product?" => "Please report it immediately through the Contact page and we will assist with the return or exchange.",
        "Can I trade items from different categories?" => "Yes, as long as both users agree to the trade.",
        "How can I reset my password?" => "You can reset your password through the 'Forgot Password' link on the login page.",
        "Is there a limit to how many products I can trade?" => "No, but the system will review your trades for fairness.",
        "Can I track my trade requests?" => "Yes, you can check the status of your trades from your account dashboard.",
        "Do you offer international trades?" => "Currently, Barter Bay operates only within certain regions, but we are expanding!",
        "What should I do if I encounter a technical issue?" => "Please contact support and we’ll resolve it as soon as possible."
    ];

    foreach ($faqs as $question => $answer) {
        echo "<div class='faq-item'>";
        echo "<p class='question'>{$question}</p>";
        echo "<p class='answer'>{$answer}</p>";
        echo "</div>";
    }
    ?>
</div>

<script>
    document.querySelectorAll('.question').forEach(question => {
        question.addEventListener('click', () => {
            let answer = question.nextElementSibling;
            answer.style.display = answer.style.display === 'block' ? 'none' : 'block';
        });
    });
</script>

</body>
</html>
