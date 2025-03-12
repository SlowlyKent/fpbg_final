<?php
session_start();
if (!isset($_SESSION['user_id'])) {
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="dashboard-container">
    <!-- Sidebar -->
    <div class="sidebar">
        <h2>FPBG STOCK</h2>
        <div class="menu-item">Dashboard</div>
        <div class="menu-item">Inventory</div>
        <div class="menu-item">Stock Out</div>
        <div class="menu-item">Stock In</div>
        <div class="menu-item">Transaction</div>
        <a href="logout.php" class="menu-item">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="dashboard-header">
            <input type="text" class="search-bar" placeholder="Search...">
        </div>

        <!-- Dashboard Cards -->
        <div class="cards">
            <div class="card">$20,993 <br> Total Sales</div>
            <div class="card">$6,774 <br> Average Sales</div>
            <div class="card">$20,200 <br> Net Sales</div>
        </div>

        <!-- Charts -->
        <div class="charts">
            <div class="chart">
                <canvas id="costRevenueChart"></canvas>
            </div>
            <div class="chart">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="script.js"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
    const ctx1 = document.getElementById('costRevenueChart').getContext('2d');
    new Chart(ctx1, {
        type: 'pie',
        data: {
            labels: ['Cost', 'Revenue'],
            datasets: [{
                data: [4895, 7850],
                backgroundColor: ['#34495e', '#2ecc71']
            }]
        }
    });

    const ctx2 = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx2, {
        type: 'bar',
        data: {
            labels: ['January', 'February', 'March', 'April'],
            datasets: [{
                label: 'Sales',
                data: [17000, 14000, 18000, 22000],
                backgroundColor: '#3498db'
            }]
        }
    });
});
</script>

</body>
</html>
