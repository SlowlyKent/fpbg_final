<?php
// update_inventory.php - Handles inventory updates via API
session_start();
include("connect.php");

// Ensure the user is logged in
if (!isset($_SESSION['user_id']) || (!in_array($_SESSION['role'], ['admin', 'staff']))) {
    header('Content-Type: application/json');
    echo json_encode(["error" => "Permission denied"]);
    exit();
}

// Check if it's a POST request with JSON data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw POST data
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!$data || !isset($data['cart']) || empty($data['cart'])) {
        header('Content-Type: application/json');
        echo json_encode(["error" => "Invalid data received"]);
        exit();
    }
    
    $cart = $data['cart'];
    $transactionId = isset($data['transaction_id']) ? $data['transaction_id'] : null;
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        foreach ($cart as $item) {
            $product_id = $item['id'];
            $quantity = $item['quantity'];
            
            // Update the inventory - reduce stock quantity
            $stmt = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?");
            $stmt->bind_param("is", $quantity, $product_id);
            $result = $stmt->execute();
            
            if (!$result) {
                throw new Exception("Failed to update inventory for product ID: $product_id");
            }
            
            // Check if we need to update stock status based on remaining quantity
            $stmt = $conn->prepare("SELECT stock_quantity FROM products WHERE product_id = ?");
            $stmt->bind_param("s", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                $newQuantity = $row['stock_quantity'];
                $newStatus = "in stock";
                
                // Set status based on remaining quantity
                if ($newQuantity <= 0) {
                    $newStatus = "out of stock";
                    // Add out of stock notification
                    $notif_msg = "Product {$product_id} is now out of stock!";
                    $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, message, is_read, created_at) VALUES (?, ?, 0, NOW())");
                    $notif_stmt->bind_param("is", $_SESSION['user_id'], $notif_msg);
                    $notif_stmt->execute();
                } elseif ($newQuantity < 10) { // Assuming 10 is the threshold for "low stock"
                    $newStatus = "low stock";
                    // Add low stock notification
                    $notif_msg = "Low stock alert: Product {$product_id} has only {$newQuantity} units remaining!";
                    $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, message, is_read, created_at) VALUES (?, ?, 0, NOW())");
                    $notif_stmt->bind_param("is", $_SESSION['user_id'], $notif_msg);
                    $notif_stmt->execute();
                } else {
                    $newStatus = "in stock";
                }
                
                // Update the status
                $updateStmt = $conn->prepare("UPDATE products SET stock_status = ? WHERE product_id = ?");
                $updateStmt->bind_param("ss", $newStatus, $product_id);
                $updateStmt->execute();
            }
            
            // Insert transaction details if transaction ID is provided
            if ($transactionId) {
                $stmt = $conn->prepare("INSERT INTO stock_transactions (transaction_id, product_id, quantity, transaction_type) VALUES (?, ?, ?, 'stock_out')");
                $stmt->bind_param("ssd", $transactionId, $product_id, $quantity);
                $stmt->execute();
            }
        }
        
        // If everything is successful, commit the transaction
        $conn->commit();
        
        header('Content-Type: application/json');
        echo json_encode(["success" => "Inventory updated successfully"]);
    } catch (Exception $e) {
        // An error occurred, rollback the transaction
        $conn->rollback();
        
        header('Content-Type: application/json');
        echo json_encode(["error" => $e->getMessage()]);
    }
    
    $conn->close();
    
} else {
    header('Content-Type: application/json');
    echo json_encode(["error" => "Invalid request method"]);
}
?>