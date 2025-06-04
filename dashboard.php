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
    <title>Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="dashboard1.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            <li><a href="stock_out.php">Transactions</a></li>
            <li><a href="create.php">Create User</a></li>
            <li><a href="read.php">View Users</a></li>
            <li><a href="check_expiration.php">Check Expiration Products</a></li>
        <?php elseif ($_SESSION['role'] === 'staff'): ?>
            <li><a href="#" onclick="loadPage('transaction.php', event, true)">Cashiering</a></li>
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

        <div id="statCards" class="stat-cards">
            <?php
            // Get gross revenue and total discounts
            $sql = "SELECT 
                COALESCE(SUM(total_amount), 0) as gross_revenue,
                COALESCE(SUM(total_amount * (discount/100)), 0) as total_discounts
                FROM transactions";
            $result = $conn->query($sql);
            if ($result === false) {
                die("Error getting sales data: " . $conn->error);
            }
            $row = $result->fetch_assoc();
            $grossRevenue = $row['gross_revenue'];
            $totalDiscounts = $row['total_discounts'];

            // Get COGS
            $sql = "SELECT COALESCE(SUM(st.quantity * p.cost_price), 0) as cogs
                    FROM stock_transactions st
                    JOIN products p ON st.product_id = p.product_id
                    WHERE st.transaction_type = 'stock_out'";
            $result = $conn->query($sql);
            if ($result === false) {
                die("Error getting COGS: " . $conn->error);
            }
            $cogs = $result->fetch_assoc()['cogs'];

            // Get average sales (per transaction)
            $sql = "SELECT COALESCE(AVG(total_amount), 0) as avg_sales FROM transactions";
            $result = $conn->query($sql);
            if ($result === false) {
                die("Error getting average sales: " . $conn->error);
            }
            $avgSales = $result->fetch_assoc()['avg_sales'];

            // Calculate net sales (gross revenue - discounts - COGS)
            $netSales = $grossRevenue - $totalDiscounts - $cogs;
            ?>
            
            <div class="stat-card">
                <h3>₱<?php echo number_format($grossRevenue, 2); ?></h3>
                <p>Gross Revenue</p>
            </div>
            <div class="stat-card">
                <h3>₱<?php echo number_format($totalDiscounts, 2); ?></h3>
                <p>Total Discounts</p>
            </div>
            <div class="stat-card">
                <h3>₱<?php echo number_format($avgSales, 2); ?></h3>
                <p>Average Sales</p>
            </div>
            <div class="stat-card">
                <h3>₱<?php echo number_format($netSales, 2); ?></h3>
                <p>Net Sales</p>
            </div>
        </div>

        <div class="charts" id="chartsSection">
            <div class="charts-row">
                <div class="chart-container">
                    <canvas id="pieChart"></canvas>
                </div>
                <div class="chart-container">
                    <canvas id="barChart"></canvas>
                </div>
            </div>
            <div class="chart-buttons">
                <button class="reset-btn" onclick="resetCharts()">
                    <i class="fas fa-trash"></i> Reset Data
                </button>
            </div>
        </div>

        <!-- This is where pages will load -->
        <div id="contentContainer"></div>
    </div>
</div>

<script>
// Load Pages (fullscreen if cashiering)
function loadPage(page, event = null, isCashiering = false) {
    if (event) event.preventDefault();

    var contentContainer = document.getElementById("contentContainer");
    if (!contentContainer) return;

    if (isCashiering) {
        // HIDE everything except cashiering content
        document.getElementById('sidebar').style.display = 'none';
        document.getElementById('notificationContainer').style.display = 'none';
        document.getElementById('searchBar').style.display = 'none';
        document.getElementById('statCards').style.display = 'none';
        document.getElementById('chartsSection').style.display = 'none';
        document.getElementById('backBtn').style.display = 'block';
    }

    // Load the page content
    fetch(page)
        .then(response => response.text())
        .then(html => {
            contentContainer.innerHTML = html;
        })
        .catch(error => {
            console.error('Error loading page:', error);
            contentContainer.innerHTML = '<p>Error loading content</p>';
        });
}

// Function to go back to dashboard
function backToDashboard() {
    // Show all dashboard elements
    document.getElementById('sidebar').style.display = 'block';
    document.getElementById('notificationContainer').style.display = 'block';
    document.getElementById('searchBar').style.display = 'block';
    document.getElementById('statCards').style.display = 'flex';
    document.getElementById('chartsSection').style.display = 'block';
    document.getElementById('backBtn').style.display = 'none';
    
    // Clear the content container
    document.getElementById('contentContainer').innerHTML = '';
}

// Initialize charts
document.addEventListener("DOMContentLoaded", function() {
    fetch('get_chart_data.php')
        .then(response => response.json())
        .then(data => {
            const pieCtx = document.getElementById('pieChart').getContext('2d');
            new Chart(pieCtx, {
                type: 'pie',
                data: {
                    labels: ['Cost', 'Revenue'],
                    datasets: [{
                        data: [data.totalCost || 0, data.totalRevenue || 0],
                        backgroundColor: ['#1D2B53', '#7FDBFF']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: { 
                            display: true, 
                            text: 'Cost vs Revenue', 
                            color: '#000', 
                            font: { size: 16 } 
                        },
                        legend: { 
                            labels: { color: '#000' } 
                        }
                    }
                }
            });

            const barCtx = document.getElementById('barChart').getContext('2d');
            new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: data.months || ['January', 'February', 'March', 'April'],
                    datasets: [{
                        label: 'Monthly Sales',
                        data: data.monthlySales || [5000, 7000, 8000, 10000],
                        backgroundColor: '#4A90E2'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: { 
                            display: true, 
                            text: 'Monthly Sales Report', 
                            color: '#000', 
                            font: { size: 16 } 
                        },
                        legend: { 
                            labels: { color: '#000' } 
                        }
                    },
                    scales: {
                        x: { ticks: { color: '#000' } },
                        y: { 
                            ticks: { 
                                color: '#000',
                                callback: function(value) {
                                    return '₱' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        })
        .catch(error => {
            console.error('Error loading chart data:', error);
        });
});

function resetCharts() {
    if (!confirm('Are you sure you want to reset all chart data to zero? This action cannot be undone.')) {
        return;
    }

    fetch('reset_chart_data.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Chart data has been reset successfully.');
                location.reload(); // Reload page to update all statistics
            } else {
                alert('Error: ' + (data.error || 'Failed to reset chart data'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to reset chart data. Please try again.');
        });
}
</script>

</body>
</html>
