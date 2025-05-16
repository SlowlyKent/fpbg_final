<?php
// inventory.php - View and manage inventory
session_start();
include("connect.php");

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

            // Update stock status based on remaining quantity
            $newQuantity = $row['stock_quantity'] - $quantity;
            $newStatus = "in stock";

            if ($newQuantity === 0) {
                $newStatus = "out-of-stock";
            } elseif ($newQuantity < 2) { // Updated threshold for low stock
                $newStatus = "low stock";
            } else {
                $newStatus = "in stock";
            }

            // Update the status
            $updateStmt = $conn->prepare("UPDATE products SET stock_status = ? WHERE product_id = ?");
            $updateStmt->bind_param("ss", $newStatus, $product_id);
            if (!$updateStmt->execute()) {
                error_log('Error updating stock_status in inventory.php: ' . $updateStmt->error);
            }

            // Insert transaction details if transaction ID is provided
            if ($transactionId) {
                $stmt = $conn->prepare("INSERT INTO transaction_details (transaction_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                $price = $item['price'];
                $stmt->bind_param("iidd", $transactionId, $product_id, $quantity, $price);
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
// Query to get inventory data
$query = "SELECT * FROM products";
$result = $conn->query($query);
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
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="#" class="back-btn" id="backBtn" onclick="backToDashboard()" style="display: none;">Back to Dashboard</a>
                <li><a href="cashiering-admin.php">Cashiering</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="inventory.php">Inventory</a></li>
                <li><a href="stock_in.php">Stock In</a></li>
                <li><a href="stock_out.php">Stock Out</a></li>
                <li><a href="create.php">Create User</a></li>
                <li><a href="read.php">View Users</a></li>
            <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'staff'): ?>
                <li><a href="#" onclick="loadPage('Cashiering.php', event, true)">Cashiering</a></li>
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
        <div>
            <span class="stock-status">Stock Status</span>
        </div>
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="success-message" style="background-color: #d4edda; color: #155724; padding: 10px; margin: 10px 0; border: 1px solid #c3e6cb; border-radius: 4px;">
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
                if ($stockQuantity === 0) {
                    $displayStatus = "out-of-stock";
                } elseif ($stockQuantity <= 2) {
                    $displayStatus = "low stock";
                } else {
                    $displayStatus = "in stock";
                }
            ?>
            <tr>
                <td><?= htmlspecialchars($row['product_id']) ?></td>
                <td><?= htmlspecialchars($row['product_name']) ?></td>
                <td><?= htmlspecialchars($row['brand']) ?></td>
                <td><?= htmlspecialchars($row['stock_quantity']) ?></td>
                <td><?= htmlspecialchars($row['unit_of_measure']) ?></td>
                <td><?= htmlspecialchars($row['cost_price']) ?></td>
                <td><?= htmlspecialchars($row['selling_price']) ?></td>
    <td><span class="status <?= strtolower(str_replace([' ', '-'], '', $displayStatus)) ?>"><?= htmlspecialchars($displayStatus) ?></span></td>
                <td><?= htmlspecialchars($row['category']) ?></td>
                <td><?= htmlspecialchars($row['expiration_date']) ?></td>
                    <button onclick="editProduct('<?= $row['product_id'] ?>')">Edit</button>
                    <button onclick="deleteProduct('<?= $row['product_id'] ?>')">Delete</button>
                </>
            </tr>
        <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="9">No inventory items found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function editProduct(productId) {
    // Redirect to an edit page or show a modal for editing
    window.location.href = `edit_product.php?id=${productId}`;
}

function deleteProduct(productId) {
    if (confirm("Are you sure you want to delete this product?")) {
        fetch('delete_product.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `product_id=${productId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Product deleted successfully");
                window.location.reload(); // Refresh the page to reflect changes
            } else {
                alert("Error deleting product: " + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
}
</script>

</body>
</html>
