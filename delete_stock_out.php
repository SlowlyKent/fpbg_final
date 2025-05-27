<?php
session_start();
include('connect.php');

// Ensure the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Permission denied']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

$transaction_id = null;
if (isset($input['transaction_id'])) {
    $transaction_id = $input['transaction_id'];
} elseif (isset($_POST['transaction_id'])) {
    $transaction_id = $_POST['transaction_id'];
}

if (!$transaction_id) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Transaction ID is required']);
    exit();
}

try {
    // Start transaction
    $conn->begin_transaction();

    // Get the stock out details
    $stmt = $conn->prepare("
        SELECT st.*, p.product_name 
        FROM stock_transactions st
        JOIN products p ON st.product_id = p.product_id
        WHERE st.transaction_id = ?
    ");
    $stmt->bind_param("s", $transaction_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Transaction not found");
    }

    $transaction = $result->fetch_assoc();

   

    // Delete the stock transaction
    $deleteStmt = $conn->prepare("DELETE FROM stock_transactions WHERE transaction_id = ?");
    $deleteStmt->bind_param("s", $transaction_id);
    $deleteStmt->execute();

    // Delete the main transaction
    $deleteMainStmt = $conn->prepare("DELETE FROM transactions WHERE transaction_id = ?");
    $deleteMainStmt->bind_param("s", $transaction_id);
    $deleteMainStmt->execute();

    // Add notification
    $notif_msg = "Stock out transaction for {$transaction['product_name']} has been deleted.";
    $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, message, type, is_read) VALUES (?, ?, 'success', 0)");
    $notif_stmt->bind_param("is", $_SESSION['user_id'], $notif_msg);
    $notif_stmt->execute();

    // Commit the transaction
    $conn->commit();

    // Determine if request is JSON or form POST
    $isJsonRequest = !empty($input);

    if ($isJsonRequest) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    } else {
        // Set session success message
        $_SESSION['success_message'] = 'Transaction deleted successfully';
        // Redirect back to stock_out.php without query parameters
        header('Location: stock_out.php');
        exit();
    }

} catch (Exception $e) {
    // Rollback the transaction on error
    
    $isJsonRequest = !empty($input);

    if ($isJsonRequest) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    } else {
        // Redirect back with error message
        header('Location: stock_out.php?error=' . urlencode($e->getMessage()));
        exit();
    }
}

$conn->close();
?> 