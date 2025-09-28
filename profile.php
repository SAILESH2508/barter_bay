<?php
session_start();
if (!isset($_SESSION['customer'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';
$customer_id = $_SESSION['customer'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];

    // Update query for SQLite
    $stmt = $conn->prepare("UPDATE users SET name = :name, email = :email WHERE id = :id");
    $stmt->bindValue(':name', $name, SQLITE3_TEXT);
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $stmt->bindValue(':id', $customer_id, SQLITE3_INTEGER);
    $stmt->execute();
    echo "<script>alert('Profile Updated Successfully!');</script>";
}

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
$stmt->bindValue(':id', $customer_id, SQLITE3_INTEGER);
$result = $stmt->execute();
$user = $result->fetchArray(SQLITE3_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <style>
        body { background: linear-gradient(to right, red, blue); color: white; text-align: center; }
        .container { margin: 50px auto; width: 50%; padding: 20px; background: rgba(0, 0, 0, 0.8); border-radius: 10px; }
        input { width: 90%; padding: 10px; margin: 10px 0; }
        .btn { background: red; color: white; padding: 10px; border: none; cursor: pointer; transition: 0.3s; }
        .btn:hover { background: blue; }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container">
    <h2>Update Profile</h2>
    <form method="POST">
        <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        <button type="submit" class="btn">Update</button>
    </form>
</div>

</body>
</html>
