<?php
// get_product.php - Fetch product details by product_id
session_start();
include("connect.php");

// Ensure the user is logged in
if (!isset($_SESSION['user_id']) || (!in_array($_SESSION['role'], ['admin', 'staff']))) {
    header('Content-Type: application/json');
    echo json_encode(["error" => "Permission denied"]);
    exit();
}

// Check if product_id parameter exists
if (!isset($_GET['product_id']) || empty($_GET['product_id'])) {
    header('Content-Type: application/json');
    echo json_encode(["error" => "Product ID is required"]);
    exit();
}

$product_id = $_GET['product_id'];

// Query the database for the product
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ? LIMIT 1");
$stmt->bind_param("s", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Content-Type: application/json');
    echo json_encode(["error" => "Product not found"]);
    exit();
}

$product = $result->fetch_assoc();

// Check if product is in stock
if ($product['stock_quantity'] <= 0) {
    header('Content-Type: application/json');
    echo json_encode(["error" => "Product is out of stock"]);
    exit();
}

// Return product details
$productData = [
    'id' => $product['product_id'],
    'name' => $product['product_name'],
    'brand' => $product['brand'],
    'price' => (float)$product['selling_price'],
    'available_quantity' => (int)$product['stock_quantity'],
    'unit' => $product['unit_of_measure'],
    'category' => $product['category']
];

header('Content-Type: application/json');
echo json_encode($productData);

$stmt->close();
$conn->close();
?>