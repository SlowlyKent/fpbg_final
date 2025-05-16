<?php
include("connect.php");

if (isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];

    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->bind_param("s", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        echo json_encode($product);
    } else {
        echo json_encode(["error" => "Product not found"]);
    }

    $stmt->close();
    $conn->close();
}
?>
