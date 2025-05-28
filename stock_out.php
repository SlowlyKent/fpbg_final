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
    SELECT st.transaction_id, st.product_id, st.quantity, st.created_at, p.brand, p.product_name, p.unit_of_measure, t.amount_paid
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

// Get total sales
$sql = "SELECT COALESCE(SUM(total_amount), 0) as total_sales FROM transactions";
$result = $conn->query($sql);
$totalSales = $result->fetch_assoc()['total_sales'];

// Get COGS (cost of goods sold) from stock_transactions
$sql = "SELECT COALESCE(SUM(st.quantity * p.cost_price), 0) as cogs
        FROM stock_transactions st
        JOIN products p ON st.product_id = p.product_id
        WHERE st.transaction_type = 'stock_out'";
$result = $conn->query($sql);
if ($result === false) {
    die('COGS Query failed: ' . $conn->error);
}
$cogs = $result->fetch_assoc()['cogs'];

// Get net sales (total sales - COGS)
$netSales = $totalSales - $cogs;

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
                        <th><i class="fas fa-ruler"></i> Unit of Measure</th>
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
        <?php if (isset($_SESSION['success_message'])): ?>
            <tr>
                <td colspan="7" class="success-message"><?php echo htmlspecialchars($_SESSION['success_message']); ?></td>
            </tr>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <tr>
                <td colspan="7" class="error-message"><?php echo htmlspecialchars($_GET['error']); ?></td>
            </tr>
        <?php endif; ?>
        <?php foreach ($transactions as $row): ?>
                            <tr>
                                <td>#<?php echo htmlspecialchars($row['transaction_id']); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['product_name']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($row['product_id']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($row['brand']); ?></td>
                                <td class="quantity-cell">
                                    <?php
                                        $quantity = floatval($row['quantity']);
                                        $unit = $row['unit_of_measure'];
                                        if ($unit === 'g') {
                                            // Convert grams to kilograms for display
                                            $quantity = $quantity / 1000;
                                            echo number_format($quantity, 3);
                                            echo ' kg';
                                        } elseif ($unit === 'kg') {
                                            // If quantity is large, assume it's in grams and convert
                                            if ($quantity > 100) {
                                                $quantity = $quantity / 1000;
                                            }
                                            echo number_format($quantity, 3);
                                            echo ' kg';
                                        } else {
                                            echo number_format($quantity, 0);
                                            echo ' ' . htmlspecialchars($unit);
                                        }
                                    ?>
                                </td>
                        <td class="unit-o-measure">
                            <?php echo htmlspecialchars($row['unit_of_measure']); ?>
                        </td>
                        <td class="transaction-amount">₱<?php echo number_format($row['amount_paid'], 2); ?></td>
                        <td class="transaction-date">
                                    <?php 
                                        $date = new DateTime($row['created_at']);
                                        echo $date->format('M d, Y h:i A'); 
                                    ?>
                                </td>
                                <td>
                                    <form method="POST" action="delete_stock_out.php" onsubmit="return confirm('Are you sure you want to delete this transaction?');">
                                        <input type="hidden" name="transaction_id" value="<?php echo htmlspecialchars($row['transaction_id']); ?>">
                                        <button type="submit" class="delete-btn">Delete</button>
                                    </form>
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
    function deleteTransaction(transactionId) {
        if (confirm('Are you sure you want to delete this transaction? This will restore the stock quantity.')) {
            fetch('delete_stock_out.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    transaction_id: transactionId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Transaction deleted successfully');
                    location.reload();
                } else {
                    alert(data.error || 'Failed to delete transaction');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while deleting the transaction');
            });
        }
    }

    // Add sorting functionality
    document.addEventListener('DOMContentLoaded', function() {
        const table = document.querySelector('table');
        const headers = table.querySelectorAll('th.sortable');
        
        headers.forEach(header => {
            header.addEventListener('click', () => {
                const sortBy = header.dataset.sort;
                const isAsc = header.classList.contains('asc');
                
                // Remove sort classes from all headers
                headers.forEach(h => h.classList.remove('asc', 'desc'));
                
                // Add sort class to clicked header
                header.classList.add(isAsc ? 'desc' : 'asc');
                
                // Sort the table
                sortTable(table, sortBy, isAsc);
            });
        });
    });

    function sortTable(table, column, reverse) {
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        rows.sort((a, b) => {
            const aValue = a.querySelector(`td:nth-child(${getColumnIndex(column)})`).textContent;
            const bValue = b.querySelector(`td:nth-child(${getColumnIndex(column)})`).textContent;
            
            if (column === 'date') {
                return reverse ? 
                    new Date(bValue) - new Date(aValue) : 
                    new Date(aValue) - new Date(bValue);
            }
            
            return reverse ? 
                bValue.localeCompare(aValue) : 
                aValue.localeCompare(bValue);
        });
        
        rows.forEach(row => tbody.appendChild(row));
    }

    function getColumnIndex(column) {
        const columnMap = {
            'product': 2,
            'brand': 3,
            'amount': 5,
            'date': 6
        };
        return columnMap[column] || 1;
    }
</script>


</body>
</html>
