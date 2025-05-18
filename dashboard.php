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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script defer src="js/notifications.js"></script>
    <style>
        /* Inline CSS for testing */
        .sidebar {
            color: black; /* Change text color to black */
        }
        .sidebar ul li a {
            color: black; /* Change link color to black */
        }
        a:-webkit-any-link {
            color: black; /* Change link color to black */
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

        <div class="search-bar" id="searchBar">
            <input type="text" placeholder="Search">
        </div>

        <div id="statCards" class="stat-cards">
            <?php
            // Get total sales
            $sql = "SELECT COALESCE(SUM(total_amount), 0) as total_sales FROM transactions";
            $result = $conn->query($sql);
            $totalSales = $result->fetch_assoc()['total_sales'];

            // Get average sales
            $sql = "SELECT COALESCE(AVG(total_amount), 0) as avg_sales FROM transactions";
            $result = $conn->query($sql);
            $avgSales = $result->fetch_assoc()['avg_sales'];

            // Get net sales (total sales - total costs)
            $sql = "SELECT 
                COALESCE((SELECT SUM(total_amount) FROM transactions), 0) -
                COALESCE((SELECT SUM(cost_price * stock_quantity) FROM products), 0) as net_sales";
            $result = $conn->query($sql);
            $netSales = $result->fetch_assoc()['net_sales'];
            ?>
            
            <div class="stat-card">
                <h3>₱<?php echo number_format($totalSales, 2); ?></h3>
                <p>Total Sales</p>
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
            <div class="chart-container">
                <canvas id="pieChart"></canvas>
            </div>
            <div class="chart-container">
                <canvas id="barChart"></canvas>
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

        // Clear dashboard background
        contentContainer.style.width = "100%";
        contentContainer.style.margin = "0 auto";
    } else {
        // NORMAL dashboard view
        document.getElementById('sidebar').style.display = 'block';
        document.getElementById('notificationContainer').style.display = 'flex';
        document.getElementById('searchBar').style.display = 'block';
        document.getElementById('statCards').style.display = 'flex';
        document.getElementById('chartsSection').style.display = 'flex';

        contentContainer.style.width = "";
        contentContainer.style.margin = "";
    }

    contentContainer.innerHTML = "<p>Loading...</p>";

    var xhr = new XMLHttpRequest();
    xhr.open("GET", page, true);
    xhr.onload = function () {
        if (xhr.status === 200) {
            contentContainer.innerHTML = xhr.responseText;
        } else {
            contentContainer.innerHTML = "<p style='color: red;'>Error loading page.</p>";
        }
    };
    xhr.onerror = function () {
        contentContainer.innerHTML = "<p style='color: red;'>Network error.</p>";
    };
    xhr.send();
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
</script>

</body>
</html>
