<?php
session_start();
include('connect.php');

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Permission denied']);
    exit();
}

// Check if transaction_id is provided
if (!isset($_POST['transaction_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Transaction ID is required']);
    exit();
}

$transaction_id = $_POST['transaction_id'];

// Start transaction
$conn->begin_transaction();

try {
    // First, get the stock transaction details to restore inventory
    $stmt = $conn->prepare("SELECT product_id, quantity FROM stock_transactions WHERE transaction_id = ?");
    $stmt->bind_param("s", $transaction_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Transaction not found');
    }
    
    // Restore inventory for each product in the transaction
    while ($row = $result->fetch_assoc()) {
        // Update product stock quantity
        $update_stmt = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE product_id = ?");
        $update_stmt->bind_param("ds", $row['quantity'], $row['product_id']);
        $update_stmt->execute();
        
        // Update stock status
        $check_stmt = $conn->prepare("SELECT stock_quantity FROM products WHERE product_id = ?");
        $check_stmt->bind_param("s", $row['product_id']);
        $check_stmt->execute();
        $product_result = $check_stmt->get_result();
        $product = $product_result->fetch_assoc();
        
        // Update stock status based on new quantity
        $new_status = 'in stock';
        if ($product['stock_quantity'] <= 0) {
            $new_status = 'out of stock';
        } elseif ($product['stock_quantity'] < 10) {
            $new_status = 'low stock';
        }
        
        $status_stmt = $conn->prepare("UPDATE products SET stock_status = ? WHERE product_id = ?");
        $status_stmt->bind_param("ss", $new_status, $row['product_id']);
        $status_stmt->execute();
    }
    
    // Delete the stock transaction records
    $delete_stmt = $conn->prepare("DELETE FROM stock_transactions WHERE transaction_id = ?");
    $delete_stmt->bind_param("s", $transaction_id);
    $delete_stmt->execute();
    
    // Delete the transaction record
    $delete_trans_stmt = $conn->prepare("DELETE FROM transactions WHERE transaction_id = ?");
    $delete_trans_stmt->bind_param("s", $transaction_id);
    $delete_trans_stmt->execute();
    
    // Commit the transaction
    $conn->commit();
    
    // Add notification for the deletion
    $notif_msg = "Stock out transaction #$transaction_id has been deleted";
    $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, message, is_read, created_at) VALUES (?, ?, 0, NOW())");
    $notif_stmt->bind_param("is", $_SESSION['user_id'], $notif_msg);
    $notif_stmt->execute();
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Stock out transaction deleted successfully']);
    
} catch (Exception $e) {
    // Rollback the transaction on error
    $conn->rollback();
    
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
?> 