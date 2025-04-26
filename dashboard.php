<?php
session_start();
include 'connect.php'; // Database connection

if (!isset($_SESSION['user_id'])) { // Check if the user is logged in
    header("Location: index.php");
    exit();
}
$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Chart.js for graphs -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

</head>

<body>

<div class="dashboard-container">
    
    <div class="sidebar"><!-- Sidebar -->
        <h2>FPBG STOCK</h2>
        <ul>
        <?php if ($_SESSION['role'] === 'admin'): ?>
        <li><a href="#" onclick="loadPage('dashboard.php')">Dashboard</a></li>
        <li><a href="#" onclick="loadPage('inventory.php')">Inventory</a></li>
        <li><a href="#" onclick="loadPage('stock_in.php')">Stock In</a></li>
        <li><a href="#" onclick="loadPage('stock_out.php')">Stock Out</a></li>
        <li><a href="#" onclick="loadPage('transaction.php')">Transaction</a></li>
        <?php elseif ($_SESSION['role'] === 'staff'): ?>
        <li><a href="#" onclick="loadPage('transaction.php')">Cashiering</a></li>
        <?php endif; ?>
        </ul>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
    <div class="notification-container">
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
        <!-- Search Bar -->
        <div class="search-bar">
            <input type="text" placeholder="Search">
        </div>

        <!-- Statistic Cards -->
        <div class="stat-cards">
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

        <!-- Charts Section -->
        <div class="charts">
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
document.addEventListener("DOMContentLoaded", function () {
    loadPage('dashboard-content.php'); // Load default dashboard content
});

// Function to load pages dynamically via AJAX
function loadPage(page, event = null) {
    if (event) {
        event.preventDefault(); // Prevent default link behavior
    }

    var contentContainer = document.getElementById("contentContainer");
    if (!contentContainer) {
        console.error("Error: contentContainer not found!");
        return;
    }

    contentContainer.innerHTML = "<p>Loading...</p>"; // Show loading text

    var xhr = new XMLHttpRequest();
    xhr.open("GET", page, true);
    
    xhr.onload = function () {
        if (xhr.status === 200) {
            contentContainer.innerHTML = xhr.responseText; // Load page content
        } else if (xhr.status === 404) {
            console.error("Error: Page not found (" + page + ")");
            contentContainer.innerHTML = "<p style='color: red;'>Error: Page not found (404). Please check the filename.</p>";
        } else {
            console.error("Error loading " + page + ": " + xhr.statusText);
            contentContainer.innerHTML = "<p style='color: red;'>Error loading page. Please try again.</p>";
        }
    };

    xhr.onerror = function () {
        console.error("Network error while loading " + page);
        contentContainer.innerHTML = "<p style='color: red;'>Network error. Please check your connection.</p>";
    };

    xhr.send();
}

// Notification Dropdown Toggle
function toggleNotifications() {
    var dropdown = document.getElementById("notifDropdown");
    dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
}

// Hide dropdown when clicking outside
document.addEventListener("click", function (event) {
    var dropdown = document.getElementById("notifDropdown");
    var icon = document.querySelector(".notification-icon");

    if (dropdown && icon && !dropdown.contains(event.target) && !icon.contains(event.target)) {
        dropdown.style.display = "none";
    }
});

// Load Charts After Page Load
document.addEventListener("DOMContentLoaded", function() {
    const pieCanvas = document.getElementById('pieChart');
    const barCanvas = document.getElementById('barChart');

    if (pieCanvas && barCanvas) {
        const pieCtx = pieCanvas.getContext('2d');
        const barCtx = barCanvas.getContext('2d');

        // Pie Chart with Title
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
                    title: {
                        display: true,
                        text: 'Cost vs. Revenue',
                        color: '#000000', // Black title color
                        font: { size: 16 }
                    },
                    legend: {
                        labels: {
                            color: '#000000' // Black legend labels
                        }
                    }
                }
            }
        });

        // Bar Chart with Title
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
                    title: {
                        display: true,
                        text: 'Monthly Sales Report',
                        color: '#000000', // Black title color
                        font: { size: 16 }
                    },
                    legend: {
                        labels: {
                            color: '#000000' // Black legend labels
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            color: '#000000' // Black X-axis labels
                        }
                    },
                    y: {
                        ticks: {
                            color: '#000000' // Black Y-axis labels
                        }
                    }
                }
            }
        });
    }
});
</script>

</body>
</html>