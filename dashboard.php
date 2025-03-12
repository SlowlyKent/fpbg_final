<?php
session_start();
include 'connect.php'; // Database connection

if (!isset($_SESSION['user_id'])) { // Check if the user is logged in

    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Chart.js for graphs -->
</head>
<body>

<div class="dashboard-container">
    
    <div class="sidebar"><!-- Sidebar -->
        <h2>FPBG STOCK</h2>
        <ul>
            <li><a href="#">Dashboard</a></li>
            <li><a href="#">Inventory</a></li>
            <li><a href="#">Stock In</a></li>
            <li><a href="#">Stock Out</a></li>
            <li><a href="#">Transaction</a></li>
        </ul>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
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
                <h3>Cost vs. Revenue per Month</h3>
                <canvas id="pieChart"></canvas>
            </div>
            <div class="chart-container">
                <h3>Sales</h3>
                <canvas id="barChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const pieCanvas = document.getElementById('pieChart');
    const barCanvas = document.getElementById('barChart');

    if (pieCanvas && barCanvas) {
        const pieCtx = pieCanvas.getContext('2d');
        const barCtx = barCanvas.getContext('2d');

        // Pie Chart
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
                maintainAspectRatio: false
            }
        });

        // Bar Chart
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
                maintainAspectRatio: false
            }
        });
    }
});
</script>

</body>
</html>