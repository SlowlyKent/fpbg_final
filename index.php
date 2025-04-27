<?php
session_start();
include 'connect.php'; 

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // First SQL query for verifying username and password
    $sql = "SELECT user_id, username, password, role FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    
    // Check if the query preparation is successful
    if (!$stmt) {
        die("SQL error: " . $conn->error); // Error handling if prepare fails
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user exists
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $stored_hash = $row['password'];

        // Verify password
        if (password_verify($password, $stored_hash)) {
            $_SESSION['user_id'] = $row['user_id']; // Change to user_id
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];

            // Debugging: Check the role value
            // echo 'Role: ' . $_SESSION['role']; // Uncomment for debugging

            // Redirect based on user role
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
} // <-- Closing brace for the POST request handling

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1 class="logo">FPBG<br> STOCK</h1>

    <div class="login-container">
        <h2>Sign in</h2>
        <?php if ($error): ?>
            <p style="color: red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="POST" action="index.php">
            <input type="text" name="username" placeholder="username" required>
            <input type="password" name="password" placeholder="password" required>
            <button type="submit" class="login-btn">Sign In</button>
        </form>
    </div>
</body>
</html>



