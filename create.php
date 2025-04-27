<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}
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
</head>

<body>

<div class="dashboard-container">
    <div class="sidebar" id="sidebar">
        <h2>FPBG STOCK</h2>
        <ul>
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <li><a href="#" onclick="loadPage('cashiering.php', event, true)">Cashiering</a></li>
            <li><a href="#" onclick="loadPage('dashboard-content.php', event)">Dashboard</a></li>
            <li><a href="#" onclick="loadPage('inventory.php', event)">Inventory</a></li>
            <li><a href="#" onclick="loadPage('stock_in.php', event)">Stock In</a></li>
            <li><a href="#" onclick="loadPage('stock_out.php', event)">Stock Out</a></li>
            <li><a href="#" onclick="loadPage('transaction.php', event)">Transaction</a></li>
        <?php elseif ($_SESSION['role'] === 'staff'): ?>
            <li><a href="#" onclick="loadPage('transaction.php', event, true)">Cashiering</a></li>
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

        <div id="contentContainer">
            <!-- Loaded pages will appear here -->
        </div>

        <div class="stat-cards" id="statCards">
            <div class="stat-card">
                <h3>₱20,993</h3>
                <p>Total Sales</p>
            </div>
            <div class="stat-card">
                <h3>₱6,774</h3>
                <p>Average Sales</p>
            </div>
            <div class="stat-card">
                <h3>₱20,200</h3>
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
    </div>
</div>

<script>
// Load default dashboard
document.addEventListener("DOMContentLoaded", function () {
    loadPage('dashboard-content.php');
});

// Load Pages with optional fullscreen
function loadPage(page, event = null, fullscreen = false) {
    if (event) event.preventDefault();

    var contentContainer = document.getElementById("contentContainer");
    if (!contentContainer) return;

    contentContainer.innerHTML = "<p>Loading...</p>";

    // Handle fullscreen mode
    if (fullscreen) {
        document.getElementById('sidebar').style.display = 'none';
        document.getElementById('notificationContainer').style.display = 'none';
        document.getElementById('searchBar').style.display = 'none';
        document.getElementById('statCards').style.display = 'none';
        document.getElementById('chartsSection').style.display = 'none';
        document.getElementById('mainContent').style.width = '100%';
        document.getElementById('mainContent').style.marginLeft = '0';
    } else {
        document.getElementById('sidebar').style.display = 'block';
        document.getElementById('notificationContainer').style.display = 'flex';
        document.getElementById('searchBar').style.display = 'block';
        document.getElementById('statCards').style.display = 'flex';
        document.getElementById('chartsSection').style.display = 'flex';
        document.getElementById('mainContent').style.width = '';
        document.getElementById('mainContent').style.marginLeft = '';
    }

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
        contentContainer.innerHTML = "<p style='color: red;'>Network error. Please check your connection.</p>";
    };
    xhr.send();
}

// Notification Toggle
function toggleNotifications() {
    var dropdown = document.getElementById("notifDropdown");
    dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
}

// Hide dropdown on outside click
document.addEventListener("click", function (event) {
    var dropdown = document.getElementById("notifDropdown");
    var icon = document.querySelector(".notification-icon");
    if (dropdown && icon && !dropdown.contains(event.target) && !icon.contains(event.target)) {
        dropdown.style.display = "none";
    }
});

// Load charts
document.addEventListener("DOMContentLoaded", function() {
    const pieCanvas = document.getElementById('pieChart');
    const barCanvas = document.getElementById('barChart');

    if (pieCanvas && barCanvas) {
        const pieCtx = pieCanvas.getContext('2d');
        const barCtx = barCanvas.getContext('2d');

        new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: ['Cost', 'Revenue'],
                datasets: [{
                    data: [4895, 7850],
                    backgroundColor: ['#1D2B53', '#7FDBFF']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: { display: true, text: 'Cost vs. Revenue', color: '#000', font: { size: 16 } },
                    legend: { labels: { color: '#000' } }
                }
            }
        });

        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: ['January', 'February', 'March', 'April'],
                datasets: [{
                    label: 'Sales',
                    data: [5000, 7000, 8000, 10000],
                    backgroundColor: '#4A90E2'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: { display: true, text: 'Monthly Sales Report', color: '#000', font: { size: 16 } },
                    legend: { labels: { color: '#000' } }
                },
                scales: {
                    x: { ticks: { color: '#000' } },
                    y: { ticks: { color: '#000' } }
                }
            }
        });
    }
});
</script>

</body>
</html>
