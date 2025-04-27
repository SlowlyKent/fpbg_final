<?php
session_start();
include 'connect.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    if (isset($_POST['username'], $_POST['password'], $_POST['confirm_password'], $_POST['role'])) {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        $confirm_password = trim($_POST['confirm_password']);
        $role = $_POST['role'];  

       
        if ($password !== $confirm_password) {
            echo "<script>alert('Passwords do not match!'); window.location.href='register.php';</script>";
            exit();
        }

     
        $checkStmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
        $checkStmt->bind_param("s", $username);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            echo "<script>alert('Username already taken!'); window.location.href='register.php';</script>";
            exit();
        }
        $checkStmt->close();

        
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

     
        $stmt = $conn->prepare("INSERT INTO users(username, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $hashed_password, $role);  

        if ($stmt->execute()) {
            
            header("Location: index.php");
            exit();
        } else {
            echo "<script>alert('Registration failed! Please try again.'); window.location.href='create.php';</script>";
        }

        
        $stmt->close();
    } else {
        echo "<script>alert('Please fill in all fields.'); window.location.href='create.php';</script>";
    }
}


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register User</title>
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

           
            <label for="role">Select Role:</label>
            <select name="role" required>
                <option value="staff">Staff</option>
                <option value="admin">Admin</option>
            </select>

            <button type="submit">Register</button>
        </form>
    </div>
</body>
</html>
