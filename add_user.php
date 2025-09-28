<?php
session_start();
include 'config.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = $_POST['name'];
    $email = $_POST['email'];
    $pass  = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Check if email already exists
    $check = $conn->prepare("SELECT * FROM users WHERE email = :email");
    $check->bindValue(':email', $email, SQLITE3_TEXT);
    $result = $check->execute();

    if ($result->fetchArray(SQLITE3_ASSOC)) {
        $error = "Email already exists!";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $stmt->bindValue(':password', $pass, SQLITE3_TEXT);

        if ($stmt->execute()) {
            header("Location: manage_users.php");
            exit();
        } else {
            $error = "Error: " . $conn->lastErrorMsg();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add User</title>
    <style>
        body { font-family: Arial; background: linear-gradient(to right, red, blue); color: white; }
        form { width: 400px; margin: 50px auto; background: rgba(0,0,0,0.7); padding: 20px; border-radius: 10px; }
        input { width: 90%; padding: 10px; margin: 10px 0; border-radius: 5px; border: none; }
        .btn { background: red; color: white; padding: 10px 20px; border: none; cursor: pointer; }
        .btn:hover { background: blue; }
        h2 { text-align: center; }
        .error { color: yellow; text-align: center; }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<h2>Add New User</h2>

<?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

<form method="post">
    <label>Name:</label>
    <input type="text" name="name" required>

    <label>Email:</label>
    <input type="email" name="email" required>

    <label>Password:</label>
    <input type="password" name="password" required>

    <button type="submit" class="btn">Add User</button>
</form>

</body>
</html>
