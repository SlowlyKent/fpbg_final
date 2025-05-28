<?php
session_start();
include('connect.php');

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

try {
    // Get total cost (COGS: sum of cost_price * quantity for stock_out)
    $sql = "SELECT COALESCE(SUM(st.quantity * p.cost_price), 0) as total_cost
            FROM stock_transactions st
            JOIN products p ON st.product_id = p.product_id
            WHERE st.transaction_type = 'stock_out'";
    $result = $conn->query($sql);
    $totalCost = (float)($result && $result->num_rows > 0 ? $result->fetch_assoc()['total_cost'] : 0);

    // Get gross revenue and total discounts
    $sql = "SELECT 
            COALESCE(SUM(total_amount), 0) as gross_revenue,
            COALESCE(SUM(total_amount * (discount/100)), 0) as total_discounts
            FROM transactions";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $grossRevenue = (float)$row['gross_revenue'];
    $totalDiscounts = (float)$row['total_discounts'];

    // Calculate net sales (gross revenue - discounts - COGS)
    $netSales = $grossRevenue - $totalDiscounts - $totalCost;

    // Get monthly sales for the last 4 months
    $sql = "SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                COALESCE(SUM(total_amount), 0) as monthly_sales,
                COALESCE(SUM(total_amount * (discount/100)), 0) as monthly_discounts
            FROM transactions
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 4 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month DESC
            LIMIT 4";
    
    $result = $conn->query($sql);
    $monthlySales = [];
    $months = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $date = new DateTime($row['month']);
            $months[] = $date->format('F Y');
            // Calculate net monthly sales (gross - discounts)
            $monthlySales[] = (float)($row['monthly_sales'] - $row['monthly_discounts']);
        }
    }

    echo json_encode([
        'totalCost' => $totalCost,
        'totalRevenue' => $netSales,
        'grossRevenue' => $grossRevenue,
        'netSales' => $netSales,
        'totalDiscounts' => $totalDiscounts,
        'months' => $months,
        'monthlySales' => $monthlySales
    ]);

} catch (Exception $e) {
    echo json_encode([
        'error' => 'Error fetching chart data: ' . $e->getMessage(),
        'totalCost' => 0,
        'totalRevenue' => 0,
        'grossRevenue' => 0,
        'netSales' => 0,
        'totalDiscounts' => 0,
        'months' => [],
        'monthlySales' => []
    ]);
}
?> 