<?php
header('Content-Type: application/json');
include ('connect.php');

// Log errors to a file
ini_set('log_errors', 1);
ini_set('error_log', 'error_log.txt');

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data)) {
    error_log('No data received');
    echo json_encode(['error' => 'No data received']);
    exit;
}

$cart = $data['cart'];
$totalAmount = $data['totalAmount'];
$amountPaid = $data['amountPaid'];
$change = $data['change'];
$discount = $data['discount'];

// Generate a unique transaction ID
$transactionId = uniqid();

// Start a transaction
$conn->begin_transaction();

try {
    // Insert into transactions table
    $transactionQuery = "INSERT INTO transactions (transaction_id, total_amount, amount_paid, `change`, discount) VALUES (?, ?, ?, ?, ?)";
    $transactionStmt = $conn->prepare($transactionQuery);
    if ($transactionStmt === false) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $transactionStmt->bind_param("sdddd", $transactionId, $totalAmount, $amountPaid, $change, $discount);
    $transactionStmt->execute();

    // Insert into stock_transactions table
    $stockTransactionQuery = "INSERT INTO stock_transactions (transaction_id, product_id, quantity, transaction_type) VALUES (?, ?, ?, 'stock_out')";
    $stockTransactionStmt = $conn->prepare($stockTransactionQuery);
    if ($stockTransactionStmt === false) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    foreach ($cart as $item) {
        $productId = $item['product_id'];
        $quantity = $item['quantity'];
        $stockTransactionStmt->bind_param("sis", $transactionId, $productId, $quantity);
        $stockTransactionStmt->execute();

        // Decrease stock quantity in inventory
        $updateStockQuery = "UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?";
        $updateStockStmt = $conn->prepare($updateStockQuery);
        if ($updateStockStmt === false) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $updateStockStmt->bind_param("ii", $quantity, $productId);
        $updateStockStmt->execute();
    }

    // Commit the transaction
    $conn->commit();

    echo json_encode(['transaction_id' => $transactionId]);

} catch (Exception $e) {
    // Rollback the transaction
    $conn->rollback();
    error_log('Failed to save transaction: ' . $e->getMessage());
    echo json_encode(['error' => 'Failed to save transaction: ' . $e->getMessage()]);
}

$conn->close();
?>
