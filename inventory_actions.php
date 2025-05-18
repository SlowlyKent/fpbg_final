<?php
session_start();
include('connect.php');

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Permission denied']);
    exit();
}

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['action']) || !isset($data['product_id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit();
}

// Function to check stock level and create notification
function checkStockLevel($conn, $productId, $quantity, $productName) {
    if ($quantity <= 0) {
        $notif_msg = "ALERT: {$productName} is now out of stock!";
        $notif_type = "danger";
    } elseif ($quantity < 3) {
        $notif_msg = "WARNING: {$productName} is running low! Only {$quantity} units remaining.";
        $notif_type = "warning";
    } else {
        return; // No notification needed
    }

    // Insert notification
    $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, message, is_read, created_at) VALUES (?, ?, 0, NOW())");
    $notif_stmt->bind_param("is", $_SESSION['user_id'], $notif_msg);
    $notif_stmt->execute();
}

switch ($data['action']) {
    case 'get_product':
        $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
        $stmt->bind_param("s", $data['product_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc();
            echo json_encode(['success' => true, 'product' => $product]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Product not found']);
        }
        break;

    case 'update':
        if (!isset($data['product_data'])) {
            echo json_encode(['success' => false, 'error' => 'No product data provided']);
            exit();
        }

        $product = $data['product_data'];
        
        $stmt = $conn->prepare("
            UPDATE products 
            SET product_name = ?, 
                brand = ?,
                stock_quantity = ?,
                unit_of_measure = ?,
                category = ?,
                cost_price = ?,
                selling_price = ?,
                expiration_date = ?
            WHERE product_id = ?
        ");

        $stmt->bind_param(
            "ssississs",
            $product['product_name'],
            $product['brand'],
            $product['stock_quantity'],
            $product['unit_of_measure'],
            $product['category'],
            $product['cost_price'],
            $product['selling_price'],
            $product['expiration_date'],
            $data['product_id']
        );

        if ($stmt->execute()) {
            // Add update notification
            $notif_msg = "Product updated: {$product['product_name']}";
            $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, message, is_read, created_at) VALUES (?, ?, 0, NOW())");
            $notif_stmt->bind_param("is", $_SESSION['user_id'], $notif_msg);
            $notif_stmt->execute();

            // Check stock level and add notification if needed
            checkStockLevel($conn, $data['product_id'], $product['stock_quantity'], $product['product_name']);

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update product']);
        }
        break;

    case 'delete':
        // Get product name before deletion for notification
        $name_stmt = $conn->prepare("SELECT product_name FROM products WHERE product_id = ?");
        $name_stmt->bind_param("s", $data['product_id']);
        $name_stmt->execute();
        $result = $name_stmt->get_result();
        $product = $result->fetch_assoc();

        if (!$product) {
            echo json_encode(['success' => false, 'error' => 'Product not found']);
            exit();
        }

        $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->bind_param("s", $data['product_id']);

        if ($stmt->execute()) {
            // Add deletion notification
            $notif_msg = "Product deleted: {$product['product_name']}";
            $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, message, is_read, created_at) VALUES (?, ?, 0, NOW())");
            $notif_stmt->bind_param("is", $_SESSION['user_id'], $notif_msg);
            $notif_stmt->execute();

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to delete product']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        break;
}

$conn->close();
?> 