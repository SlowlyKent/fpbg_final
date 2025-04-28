<?php
session_start();
include("connect.php");
header('Content-Type: application/json');


// Log the request to check the data
error_log("Transaction request received: " . print_r($_POST, true));

// Check if user is authorized
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Read the raw POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate the data
if (!$data || !isset($data['cart']) || !is_array($data['cart']) || empty($data['cart'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid transaction data']);
    exit;
}

$totalAmount = $data['totalAmount'] ?? 0;
$amountPaid = $data['amountPaid'] ?? 0;
$changeAmount = $data['change'] ?? 0;
$discount = $data['discount'] ?? 0;

// Validate numeric values
if (!is_numeric($totalAmount) || !is_numeric($amountPaid) || !is_numeric($changeAmount) || !is_numeric($discount)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payment amounts']);
    exit;
}

try {
    // Begin transaction
    $conn->begin_transaction();

    // Insert into transactions table
    $stmt = $conn->prepare("
        INSERT INTO transactions (user_id, total_amount, discount, amount_paid, change_amount)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("idddd", $_SESSION['user_id'], $totalAmount, $discount, $amountPaid, $changeAmount);
    $stmt->execute();

    // Get the transaction ID
    $transaction_id = $conn->insert_id;

    // Optionally, insert transaction items into a new table (transaction_items), if necessary
    // Assuming you have a table `transaction_items`, you can loop through the cart
    foreach ($data['cart'] as $item) {
        $stmt_item = $conn->prepare("INSERT INTO transaction_items (transaction_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
        $stmt_item->bind_param("iiid", $transaction_id, $item['product_id'], $item['quantity'], $item['price']);
        $stmt_item->execute();
    }

    // Commit the transaction
    $conn->commit();

    // Return success response with transaction ID
    echo json_encode(['success' => true, 'transaction_id' => $transaction_id]);

} catch (Exception $e) {
    // Rollback if any error occurs
    $conn->rollback();
    error_log("Transaction failed: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
} finally {
    $conn->close();
}
?>
