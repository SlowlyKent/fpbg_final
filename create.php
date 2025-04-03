<?php
session_start();
include 'connect.php'; // Ensure this file contains the correct database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if all required fields are set
    if (!isset($_POST['username'], $_POST['password'], $_POST['confirm_password'], $_POST['role'])) {
        $_SESSION['error'] = "Please fill in all fields.";
        header("Location: register.php");
        exit();
    }

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $role = trim($_POST['role']); // Get selected role

    // Validate role (ensure only "Admin" or "Staff" is allowed)
    if ($role !== "Admin" && $role !== "Staff") {
        $_SESSION['error'] = "Invalid role selected!";
        header("Location: register.php");
        exit();
    }

    // Check if passwords match
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match!";
        header("Location: register.php");
        exit();
    }

    // Check if username already exists
    $checkStmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
    $checkStmt->bind_param("s", $username);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        $_SESSION['error'] = "Username already taken!";
        header("Location: register.php");
        exit();
    }
    $checkStmt->close();

    // Hash password for security
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Insert user into database with role
    $stmt = $conn->prepare("INSERT INTO user_final (username, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $hashed_password, $role);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Registration successful! Please log in.";
        header("Location: index.php");
        exit();
    } else {
        $_SESSION['error'] = "Registration failed! Please try again.";
        header("Location: register.php");
        exit();
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
}
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

        <!-- Display error messages -->
        <?php if (isset($_SESSION['error'])): ?>
            <p style="color: red;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
        <?php endif; ?>

        <!-- Display success messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <p style="color: green;"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
        <?php endif; ?>

        <form action="create.php" method="POST">
            <input type="text" name="username" placeholder="Enter Username" required>
            <input type="password" name="password" placeholder="Enter Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>

            <!-- Role selection -->
            <label for="role">Select Role:</label>
            <select name="role" required>
                <option value="Staff">Staff</option>
                <option value="Admin">Admin</option>
            </select>

            <button type="submit">Register</button>
        </form>
    </div>
</body>
</html>
