<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

if ($isAdmin && isset($_POST['reset'])) {
    $conn->query("DELETE FROM users");
    echo "<script>alert('All users have been deleted.'); window.location.href='read.php';</script>";
    exit();
}

$result = $conn->query("SELECT * FROM users");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registered Users</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="read.css">
</head>
<body>
    <div class="container">
        <h1 class="logo">FPBG<br>STOCK</h1>
        <h2 class="page-title">Registered Users</h2>

        <?php if ($isAdmin): ?>
        <div class="admin-controls">
            <form method="POST">
                <button type="submit" name="reset" class="btn-danger">Reset Table</button>
            </form>
            <a href="create.php" class="btn-primary">Create New User</a>
        </div>
        <?php endif; ?>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['user_id']) ?></td>
                                <td><?= htmlspecialchars($row['username']) ?></td>
                                <td><?= htmlspecialchars($row['role']) ?></td>
                                <td>
                                    <?php if ($isAdmin): ?>
                                        <a href="update.php?user_id=<?= $row['user_id'] ?>" class="btn-edit">Edit</a>
                                        <a href="delete.php?user_id=<?= $row['user_id'] ?>" onclick="return confirm('Are you sure?')" class="btn-delete">Delete</a>
                                    <?php else: ?>
                                        No actions
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4">No users found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="back-link">
            <a href="dashboard.php" class="btn-back">← Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
