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
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
       
    </style>
</head>
<body>
    <div class="back-button">
        <form method="POST" action="dashboard.php">
            <button type="submit">Back to Dashboard</button>
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
