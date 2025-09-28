<?php
session_start();
include 'config.php';

// Check if the admin is logged in
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Check if the user ID is set in the URL
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Fetch the user details from the database
    $query = "SELECT * FROM users WHERE id = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
    $result = $stmt->execute();

    // Check if the user exists
    $user = $result->fetchArray(SQLITE3_ASSOC);

    if ($user === false) {
        echo "User not found!";
        exit();
    }
} else {
    echo "No user selected!";
    exit();
}

// Update the user details
if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if password is provided, if not, keep the existing password
    if (!empty($password)) {
        $password = password_hash($password, PASSWORD_DEFAULT);
    } else {
        $password = $user['password']; // Keep the existing password
    }

    $update_query = "UPDATE users SET name = :name, email = :email, password = :password WHERE id = :user_id";
    $stmt = $conn->prepare($update_query);

    // Bind the values
    $stmt->bindValue(':name', $name, SQLITE3_TEXT);
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $stmt->bindValue(':password', $password, SQLITE3_TEXT);
    $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);

    if ($stmt->execute()) {
        header("Location: manage_users.php");
        exit();
    } else {
        echo "Error updating user: " . $conn->lastErrorMsg();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
    <style>
        body { font-family: Arial; background: linear-gradient(to right, red, blue); color: white; text-align: center; }
        form { width: 50%; margin: 30px auto; padding: 20px; background: rgba(0, 0, 0, 0.7); border-radius: 10px; }
        label { font-weight: bold; }
        input { width: 90%; padding: 10px; margin-bottom: 15px; background: rgba(255, 255, 255, 0.1); border: 1px solid white; color: white; }
        button { padding: 10px 20px; background: red; color: white; border: none; cursor: pointer; }
        button:hover { background: blue; }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
    <h2>Edit User</h2>
    <form method="post">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
        
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
        
        <label for="password">Password (leave empty to keep current):</label>
        <input type="password" id="password" name="password">
        
        <button type="submit" name="submit">Update User</button>
    </form>
</body>
</html>
