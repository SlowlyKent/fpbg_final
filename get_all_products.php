<?php
session_start();
include("connect.php");

// Ensure the user is logged in
if (!isset($_SESSION['user_id']) || (!in_array($_SESSION['role'], ['admin', 'staff']))) {
    header('Content-Type: application/json');
    echo json_encode(["error" => "Permission denied"]);
    exit();
}

// Query the database for all products
$stmt = $conn->prepare("SELECT product_id, product_name, brand, selling_price, stock_quantity FROM products WHERE stock_quantity > 0 ORDER BY product_name");
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = [
        'id' => $row['product_id'],
        'name' => $row['product_name'],
        'brand' => $row['brand'],
        'price' => (float)$row['selling_price'],
        'stock' => (int)$row['stock_quantity']
    ];
}

header('Content-Type: application/json');
echo json_encode($products);

$stmt->close();
$conn->close();
?> 