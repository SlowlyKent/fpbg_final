<?php
session_start();
include 'connect.php'; // Ensure this file correctly connects to the database

if (!isset($_SESSION['user_id'])) {
    header('Location: permission-denied.php');
    exit();
}

$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['user_id'], $_POST['username'])) {
        $user_id = $_POST['user_id'];
        $username = trim($_POST['username']);
        $new_password = trim($_POST['new_password']); // Fix: Match the input name from update.php

        if (!empty($new_password)) {
            // Fix: Use new_password correctly
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $sql = "UPDATE users SET username=?, password=? WHERE user_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $username, $hashed_password, $user_id);
        } else {
            // If no new password is provided, update only the username
            $sql = "UPDATE users SET username=? WHERE user_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $username, $user_id);
        }

        if ($stmt->execute()) {
            echo "<script>alert('User updated successfully!'); window.location.href='read.php';</script>";
        } else {
            echo "<script>alert('Error updating user!'); window.location.href='update.php?user_id=$user_id';</script>";
        }

        $stmt->close();
    } else {
        echo "<script>alert('Invalid request! Missing user_id or username.'); window.location.href='read.php';</script>";
    }
}

$conn->close();
?>
