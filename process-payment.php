<?php
include("connect.php");

$data = json_decode(file_get_contents('php://input'), true);
$orders = $data['orders'];
$discount = $data['discount'];
$paidAmount = $data['paidAmount'];
$totalAmount = $data['totalAmount'];

// Insert into sales table
foreach ($orders as $order) {
    $product_id = $order['product_id'];
    $quantity = $order['quantity'];
    $total = $order['total'];

    $conn->query("INSERT INTO sales (product_id, quantity, total_price, sale_date) 
                  VALUES ('$product_id', '$quantity', '$total', NOW())");

    // Decrease product stock
    $conn->query("UPDATE products SET stock = stock - $quantity WHERE id = '$product_id'");
}

echo json_encode(["status" => "success"]);
?>
