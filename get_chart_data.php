<?php
session_start();
include('connect.php');

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

try {
    // Get total cost (sum of cost_price * stock_quantity)
    $sql = "SELECT SUM(cost_price * stock_quantity) as total_cost FROM products";
    $result = $conn->query($sql);
    $totalCost = $result->fetch_assoc()['total_cost'] ?? 0;

    // Get total revenue (sum of total_amount from transactions)
    $sql = "SELECT SUM(total_amount) as total_revenue FROM transactions";
    $result = $conn->query($sql);
    $totalRevenue = $result->fetch_assoc()['total_revenue'] ?? 0;

    // Get monthly sales for the last 4 months
    $sql = "SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                SUM(total_amount) as monthly_sales
            FROM transactions
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 4 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month DESC
            LIMIT 4";
    
    $result = $conn->query($sql);
    $monthlySales = [];
    $months = [];
    
    while ($row = $result->fetch_assoc()) {
        $date = new DateTime($row['month']);
        $months[] = $date->format('F Y');
        $monthlySales[] = (float)$row['monthly_sales'];
    }

    // If we have less than 4 months of data, pad with zeros
    while (count($months) < 4) {
        array_unshift($months, 'No Data');
        array_unshift($monthlySales, 0);
    }

    echo json_encode([
        'totalCost' => (float)$totalCost,
        'totalRevenue' => (float)$totalRevenue,
        'months' => $months,
        'monthlySales' => $monthlySales
    ]);

} catch (Exception $e) {
    echo json_encode([
        'error' => 'Error fetching chart data: ' . $e->getMessage(),
        'totalCost' => 4895,
        'totalRevenue' => 7850,
        'months' => ['January', 'February', 'March', 'April'],
        'monthlySales' => [5000, 7000, 8000, 10000]
    ]);
}
?> 