<?php
session_start();
include('connect.php');

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
    $stock_quantity = (int) htmlspecialchars(trim($_POST['stock_quantity']));
    $unit_of_measure = htmlspecialchars(trim($_POST['unit_of_measure']));
    $category = htmlspecialchars(trim($_POST['category']));
    $cost_price = (float) htmlspecialchars(trim($_POST['cost_price']));
    $selling_price = (float) htmlspecialchars(trim($_POST['selling_price']));
    $expiration_date = htmlspecialchars(trim($_POST['expiration_date']));

    // Start transaction
    $conn->begin_transaction();

    try {
        // Check if the product ID already exists
        $check_sql = "SELECT * FROM products WHERE product_id = ?";
        $check_stmt = $conn->prepare($check_sql);

        if ($check_stmt === false) {
            throw new Exception('Error in preparing check statement: ' . $conn->error);
        }

        $check_stmt->bind_param("s", $product_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result && $check_result->num_rows > 0) {
            // Product exists, update the stock quantity
            $row = $check_result->fetch_assoc();
            $new_stock_quantity = $row['stock_quantity'] + $stock_quantity;

            // Update product
            $update_sql = "UPDATE products SET stock_quantity = ? WHERE product_id = ?";
            $update_stmt = $conn->prepare($update_sql);

            if ($update_stmt === false) {
                throw new Exception('Error in preparing update statement: ' . $conn->error);
            }

            $update_stmt->bind_param("is", $new_stock_quantity, $product_id);

            if (!$update_stmt->execute()) {
                throw new Exception('Error updating record: ' . $update_stmt->error);
            }

            // Record stock-in transaction
            $transaction_id = uniqid('STKN', true);
            $stock_trans_sql = "INSERT INTO stock_transactions (transaction_id, product_id, quantity, transaction_type) VALUES (?, ?, ?, 'stock_in')";
            $stock_trans_stmt = $conn->prepare($stock_trans_sql);
            $stock_trans_stmt->bind_param("ssd", $transaction_id, $product_id, $stock_quantity);
            $stock_trans_stmt->execute();

            // Update stock status and create notification if needed
            updateStockStatus($conn, $product_id);

            $_SESSION['success_message'] = 'Product stock updated successfully';
        } else {
            // New product, insert record
            $insert_sql = "INSERT INTO products (product_id, product_name, brand, stock_quantity, unit_of_measure, category, cost_price, selling_price, expiration_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);

            if ($insert_stmt === false) {
                throw new Exception('Error in preparing insert statement: ' . $conn->error);
            }

            $insert_stmt->bind_param("sssissdds", 
                $product_id, 
                $product_name, 
                $brand, 
                $stock_quantity, 
                $unit_of_measure, 
                $category, 
                $cost_price, 
                $selling_price, 
                $expiration_date
            );

            if (!$insert_stmt->execute()) {
                throw new Exception('Error inserting record: ' . $insert_stmt->error);
            }

            // Record stock-in transaction for new product
            $transaction_id = uniqid('STKN', true);
            $stock_trans_sql = "INSERT INTO stock_transactions (transaction_id, product_id, quantity, transaction_type) VALUES (?, ?, ?, 'stock_in')";
            $stock_trans_stmt = $conn->prepare($stock_trans_sql);
            $stock_trans_stmt->bind_param("ssd", $transaction_id, $product_id, $stock_quantity);
            $stock_trans_stmt->execute();

            // Create notification for new product
            $notif_msg = "✨ NEW PRODUCT ADDED: {$product_name}\n";
            $notif_msg .= "• Initial Stock: {$stock_quantity} {$unit_of_measure}\n";
            $notif_msg .= "• Category: {$category}\n";
            $notif_msg .= "• Selling Price: ₱" . number_format($selling_price, 2);
            
            $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, type, is_read) VALUES (?, ?, 'success', 0)");
            $stmt->bind_param("is", $_SESSION['user_id'], $notif_msg);
            $stmt->execute();

            $_SESSION['success_message'] = 'New product added successfully';
        }

        // Commit transaction
        $conn->commit();
        
        header('Location: inventory.php');
        exit();

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['error_message'] = $e->getMessage();
        header('Location: stock_in.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Stock In</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script defer src="js/notifications.js"></script>
    <style>
        .stock-in-container {
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        .stock-in-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .stock-in-title {
            font-size: 24px;
            color: #003366;
        }

        .form-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #003366;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
        }

        button[type="submit"] {
            background-color: #007bff;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            width: 100%;
            transition: background-color 0.3s;
        }

        button[type="submit"]:hover {
            background-color: #0056b3;
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

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
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

        <div class="stock-in-container">
            <div class="stock-in-header">
                <h2 class="stock-in-title">Add New Stock</h2>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="success-message">
                    <?php 
                        echo $_SESSION['success_message'];
                        unset($_SESSION['success_message']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="error-message">
                    <?php 
                        echo $_SESSION['error_message'];
                        unset($_SESSION['error_message']);
                    ?>
                </div>
            <?php endif; ?>

            <div class="form-container">
                <form action="stock_in.php" method="POST">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="product_id">
                                <i class="fas fa-barcode"></i> Product ID
                            </label>
                            <input type="text" id="product_id" name="product_id" required />
                        </div>

                        <div class="form-group">
                            <label for="product_name">
                                <i class="fas fa-box"></i> Product Name
                            </label>
                            <input type="text" id="product_name" name="product_name" required />
                        </div>

                        <div class="form-group">
                            <label for="brand">
                                <i class="fas fa-tag"></i> Brand
                            </label>
                            <input type="text" id="brand" name="brand" required />
                        </div>

                        <div class="form-group">
                            <label for="stock_quantity">
                                <i class="fas fa-cubes"></i> Stock Quantity
                            </label>
                            <input type="number" id="stock_quantity" name="stock_quantity" required />
                        </div>

                        <div class="form-group">
                            <label for="unit_of_measure">
                                <i class="fas fa-ruler"></i> Unit of Measure
                            </label>
                            <input type="text" id="unit_of_measure" name="unit_of_measure" required />
                        </div>

                        <div class="form-group">
                            <label for="category">
                                <i class="fas fa-folder"></i> Category
                            </label>
                            <input type="text" id="category" name="category" required />
                        </div>

                        <div class="form-group">
                            <label for="cost_price">
                                <i class="fas fa-tags"></i> Cost Price
                            </label>
                            <input type="number" step="0.01" id="cost_price" name="cost_price" required />
                        </div>

                        <div class="form-group">
                            <label for="selling_price">
                                <i class="fas fa-money-bill-wave"></i> Selling Price
                            </label>
                            <input type="number" step="0.01" id="selling_price" name="selling_price" required />
                        </div>

                        <div class="form-group">
                            <label for="expiration_date">
                                <i class="fas fa-calendar"></i> Expiration Date
                            </label>
                            <input type="date" id="expiration_date" name="expiration_date" required />
                        </div>
                    </div>

                    <button type="submit">
                        <i class="fas fa-plus-circle"></i> Add Stock
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add any additional JavaScript functionality here
});
</script>

</body>
</html>
