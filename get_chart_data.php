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
    $sql = "SELECT COALESCE(SUM(cost_price * stock_quantity), 0) as total_cost FROM products";
    $result = $conn->query($sql);
    $totalCost = (float)($result->fetch_assoc()['total_cost'] ?? 0);

    // Get total revenue (sum of total_amount from transactions)
    $sql = "SELECT COALESCE(SUM(total_amount), 0) as total_revenue FROM transactions";
    $result = $conn->query($sql);
    $totalRevenue = (float)($result->fetch_assoc()['total_revenue'] ?? 0);

    // Get monthly sales for the last 4 months
    $sql = "SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                COALESCE(SUM(total_amount), 0) as monthly_sales
            FROM transactions
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 4 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month DESC
            LIMIT 4";
    
    $result = $conn->query($sql);
    $monthlySales = [];
    $months = [];
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $date = new DateTime($row['month']);
            $months[] = $date->format('F Y');
            $monthlySales[] = (float)$row['monthly_sales'];
        }
    }

    // Remove the padding of "No Data" - return empty arrays if no data exists
    echo json_encode([
        'totalCost' => $totalCost,
        'totalRevenue' => $totalRevenue,
        'months' => $months,
        'monthlySales' => $monthlySales
    ]);

} catch (Exception $e) {
    echo json_encode([
        'error' => 'Error fetching chart data: ' . $e->getMessage(),
        'totalCost' => 0,
        'totalRevenue' => 0,
        'months' => [],
        'monthlySales' => []
    ]);
}
?> 