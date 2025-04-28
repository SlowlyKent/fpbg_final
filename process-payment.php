<?php
// Read raw POST data
$jsonData = file_get_contents("php://input");

// Decode the JSON data
$data = json_decode($jsonData, true);

// Check for decoding errors
if (!$data) {
    echo json_encode(["error" => "Failed to decode JSON"]);
    exit();
}

// Ensure 'items' key exists and is an array
if (!isset($data['items']) || !is_array($data['items'])) {
    echo json_encode(["error" => "Invalid or missing items"]);
    exit();
}

// Database connection
include("connect.php");

// Start a transaction for database consistency
$conn->begin_transaction();

try {
    // Insert transaction record (assuming $transaction_id and other necessary fields are available)
    $transaction_id = $data['transaction_id'];  // Example: transaction_id can be passed or generated
    $user_id = $data['user_id']; // Assuming user_id is passed or retrieved from session

    $query = "INSERT INTO transactions (transaction_id, user_id, total_amount, payment_date) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiis", $transaction_id, $user_id, $data['total_amount'], date('Y-m-d H:i:s'));
    $stmt->execute();

    // Prepare SQL for inserting transaction items
    $stmt = $conn->prepare("INSERT INTO transaction_items (transaction_id, barcode, product_name, brand, price, quantity, expiration_date) VALUES (?, ?, ?, ?, ?, ?, ?)");

    // Insert each item into transaction_items table
    foreach ($data['items'] as $item) {
        // Bind parameters for each item and execute the insert
        $stmt->bind_param("issdids", 
            $transaction_id,  // Transaction ID
            $item['barcode'],
            $item['name'],
            $item['brand'],
            $item['price'],
            $item['quantity'],
            $item['expiration_date']
        );
        $stmt->execute();
    }

    // Commit the transaction if everything is successful
    $conn->commit();

    // Return success response
    echo json_encode(["success" => "Transaction processed successfully"]);
} catch (Exception $e) {
    // Rollback transaction if there is any error
    $conn->rollback();
    echo json_encode(["error" => "Transaction failed: " . $e->getMessage()]);
}
?>
