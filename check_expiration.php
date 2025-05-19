<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: permission-denied.php');
    exit();
}
include('connect.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Check Expiration</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="check_expiration.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script defer src="js/notifications.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize notification system
        const notificationIcon = document.querySelector('.notification-icon');
        const notificationDropdown = document.getElementById('notifDropdown');

        notificationIcon.addEventListener('click', function(e) {
            e.stopPropagation();
            notificationDropdown.style.display = notificationDropdown.style.display === 'block' ? 'none' : 'block';
        });

        document.addEventListener('click', function(e) {
            if (!notificationDropdown.contains(e.target) && !notificationIcon.contains(e.target)) {
                notificationDropdown.style.display = 'none';
            }
        });
    });
    </script>
</head>

<body>
<div class="dashboard-container" id="dashboardContainer">
    <div class="sidebar" id="sidebar">
        <h2>FPBG<br>STOCK</h2>
        <ul>
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <li><a href="cashiering-admin.php">Cashiering</a></li>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="inventory.php">Inventory</a></li>
            <li><a href="stock_in.php">Stock In</a></li>
            <li><a href="stock_out.php">Stock Out</a></li>
            <li><a href="create.php">Create User</a></li>
            <li><a href="read.php">View Users</a></li>
            <li><a href="check_expiration.php" class="active">Check Expiration Products</a></li>
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

        <div class="expiration-container">
            <div class="expiration-header">
                <h2 class="expiration-title">Product Expiration Status</h2>
                <button class="refresh-btn" onclick="checkExpirations()">
                    <i class="fas fa-sync-alt"></i> Check Expirations
                </button>
            </div>

            <table class="expiration-table">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Expiration Date</th>
                        <th>Days Until Expiration</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Get current date
                    $currentDate = date('Y-m-d');
                    
                    // Get products that will expire within 30 days or have already expired
                    $stmt = $conn->prepare("
                        SELECT product_id, product_name, expiration_date 
                        FROM products 
                        WHERE expiration_date IS NOT NULL 
                        ORDER BY expiration_date ASC
                    ");
                    
                    if ($stmt->execute()) {
                        $result = $stmt->get_result();
                        
                        while ($product = $result->fetch_assoc()) {
                            $expirationDate = new DateTime($product['expiration_date']);
                            $today = new DateTime();
                            $interval = $today->diff($expirationDate);
                            $daysUntilExpiration = $expirationDate > $today ? $interval->days : -$interval->days;
                            
                            $statusClass = '';
                            $statusText = '';
                            
                            if ($daysUntilExpiration < 0) {
                                $statusClass = 'status-expired';
                                $statusText = 'Expired';
                            } elseif ($daysUntilExpiration <= 7) {
                                $statusClass = 'status-critical';
                                $statusText = 'Critical';
                            } elseif ($daysUntilExpiration <= 30) {
                                $statusClass = 'status-warning';
                                $statusText = 'Warning';
                            }
                            
                            if ($statusClass) { // Only show products that are expired or will expire within 30 days
                                echo "<tr>";
                                echo "<td>{$product['product_name']}</td>";
                                echo "<td>{$product['expiration_date']}</td>";
                                echo "<td>" . ($daysUntilExpiration < 0 ? "Expired " . abs($daysUntilExpiration) . " days ago" : $daysUntilExpiration . " days") . "</td>";
                                echo "<td><span class='status-badge {$statusClass}'>{$statusText}</span></td>";
                                echo "</tr>";
                            }
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function checkExpirations() {
    fetch('check_expiration_ajax.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Expiration check completed successfully. New notifications have been created.');
                location.reload(); // Reload the page to show updated data
            } else {
                alert('Error checking expirations. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to check expirations. Please try again.');
        });
}

// Initialize notification system
document.addEventListener('DOMContentLoaded', function() {
    const notificationIcon = document.querySelector('.notification-icon');
    const notificationDropdown = document.getElementById('notifDropdown');

    notificationIcon.addEventListener('click', function(e) {
        e.stopPropagation();
        notificationDropdown.style.display = notificationDropdown.style.display === 'block' ? 'none' : 'block';
    });

    document.addEventListener('click', function(e) {
        if (!notificationDropdown.contains(e.target) && !notificationIcon.contains(e.target)) {
            notificationDropdown.style.display = 'none';
        }
    });
});
</script>

</body>
</html>