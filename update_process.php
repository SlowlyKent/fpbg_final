<?php
include 'connect.php'; // Ensure database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'];
    $username = trim($_POST['username']);

    // Validate if username is empty
    if (empty($username)) {
        echo "<script>alert('Username cannot be empty!'); window.location.href='update.php?user_id=$user_id';</script>";
        exit();
    }

    // Update user in the database
    $stmt = $conn->prepare("UPDATE users SET username = ? WHERE user_id = ?");
    $stmt->bind_param("si", $username, $user_id);

    if ($stmt->execute()) {
        echo "<script>alert('User updated successfully!'); window.location.href='read.php';</script>";
    } else {
        echo "<script>alert('Update failed!'); window.location.href='update.php?user_id=$user_id';</script>";
    }

    $stmt->close();
}

$conn->close();
?>
