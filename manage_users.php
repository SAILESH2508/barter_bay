<?php
session_start();
include 'config.php';

// Check if the admin is logged in
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Fetch users from SQLite database
$result = $conn->query("SELECT * FROM users");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
    <style>
        body { 
            font-family: Arial; 
            background: linear-gradient(to right, red, blue); 
            color: white; 
            text-align: center; 
        }
        table { 
            width: 90%; 
            margin: 30px auto; 
            border-collapse: collapse; 
            background: rgba(0,0,0,0.8); 
        }
        th, td { 
            padding: 10px; 
            border: 1px solid white; 
        }
        th { 
            background-color: red; 
        }
        a.button {
            background: blue; 
            padding: 8px 12px; 
            color: white; 
            text-decoration: none; 
            border-radius: 5px;
        }
        a.button:hover { 
            background: darkred; 
        }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
    <h2>Manage Customers</h2>
    <table>
        <tr>
            <th>ID</th><th>Name</th><th>Email</th><th>Actions <div style="margin: 20px;">
        <a href="add_user.php" class="button">Add New User</a>
    </div></th>
        </tr>
        <?php while ($user = $result->fetchArray(SQLITE3_ASSOC)): ?>
            <tr>
                <td><?= $user['id'] ?></td>
                <td><?= htmlspecialchars($user['name']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td>
                    <a href="edit_user.php?id=<?= $user['id'] ?>" class="button">Edit</a>
                    <a href="delete_user.php?id=<?= $user['id'] ?>" class="button" onclick="return confirm('Delete user?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
