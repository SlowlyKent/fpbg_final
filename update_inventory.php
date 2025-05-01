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
                } elseif ($newQuantity < 10) { // Assuming 10 is the threshold for "low stock"
                    $newStatus = "low stock";
                }
                
                // Update the status
                $updateStmt = $conn->prepare("UPDATE products SET stock_status = ? WHERE product_id = ?");
                $updateStmt->bind_param("ss", $newStatus, $product_id);
                $updateStmt->execute();
            }
            
            // Insert transaction details if transaction ID is provided
            if ($transactionId) {
                $stmt = $conn->prepare("INSERT INTO transaction_details (transaction_id, product_id, quantity, price) 
                                        VALUES (?, ?, ?, ?)");
                $price = $item['price'];
                $stmt->bind_param("iidd", $transactionId, $product_id, $quantity, $price);
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