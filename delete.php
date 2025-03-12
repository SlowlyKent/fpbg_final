<?php
include 'connect.php'; // Ensure database connection

if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    // Prepare and execute delete query
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        echo "<script>alert('User deleted successfully!'); window.location.href='read.php';</script>";
    } else {
        echo "<script>alert('Error deleting user!'); window.location.href='read.php';</script>";
    }

    $stmt->close();
} else {
    echo "<script>alert('Invalid request!'); window.location.href='read.php';</script>";
}

$conn->close();
?>
