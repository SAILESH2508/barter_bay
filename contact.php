<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $message = $_POST["message"];

    // Prepare SQL query to insert the contact form data into SQLite
    $stmt = $conn->prepare("INSERT INTO contact (name, email, message) VALUES (:name, :email, :message)");
    $stmt->bindValue(':name', $name, SQLITE3_TEXT);
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $stmt->bindValue(':message', $message, SQLITE3_TEXT);

    // Execute the query
    $stmt->execute();

    echo "<script>alert('Message Sent Successfully!');</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <style>
        body { background: linear-gradient(to right, red, blue); color: white; text-align: center; }
        .container { margin: 50px auto; width: 50%; padding: 20px; background: rgba(0, 0, 0, 0.8); border-radius: 10px; }
        input, textarea { width: 90%; padding: 10px; margin: 10px 0; }
        .btn { background: red; color: white; padding: 10px; border: none; cursor: pointer; transition: 0.3s; }
        .btn:hover { background: blue; }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container">
    <h2>Contact Us</h2>
    <form method="POST">
        <input type="text" name="name" placeholder="Your Name" required>
        <input type="email" name="email" placeholder="Your Email" required>
        <textarea name="message" rows="5" placeholder="Your Message" required></textarea>
        <button type="submit" class="btn">Send Message</button>
    </form>
</div>

</body>
</html>
