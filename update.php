<?php
include 'connect.php'; 
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

if (!$isAdmin) {
    echo "<script>alert('Permission Denied'); window.location.href='read.php';</script>";
    exit();
}


if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    // Fetch user data based on user_id
    $stmt = $conn->prepare("SELECT username FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Check if user exists
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
    } else {
        echo "<script>alert('User not found!'); window.location.href='read.php';</script>";
        exit();
    }
} else {
    echo "<script>alert('Invalid request!'); window.location.href='read.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="update.css">
    <h1 class="logo">FPBG<br> STOCK</h1> 
</head>
<body>
    <div class="edit-container">
    <h2>Edit User</h2>
    <form action="update_process.php" method="POST">
        <label>Update Name:</label>
        <input type="hidden" name="user_id" value="<?= $user_id; ?>">
        <input type="text" name="username" value="<?= $user['username']; ?>" required>
        <label>New Password (leave blank to keep current password):</label>
        <input type="password" name="new_password" placeholder="Enter new password">

        <button type="submit">Update</button> 
    </form>
</body>
</html>
