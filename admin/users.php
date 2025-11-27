<?php
require '../includes/config.php';
require '../includes/functions.php';

if(!isset($_SESSION['license'])) header('Location: index.php');

// Tambah user
if(isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $license_key = $_POST['license_key'];
    $stmt = $conn->prepare("INSERT INTO users (username, password, license_key) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $password, $license_key);
    $stmt->execute();
}

// Hapus user
if(isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM users WHERE id = $id");
}

$users = $conn->query("SELECT * FROM users");
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Manager</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Kangen Bojo Admin Panel</h1>
    </header>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="config.php">Site Configuration</a>
        <a href="licenses.php">License Manager</a>
        <a href="users.php">User Manager</a>
        <a href="../index.php">View Site</a>
        <a href="logout.php">Logout</a>
    </nav>
    <div class="container">
        <h1>User Manager</h1>
        
        <form method="post">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="text" name="license_key" placeholder="License Key" required>
            <button type="submit" name="add_user">Add User</button>
        </form>
        
        <table>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>License Key</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
            <?php while($row = $users->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['username'] ?></td>
                <td><?= $row['license_key'] ?></td>
                <td><?= $row['created_at'] ?></td>
                <td>
                    <a href="users.php?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>