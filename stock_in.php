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
    $product_name = htmlspecialchars(trim($_POST['product_name']));
    $brand = htmlspecialchars(trim($_POST['brand']));
    $input_quantity = floatval(htmlspecialchars(trim($_POST['stock_quantity'])));
    // Convert grams to kilograms (if input is less than 1, assume it's in grams)
    $stock_quantity = $input_quantity < 1 ? $input_quantity * 1000 : $input_quantity;
    $unit_of_measure = htmlspecialchars(trim($_POST['unit_of_measure']));
    $category = htmlspecialchars(trim($_POST['category']));
    $cost_price = (float) htmlspecialchars(trim($_POST['cost_price']));
    $selling_price = (float) htmlspecialchars(trim($_POST['selling_price']));
    $expiration_date = htmlspecialchars(trim($_POST['expiration_date']));

    // Start transaction
    $conn->begin_transaction();

    try {
        // Generate a unique transaction ID first
        $transaction_id = uniqid('STKN', true);

        // Create the transaction record first
        $trans_sql = "INSERT INTO transactions (transaction_id, total_amount, amount_paid, `change`, discount) VALUES (?, ?, ?, ?, ?)";
        $trans_stmt = $conn->prepare($trans_sql);
        $total_amount = $cost_price * $stock_quantity;
        $amount_paid = $total_amount;
        $change = 0;
        $discount = 0;
        
        if ($trans_stmt === false) {
            throw new Exception('Error in preparing transaction statement: ' . $conn->error);
        }
        
        $trans_stmt->bind_param("sdddd", $transaction_id, $total_amount, $amount_paid, $change, $discount);
        
        if (!$trans_stmt->execute()) {
            throw new Exception('Error creating transaction record: ' . $trans_stmt->error);
        }

        // Determine initial stock status
        $stock_status = 'in stock';
        if ($stock_quantity <= 0) {
            $stock_status = 'out of stock';
        } elseif ($stock_quantity < 10) { // Assuming 10 is the threshold for low stock
            $stock_status = 'low stock';
        }

        // Now insert the new product
        $insert_sql = "INSERT INTO products (product_name, brand, stock_quantity, unit_of_measure, category, cost_price, selling_price, expiration_date, stock_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);

        if ($insert_stmt === false) {
            throw new Exception('Error in preparing insert statement: ' . $conn->error);
        }

        $insert_stmt->bind_param("ssissddss", 
            $product_name, 
            $brand, 
            $stock_quantity, 
            $unit_of_measure, 
            $category, 
            $cost_price, 
            $selling_price, 
            $expiration_date,
            $stock_status
        );

        if (!$insert_stmt->execute()) {
            throw new Exception('Error inserting product record: ' . $insert_stmt->error);
        }

        // Get the auto-generated product_id
        $product_id = $conn->insert_id;

        // Convert product_id to string for stock_transactions
        $product_id_str = (string)$product_id;

        // Now record stock-in transaction
        $stock_trans_sql = "INSERT INTO stock_transactions (transaction_id, product_id, quantity, transaction_type) VALUES (?, ?, ?, 'stock_in')";
        $stock_trans_stmt = $conn->prepare($stock_trans_sql);
        
        if ($stock_trans_stmt === false) {
            throw new Exception('Error in preparing stock transaction statement: ' . $conn->error);
        }
        
        $stock_trans_stmt->bind_param("ssd", $transaction_id, $product_id_str, $stock_quantity);
        
        if (!$stock_trans_stmt->execute()) {
            throw new Exception('Error creating stock transaction record: ' . $stock_trans_stmt->error);
        }

        // Create notification for new product
        $notif_msg = "NEW PRODUCT ADDED: {$product_name}\n";
        $notif_msg .= "• Initial Stock: {$stock_quantity} {$unit_of_measure}\n";
        $notif_msg .= "• Category: {$category}\n";
        $notif_msg .= "• Selling Price: ₱" . number_format($selling_price, 2);
        
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, type, is_read) VALUES (?, ?, 'success', 0)");
        $stmt->bind_param("is", $_SESSION['user_id'], $notif_msg);
        $stmt->execute();

        $_SESSION['success_message'] = 'New product added successfully';

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
    <link rel="stylesheet" href="stock_in.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script defer src="js/notifications.js"></script>
    <style>
        .quantity-input-group {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .quantity-input-group input[type="number"] {
            flex: 1;
        }

        .quantity-input-group select {
            width: 150px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: white;
            font-size: 14px;
        }

        .quantity-input-group select:focus {
            border-color: #4CAF50;
            outline: none;
        }

        #quantityPerUnitGroup {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            margin-top: 10px;
        }

        #quantityPerUnitGroup label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        #quantityPerUnitGroup input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 5px;
        }

        #quantityPerUnitGroup input:focus {
            border-color: #4CAF50;
            outline: none;
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
                            <label for="stock_quantity">Stock Quantity:</label>
                            <div class="quantity-input-group">
                                <input type="number" id="stock_quantity" name="stock_quantity" step="0.001" min="0.001" required />
                                <select id="unit_of_measure" name="unit_of_measure" required>
                                    <option value="kg">Kilograms (kg)</option>
                                    <option value="g">Grams (g)</option>
                                    <option value="pcs">Pieces (pcs)</option>
                                    <option value="box">Box</option>
                                    <option value="pack">Pack</option>
                                </select>
                            </div>
                            <small class="input-help">Enter the quantity and select the appropriate unit of measure</small>
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
document.getElementById('stock_in_form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const quantity = parseFloat(document.getElementById('stock_quantity').value);
    const unit = document.getElementById('unit_of_measure').value;
    
    // Convert to kg if unit is grams
    let effectiveQuantity = quantity;
    if (unit === 'g') {
        effectiveQuantity = quantity / 1000;
    }
    
    // Create FormData object
    const formData = new FormData(this);
    formData.set('stock_quantity', effectiveQuantity);
    
    // Send the form data
    fetch('save_stock_in.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Stock added successfully!');
            window.location.href = 'inventory.php';
        } else {
            alert(data.error || 'Failed to add stock');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while adding stock');
    });
});
</script>

</body>
</html>
