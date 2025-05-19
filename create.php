<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin')) {
    header('Location: permission-denied.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['username'], $_POST['password'], $_POST['confirm_password'], $_POST['role'])) {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        $confirm_password = trim($_POST['confirm_password']);
        $role = $_POST['role'];

        if ($password !== $confirm_password) {
            echo "<script>alert('Passwords do not match!'); window.location.href='create.php';</script>";
            exit();
        }

        $checkStmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
        if ($checkStmt === false) {
            error_log("Prepare statement error: " . $conn->error);
            echo "<script>alert('An error occurred. Please try again later.'); window.location.href='create.php';</script>";
            exit();
        }

        $checkStmt->bind_param("s", $username);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            echo "<script>alert('Username already taken!'); window.location.href='create.php';</script>";
            exit();
        }
        $checkStmt->close();

        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        if ($stmt === false) {
            error_log("Prepare statement error: " . $conn->error);
            echo "<script>alert('An error occurred. Please try again later.'); window.location.href='create.php';</script>";
            exit();
        }

        $stmt->bind_param("sss", $username, $hashed_password, $role);

        if ($stmt->execute()) {
            echo "<script>alert('User registered successfully!'); window.location.href='read.php';</script>";
            exit();
        } else {
            error_log("Registration failed: " . $stmt->error);
            echo "<script>alert('Registration failed! Please try again later.'); window.location.href='create.php';</script>";
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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create User</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script defer src="js/notifications.js"></script>
    <style>
        .user-form-container {
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .user-form-header {
            margin-bottom: 20px;
            text-align: center;
        }

        .user-form-title {
            font-size: 24px;
            color: #003366;
        }

        .user-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .user-form input,
        .user-form select {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            width: 100%;
        }

        .user-form button {
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .user-form button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="dashboard-container" id="dashboardContainer">
    <div class="sidebar" id="sidebar">
        <h2>FPBG<br>STOCK</h2>
        <ul>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="#" class="back-btn" id="backBtn" onclick="backToDashboard()" style="display: none;">Back to Dashboard</a>
                <li><a href="cashiering-admin.php">Cashiering</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="inventory.php">Inventory</a></li>
                <li><a href="stock_in.php">Stock In</a></li>
                <li><a href="stock_out.php">Stock Out</a></li>
                <li><a href="create.php">Create User</a></li>
                <li><a href="read.php">View Users</a></li>
                <li><a href="check_expiration.php">Check Expiration Products</a></li>
            <?php endif; ?>
        </ul>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="main-content" id="mainContent">
        <div class="notification-container" id="notificationContainer">
            <div class="notification-icon">
                <i class="fa-solid fa-bell"></i>
                <span class="notification-badge" id="notifBadge">0</span>
            </div>
            <div class="notification-dropdown" id="notifDropdown">
                <h4>Notifications</h4>
                <ul id="notifList">
                    <!-- Notifications will be dynamically inserted here -->
                </ul>
            </div>
        </div>

        <div class="user-form-container">
            <div class="user-form-header">
                <h2 class="user-form-title">Create New User</h2>
            </div>
            <form action="create.php" method="POST" class="user-form">
                <input type="text" name="username" placeholder="Enter Username" required>
                <input type="password" name="password" placeholder="Enter Password" required>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                <select name="role" required>
                    <option value="" disabled selected>Select Role</option>
                    <option value="staff">Staff</option>
                    <option value="admin">Admin</option>
                </select>
                <button type="submit">Create User</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
