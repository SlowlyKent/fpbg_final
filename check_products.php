<?php
include('connect.php');

try {
    $result = $conn->query("SELECT * FROM products");
    if ($result) {
        echo "Products in database:\n";
        echo "-------------------\n";
        while ($row = $result->fetch_assoc()) {
            echo "ID: {$row['product_id']}\n";
            echo "Name: {$row['product_name']}\n";
            echo "Stock: {$row['stock_quantity']}\n";
            echo "Price: {$row['selling_price']}\n";
            echo "-------------------\n";
        }
    } else {
        echo "Error getting products: " . $conn->error . "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

$conn->close();
?> 