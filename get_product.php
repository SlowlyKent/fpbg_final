<?php
session_start();
include("connect.php");

// Always set JSON response header
header('Content-Type: application/json');

// Turn on full error reporting (development only)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// --- Check Authorization ---
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// --- Get and Validate Barcode ---
$barcode = $_GET['barcode'] ?? '';

if (empty($barcode)) {
    http_response_code(400);
    echo json_encode(['error' => 'Barcode is required']);
    exit;
}

// --- Fetch Product from Database ---
try {
    $stmt = $conn->prepare("
        SELECT product_id, product_name AS name, brand,
               CAST(selling_price AS DECIMAL(10,2)) AS price,
               stock_quantity, expiration_date
        FROM products
        WHERE barcode = ?
        LIMIT 1
    ");

    if (!$stmt) {
        throw new Exception("SQL error: " . $conn->error);
    }

    $stmt->bind_param("s", $barcode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Product not found']);
        exit;
    }

    $product = $result->fetch_assoc();

    // Check stock and expiration
    if (!empty($product['expiration_date']) && strtotime($product['expiration_date']) < time()) {
        $product['expiration_warning'] = 'This product has expired';
    }

    if ((int)$product['stock_quantity'] <= 0) {
        $product['stock_warning'] = 'Out of stock';
    }

    // Return only safe data
    echo json_encode($product);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
} finally {
    $conn->close();
}
?>
