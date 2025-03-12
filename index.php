<?php

session_start();
include 'connect.php'; // Database connection


$error = ""; // Initialize the variable to prevent warnings


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    echo "<pre>";
    print_r($_POST); // Show what is being sent
    echo "</pre>";

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        $stmt = $conn->prepare("SELECT user_id, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo "Stored Hash: " . $row['password'] . "<br>"; // Debug stored hash
            echo "Entered Password: " . $password . "<br>"; // Debug entered password
            
            if (password_verify($password, $row['password'])) { 
                $_SESSION['user_id'] = $row['user_id'];
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Incorrect password!";
            }
        } else {
            $error = "User not found!";
        }
    } else {
        $error = "Please enter both username and password!";
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Logo at Top Left -->
    <h1 class="logo">FPBG<br> STOCK</h1>

    <div class="login-container">
        <h2>Sign in</h2>
        <?php if ($error): ?>
            <p style="color: red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="POST" action="index.php">
    <input type="text" name="username" placeholder="username" required>
    <input type="password" name="password" placeholder="password" required>
    <a href="forgot_password.php" class="forgot-password">Forgot Password?</a>

    <button type="submit" class="login-btn">Sign In</button>

</form>
       
    </div>
</body>
</html>
