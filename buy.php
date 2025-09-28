<?php
session_start();
if (!isset($_SESSION['customer'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';

$customer_id = $_SESSION['customer'];

// Fetch cart items
$query = "SELECT c.product_id, p.name, p.price, c.quantity 
          FROM cart c 
          JOIN products p ON c.product_id = p.id 
          WHERE c.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bindValue(1, $customer_id, SQLITE3_INTEGER);
$result = $stmt->execute();

$cart_items = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $cart_items[] = $row;
}

if (empty($cart_items)) {
    die("Your cart is empty!");
}

// Payment confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    $payment_method = $_POST['payment_method'] ?? 'upi';
    $delivery_address = $_POST['delivery_address'] ?? '';

    $conn->exec('BEGIN');

    try {
        foreach ($cart_items as $item) {
            $insert = $conn->prepare("INSERT INTO purchases (user_id, product_id, price, purchase_date, payment_method, delivery_address) 
                                      VALUES (?, ?, ?, datetime('now'), ?, ?)");
            $insert->bindValue(1, $customer_id, SQLITE3_INTEGER);
            $insert->bindValue(2, $item['product_id'], SQLITE3_INTEGER);
            $insert->bindValue(3, $item['price'], SQLITE3_FLOAT);
            $insert->bindValue(4, $payment_method, SQLITE3_TEXT);
            $insert->bindValue(5, $delivery_address, SQLITE3_TEXT);
            $insert->execute();
        }

        $delete = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $delete->bindValue(1, $customer_id, SQLITE3_INTEGER);
        $delete->execute();

        $conn->exec('COMMIT');

        header("Location: receipt_purchase.php?customer_id=" . $customer_id . "&method=" . $payment_method);
        exit();
    } catch (Exception $e) {
        $conn->exec('ROLLBACK');
        die("Error: Could not process the payment. Please try again.");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Complete Your Purchase</title>
    <style>
     body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, red, blue);
            color: white;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: rgba(0, 0, 0, 0.9);
            border-radius: 12px;
            text-align: center;
        }
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
            background: white;
            color: black;
        }
        th, td {
            padding: 10px;
            border: 1px solid black;
        }
        th {
            background: #ddd;
        }
        .btn {
            background: red;
            color: white;
            padding: 12px 25px;
            border: none;
            margin-top: 20px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background: blue;
        }
        .qr-section, .bank-section, .card-section {
            display: none;
            margin-top: 20px;
            background: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 10px;
        }
        select {
            padding: 8px;
            font-size: 16px;
            margin-top: 20px;
        }
        label {
            font-weight: bold;
        }
        .card-section {
    width: 360px;
    border-radius: 15px;
    background: linear-gradient(135deg, #283e51, #485563);
    color: #fff;
    padding: 20px;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.4);
    font-family: 'Segoe UI', sans-serif;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.chip {
    width: 40px;
    height: 30px;
    background: gold;
    border-radius: 5px;
}

.card-brand {
    width: 50px;
}

form label {
    font-size: 12px;
    margin-top: 10px;
    display: block;
}

form input {
    width: 90%;
    padding: 8px;
    margin-top: 3px;
    border: none;
    border-radius: 5px;
    background-color: rgba(255,255,255,0.1);
    color: white;
    font-size: 14px;
}

.card-row {
    display: flex;
    gap: 10px;
}

.card-row div {
    flex: 1;
}

button {
    margin-top: 15px;
    width: 100%;
    padding: 10px;
    border: none;
    background-color: #00b894;
    color: white;
    font-size: 16px;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s;
}

button:hover {
    background-color: #019875;
}
.qr-section {
    width: 100%;
    max-width: 400px;
    background-color: #f9f9f9;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    font-family: 'Segoe UI', sans-serif;
    color: #333;
}

.cod-header {
    display: flex;
    align-items: center;
    gap: 10px;
}

.cod-icon {
    width: 32px;
    height: 32px;
}

.cod-details {
    margin-top: 10px;
}

.address-box {
    width: 90%;
    border: 1px solid #ccc;
    border-radius: 6px;
    padding: 8px;
    resize: vertical;
    background-color: #fff;
    color: #333;
    font-family: inherit;
    margin-top: 5px;
}

.cod-note {
    margin-top: 12px;
    font-size: 13px;
    color: #555;
}

.cod-support {
    display: inline-block;
    margin-top: 15px;
    color: #007bff;
    text-decoration: none;
    font-size: 14px;
}

.cod-support:hover {
    text-decoration: underline;
}

    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container">
    <h2>Complete Your Purchase</h2>

    <table>
        <tr>
            <th>Product Name</th>
            <th>Price</th>
            <th>Qty</th>
            <th>Total</th>
        </tr>
        <?php 
        $total = 0;
        foreach ($cart_items as $item):
            $item_total = $item['price'] * $item['quantity'];
            $total += $item_total;
        ?>
            <tr>
                <td><?= htmlspecialchars($item['name']) ?></td>
                <td>₹<?= number_format($item['price'], 2) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td>₹<?= number_format($item_total, 2) ?></td>
            </tr>
        <?php endforeach; ?>

        <?php
            $cgst = $total * 0.09;
            $sgst = $total * 0.09;
            $grand_total = $total + $cgst + $sgst;
        ?>

        <tr><td colspan="3"><strong>Subtotal</strong></td><td><strong>₹<?= number_format($total, 2) ?></strong></td></tr>
        <tr><td colspan="3"><strong>CGST (9%)</strong></td><td><strong>₹<?= number_format($cgst, 2) ?></strong></td></tr>
        <tr><td colspan="3"><strong>SGST (9%)</strong></td><td><strong>₹<?= number_format($sgst, 2) ?></strong></td></tr>
        <tr><td colspan="3"><strong>Grand Total</strong></td><td><strong>₹<?= number_format($grand_total, 2) ?></strong></td></tr>
    </table>
    <div class="container">
    <div class="pay-container">
        <form method="POST <label for="payment_method">Select Payment Method:</label><br>
            <select name="payment_method" id="payment_method" onchange="showSection(this.value)">
                <option value="select">--Select--</option>
                <option value="upi">UPI / QR Code</option>
                <option value="net_banking">Net Banking</option>
                <option value="card">Credit/Debit Card</option>
                <option value="cod">Cash on Delivery</option>
            </select>
            <center>
                <div id="upi" class="qr-section" style="text-align: center; padding: 20px; background-color:gray; border-radius: 10px; color: white;">
                    <div style="margin-bottom: 15px;">
                        <img src="https://img.icons8.com/color/48/000000/google-pay.png" alt="Google Pay" style="margin: 0 8px;">
                        <img src="https://img.icons8.com/color/48/000000/phone-pe.png" alt="PhonePe" style="margin: 0 8px;">
                        <img src="https://img.icons8.com/color/48/000000/paytm.png" alt="Paytm" style="margin: 0 8px;">
                        <img src="https://img.icons8.com/color/48/000000/bhim.png" alt="BHIM UPI" style="margin: 0 8px;">
                        <img src="https://img.icons8.com/color/48/000000/visa.png" alt="Visa" style="margin: 0 8px;">
                    </div>
                    <h3 style="color: #00ff99;">Scan to Pay</h3>
                    <?php if (file_exists("qr_code.jpg")): ?>
                        <img src="qr_code.jpg" alt="QR Code" width="200" style="margin: 15px 0;"><br>
                        <small style="display: block; color: #ccc;">Scan using any UPI app and click 'Confirm Payment' after successful payment.</small>
                    <?php else: ?>
                        <p style="color: yellow;">QR Code not available. Please contact support.</p>
                    <?php endif; ?>
                </div>
                <div id="net_banking" class="bank-section" style="text-align: center; padding: 20px; background:grey; border-radius: 10px; color: white;">
                    <div style="margin-bottom: 15px;">
                        <img src="https://img.icons8.com/color/48/000000/visa.png" alt="Visa" style="margin: 0 8px;">
                        <img src="https://img.icons8.com/color/48/000000/mastercard-logo.png" alt="MasterCard" style="margin: 0 8px;">
                        <img src="https://img.icons8.com/color/48/000000/rupay.png" alt="RuPay" style="margin: 0 8px;">
                    </div>
                    <h3 style="color: #00ff99;">Bank Transfer Instructions</h3>
                    <p>Please transfer the total amount to:</p>
                    <p style="line-height: 1.6;">
                        <strong>Bank:</strong> Barter Bank<br>
                        <strong>A/C No:</strong> 1234567890<br>
                        <strong>IFSC:</strong> BART0000123<br>
                        <strong>Name:</strong> Barter Bay Pvt Ltd
                    </p>
                    <p>After payment, click 'Confirm Payment'.</p>
                </div>
                <div id="card" class="card-section">
                    <div class="card-header">
                        <div class="chip"></div>
                        <img src="https://img.icons8.com/color/48/000000/visa.png" alt="Visa" class="card-brand">
                    </div>
                    <label for="card-number">Card Number</label>
                    <input type="text" id="card-number" maxlength="19" placeholder="XXXX XXXX XXXX XXXX" required>

                    <label for="card-name">Card Holder</label>
                    <input type="text" id="card-name" placeholder="Full Name" required>

                    <div class="card-row">
                        <div>
                            <label for="expiry">Expiry</label>
                            <input type="text" id="expiry" placeholder="MM/YY" maxlength="5" required>
                        </div>
                        <div>
                            <label for="cvv">CVV</label>
                            <input type="password" id ="cvv" maxlength="4" placeholder="***" required>
                        </div>
                    </div>

                    <button type="submit">Pay Now</button>
                </div>

                <div id="cod" class="qr-section">
                    <div class="cod-header">
                        <img src="https://img.icons8.com/ios-filled/50/cash-on-delivery.png" alt="COD Icon" class="cod-icon">
                        <h3>Cash on Delivery</h3>
                    </div>

                    <p>Your order will be processed and delivered in 2–5 business days.<br>
                    Please keep correct change ready</p>

                    <div class="cod-details">
                        <label for="delivery-address"><strong>Enter Delivery Address:</strong></label><br>
                        <textarea id="delivery-address" name="delivery_address" rows="3" class="address-box" placeholder="e.g., 123 Main Street, City, ZIP"></textarea>
                    </div>

                    <p class="cod-note">Please ensure someone is available to receive the package. Payment must be made in full at the time of delivery.</p>

                    <a href="#" class="cod-support">Need help with your order?</a>
                </div>
                <button class="btn" onclick="redirectToReceipt()">Confirm Payment</button>
            </form>
        </div>
    </center>
    </div>
    <script>
        function showSection(method) {
            const methods = ['upi', 'net_banking', 'card', 'cod'];
            methods.forEach(id => {
                document.getElementById(id).style.display = (method === id) ? 'block' : 'none';
            });
        }
        window.onload = () => showSection(document.getElementById('payment_method').value);
        function redirectToReceipt() {
        const customerId = <?= json_encode($customer_id) ?>; // Pass the customer ID from PHP
        const paymentMethod = document.getElementById('payment_method').value; // Get selected payment method
        window.location.href = `receipt_purchase.php?customer_id=${customerId}&method=${paymentMethod}`;
    }
    </script>

</body>
</html>