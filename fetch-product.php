<?php
include("connect.php");

$barcode = $_POST ["barcode"];


$sql = "SELECT product_id, product_name, selling_price, stock_quantity FROM products WHERE barcode = '$barcode' LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo json_encode($result->fetch_assoc());
} else {
    echo json_encode(null);
}
?>