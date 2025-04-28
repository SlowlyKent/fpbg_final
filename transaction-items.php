<?php
// Include database connection
include("connect.php");

// SQL query to fetch transactions
$sql = "SELECT * FROM transaction_items";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Store all the rows in an array
    $transactions = [];
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }
    // Return transactions as JSON
    echo json_encode($transactions);
} else {
    // No transactions found
    echo json_encode(["error" => "No transactions found"]);
}

$conn->close();
?>
