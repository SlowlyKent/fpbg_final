<?php

function getAverageDailySales($conn, $product_id, $days = 30) {
    // Calculate average daily sales for the last X days
    $sql = "
        SELECT COALESCE(AVG(daily_quantity), 0) as avg_daily_sales
        FROM (
            SELECT DATE(st.created_at) as sale_date, SUM(st.quantity) as daily_quantity
            FROM stock_transactions st
            WHERE st.product_id = ? 
            AND st.transaction_type = 'stock_out'
            AND st.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY DATE(st.created_at)
        ) daily_sales";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $product_id, $days);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return floatval($row['avg_daily_sales']);
}

function getStockStatus($quantity, $avgDailySales) {
    // Define thresholds
    $criticalThreshold = 2; // Days of inventory
    $lowThreshold = 7; // Days of inventory
    $quantityThreshold = 5000; // Minimum quantity threshold (5000g = 5kg)
    
    if ($quantity <= 0) {
        return 'out of stock';
    }
    
    // Always check quantity threshold first
    if ($quantity < $quantityThreshold) {
        return 'low stock';
    }
    
    // If there are no sales, return in stock (since we already checked quantity)
    if ($avgDailySales <= 0) {
        return 'in stock';
    }
    
    // Calculate days of inventory for items with sales history
    $daysOfInventory = $quantity / $avgDailySales;
    
    if ($daysOfInventory <= $criticalThreshold) {
        return 'critical stock';
    } elseif ($daysOfInventory <= $lowThreshold) {
        return 'low stock';
    }
    
    return 'in stock';
}

function createStockNotification($conn, $product_id, $quantity, $status) {
    // Get product details
    $stmt = $conn->prepare("SELECT product_name FROM products WHERE product_id = ?");
    $stmt->bind_param("s", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    
    if (!$product) {
        return false;
    }
    
    // Create notification message based on status
    if ($status === 'out of stock') {
        $message = "WARNING: {$product['product_name']} is out of stock! Only 0 units remaining.";
        $type = "danger";
    } elseif ($status === 'low stock') {
        $message = "WARNING: {$product['product_name']} is running low! Only {$quantity} units remaining.";
        $type = "warning";
    } else {
        return false;
    }
    
    // Insert notification
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, type, is_read, created_at) VALUES (?, ?, ?, 0, NOW())");
    $stmt->bind_param("iss", $_SESSION['user_id'], $message, $type);
    return $stmt->execute();
}

function updateStockStatus($conn, $product_id) {
    // Get current stock quantity
    $stmt = $conn->prepare("SELECT stock_quantity FROM products WHERE product_id = ?");
    $stmt->bind_param("s", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    
    if (!$product) {
        return false;
    }
    
    // Calculate average daily sales
    $avgDailySales = getAverageDailySales($conn, $product_id);
    
    // Get new status
    $status = getStockStatus($product['stock_quantity'], $avgDailySales);
    
    // Update status in database
    $stmt = $conn->prepare("UPDATE products SET stock_status = ? WHERE product_id = ?");
    $stmt->bind_param("ss", $status, $product_id);
    $success = $stmt->execute();
    
    // Create notification if needed
    if ($success && in_array($status, ['critical stock', 'low stock', 'out of stock'])) {
        createStockNotification($conn, $product_id, $product['stock_quantity'], $status);
    }
    
    return $success;
} 