<?php
// inventory.php - View and manage inventory
session_start();
include("connect.php");
include("stock_helper.php");

// Ensure the user is logged in
if (!isset($_SESSION['user_id']) || (!in_array($_SESSION['role'], ['admin', 'staff']))) {
    header("Location: login.php");
    exit();
}

// Process POST requests for API calls
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw POST data
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data || !isset($data['cart']) || empty($data['cart'])) {
        header('Content-Type: application/json');
        echo json_encode(["error" => "Invalid data received"]);
        exit();
    }

    $cart = $data['cart'];
    $transactionId = isset($data['transaction_id']) ? $data['transaction_id'] : null;

    // Begin transaction and handle inventory updates
    try {
        $conn->begin_transaction();

        foreach ($cart as $item) {
            $product_id = $item['id'];
            $quantity = $item['quantity'];

            // Check if the product exists and has sufficient stock
            $stmt = $conn->prepare("SELECT stock_quantity FROM products WHERE product_id = ? FOR UPDATE");
            $stmt->bind_param("s", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                throw new Exception("Product not found: $product_id");
            }

            $row = $result->fetch_assoc();
            if ($row['stock_quantity'] < $quantity) {
                throw new Exception("Insufficient stock for product ID: $product_id");
            }

            // Update the inventory - reduce stock quantity
            $stmt = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?");
            $stmt->bind_param("is", $quantity, $product_id);
            $stmt->execute();

            // Calculate new quantity after update
            $newQuantity = $row['stock_quantity'] - $quantity;

            // Update stock status based on remaining quantity
            if ($newQuantity === 0) {
                $newStatus = "out-of-stock";
                // Add out of stock notification
                $notif_msg = "Product {$product_id} is now out of stock!";
                $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, message, is_read, created_at) VALUES (?, ?, 0, NOW())");
                $notif_stmt->bind_param("is", $_SESSION['user_id'], $notif_msg);
                $notif_stmt->execute();
            } elseif ($newQuantity < 10) { // Threshold for low stock
                $newStatus = "low stock";
                // Add low stock notification
                $notif_msg = "Low stock alert: Product {$product_id} has only {$newQuantity} units remaining!";
                $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, message, is_read, created_at) VALUES (?, ?, 0, NOW())");
                $notif_stmt->bind_param("is", $_SESSION['user_id'], $notif_msg);
                $notif_stmt->execute();
            } else {
                $newStatus = "in stock";
            }

            // Update the status
            $updateStmt = $conn->prepare("UPDATE products SET stock_status = ? WHERE product_id = ?");
            $updateStmt->bind_param("ss", $newStatus, $product_id);
            if (!$updateStmt->execute()) {
                error_log('Error updating stock_status in inventory.php: ' . $updateStmt->error);
            }

            include_once 'notification_helper.php';
            // Insert low stock notification if needed
            if ($newStatus === "low stock") {
                $notifMessage = "Stock is running low for product: " . $product_id;
                create_notification($conn, $_SESSION['user_id'], $notifMessage);
            }

            // Insert transaction details if transaction ID is provided
            if ($transactionId) {
                $stmt = $conn->prepare("INSERT INTO stock_transactions (transaction_id, product_id, quantity, transaction_type) VALUES (?, ?, ?, 'stock_out')");
                $stmt->bind_param("ssd", $transactionId, $product_id, $quantity);
                $stmt->execute();
            }
        }

        // If everything is successful, commit the transaction
        $conn->commit();

        header('Content-Type: application/json');
        echo json_encode(["success" => "Inventory updated successfully"]);
        exit();
    } catch (Exception $e) {
        // An error occurred, rollback the transaction
        $conn->rollback();

        header('Content-Type: application/json');
        echo json_encode(["error" => $e->getMessage()]);
        exit();
    }
}

// For GET requests - display the inventory page
// Query to get inventory data with additional stock information
$query = "
    SELECT p.*,
           COALESCE(
               (SELECT SUM(st.quantity)
                FROM stock_transactions st
                WHERE st.product_id = p.product_id
                AND st.transaction_type = 'stock_out'
                AND st.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
               ), 0
           ) as monthly_sales
    FROM products p
";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inventory</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script defer src="js/notifications.js"></script>
    <script defer src="js/inventory.js"></script>
    <style>
        .inventory-container {
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        .inventory-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .inventory-title {
            font-size: 24px;
            color: #003366;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f8f9fa;
            color: #003366;
            font-weight: 600;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .instock {
            background-color: #d4edda;
            color: #155724;
        }

        .lowstock {
            background-color: #fff3cd;
            color: #856404;
        }

        .outofstock {
            background-color: #f8d7da;
            color: #721c24;
        }

        .edit-btn, .delete-btn, .btn-edit, .btn-delete {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin: 0 4px;
            transition: background-color 0.3s;
        }

        .edit-btn, .btn-edit {
            background-color: #007bff;
            color: white;
        }

        .delete-btn, .btn-delete {
            background-color: #dc3545;
            color: white;
        }

        .edit-btn:hover, .btn-edit:hover {
            background-color: #0056b3;
        }

        .delete-btn:hover, .btn-delete:hover {
            background-color: #c82333;
        }

        .search-bar {
            margin-bottom: 20px;
        }

        .search-bar input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            text-align: center;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            text-align: center;
        }

        .criticalstock {
            background-color: #ffe5e5;
        }

        .status-cell {
            font-weight: bold;
        }

        .status-cell.criticalstock {
            color: #dc3545;
        }

        .status-cell.lowstock {
            color: #856404;
        }

        .status-cell.outofstock {
            color: #721c24;
        }

        .status-cell.instock {
            color: #155724;
        }

        .days-inventory {
            color: #666;
            font-style: italic;
        }

        .avg-sales {
            font-size: 0.85em;
            color: #666;
        }

        /* Additional styles for better table layout */
        td, th {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 200px;
        }

        /* Ensure the table is responsive */
        .inventory-container {
            overflow-x: auto;
        }

        /* Style for table header row */
        thead tr {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }

        /* Alternating row colors */
        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        /* Hover effect for action buttons */
        .btn-edit:hover, .btn-delete:hover {
            opacity: 0.9;
            transform: translateY(-1px);
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
            <?php elseif ($_SESSION['role'] === 'staff'): ?>
                <li><a href="cashiering.php">Cashiering</a></li>
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

        <div class="inventory-container">
            <div class="inventory-header">
                <h2 class="inventory-title">Inventory Management</h2>
            </div>

            <div class="search-bar">
                <input type="text" placeholder="Search inventory..." id="searchInput">
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="success-message">
                    <?php 
                        echo $_SESSION['success_message'];
                        unset($_SESSION['success_message']);
                    ?>
                </div>
            <?php endif; ?>

            <table>
                <thead>
                    <tr>
                        <th>Product ID</th>
                        <th>Product Name</th>
                        <th>Brand</th>
                        <th>Stock Quantity</th>
                        <th>Unit of Measure</th>
                        <th>Cost Price</th>
                        <th>Selling Price</th>
                        <th>Stock Status</th>
                        <th>Category</th>
                        <th>Expiration Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <?php
                                $stockQuantity = (int)$row['stock_quantity'];
                                $avgDailySales = $row['monthly_sales'] / 30; // Calculate average daily sales
                                
                                // Get stock status using helper function
                                $status = getStockStatus($stockQuantity, $avgDailySales);
                                
                                // Determine CSS class and display text
                                switch ($status) {
                                    case 'out of stock':
                                        $statusClass = "outofstock";
                                        $displayStatus = "Out of Stock";
                                        break;
                                    case 'critical stock':
                                        $statusClass = "criticalstock";
                                        $displayStatus = "Critical Stock";
                                        break;
                                    case 'low stock':
                                        $statusClass = "lowstock";
                                        $displayStatus = "Low Stock";
                                        break;
                                    default:
                                        $statusClass = "instock";
                                        $displayStatus = "In Stock";
                                }
                            ?>
                            <tr class="<?= $statusClass ?>">
                                <td><?= htmlspecialchars($row['product_id']) ?></td>
                                <td><?= htmlspecialchars($row['product_name']) ?></td>
                                <td><?= htmlspecialchars($row['brand']) ?></td>
                                <td><?= htmlspecialchars($stockQuantity) ?></td>
                                <td><?= htmlspecialchars($row['unit_of_measure']) ?></td>
                                <td>₱<?= number_format($row['cost_price'], 2) ?></td>
                                <td>₱<?= number_format($row['selling_price'], 2) ?></td>
                                <td class="status-cell <?= $statusClass ?>">
                                    <?= $displayStatus ?>
                                    <?php if ($avgDailySales > 0): ?>
                                        <br>
                                        <small class="avg-sales">
                                            Avg. Daily Sales: <?= number_format($avgDailySales, 1) ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($row['category']) ?></td>
                                <td><?= htmlspecialchars($row['expiration_date']) ?></td>
                                <td>
                                    <button onclick="editProduct('<?= $row['product_id'] ?>')" class="btn-edit">Edit</button>
                                    <button onclick="deleteProduct('<?= $row['product_id'] ?>')" class="btn-delete">Delete</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="11">No products found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    const tableRows = document.querySelectorAll('tbody tr');

    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        
        tableRows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
});
</script>

</body>
</html>
