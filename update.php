<?php
include 'connect.php'; // Ensure database connection

// Check if user_id is passed
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
</head>
<body>
    <h2>Edit User</h2>
    <form action="update_process.php" method="POST">
        <input type="hidden" name="user_id" value="<?= $user_id; ?>">
        <input type="text" name="username" value="<?= $user['username']; ?>" required>
        <button type="submit">Update</button>
    </form>
</body>
</html>
