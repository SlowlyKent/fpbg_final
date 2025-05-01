<?php
session_start();
include ('connect.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: permission-denied.php');
    exit();
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input data
    $product_id = htmlspecialchars(trim($_POST['product_id']));
    $product_name = htmlspecialchars(trim($_POST['product_name']));
    $brand = htmlspecialchars(trim($_POST['brand']));
    $stock_quantity = htmlspecialchars(trim($_POST['stock_quantity']));
    $unit_of_measure = htmlspecialchars(trim($_POST['unit_of_measure']));
    $category = htmlspecialchars(trim($_POST['category']));
    $cost_price = htmlspecialchars(trim($_POST['cost_price']));
    $selling_price = htmlspecialchars(trim($_POST['selling_price']));
    $stock_status = htmlspecialchars(trim($_POST['stock_status']));
    $expiration_date = htmlspecialchars(trim($_POST['expiration_date']));

    // Insert data into the database
    $sql = "INSERT INTO inventory (product_id, product_name, brand, stock_quantity, unit_of_measure, category, cost_price, selling_price, stock_status, expiration_date)
            VALUES ('$product_id', '$product_name', '$brand', '$stock_quantity', '$unit_of_measure', '$category', '$cost_price', '$selling_price', '$stock_status', '$expiration_date')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('New stock added successfully!');</script>";
    } else {
        echo "<script>alert('Error: " . $conn->error . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock In</title>
    <link rel="stylesheet" href="stock_in.css">
    <link rel="stylesheet" href="../css/fontawesome/css/all.min.css">
    <script defer src="../js/inventory.js"></script>
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
                <li><a href="transaction.php">Transaction</a></li>
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

        <div class="form-container">
            <h2>Add New Stock</h2>
            <form action="stock_in.php" method="POST">
                <div class="form-group">
                    <label for="product_id">Product ID:</label>
                    <input type="text" id="product_id" name="product_id" required>
                </div>

                <div class="form-group">
                    <label for="product_name">Product Name:</label>
                    <input type="text" id="product_name" name="product_name" required>
                </div>

                <div class="form-group">
                    <label for="brand">Brand:</label>
                    <input type="text" id="brand" name="brand" required>
                </div>

                <div class="form-group">
                    <label for="stock_quantity">Stock Quantity:</label>
                    <input type="number" id="stock_quantity" name="stock_quantity" required>
                </div>

                <div class="form-group">
                    <label for="unit_of_measure">Unit of Measure:</label>
                    <input type="text" id="unit_of_measure" name="unit_of_measure" required>
                </div>

                <div class="form-group">
                    <label for="category">Category:</label>
                    <input type="text" id="category" name="category" required>
                </div>

                <div class="form-group">
                    <label for="cost_price">Cost Price:</label>
                    <input type="number" step="0.01" id="cost_price" name="cost_price" required>
                </div>

                <div class="form-group">
                    <label for="selling_price">Selling Price:</label>
                    <input type="number" step="0.01" id="selling_price" name="selling_price" required>
                </div>


                <div class="form-group">
                    <label for="expiration_date">Expiration Date:</label>
                    <input type="date" id="expiration_date" name="expiration_date" required>
                </div>

                <button type="submit">Add Stock</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
