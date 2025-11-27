<?php
require '../includes/config.php';
require '../includes/functions.php';

if(!isset($_SESSION['license'])) header('Location: index.php');

// Tambah lisensi
if(isset($_POST['add_license'])) {
    $license_key = $_POST['license_key'];
    $stmt = $conn->prepare("INSERT INTO licenses (license_key) VALUES (?)");
    $stmt->bind_param("s", $license_key);
    $stmt->execute();
}

// Aktivasi/nonaktifkan
if(isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $conn->query("UPDATE licenses SET active = NOT active WHERE id = $id");
}

// Hapus lisensi
if(isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM licenses WHERE id = $id");
}

$licenses = $conn->query("SELECT * FROM licenses");
?>
<!DOCTYPE html>
<html>
<head>
    <title>License Manager</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Kangen Bojo Admin Panel</h1>
    </header>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="config.php">Site Configuration</a>
        <a hrefenses.php">License Manager</a>
        <a href="users.php">User Manager</a>
        <a href="../index.php">View Site</a>
        <a href="logout.php">Logout</a>
    </nav>
    <div class="container">
        <h1>License Manager</h1>
        
        <form method="post">
            <input type="text" name="license_key" placeholder="Enter new license key" required>
            <button type="submit" name="add_license">Add License</button>
        </form>
        
        <table>
            <tr>
                <th>ID</th>
                <th>License Key</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
            <?php while($row = $licenses->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['license_key'] ?></td>
                <td><?= $row['active'] ? 'Active' : 'Inactive' ?></td>
                <td><?= $row['created_at'] ?></td>
                <td>
                    <a href="licenses.php?toggle=<?= $row['id'] ?>">Toggle</a>
                    <a href="licenses.php?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>