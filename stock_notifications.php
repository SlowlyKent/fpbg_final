<?php
session_start();
include('connect.php');

function addStockNotification($user_id, $product_name, $quantity, $type) {
    global $conn;
    
    $message = '';
    switch($type) {
        case 'low':
            $message = "Low stock alert: $product_name has only $quantity units remaining!";
            break;
        case 'out':
            $message = "Out of stock alert: $product_name is now out of stock!";
            break;
        case 'added':
            $message = "Stock added: $quantity units of $product_name have been added to inventory.";
            break;
        case 'updated':
            $message = "Stock updated: $product_name now has $quantity units in stock.";
            break;
    }
    
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, is_read, created_at) VALUES (?, ?, 0, NOW())");
    $stmt->bind_param("is", $user_id, $message);
    return $stmt->execute();
}

function checkStockLevel($product_id, $quantity) {
    global $conn;
    
    // Get product details
    $stmt = $conn->prepare("SELECT product_name FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    
    if ($quantity <= 0) {
        // Out of stock notification
        addStockNotification($_SESSION['user_id'], $product['product_name'], 0, 'out');
    } elseif ($quantity <= 10) { // You can adjust this threshold
        // Low stock notification
        addStockNotification($_SESSION['user_id'], $product['product_name'], $quantity, 'low');
    }
}
?> 