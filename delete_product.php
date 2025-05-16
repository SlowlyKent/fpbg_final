<?php
include("connect.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];

    $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
    $stmt->bind_param("s", $product_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => "Product deleted successfully"]);
    } else {
        echo json_encode(["error" => "Failed to delete product"]);
    }

    $stmt->close();
    $conn->close();
}
?>
