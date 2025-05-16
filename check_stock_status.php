<?php
include('connect.php');

$product_ids = ['111111', '20251001'];

echo "<h2>Stock Status Check</h2>";
echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>Product ID</th><th>Stock Quantity</th><th>Stock Status</th></tr>";

foreach ($product_ids as $product_id) {
    $stmt = $conn->prepare("SELECT product_id, stock_quantity, stock_status FROM products WHERE product_id = ?");
    $stmt->bind_param("s", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['product_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['stock_quantity']) . "</td>";
        echo "<td>" . htmlspecialchars($row['stock_status']) . "</td>";
        echo "</tr>";
    } else {
        echo "<tr><td colspan='3'>Product ID $product_id not found.</td></tr>";
    }
}

echo "</table>";
?>
