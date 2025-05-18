<?php
session_start();
include('connect.php');

// Ensure only admin can reset data
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Permission denied']);
    exit();
}

header('Content-Type: application/json');

try {
    // Start transaction
    $conn->begin_transaction();

    // Reset transactions table
    $sql = "DELETE FROM transactions";
    $conn->query($sql);
    $sql = "ALTER TABLE transactions AUTO_INCREMENT = 1";
    $conn->query($sql);

    // Reset stock_transactions table
    $sql = "DELETE FROM stock_transactions";
    $conn->query($sql);
    $sql = "ALTER TABLE stock_transactions AUTO_INCREMENT = 1";
    $conn->query($sql);

    // Reset product costs and quantities
    $sql = "UPDATE products SET stock_quantity = 0, total_amount = 0, sales_count = 0";
    $conn->query($sql);

    // Reset any notifications related to transactions
    $sql = "DELETE FROM notifications WHERE type IN ('transaction', 'stock', 'sales')";
    $conn->query($sql);

    // Reset transaction_items table
    $sql = "DELETE FROM transaction_items";
    $conn->query($sql);
    $sql = "ALTER TABLE transaction_items AUTO_INCREMENT = 1";
    $conn->query($sql);

    // Commit transaction
    $conn->commit();

    // Clear ALL session variables that might affect the charts
    unset($_SESSION['cart']);
    unset($_SESSION['last_transaction']);
    unset($_SESSION['sales_data']);
    unset($_SESSION['chart_data']);
    unset($_SESSION['monthly_stats']);

    echo json_encode([
        'success' => true,
        'message' => 'All sales and transaction data has been reset to zero'
    ]);

} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    
    echo json_encode([
        'error' => 'Error resetting data: ' . $e->getMessage()
    ]);
}
?> 