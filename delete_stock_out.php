<?php
session_start();
include('connect.php');

// Ensure the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Permission denied']);
    exit();
}

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['transaction_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Transaction ID is required']);
    exit();
}

$transaction_id = $data['transaction_id'];

try {
    // Start transaction
    $conn->begin_transaction();

    // Get the stock out details
    $stmt = $conn->prepare("
        SELECT st.*, p.product_name 
        FROM stock_transactions st
        JOIN products p ON st.product_id = p.product_id
        WHERE st.transaction_id = ? AND st.transaction_type = 'stock_out'
    ");
    $stmt->bind_param("s", $transaction_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Transaction not found");
    }

    $transaction = $result->fetch_assoc();

    // Restore the stock quantity
    $updateStmt = $conn->prepare("
        UPDATE products 
        SET stock_quantity = stock_quantity + ? 
        WHERE product_id = ?
    ");
    $updateStmt->bind_param("ds", $transaction['quantity'], $transaction['product_id']);
    $updateStmt->execute();

    // Delete the stock transaction
    $deleteStmt = $conn->prepare("DELETE FROM stock_transactions WHERE transaction_id = ?");
    $deleteStmt->bind_param("s", $transaction_id);
    $deleteStmt->execute();

    // Delete the main transaction
    $deleteMainStmt = $conn->prepare("DELETE FROM transactions WHERE transaction_id = ?");
    $deleteMainStmt->bind_param("s", $transaction_id);
    $deleteMainStmt->execute();

    // Add notification
    $notif_msg = "Stock out transaction for {$transaction['product_name']} has been deleted and stock restored.";
    $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, message, is_read, created_at) VALUES (?, ?, 0, NOW())");
    $notif_stmt->bind_param("is", $_SESSION['user_id'], $notif_msg);
    $notif_stmt->execute();

    // Commit the transaction
    $conn->commit();

    header('Content-Type: application/json');
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    // Rollback the transaction on error
    $conn->rollback();
    
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
?> 