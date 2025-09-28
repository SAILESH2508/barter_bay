<?php
session_start();
include 'config.php';

if (!isset($_SESSION['customer'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['customer'];

// Get payment method
$method_raw = $_GET['method'] ?? 'upi';
$method_map = [
    'upi' => 'QR Code via UPI',
    'card' => 'Credit/Debit Card',
    'cod' => 'Cash On Delivery',
    'netbanking' => 'Net Banking',
];
$payment_method_label = $method_map[$method_raw] ?? 'Unknown';

// Fetch cart items
$query = "SELECT c.id, pr.name AS product_name, pr.category, pr.price, c.quantity 
          FROM cart c 
          JOIN products pr ON c.product_id = pr.id 
          WHERE c.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bindValue(1, $customer_id, SQLITE3_INTEGER);
$result = $stmt->execute();

$purchases = [];
$total_amount = 0;

while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $row['total_price'] = $row['price'] * $row['quantity'];
    $total_amount += $row['total_price'];
    $purchases[] = $row;
}

if (empty($purchases)) {
    die("No items found in cart.");
}

// Tax calculations
$gst_rate = 0.18;
$gst_amount = $total_amount * $gst_rate;
$cgst = $gst_amount / 2;
$sgst = $gst_amount / 2;
$grand_total = $total_amount + $gst_amount;

$receipt_id = rand(100000, 999999);
$today = date("Y-m-d H:i:s");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cart Receipt - Barter Bay</title>
    <style>
        body {
            background: linear-gradient(to right, red, blue);
            color: white;
            font-family: 'Courier New', Courier, monospace;
            margin: 0;
            padding: 0;
        }

        .wrapper-border {
            margin: 20px auto;
            padding: 0;
            border: 3px dashed #000;
            border-radius: 16px;
            background: white;
            color: black;
            max-width: 750px;
        }

        .bill-container {
            background: #fff;
            color: #000;
            padding: 30px 40px;
            border-radius: 12px;
            max-width: 750px;
            margin: 0 auto;
            box-sizing: border-box;
        }

        .header {
            text-align: center;
            padding-bottom: 10px;
            border-bottom: 2px dotted #333;
        }

        .header img {
            width: 80px;
            height: auto;
            margin-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            color: #c0392b;
        }

        .tagline {
            font-style: italic;
            font-size: 14px;
            color: #555;
        }

        .bill-details {
            margin-top: 20px;
            font-size: 14px;
        }

        .bill-details table {
            width: 100%;
        }

        .bill-details td {
            padding: 5px 0;
        }

        table.items {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
            font-size: 15px;
        }

        table.items th, table.items td {
            border: 1px solid #000;
            padding: 10px;
            text-align: center;
        }

        table.items th {
            background: #c0392b;
            color: #fff;
        }

        .total-row {
            font-weight: bold;
            background: #f7f7f7;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 13px;
            color: #333;
        }

        .offer-section {
            margin-top: 30px;
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            border: 1px dashed #c0392b;
            color: #2c3e50;
            text-align: center;
        }

        .offer-section h3 {
            margin-top: 0;
            color: #c0392b;
        }

        .btn-container {
            margin-top: 30px;
            text-align: center;
        }

        .btn {
            background: #c0392b;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin: 0 10px;
        }

        .btn:hover {
            background: #2980b9;
        }

        @media print {
            body {
                background: white;
                color: black;
            }

            .btn-container {
                display: none;
            }

            .wrapper-border {
                border: 3px dashed #000;
                margin: 0;
                padding: 10px;
            }

            .bill-container {
                box-shadow: none;
                margin: 0;
                padding: 0;
                border: none;
            }

            #navbar {
                display: none !important;
            }
        }
    </style>
</head>
<body>
<div id="navbar"><?php include 'navbar.php'; ?></div>
<div class="wrapper-border">
    <div class="bill-container">
        <div class="header">
            <img src="images/seal.png" alt="Barter Bay">
            <h1>Barter Bay</h1>
            <div class="tagline">"Where every trade is a treasure!"</div>
        </div>

        <div class="bill-details">
            <table>
                <tr>
                    <td><strong>Receipt #:</strong> <?= $receipt_id ?></td>
                    <td style="text-align:right;"><strong>Date:</strong> <?= date("F j, Y, g:i A") ?></td>
                </tr>
                <tr>
                    <td><strong>Customer ID:</strong> <?= $customer_id ?></td>
                    <td style="text-align:right;"><strong>Cashier ID:</strong> BB-CASH-001</td>
                </tr>
                <tr>
                    <td><strong>Payment Mode:</strong> <?= htmlspecialchars($payment_method_label) ?></td>
                    <td style="text-align:right;"><strong>Store:</strong> Barter Bay, Coimbatore, Tamil Nadu</td>
                </tr>
            </table>
        </div>

        <table class="items">
            <tr>
                <th>Cart ID</th>
                <th>Product</th>
                <th>Category</th>
                <th>Qty</th>
                <th>Price (₹)</th>
                <th>Total (₹)</th>
            </tr>
            <?php foreach ($purchases as $purchase): ?>
                <tr>
                    <td><?= htmlspecialchars($purchase['id']) ?></td>
                    <td><?= htmlspecialchars($purchase['product_name']) ?></td>
                    <td><?= htmlspecialchars($purchase['category']) ?></td>
                    <td><?= htmlspecialchars($purchase['quantity']) ?></td>
                    <td><?= number_format($purchase['price'], 2) ?></td>
                    <td><?= number_format($purchase['total_price'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
            <tr class="total-row">
                <td colspan="5" style="text-align: right;">Subtotal</td>
                <td>₹<?= number_format($total_amount, 2) ?></td>
            </tr>
            <tr class="total-row">
                <td colspan="5" style="text-align: right;">CGST (9%)</td>
                <td>₹<?= number_format($cgst, 2) ?></td>
            </tr>
            <tr class="total-row">
                <td colspan="5" style="text-align: right;">SGST (9%)</td>
                <td>₹<?= number_format($sgst, 2) ?></td>
            </tr>
            <tr class="total-row">
                <td colspan="5" style="text-align: right;">Grand Total</td>
                <td><strong>₹<?= number_format($grand_total, 2) ?></strong></td>
            </tr>
        </table>

        <div class="offer-section">
            <h3>🎉 Special Offer!</h3>
            <p>Use code <strong>BARTER20</strong> on your next trade or purchase and get <strong>20% OFF</strong> instantly!</p>
            <small>Valid for 7 days from today. Don't miss out!</small>
        </div>

        <div class="footer">
            <hr style="border-top: 2px dotted #bbb; width: 80%; margin: 20px auto;">
            <p>Thank you for shopping with <strong>Barter Bay</strong>!</p>
            <ul style="list-style-type: none; padding-left: 0; line-height: 1.8;">
                <li><strong>Store Address:</strong> Barter Bay, Coimbatore, Tamil Nadu</li>
                <li><strong>Support:</strong> 📞 9688748656, 9342816669</li>
                <li><strong>Email:</strong> 
                    <a href="mailto:support@barterbay.com">support@barterbay.com</a></li>
                <li>Visit again for the best trade and purchase experience! 🛍️</li>
            </ul>
        </div>
    </div>
</div>
<div class="btn-container">
    <button class="btn" onclick="window.print()">🖨️ Print Bill</button>
    <button class="btn" onclick="window.location.href='dashboard.php'">🏠 Back to Dashboard</button>
</div>
</body>
</html>
