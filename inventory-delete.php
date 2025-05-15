<?php
// inventory-delete.php - View inventory with delete option
session_start();
include("connect.php");

// Ensure the user is logged in and has appropriate role
if (!isset($_SESSION['user_id']) || (!in_array($_SESSION['role'], ['admin', 'staff']))) {
    header("Location: login.php");
    exit();
}

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product_id'])) {
    $product_id = $_POST['delete_product_id'];

    // Check for related records in stock_transactions
    $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM stock_transactions WHERE product_id = ?");
    $checkStmt->bind_param("s", $product_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $row = $checkResult->fetch_assoc();

    if ($row['count'] > 0) {
        $_SESSION['success_message'] = "Cannot delete Product ID $product_id because it has related stock transactions.";
    } else {
        // Prepare and execute delete query
        $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->bind_param("s", $product_id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Product ID $product_id deleted successfully.";
        } else {
            $error = $stmt->error;
            $_SESSION['success_message'] = "Failed to delete Product ID $product_id. Error: $error";
        }
    }
    header("Location: inventory-delete.php");
    exit();
}

// Fetch inventory data
$query = "SELECT * FROM products";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>FPBG Inventory Delete</title>
    <link rel="stylesheet" href="inventory.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <script>
        function confirmDelete(productId) {
            if (confirm("Are you sure you want to delete product ID " + productId + "?")) {
                document.getElementById('delete_product_id').value = productId;
                document.getElementById('deleteForm').submit();
            }
        }
    </script>
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
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="success-message" style="background-color: #d4edda; color: #155724; padding: 10px; margin: 10px 0; border: 1px solid #c3e6cb; border-radius: 4px;">
                <?php 
                    echo $_SESSION['success_message']; 
                    unset($_SESSION['success_message']);
                ?>
            </div>
        <?php endif; ?>

        <h1>Inventory Delete</h1>
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
                    <th>Expiration Date</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['product_id']) ?></td>
                            <td><?= htmlspecialchars($row['product_name']) ?></td>
                            <td><?= htmlspecialchars($row['brand']) ?></td>
                            <td><?= htmlspecialchars($row['stock_quantity']) ?></td>
                            <td><?= htmlspecialchars($row['unit_of_measure']) ?></td>
                            <td><?= htmlspecialchars($row['category']) ?></td>
                            <td><?= htmlspecialchars($row['cost_price']) ?></td>
                            <td><?= htmlspecialchars($row['selling_price']) ?></td>
                            <td><?= htmlspecialchars($row['expiration_date']) ?></td>
                            <td>
                                <button onclick="confirmDelete('<?= htmlspecialchars($row['product_id']) ?>')" class="delete-btn">
                                    <i class="fa-solid fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="10">No inventory items found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <form id="deleteForm" method="POST" style="display:none;">
            <input type="hidden" name="delete_product_id" id="delete_product_id" value="" />
        </form>
    </div>
</div>
</body>
</html>
