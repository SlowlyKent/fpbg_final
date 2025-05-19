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




