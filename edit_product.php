<?php
// edit_product.php - Edit a product
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include("connect.php");

// Ensure the user is logged in and is an admin or staff
if (!isset($_SESSION['user_id']) || (!in_array($_SESSION['role'], ['admin', 'staff']))) {
    header("Location: login.php");
    exit();
}

// Get the product ID from the query string
$productId = isset($_GET['id']) ? $_GET['id'] : null;

if (!$productId) {
    die("Product ID not provided");
}

// Fetch the product details
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->bind_param("s", $productId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Product not found");
}

$product = $result->fetch_assoc();

// Handle form submission for updating the product
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productName = $_POST['product_name'];
    $brand = $_POST['brand'];
    $stockQuantity = $_POST['stock_quantity'];
    $unitOfMeasure = $_POST['unit_of_measure'];
    $category = $_POST['category'];
    $costPrice = $_POST['cost_price'];
    $sellingPrice = $_POST['selling_price'];
    $expirationDate = $_POST['expiration_date'];

    $stmt = $conn->prepare("UPDATE products SET product_name = ?, brand = ?, stock_quantity = ?, unit_of_measure = ?, category = ?, cost_price = ?, selling_price = ?, expiration_date = ? WHERE product_id = ?");
    $stmt->bind_param("ssissddss", $productName, $brand, $stockQuantity, $unitOfMeasure, $category, $costPrice, $sellingPrice, $expirationDate, $productId);

    if ($stmt->execute()) {
        // Add update notification
        $notif_msg = "Product updated: {$productName}";
        $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, message, type, is_read, created_at) VALUES (?, ?, 'info', 0, NOW())");
        $notif_stmt->bind_param("is", $_SESSION['user_id'], $notif_msg);
        $notif_stmt->execute();

        $_SESSION['success_message'] = "Product updated successfully";
        header("Location: inventory.php");
        exit();
    } else {
        $error = "Error updating product: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link rel="stylesheet" href="edit_product.css">
    <script>
        function closeModal() {
            window.location.href = 'inventory.php';
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="modal-header">
            <h1>Edit Product</h1>
            <button onclick="closeModal()" class="close-btn">&times;</button>
        </div>
        <?php if (isset($error)): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <label for="product_name">Product Name:</label>
            <input type="text" id="product_name" name="product_name" value="<?= htmlspecialchars($product['product_name']) ?>" required>

            <label for="brand">Brand:</label>
            <input type="text" id="brand" name="brand" value="<?= htmlspecialchars($product['brand']) ?>" required>

            <label for="stock_quantity">Stock Quantity:</label>
            <input type="number" id="stock_quantity" name="stock_quantity" value="<?= htmlspecialchars($product['stock_quantity']) ?>" required>

            <label for="unit_of_measure">Unit of Measure:</label>
            <select id="unit_of_measure" name="unit_of_measure" class="unit-select" required>
                <option value="kg">Kilograms (kg)</option>
                <option value="g">Grams (g)</option>
                <option value="pcs">Pieces (pcs)</option>
                <option value="box">Box</option>
                <option value="pack">Pack</option>
            </select>

            <label for="category">Category:</label>
            <select id="category" name="category" required>
                <option value="Processed Meat" <?= $product['category'] === 'Processed Meat' ? 'selected' : '' ?>>Processed Meat</option>
                <option value="Raw Meat" <?= $product['category'] === 'Raw Meat' ? 'selected' : '' ?>>Raw Meat</option>
                <option value="Fried Snacks" <?= $product['category'] === 'Fried Snacks' ? 'selected' : '' ?>>Fried Snacks</option>
            </select>

            <label for="cost_price">Cost Price:</label>
            <input type="number" step="0.01" id="cost_price" name="cost_price" value="<?= htmlspecialchars($product['cost_price']) ?>" required>

            <label for="selling_price">Selling Price:</label>
            <input type="number" step="0.01" id="selling_price" name="selling_price" value="<?= htmlspecialchars($product['selling_price']) ?>" required>

            <label for="expiration_date">Expiration Date:</label>
            <input type="date" id="expiration_date" name="expiration_date" value="<?= htmlspecialchars($product['expiration_date']) ?>" required>

            <button type="submit">Update Product</button>
        </form>
    </div>
</body>
</html>