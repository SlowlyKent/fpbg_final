<?php
// Set JSON headers
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

// Database connection
$conn = new mysqli("localhost", "root", "", "fpbg_final");
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Verify API key for all requests
require_once 'auth_middleware.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    verifyApiKey();
}

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Get all products or single product
        if (isset($_GET['product_id'])) {
            $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
            $stmt->bind_param("i", $_GET['product_id']);
        } else {
            $stmt = $conn->prepare("SELECT * FROM products");
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $products = $result->fetch_all(MYSQLI_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $products
        ]);
        break;

    case 'POST':
        // Create new product
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['product_name']) || !isset($input['stock_quantity']) || 
            !isset($input['unit_of_measure']) || !isset($input['category']) || 
            !isset($input['cost_price']) || !isset($input['selling_price']) || 
            !isset($input['expiration_date'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Missing required fields'
            ]);
            exit();
        }

        $stock_status = isset($input['stock_status']) ? $input['stock_status'] : 'In Stock';
        
        $stmt = $conn->prepare("INSERT INTO products (product_name, stock_quantity, unit_of_measure, 
                                category, cost_price, selling_price, stock_status, expiration_date) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sissddss", 
            $input['product_name'], 
            $input['stock_quantity'],
            $input['unit_of_measure'], 
            $input['category'],
            $input['cost_price'], 
            $input['selling_price'],
            $stock_status, 
            $input['expiration_date']
        );

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Product created successfully',
                'product_id' => $conn->insert_id
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to create product'
            ]);
        }
        break;

    case 'PUT':
        // Update product
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['product_id']) || !isset($input['product_name']) || 
            !isset($input['stock_quantity']) || !isset($input['unit_of_measure']) || 
            !isset($input['category']) || !isset($input['cost_price']) || 
            !isset($input['selling_price']) || !isset($input['expiration_date'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Missing required fields'
            ]);
            exit();
        }

        $stock_status = isset($input['stock_status']) ? $input['stock_status'] : 'In Stock';
        
        $stmt = $conn->prepare("UPDATE products SET 
            product_name = ?, 
            stock_quantity = ?, 
            unit_of_measure = ?, 
            category = ?, 
            cost_price = ?, 
            selling_price = ?, 
            stock_status = ?, 
            expiration_date = ? 
            WHERE product_id = ?");
            
        $stmt->bind_param("sissddssi", 
            $input['product_name'],
            $input['stock_quantity'],
            $input['unit_of_measure'],
            $input['category'],
            $input['cost_price'],
            $input['selling_price'],
            $stock_status,
            $input['expiration_date'],
            $input['product_id']
        );

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Product updated successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to update product'
            ]);
        }
        break;

    case 'DELETE':
        // Delete product
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['product_id'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Product ID is required'
            ]);
            exit();
        }

        $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $input['product_id']);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Product deleted successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to delete product'
            ]);
        }
        break;

    default:
        echo json_encode([
            'success' => false,
            'message' => 'Invalid request method'
        ]);
}

$conn->close();
?>
