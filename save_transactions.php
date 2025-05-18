<?php
session_start();
// Ensure clean output buffer at the start
ob_start();

// Set proper JSON content type
header('Content-Type: application/json');

// Include database connection
include ('connect.php');

// Log errors to a file
ini_set('log_errors', 1);
ini_set('error_log', 'error_log.txt');

// Disable error output
ini_set('display_errors', 0);

// Clean any existing output
ob_clean();

try {
    // Get and decode JSON input
    $input = file_get_contents('php://input');
    if (!$input) {
        throw new Exception('No input data received');
    }

    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data: ' . json_last_error_msg());
    }

    // Validate required fields
    if (!isset($data['cart']) || !isset($data['totalAmount']) || !isset($data['amountPaid']) || 
        !isset($data['change']) || !isset($data['discount'])) {
        throw new Exception('Missing required fields');
    }

    $cart = $data['cart'];
    $totalAmount = floatval($data['totalAmount']);
    $amountPaid = floatval($data['amountPaid']);
    $change = floatval($data['change']);
    $discount = floatval($data['discount']);

    // Generate a unique transaction ID
    $transactionId = uniqid();

    // Start a transaction
    $conn->begin_transaction();

    // Insert into transactions table
    $transactionQuery = "INSERT INTO transactions (transaction_id, total_amount, amount_paid, `change`, discount, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
    $transactionStmt = $conn->prepare($transactionQuery);
    if ($transactionStmt === false) {
        throw new Exception("Failed to prepare transaction query: " . $conn->error);
    }
    
    if (!$transactionStmt->bind_param("sdddd", $transactionId, $totalAmount, $amountPaid, $change, $discount)) {
        throw new Exception("Failed to bind transaction parameters: " . $transactionStmt->error);
    }
    
    if (!$transactionStmt->execute()) {
        throw new Exception("Failed to insert transaction: " . $transactionStmt->error);
    }

    // Insert into stock_transactions table
    $stockTransactionQuery = "INSERT INTO stock_transactions (transaction_id, product_id, quantity, transaction_type) VALUES (?, ?, ?, 'stock_out')";
    $stockTransactionStmt = $conn->prepare($stockTransactionQuery);
    if ($stockTransactionStmt === false) {
        throw new Exception("Failed to prepare stock transaction query: " . $conn->error);
    }

    foreach ($cart as $item) {
        if (!isset($item['product_id']) || !isset($item['quantity'])) {
            throw new Exception("Invalid cart item data");
        }

        $productId = $item['product_id'];
        $quantity = floatval($item['quantity']);
        
        if (!$stockTransactionStmt->bind_param("ssd", $transactionId, $productId, $quantity)) {
            throw new Exception("Failed to bind stock transaction parameters: " . $stockTransactionStmt->error);
        }
        
        if (!$stockTransactionStmt->execute()) {
            throw new Exception("Failed to insert stock transaction: " . $stockTransactionStmt->error);
        }

        // Decrease stock quantity in inventory
        $updateStockQuery = "UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?";
        $updateStockStmt = $conn->prepare($updateStockQuery);
        if ($updateStockStmt === false) {
            throw new Exception("Failed to prepare update stock query: " . $conn->error);
        }
        
        if (!$updateStockStmt->bind_param("ds", $quantity, $productId)) {
            throw new Exception("Failed to bind update stock parameters: " . $updateStockStmt->error);
        }
        
        if (!$updateStockStmt->execute()) {
            throw new Exception("Failed to update stock: " . $updateStockStmt->error);
        }

        // Update stock status and create notifications if needed
        updateStockStatus($conn, $productId);
    }

    // Commit the transaction
    $conn->commit();

    // Clean output buffer before sending success response
    while (ob_get_level()) {
        ob_end_clean();
    }

    // Send success response
    echo json_encode([
        'success' => true,
        'transaction_id' => $transactionId
    ]);

} catch (Exception $e) {
    // Log the error
    error_log('Transaction Error: ' . $e->getMessage());
    
    // Clean output buffer before sending error response
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Send error response
    http_response_code(400); // Set appropriate error code
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
    exit;
}

// Close database connection
if (isset($conn)) {
    $conn->close();
}

// Helper function to create notifications
function createNotification($conn, $message, $type) {
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, type, is_read) VALUES (1, ?, ?, 0)");
    if ($stmt === false) {
        throw new Exception("Failed to prepare notification query: " . $conn->error);
    }
    if (!$stmt->bind_param("ss", $message, $type)) {
        throw new Exception("Failed to bind notification parameters: " . $stmt->error);
    }
    if (!$stmt->execute()) {
        throw new Exception("Failed to insert notification: " . $stmt->error);
    }
}

// Helper function to calculate average daily sales
function getAverageDailySales($conn, $productId) {
    // Calculate average daily sales for the last 30 days
    $query = "SELECT COALESCE(AVG(daily_sales), 0) as avg_daily_sales FROM (
        SELECT DATE(created_at) as sale_date, SUM(quantity) as daily_sales
        FROM stock_transactions
        WHERE product_id = ? 
        AND transaction_type = 'stock_out'
        AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(created_at)
    ) as daily_stats";
    
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        return 0;
    }
    
    if (!$stmt->bind_param("s", $productId)) {
        return 0;
    }
    
    if (!$stmt->execute()) {
        return 0;
    }
    
    $result = $stmt->get_result();
    if (!$result) {
        return 0;
    }
    
    $row = $result->fetch_assoc();
    return floatval($row['avg_daily_sales']);
}

// Helper function to update stock status and create notifications
function updateStockStatus($conn, $productId) {
    // Check updated stock level and create notification if needed
    $checkStockQuery = "SELECT product_name, stock_quantity FROM products WHERE product_id = ?";
    $checkStockStmt = $conn->prepare($checkStockQuery);
    if ($checkStockStmt === false) {
        throw new Exception("Failed to prepare check stock query: " . $conn->error);
    }
    
    if (!$checkStockStmt->bind_param("s", $productId)) {
        throw new Exception("Failed to bind check stock parameters: " . $checkStockStmt->error);
    }
    
    if (!$checkStockStmt->execute()) {
        throw new Exception("Failed to check stock level: " . $checkStockStmt->error);
    }
    
    $result = $checkStockStmt->get_result();
    if (!$result) {
        throw new Exception("Failed to get stock level result: " . $checkStockStmt->error);
    }
    
    $product = $result->fetch_assoc();
    if (!$product) {
        throw new Exception("Product not found after update");
    }

    // Get average daily sales
    $avgDailySales = getAverageDailySales($conn, $productId);
    
    // Get stock status
    $status = getStockStatus($product['stock_quantity'], $avgDailySales);
    
    // Create notification based on status
    if ($status === 'out of stock') {
        $message = "WARNING: {$product['product_name']} is out of stock! Only 0 units remaining.";
        $type = "danger";
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, type, is_read, created_at) VALUES (?, ?, ?, 0, NOW())");
        $stmt->bind_param("iss", $_SESSION['user_id'], $message, $type);
        $stmt->execute();
    } elseif ($status === 'low stock') {
        $message = "WARNING: {$product['product_name']} is running low! Only {$product['stock_quantity']} units remaining.";
        $type = "warning";
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, type, is_read, created_at) VALUES (?, ?, ?, 0, NOW())");
        $stmt->bind_param("iss", $_SESSION['user_id'], $message, $type);
        $stmt->execute();
    }
    
    // Update stock status in database
    $updateStatusStmt = $conn->prepare("UPDATE products SET stock_status = ? WHERE product_id = ?");
    $updateStatusStmt->bind_param("ss", $status, $productId);
    $updateStatusStmt->execute();
    
    return true;
}

// Helper function to get stock status
function getStockStatus($stockQuantity, $avgDailySales) {
    // Define stock status thresholds
    $outOfStockThreshold = 0;
    $lowStockThreshold = 3;

    if ($stockQuantity <= $outOfStockThreshold) {
        return 'out of stock';
    } elseif ($stockQuantity <= $lowStockThreshold) {
        return 'low stock';
    } else {
        return 'in stock';
    }
}
?>
