<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: permission-denied.php');
    exit();
}

$sql = "SELECT * FROM inventory";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FPBG Stock Inventory</title>
    <link rel="stylesheet" href="inventory.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script defer src="inventory.js"></script>
</head>
<body>

<div class="container">
    <div class="sidebar" id="sidebar">
        <h2>FPBG STOCK</h2>
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
            <?php elseif ($_SESSION['role'] === 'staff'): ?>
                <li><a href="#" onclick="loadPage('transaction.php', event, true)">Cashiering</a></li>
            <?php endif; ?>
        </ul>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="main-content" id="mainContent">
        <div class="notification-container" id="notificationContainer">
            <div class="notification-icon" onclick="toggleNotifications()">
                <i class="fa-solid fa-bell"></i>
                <span class="notification-badge" id="notifBadge">3</span>
            </div>
            <div class="notification-dropdown" id="notifDropdown">
                <h4>Notifications</h4>
                <ul id="notifList">
                    <li>New stock added</li>
                    <li>Stock running low</li>
                    <li>Transaction completed</li>
                </ul>
            </div>
        </div>

        <div class="search-bar" id="searchBar">
            <input type="text" placeholder="Search">
        </div>

        <table>
            <thead>
                <tr>
                    <th>Product ID</th>
                    <th>Product Name</th>
                    <th>Brand</th>
                    <th>Stock Quantity</th>
                    <th>Unit of Measure</th>
                    <th>Category</th>
                    <th>Cost Price</th>
                    <th>Selling Price</th>
                    <th>Stock Status</th>
                    <th>Expiration Date</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>01</td>
                    <td>Nuggets</td>
                    <td>Bingo</td>
                    <td>10</td>
                    <td>grams</td>
                    <td>Chicken</td>
                    <td>60</td>
                    <td>65</td>
                    <td><span class="status outofstock">Out of Stock</span></td>
                    <td>08/10/25</td>
                </tr>
                <tr>
                    <td>02</td>
                    <td>Tocino</td>
                    <td>Virginia</td>
                    <td>23</td>
                    <td>grams</td>
                    <td>Pork</td>
                    <td>110</td>
                    <td>120</td>
                    <td><span class="status normal">Normal</span></td>
                    <td>08/10/25</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
