<?php
session_start();
include 'connect.php'; 

$error = '';
$success_message = '';

// Get and clear the success message if it exists
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        
        $sql = "SELECT user_id, username, password, role FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            die("SQL error: " . $conn->error); 
        }

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            $stored_hash = $row['password'];

            if (password_verify($password, $stored_hash)) {
                $_SESSION['user_id'] = $row['user_id']; 
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = $row['role'];

                $role = $_SESSION['role'];
                if ($row['role'] == 'admin') {
                    header("Location: dashboard.php");
                    exit();
                } else if ($row['role'] == 'staff') {
                    header("Location: cashiering-staff.php");
                    exit();
                } else {
                    $error = "Invalid role.";
                }
            } else {
                $error = "Incorrect password!";
            }
        } else {
            $error = "Username not found.";
        }

        $stmt->close();
    }
} 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FPBG STOCK - Login</title>
    <link rel="stylesheet" href="index.css">
   
   
       
</head>
<body>
    <div class="login-container">
        <div class="logo">FPBG<br>STOCK</div>
        <h2 class="login-title">Sign In</h2>
        
        <?php if ($success_message): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($success_message) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php">
            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-icon">
                    <i class="fas fa-user"></i>
                    <input type="text" id="username" name="username" placeholder="Enter your username" required>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-icon">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>
            </div>

            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
        </form>
    </div>
</body>
</html>



