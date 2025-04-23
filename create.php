<?php
session_start();
include 'connect.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if form fields are set
    if (isset($_POST['username'], $_POST['password'], $_POST['confirm_password'])) {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        $confirm_password = trim($_POST['confirm_password']);

        // Check if passwords match
        if ($password !== $confirm_password) {
            echo "<script>alert('Passwords do not match!'); window.location.href='register.php';</script>";
            exit();
        }

        // Check if username already exists
        $checkStmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
        $checkStmt->bind_param("s", $username);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            echo "<script>alert('Username already taken!'); window.location.href='register.php';</script>";
            exit();
        }
        $checkStmt->close();

        // Hash password for security
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Insert user into database
        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $hashed_password);

        if ($stmt->execute()) {
            echo "<script>alert('Registration successful! Please log in.'); window.location.href='index.php';</script>";
        } else {
            echo "<script>alert('Registration failed! Please try again.'); window.location.href='register.php';</script>";
        }

        // Close statement
        $stmt->close();
    } else {
        echo "<script>alert('Please fill in all fields.'); window.location.href='register.php';</script>";
    }
}


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="create.css">

</head>
<body>
    <h1 class="logo">FPBG<br> STOCK</h1>   
    <div class="login-container">
    <h2>User Registration</h2>
    <form action="create.php" method="POST">
        <input type="text" name="username" placeholder="Enter Username" required>
        <input type="password" name="password" placeholder="Enter Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        <button type="submit">Register</button>
    </form>
</div>
</body>
</html>
