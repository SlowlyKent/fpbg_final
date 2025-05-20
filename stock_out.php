<?php
session_start();
include ('connect.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: permission-denied.php');
    exit();
}

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch transactions from the database with brand information
$sql = "
    SELECT st.transaction_id, st.product_id, st.quantity, st.created_at, p.brand, p.product_name, t.amount_paid
    FROM stock_transactions st
    JOIN products p ON st.product_id = p.product_id
    JOIN transactions t ON st.transaction_id = t.transaction_id
    ORDER BY st.created_at DESC
";
$result = $conn->query($sql);

if ($result === false) {
    die("Query failed: " . $conn->error);
}

$transactions = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Stock Out</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="stock_out.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script defer src="js/notifications.js"></script>
    <style>
        
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

        <div class="stock-out-container">
            <div class="stock-out-header">
                <h2 class="stock-out-title">Stock Out Transactions</h2>
            </div>

            <div class="search-bar">
                <input type="text" id="searchInput" placeholder="Search transactions..." />
            </div>

            <table>
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag"></i> Transaction ID</th>
                        <th class="sortable" data-sort="product"><i class="fas fa-box"></i> Product <i class="fas fa-sort"></i></th>
                        <th class="sortable" data-sort="brand"><i class="fas fa-tag"></i> Brand <i class="fas fa-sort"></i></th>
                        <th><i class="fas fa-cubes"></i> Quantity</th>
                        <th class="sortable" data-sort="amount"><i class="fas fa-money-bill-wave"></i> Amount Paid <i class="fas fa-sort"></i></th>
                        <th class="sortable desc" data-sort="date"><i class="fas fa-calendar"></i> Transaction Date <i class="fas fa-sort"></i></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="7" class="empty-message">No transactions found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $row): ?>
                            <tr>
                                <td>#<?php echo htmlspecialchars($row['transaction_id']); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['product_name']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($row['product_id']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($row['brand']); ?></td>
                                <td class="quantity-cell"><?php echo htmlspecialchars($row['quantity']); ?> units</td>
                                <td class="transaction-amount">₱<?php echo number_format($row['amount_paid'], 2); ?></td>
                                <td class="transaction-date">
                                    <?php 
                                        $date = new DateTime($row['created_at']);
                                        echo $date->format('M d, Y h:i A'); 
                                    ?>
                                </td>
                                <td>
                                    <button class="delete-btn" data-transaction-id="<?php echo htmlspecialchars($row['transaction_id']); ?>">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="stock_out.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners to delete buttons
    const deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const transactionId = this.getAttribute('data-transaction-id');
            if (confirm('Are you sure you want to delete this stock out transaction? This action cannot be undone.')) {
                deleteStockOut(transactionId);
            }
        });
    });
});

function deleteStockOut(transactionId) {
    const formData = new FormData();
    formData.append('transaction_id', transactionId);

    fetch('delete_stock_out.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Stock out transaction deleted successfully');
            // Reload the page to reflect changes
            window.location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to delete stock out transaction'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the stock out transaction');
    });
}
</script>

<style>
.delete-btn {
    background-color: #dc3545;
    color: white;
    border: none;
    padding: 5px 10px;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s;
    font-size: 12px;
    font-weight: 500;
}

.delete-btn:hover {
    background-color: #c82333;
}
</style>

</body>
</html>
