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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script defer src="js/notifications.js"></script>
    <style>
        .stock-out-container {
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        .stock-out-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .stock-out-title {
            font-size: 24px;
            color: #003366;
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

        /* Add sorting styles */
        .sortable {
            cursor: pointer;
            user-select: none;
        }

        .sortable i {
            margin-left: 5px;
            color: #999;
        }

        .sortable.asc i::before {
            content: "\f0de"; /* fa-sort-up */
            color: #003366;
        }

        .sortable.desc i::before {
            content: "\f0dd"; /* fa-sort-down */
            color: #003366;
        }

        .transaction-amount {
            font-weight: 500;
            color: #28a745;
        }

        .transaction-date {
            color: #6c757d;
            font-size: 0.9em;
        }

        .quantity-cell {
            font-weight: 500;
        }

        .empty-message {
            text-align: center;
            padding: 20px;
            color: #6c757d;
            font-style: italic;
        }

        @media screen and (max-width: 768px) {
            .stock-out-container {
                padding: 15px;
            }

            th, td {
                padding: 8px;
            }

            .stock-out-title {
                font-size: 20px;
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
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="6" class="empty-message">No transactions found</td>
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
                            </tr>
                        <?php endforeach; ?>
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

    // Sorting functionality
    const sortableHeaders = document.querySelectorAll('th.sortable');
    let currentSort = {
        column: 'date', // Default sort by date
        direction: 'desc' // Default sort direction
    };

    function compareValues(a, b, isAsc) {
        // Remove currency symbol and commas for amount comparison
        if (a.includes('₱')) {
            a = parseFloat(a.replace('₱', '').replace(/,/g, ''));
            b = parseFloat(b.replace('₱', '').replace(/,/g, ''));
        }
        
        // Handle date comparison
        if (a.includes('AM') || a.includes('PM')) {
            return isAsc ? 
                new Date(a) - new Date(b) :
                new Date(b) - new Date(a);
        }
        
        // Regular string/number comparison
        if (typeof a === 'number' && typeof b === 'number') {
            return isAsc ? a - b : b - a;
        }
        
        return isAsc ? 
            a.toString().localeCompare(b.toString()) :
            b.toString().localeCompare(a.toString());
    }

    sortableHeaders.forEach(header => {
        header.addEventListener('click', () => {
            const column = header.dataset.sort;
            const columnIndex = Array.from(header.parentElement.children).indexOf(header);

            // Reset other headers
            sortableHeaders.forEach(h => {
                if (h !== header) {
                    h.classList.remove('asc', 'desc');
                }
            });

            // Toggle sort direction
            if (currentSort.column === column) {
                currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
            } else {
                currentSort.column = column;
                currentSort.direction = 'asc';
            }

            // Update header classes
            header.classList.remove('asc', 'desc');
            header.classList.add(currentSort.direction);

            // Sort the table
            const tbody = document.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));

            const sortedRows = rows.sort((a, b) => {
                let aValue, bValue;
                
                // Get the correct cell based on the column
                const aCell = a.children[columnIndex];
                const bCell = b.children[columnIndex];
                
                // Extract values based on column type
                switch(column) {
                    case 'product':
                        aValue = aCell.querySelector('strong').textContent.trim();
                        bValue = bCell.querySelector('strong').textContent.trim();
                        break;
                    case 'amount':
                        aValue = aCell.textContent.trim();
                        bValue = bCell.textContent.trim();
                        break;
                    case 'date':
                        aValue = aCell.textContent.trim();
                        bValue = bCell.textContent.trim();
                        break;
                    default:
                        aValue = aCell.textContent.trim();
                        bValue = bCell.textContent.trim();
                }

                return compareValues(aValue, bValue, currentSort.direction === 'asc');
            });

            // Clear and re-append sorted rows
            while (tbody.firstChild) {
                tbody.removeChild(tbody.firstChild);
            }

            sortedRows.forEach(row => tbody.appendChild(row));
        });
    });

    // Trigger initial sort on Transaction Date (desc)
    const dateHeader = document.querySelector('th[data-sort="date"]');
    if (dateHeader) {
        dateHeader.click();
    }
});
</script>

</body>
</html>
