<?php
session_start();
include ("connect.php");
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff')) {
    header('Location: index.php');
    exit();
}

$role = $_SESSION['role'];


if ($role !== 'admin' && $role !== 'staff') {
    header("Location: index.php");
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
     <button class="logout-button" onclick="logout()">Logout</button>
    <div class="container">
        <div class="header">
            <div class="fpbg">FPBG</div>
            <div class="stock">STOCK</div>
            <div class="cashiering">CASHIERING SYSTEM</div>
        </div>
        <div class="total">Total: $<span id="totalAmount">0.00</span></div>
        
        <h3>Barcode / Product Code</h3>
        <input type="text" id="barcode" placeholder="Enter barcode">
        
        <label for="quantity">Quantity:</label>
        <input type="number" id="quantity" placeholder="Enter quantity" min="1" value="1">
        
        <button onclick="addProduct()">Add</button>
        
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
        
        <div class="payment">
            <label>Discount (%): <input type="number" id="discount" value="0" onchange="updateTotal()"></label>
            <label>Amount Paid: <input type="number" id="paidAmount" value="0" onchange="updateChange()"></label>
            <h3>Total Payable: $<span id="payableAmount">0.00</span></h3>
            <h3>Change: $<span id="change">0.00</span></h3>
        </div>
        
        <div class="buttons">
            <button onclick="processPayment()">Pay</button>
            <button onclick="resetSystem()">Reset</button>
            
        </div>
    </div>
    
    <script src="cashiering.js"></script>
</body>
</html>