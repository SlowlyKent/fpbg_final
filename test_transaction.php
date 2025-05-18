<?php
include('connect.php');

try {
    // Prepare test transaction data
    $data = [
        'cart' => [
            [
                'product_id' => 'TEST001',
                'quantity' => 3,  // Buy 3 units to reduce stock below 3
                'price' => 10.00
            ]
        ],
        'totalAmount' => 30.00,
        'amountPaid' => 50.00,
        'change' => 20.00,
        'discount' => 0
    ];

    // Set content type to JSON
    header('Content-Type: application/json');

    // Call save_transactions.php using curl
    $ch = curl_init('http://localhost/fpbg_final/save_transactions.php');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "Transaction Response (HTTP $httpCode):\n";
    echo $response . "\n\n";

    // Check notifications
    $result = $conn->query("SELECT * FROM notifications ORDER BY created_at DESC LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $notification = $result->fetch_assoc();
        echo "Latest Notification:\n";
        echo "Type: {$notification['type']}\n";
        echo "Message: {$notification['message']}\n";
        echo "Created At: {$notification['created_at']}\n";
    } else {
        echo "No notifications found.\n";
    }

    // Check updated stock level
    $result = $conn->query("SELECT * FROM products WHERE product_id = 'TEST001'");
    if ($result && $result->num_rows > 0) {
        $product = $result->fetch_assoc();
        echo "\nUpdated Product Stock:\n";
        echo "Product: {$product['product_name']}\n";
        echo "Current Stock: {$product['stock_quantity']}\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

$conn->close();
?> 