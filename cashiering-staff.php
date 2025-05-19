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
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: #f4f4f4;
        }

        .back-button {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
        }

        .back-button button {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
        }

        .main-container {
            display: grid;
            grid-template-columns: 1fr 3fr;
            gap: 20px;
            padding: 60px 20px 20px;
            height: calc(100vh - 80px);
        }

        .left-panel {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .center-panel {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .right-panel {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            position: static;
            width: auto;
        }

        .header {
            margin-bottom: 20px;
        }

        .fpbg {
            font-size: 24px;
            font-weight: bold;
            color: #003366;
        }

        .stock {
            font-size: 20px;
            color: #003366;
        }

        .cashiering {
            font-size: 16px;
            color: #003366;
        }

        .total {
            font-size: 18px;
            color: #003366;
            margin-bottom: 20px;
        }

        .input-group {
            margin-bottom: 15px;
        }

        .input-group label {
            display: block;
            margin-bottom: 5px;
            color: #003366;
        }

        .input-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .add-button {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px;
            width: 100%;
            border-radius: 4px;
            cursor: pointer;
        }

        .order-header {
            margin-bottom: 20px;
        }

        .order-list-container {
            flex: 1;
            overflow-y: auto;
            margin-bottom: 20px;
        }

        .order-list {
            width: 100%;
            border-collapse: collapse;
        }

        .order-list thead {
            position: sticky;
            top: 0;
            background: white;
            z-index: 1;
        }

        .order-list th {
            background-color: #f8f9fa;
            color: #003366;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #dee2e6;
        }

        .order-list td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
        }

        .order-list tbody tr:hover {
            background-color: #f5f5f5;
        }

        .payment-info {
            margin-top: 0;
        }

        .payment-row {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            margin-bottom: 8px;
            text-align: right;
        }

        .payment-row label {
            color: #003366;
            font-size: 14px;
            margin-right: 10px;
        }

        .payment-row input {
            width: 80px;
            padding: 4px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .total-section {
            margin: 10px 0;
            padding: 8px;
            background: #f8f9fa;
            border-radius: 4px;
            text-align: left;
        }

        .total-row {
            margin: 8px 0;
            font-size: 18px;
            font-weight: bold;
            color: #003366;
        }

        #payableAmount,
        #change {
            font-size: 20px;
            color: #003366;
            margin-left: 5px;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            margin-top: 10px;
            justify-content: flex-end;
        }

        .action-buttons button {
            flex: 1;
            padding: 8px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            color: white;
            font-size: 14px;
        }

        .pay-button {
            background: #28a745;
        }

        .reset-button {
            background: #dc3545;
        }

        .remove-button {
            background: #007bff;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .remove-button:hover {
            background: #0056b3;
        }

        .payment-inputs {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 20px;
            text-align: left;
        }

        .payment-inputs label {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #003366;
        }

        .payment-inputs input {
            width: 150px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .payment-info {
            margin-top: 0;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        /* Updated Notification Styles */
        .notification-dropdown {
            position: absolute;
            right: 0;
            top: 100%;
            width: 350px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            overflow: hidden;
        }

        .notification-dropdown h4 {
            margin: 0;
            padding: 15px;
            color: #003366;
            border-bottom: 1px solid #eee;
            font-size: 16px;
        }

        #notifList {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .notification-item {
            padding: 15px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            position: relative;
        }

        .notification-content {
            color: #333;
            margin-bottom: 5px;
            line-height: 1.4;
        }

        .notification-timestamp {
            font-size: 12px;
            color: #666;
        }

        /* Notification Types */
        .notification-danger {
            background-color: #ffebee;
        }

        .notification-warning {
            background-color: #fff8e1;
        }

        .notification-success {
            background-color: #e8f5e9;
        }

        /* Checkmark styles */
        .notification-checkmark {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #4CAF50;
            display: none;
        }

        .notification-item.read .notification-checkmark {
            display: block;
        }

        .payment-section {
            margin-top: auto;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }

        .payment-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .payment-input-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .payment-input-group label {
            color: #003366;
            font-weight: 500;
        }

        .payment-input-group input {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        .totals-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .total-item {
            background: white;
            padding: 15px;
            border-radius: 4px;
            text-align: center;
        }

        .total-label {
            color: #003366;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .total-value {
            font-size: 20px;
            font-weight: 600;
            color: #003366;
        }

        .action-button {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            color: white;
            transition: background-color 0.3s;
        }

        .pay-button:hover {
            background: #218838;
        }

        .reset-button:hover {
            background: #c82333;
        }
    </style>
</head>
<body>
    <div class="back-button">
        <form method="POST" action="logout.php">
            <button type="submit">Log out</button>
        </form>
    </div>

    <div class="main-container">
        <div class="left-panel">
            <div class="header">
                <div class="fpbg">FPBG</div>
                <div class="stock">STOCK</div>
                <div class="cashiering">CASHIERING SYSTEM</div>
            </div>
            <div class="total">Total: ₱<span id="totalAmount">0.00</span></div>
            
            <div class="input-group">
                <label>Product ID / Product Code</label>
                <input type="text" id="product_id" placeholder="Enter the Product ID">
            </div>
            
            <div class="input-group">
                <label>Quantity:</label>
                <input type="number" id="quantity" value="1" min="1">
            </div>
            
            <div class="input-group">
                <label>Kilogram (kg):</label>
                <input type="number" id="kilogram" value="1" min="0.01" step="0.01">
            </div>
            
            <button class="add-button" onclick="addProduct()">Add</button>
        </div>

        <div class="center-panel">
            <div class="order-header">
                <h3>Order List</h3>
            </div>

            <div class="order-list-container">
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
            </div>

            <div class="payment-section">
                <div class="payment-grid">
                    <div class="payment-input-group">
                        <label>Amount Paid:</label>
                        <input type="number" id="paidAmount" value="0" onchange="updateChange()">
                    </div>
                    <div class="payment-input-group">
                        <label>Discount (%):</label>
                        <input type="number" id="discount" value="0" onchange="updateTotal()">
                    </div>
                </div>

                <div class="totals-grid">
                    <div class="total-item">
                        <div class="total-label">Total Payable</div>
                        <div class="total-value">₱<span id="payableAmount">0.00</span></div>
                    </div>
                    <div class="total-item">
                        <div class="total-label">Amount Paid</div>
                        <div class="total-value">₱<span id="paidAmountDisplay">0.00</span></div>
                    </div>
                    <div class="total-item">
                        <div class="total-label">Change</div>
                        <div class="total-value">₱<span id="change">0.00</span></div>
                    </div>
                </div>

                <div class="action-buttons">
                    <button class="action-button pay-button" onclick="processPayment()">Pay</button>
                    <button class="action-button reset-button" onclick="resetSystem()">Reset</button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'notification_ui.php'; ?>

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
        let kilogram = parseFloat(document.getElementById("kilogram")?.value) || 1;

        // Validate the quantity
        if (quantity < 1) {
            alert("Quantity must be at least 1");
            return;
        }
        if (kilogram <= 0) {
            alert("Kilogram must be at least 0.01");
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

            // Calculate effective quantity for stock check
            const effectiveQuantity = quantity * kilogram;

            // Check if we're ordering more than available
            if (effectiveQuantity > product.available_quantity) {
                alert(`Only ${product.available_quantity} units available for this product!`);
                return;
            }

            let productIndex = cart.findIndex(item => item.product_id === product_id);

            if (productIndex !== -1) {
                // Check if new total effective quantity exceeds available stock
                const newTotalEffectiveQuantity = cart[productIndex].quantity * cart[productIndex].kilogram + effectiveQuantity;
                if (newTotalEffectiveQuantity > product.available_quantity) {
                    alert(`Cannot add more. Only ${product.available_quantity} units available for this product!`);
                    return;
                }

                cart[productIndex].quantity += quantity;
                cart[productIndex].kilogram += kilogram;
            } else {
                cart.push({
                    ...product,
                    quantity,
                    kilogram,
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
        document.getElementById("kilogram").value = "1";
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
                <td>${item.quantity} x ${item.kilogram.toFixed(2)}</td>
                <td>₱${(price * item.quantity * item.kilogram).toFixed(2)}</td>
                <td><button class="remove-button" onclick="removeItem(${index})">Remove</button></td>
            </tr>`;
            total += price * item.quantity * item.kilogram;
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
        let paidAmountDisplayField = document.getElementById("paidAmountDisplay");

        if (!payableAmountField || !amountPaidField || !paidAmountDisplayField) {
            console.error("Error: Required elements missing from DOM.");
            return;
        }

        let total = parseFloat(payableAmountField.innerText) || 0;
        let amountPaid = parseFloat(amountPaidField.value) || 0;

        // Update the display of amount paid
        paidAmountDisplayField.innerText = amountPaid.toFixed(2);

        if (amountPaid < total) {
            document.getElementById("change").innerText = "0.00";
            return;
        }

        document.getElementById("change").innerText = (amountPaid - total).toFixed(2);
    }

    // Process payment
    async function processPayment() {
        if (cart.length === 0) {
            alert("Your cart is empty. Please add items before processing payment.");
            return;
        }

        let totalAmountElement = document.getElementById("payableAmount");
        let amountPaidElement = document.getElementById("paidAmount");
        let discountElement = document.getElementById("discount");

        console.log("Total Amount Element:", totalAmountElement);
        console.log("Amount Paid Element:", amountPaidElement);
        console.log("Discount Element:", discountElement);

        if (!totalAmountElement || !amountPaidElement || !discountElement) {
            console.error("Error: Required elements missing from DOM.");
            return;
        }

        let totalAmount = parseFloat(totalAmountElement.innerText);
        let amountPaid = parseFloat(amountPaidElement.value);
        let discount = parseFloat(discountElement.value) || 0;

        if (isNaN(amountPaid) || amountPaid < totalAmount) {
            alert("Insufficient payment. Please ensure the amount paid is correct.");
            return;
        }

        let changeAmountElement = document.getElementById("change");
        console.log("Change Amount Element:", changeAmountElement);

        if (!changeAmountElement) {
            console.error("Error: Change element missing from DOM.");
            return;
        }

        let changeAmount = parseFloat(changeAmountElement.innerText);

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

            // Dispatch transaction completed notification
            var event = new CustomEvent('newNotification', { detail: 'Transaction completed' });
            document.dispatchEvent(event);

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
        document.getElementById("kilogram").value = "1";
        document.getElementById("paidAmount").value = "0";
        document.getElementById("discount").value = "0";
    }

    function getNotificationTypeClass(message) {
        const lowerMessage = message.toLowerCase();
        if (lowerMessage.includes('out of stock')) {
            return 'notification-danger';
        } else if (lowerMessage.includes('running low') || lowerMessage.includes('warning')) {
            return 'notification-warning';
        } else if (lowerMessage.includes('new product added')) {
            return 'notification-success';
        }
        return '';
    }

    // Update loadNotifications function
    function loadNotifications() {
        fetch('get_notifications.php')
            .then(response => response.json())
            .then(data => {
                const notifList = document.getElementById('notifList');
                const notifBadge = document.getElementById('notifBadge');
                notifList.innerHTML = '';

                if (!data || data.length === 0) {
                    notifList.innerHTML = '<li class="notification-item"><div class="notification-content">No new notifications</div></li>';
                    notifBadge.style.display = 'none';
                    return;
                }

                notifBadge.style.display = 'flex';
                notifBadge.textContent = data.length;

                data.forEach(notif => {
                    const typeClass = getNotificationTypeClass(notif.message);
                    const date = new Date(notif.created_at);
                    const formattedDate = date.toLocaleDateString('en-US', { 
                        month: 'numeric', 
                        day: 'numeric', 
                        year: 'numeric'
                    });
                    const formattedTime = date.toLocaleTimeString('en-US', { 
                        hour: '2-digit', 
                        minute: '2-digit',
                        hour12: true 
                    });
                    
                    const li = document.createElement('li');
                    li.className = `notification-item ${typeClass}`;
                    li.innerHTML = `
                        <div class="notification-content">${notif.message}</div>
                        <div class="notification-timestamp">${formattedDate}, ${formattedTime}</div>
                        <i class="fas fa-check notification-checkmark"></i>
                    `;
                    
                    li.onclick = () => {
                        updateNotification(notif.id);
                        li.classList.add('read');
                    };
                    notifList.appendChild(li);
                });
            })
            .catch(err => {
                console.error('Error fetching notifications:', err);
                const notifList = document.getElementById('notifList');
                notifList.innerHTML = '<li class="notification-item notification-danger"><div class="notification-content">Error loading notifications</div></li>';
            });
    }
    </script>
</body>
</html>
