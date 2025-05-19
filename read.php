<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

if ($isAdmin && isset($_POST['reset'])) {
    $conn->query("DELETE FROM users");
    echo "<script>alert('All users have been deleted.'); window.location.href='read.php';</script>";
    exit();
}

$result = $conn->query("SELECT * FROM users");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>View Users</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script defer src="js/notifications.js"></script>
    <style>
        .users-container {
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        .users-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .users-title {
            font-size: 24px;
            color: #003366;
        }

        .users-controls {
            display: flex;
            gap: 10px;
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .users-table th,
        .users-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .users-table th {
            background-color: #f8f9fa;
            color: #003366;
            font-weight: 600;
        }

        .users-table tr:hover {
            background-color: #f8f9fa;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .btn-create {
            background-color: #28a745;
            color: white;
        }

        .btn-create:hover {
            background-color: #218838;
        }

        .btn-reset {
            background-color: #dc3545;
            color: white;
            border: none;
        }

        .btn-reset:hover {
            background-color: #c82333;
        }

        .btn-edit {
            background-color: #007bff;
            color: white;
            padding: 4px 8px;
            margin-right: 5px;
        }

        .btn-edit:hover {
            background-color: #0056b3;
        }

        .btn-delete {
            background-color: #dc3545;
            color: white;
            padding: 4px 8px;
        }

        .btn-delete:hover {
            background-color: #c82333;
        }

        .search-container {
            margin-bottom: 20px;
        }

        .search-container input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
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

        <div class="users-container">
            <div class="users-header">
                <h2 class="users-title">Registered Users</h2>
                <?php if ($isAdmin): ?>
                <div class="users-controls">
                    <form method="POST" style="display: inline;">
                        <button type="submit" name="reset" class="btn btn-reset" onclick="return confirm('Are you sure you want to reset the table?')">Reset Table</button>
                    </form>
                    <a href="create.php" class="btn btn-create">Create New User</a>
                </div>
                <?php endif; ?>
            </div>

            <div class="search-container">
                <input type="text" id="searchUsers" placeholder="Search users..." onkeyup="searchUsers()">
            </div>

            <table class="users-table">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['user_id']) ?></td>
                                <td><?= htmlspecialchars($row['username']) ?></td>
                                <td><?= htmlspecialchars($row['role']) ?></td>
                                <td>
                                    <?php if ($isAdmin): ?>
                                        <a href="update.php?user_id=<?= $row['user_id'] ?>" class="btn btn-edit">Edit</a>
                                        <a href="delete.php?user_id=<?= $row['user_id'] ?>" onclick="return confirm('Are you sure you want to delete this user?')" class="btn btn-delete">Delete</a>
                                    <?php else: ?>
                                        No actions
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4">No users found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function searchUsers() {
    const input = document.getElementById('searchUsers');
    const filter = input.value.toLowerCase();
    const tbody = document.getElementById('usersTableBody');
    const rows = tbody.getElementsByTagName('tr');

    for (let row of rows) {
        const cells = row.getElementsByTagName('td');
        let found = false;
        for (let cell of cells) {
            if (cell.textContent.toLowerCase().indexOf(filter) > -1) {
                found = true;
                break;
            }
        }
        row.style.display = found ? '' : 'none';
    }
}
</script>

</body>
</html>
