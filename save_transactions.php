<?php
// save_transactions.php

header('Content-Type: application/json');

// Get the raw POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Check if the data is valid
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

// Process the transaction (this is just an example, replace with your actual logic)
$transaction_id = uniqid(); // Generate a unique transaction ID

// Simulate a successful transaction
$response = [
    'transaction_id' => $transaction_id,
    'message' => 'Transaction processed successfully'
];

// Return the response as JSON
echo json_encode($response);
?>
