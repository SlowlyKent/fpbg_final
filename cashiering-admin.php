<?php
session_start();
include ('connect.php');
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
<form method="POST" action="dashboard.php">
    <button type="submit">Back to Dashboard</button>
</form>
<div class="container">
    <div class="header">
        <div class="fpbg">FPBG</div>
        <div class="stock">STOCK</div>
        <div class="cashiering">CASHIERING SYSTEM</div>
    </div>
    <div class="total">Total: $<span id="totalAmount">0.00</span></div>

    <h3>Product ID / Product Code</h3>
    <input type="text" id="product_id" placeholder="Enter the Product ID">

    <label for="quantity">Quantity:</label>
    <input type="number" id="quantity" placeholder="Enter quantity" min="1" value="1">

    <button onclick="addProduct()">Add</button>

    <h3>Order List</h3>
    <table class="order-list">
        <thead>
            <tr>
                <th>Item</th>
                <th>Brand</th>
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

<script>
    // Initialize cart array
    let cart = [];

    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('product_id')?.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                addProduct();
            }
        });

        document.getElementById('quantity')?.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                addProduct();
            }
        });

        document.getElementById('paidAmount')?.addEventListener("input", updateChange);
        document.getElementById('discount')?.addEventListener("input", updateTotal);
    });

    async function addProduct() {
        let product_id = document.getElementById("product_id")?.value;

        if (!product_id || !product_id.trim()) {
            alert("Barcode is required.");
            return;
        }

        let quantity = parseInt(document.getElementById("quantity")?.value) || 1;

        // Validate the quantity
        if (quantity <= 0) {
            alert("Quantity must be at least 1");
            return;
        }

        try {
            // Fetch product details from the server
            const response = await fetch("get_product.php?product_id=" + encodeURIComponent(product_id));

            if (!response.ok) {
                throw new Error('Failed to fetch product');
            }

            const product = await response.json();

            if (product.error) {
                alert(product.error);
                return;
            }

            // Ensure price is a number
            if (typeof product.price === 'string') {
                product.price = parseFloat(product.price);
            }

            if (isNaN(product.price)) {
                alert("Invalid product price!");
                return;
            }

            // Check if we're ordering more than available
            if (quantity > product.available_quantity) {
                alert(`Only ${product.available_quantity} units available for this product!`);
                return;
            }

            let productIndex = cart.findIndex(item => item.product_id === product_id);

            if (productIndex !== -1) {
                // Check if new total quantity exceeds available stock
                const newTotalQuantity = cart[productIndex].quantity + quantity;
                if (newTotalQuantity > product.available_quantity) {
                    alert(`Cannot add more. Only ${product.available_quantity} units available for this product!`);
                    return;
                }

                cart[productIndex].quantity += quantity;
            } else {
                cart.push({
                    ...product,
                    quantity,
                    product_id: product_id,
                    brand: product.brand || 'No Brand'
                });
            }

            updateTable();

        } catch (error) {
            console.error('API Error:', error);
            alert(error.message || 'An error occurred while adding product.');
        }

        document.getElementById("product_id").value = "";
        document.getElementById("quantity").value = "1";
    }

    // Update the order table display
    function updateTable() {
        let table = document.getElementById("orderTable");
        table.innerHTML = "";
        let total = 0;

        cart.forEach((item, index) => {
            const price = typeof item.price === 'number' ? item.price : parseFloat(item.price);

            if (isNaN(price)) {
                console.error("Invalid price for item:", item);
                return;
            }

            let row = `<tr>
                <td>${index + 1}</td>
                <td>${item.brand || 'N/A'}</td>
                <td>${item.name}</td>
                <td>₱${price.toFixed(2)}</td>
                <td>${item.quantity}</td>
                <td>₱${(price * item.quantity).toFixed(2)}</td>
                <td><button onclick="removeItem(${index})">Remove</button></td>
            </tr>`;
            total += price * item.quantity;
            table.innerHTML += row;
        });

        document.getElementById("totalAmount").innerText = total.toFixed(2);
        updateTotal();
    }

    // Remove item from cart
    function removeItem(index) {
        cart.splice(index, 1);
        updateTable();
    }

    // Update total with discount
    function updateTotal() {
        let discount = parseFloat(document.getElementById("discount")?.value) || 0;
        let total = parseFloat(document.getElementById("totalAmount")?.innerText) || 0;
        let discountedTotal = total - (total * (discount / 100));
        document.getElementById("payableAmount").innerText = discountedTotal.toFixed(2);
        updateChange();
    }

    // Calculate change
    function updateChange() {
        let payableAmountField = document.getElementById("payableAmount");
        let amountPaidField = document.getElementById("paidAmount");

        if (!payableAmountField || !amountPaidField) {
            console.error("Error: Required elements missing from DOM.");
            return;
        }

        let total = parseFloat(payableAmountField.innerText) || 0;
        let amountPaid = parseFloat(amountPaidField.value) || 0;

        if (amountPaid < total) {
            document.getElementById("change").innerText = "Insufficient payment";
            return;
        }

        document.getElementById("change").innerText = (amountPaid - total).toFixed(2);
    }

    // Process payment
     // Process payment
async function processPayment() {
    if (cart.length === 0) {
        alert("Your cart is empty. Please add items before processing payment.");
        return;
    }

    let totalAmount = parseFloat(document.getElementById("payableAmount").innerText);
    let amountPaid = parseFloat(document.getElementById("paidAmount").value);
    let discount = parseFloat(document.getElementById("discount").value) || 0;

    if (isNaN(amountPaid) || amountPaid < totalAmount) {
        alert("Insufficient payment. Please ensure the amount paid is correct.");
        return;
    }

    let changeAmount = parseFloat(document.getElementById("change").innerText);

    try {
        const data = {
            cart: cart,
            totalAmount: totalAmount,
            amountPaid: amountPaid,
            change: changeAmount,
            discount: discount
        };

        console.log('Sending data:', data); // Log the data being sent

        const response = await fetch("save_transactions.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(data)
        });

        const text = await response.text();
        console.log(text); // Log the response text
        let result;
        try {
            result = JSON.parse(text);
        } catch (e) {
            console.error('Server response is not JSON:', text);
            throw new Error("Failed to save transaction. Server response is not JSON.");
        }

        if (!response.ok || result.error) {
            throw new Error(result.error || "Failed to save transaction.");
        }

        // Show success alert
        alert("Payment processed successfully! Transaction ID: " + result.transaction_id);

        // Reset the cart and UI
        resetSystem();

    } catch (error) {
        console.error('Transaction Error:', error);
        alert(error.message);
    }
}


    // Reset UI and cart
    function resetSystem() {
        cart = [];

        // Clear the order table and reset amounts
        document.getElementById("orderTable").innerHTML = "";
        document.getElementById("totalAmount").innerText = "0.00";
        document.getElementById("payableAmount").innerText = "0.00";
        document.getElementById("change").innerText = "0.00";

        // Reset input fields
        document.getElementById("product_id").value = "";
        document.getElementById("quantity").value = "1";
        document.getElementById("paidAmount").value = "0";
        document.getElementById("discount").value = "0";
    }
</script>
</body>
</html>
