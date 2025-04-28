<?php
session_start();
include("connect.php");

// Ensure the user is logged in
if (!isset($_SESSION['user_id']) || (!in_array($_SESSION['role'], ['admin', 'staff']))) {
    header('Location: permission-denied.php');
    exit();
}

// Handle payment POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amountPaid = $_POST['amountPaid'];
    $discount = $_POST['discount'];
    $cart = $_POST['cart']; // JSON array of cart items

    $cartItems = json_decode($cart, true);

    if (!$cartItems) {
        echo json_encode(["error" => "Cart is empty."]);
        exit();
    }

    // Calculate total
    $totalAmount = 0;
    foreach ($cartItems as $item) {
        $totalAmount += $item['price'] * $item['quantity'];
    }

    // Apply discount
    $totalAmount = $totalAmount - ($totalAmount * ($discount / 100));

    // Check if payment is enough
    if ($amountPaid < $totalAmount) {
        echo json_encode(["error" => "Insufficient payment."]);
        exit();
    }

    $userId = $_SESSION['user_id'];
    $change = $amountPaid - $totalAmount;

    // Save transaction (corrected columns)
    $stmt = $conn->prepare("INSERT INTO transactions (user_id, total_amount, discount, amount_paid, change_amount) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("idddd", $userId, $totalAmount, $discount, $amountPaid, $change);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(["success" => "Payment successful!"]);
    } else {
        echo json_encode(["error" => "Failed to record transaction."]);
    }

    $stmt->close();
    $conn->close();
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FPBG STOCK CASHIERING SYSTEM</title>
    <link rel="stylesheet" href="cashiering.css">
</head>
<body>
    <button class="logout-button" onclick="window.location.href='dashboard.php';">Back to Dashboard</button>

    <div class="container">
        <div class="header">
            <div class="fpbg">FPBG</div>
            <div class="stock">STOCK</div>
            <div class="caahiering">CASHIERING SYSTEM</div>
        </div>

        <div class="total">Total: ₱<span id="totalAmount">0.00</span></div>

        <!-- Product details -->
        <h3>Barcode / Product Code</h3>
        <input type="text" id="barcode" placeholder="Enter barcode">

        <label for="quantity">Quantity:</label>
        <input type="number" id="quantity" placeholder="Enter quantity" min="1" value="1">

        <button id="addProductBtn">Add</button>

        <h3>Order List</h3>
        <table class="order-list">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Product Name</th>
                    <th>Unit Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="orderTable"></tbody>
        </table>

        <!-- Payment section (moved below) -->
        <div class="payment">
            <label>Discount (%):</label>
            <input type="number" id="discount" value="0" onchange="updateTotal()">
            
            <label>Amount Paid:</label>
            <input type="number" id="amountPaid" value="0" onchange="updateChange()">

            <h3>Total Payable: ₱<span id="payableAmount">0.00</span></h3>
            <h3>Change: ₱<span id="change">0.00</span></h3>

            <!-- Error message for insufficient payment -->
            <div id="error-message"></div>  <!-- This is where the error will be displayed -->
        </div>

        <div class="buttons">
        <button id="payButton">Pay</button>
            <button onclick="resetSystem()">Reset</button>
        </div>
    </div>

    <!-- Make sure script is loaded at the end of the body -->
    <script src="cashiering-admin.js"></script>
</body>
</html>