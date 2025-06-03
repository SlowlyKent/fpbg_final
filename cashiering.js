// Initialize cart array
    let cart = [];

// Load products when page loads
    document.addEventListener('DOMContentLoaded', function () {
    loadProducts();

        document.getElementById('product_id')?.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') addProduct();
    });
    document.getElementById('quantity')?.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') addProduct();
    });
    document.getElementById('paidAmount')?.addEventListener("input", updateChange);
    document.getElementById('discount')?.addEventListener("input", updateTotal);
});

// Fetch and populate product dropdown
async function loadProducts() {
    try {
        const response = await fetch('get_all_products.php');
        const products = await response.json();
        const select = document.getElementById('product_id');
        select.innerHTML = '<option value="">Select a product...</option>';
        products.forEach(product => {
            const option = document.createElement('option');
            option.value = product.id;
            option.setAttribute('data-unit', product.unit_of_measure);
            option.textContent = `${product.name} (${product.brand}) - ${product.unit_of_measure}`;
            select.appendChild(option);
        });
    } catch (error) {
        console.error('Error loading products:', error);
    }
}

// Add product to cart
async function addProduct() {
    let product_id = document.getElementById("product_id")?.value;
    if (!product_id || !product_id.trim()) {
        alert("Please select a product.");
        return;
    }
    let quantity = parseInt(document.getElementById("quantity")?.value) || 1;
    let kilogram = 1; // Default value
    const kilogramInput = document.getElementById("kilogram");

    try {
        const response = await fetch("get_product.php?product_id=" + encodeURIComponent(product_id));
        if (!response.ok) throw new Error('Failed to fetch product');
        const product = await response.json();
        if (product.error) {
            alert(product.error);
            return;
        }

        console.log("Fetched product:", product);
        console.log("Fetched product unit:", product.unit_of_measure);

        // Only process kilogram for kg/g products
        if (product.unit_of_measure === 'kg' || product.unit_of_measure === 'g') {
            if (kilogramInput) {
                let inputKilogram = kilogramInput.value;
                if (!inputKilogram) {
                    alert("Please enter the kilogram value for this product.");
                    return;
                }
                let floatValue = parseFloat(inputKilogram);
                if (product.unit_of_measure === 'g') {
                    if (floatValue < 1) {
                        // User entered grams as a decimal (e.g., 0.567 for 567g)
                        kilogram = floatValue * 1000; // 0.567 * 1000 = 567g
                    } else {
                        // User entered kilograms
                        kilogram = floatValue * 1000; // 1.5 * 1000 = 1500g
                    }
                } else if (product.unit_of_measure === 'kg') {
                    // For kg products, keep the exact value entered without any conversion
                    kilogram = floatValue;
                }
            }
        }

        // Validate the quantity
        if (quantity < 1) {
            alert("Quantity must be at least 1");
            return;
        }

        // Calculate effective quantity for stock check
        let effectiveQuantity;
        if (product.unit_of_measure === 'g') {
            // For grams, keep the gram value
            effectiveQuantity = quantity * kilogram;
        } else if (product.unit_of_measure === 'kg') {
            // For kilograms, use the exact value entered
            effectiveQuantity = kilogram;
        } else {
            effectiveQuantity = quantity;
        }

        // Convert available quantity to match the unit being used
        let availableQuantity;
        if (product.unit_of_measure === 'g') {
            availableQuantity = product.available_quantity * 1000; // Convert kg to g
        } else {
            availableQuantity = product.available_quantity;
        }

        // Check if we're ordering more than available
        if (effectiveQuantity > availableQuantity) {
            if (product.unit_of_measure === 'g') {
                alert(`Only ${availableQuantity} ${product.unit_of_measure} available for this product!`);
            } else {
                alert(`Only ${availableQuantity} ${product.unit_of_measure} available for this product!`);
            }
            return;
        }

        let productIndex = cart.findIndex(item => item.product_id === product_id);

        if (productIndex !== -1) {
            // Check if new total effective quantity exceeds available stock
            const currentEffectiveQuantity = product.unit_of_measure === 'kg' || product.unit_of_measure === 'g'
                ? cart[productIndex].kilogram
                : cart[productIndex].quantity;
            const newTotalEffectiveQuantity = currentEffectiveQuantity + effectiveQuantity;
            
            if (newTotalEffectiveQuantity > product.available_quantity) {
                alert(`Cannot add more. Only ${product.available_quantity} ${product.unit_of_measure} available for this product!`);
                return;
            }

            cart[productIndex].quantity += quantity;
            if (product.unit_of_measure === 'kg' || product.unit_of_measure === 'g') {
                cart[productIndex].kilogram += kilogram;
            }
        } else {
            cart.push({
                ...product,
                quantity,
                kilogram: product.unit_of_measure === 'kg' || product.unit_of_measure === 'g' ? kilogram : 1,
                product_id: product_id,
                brand: product.brand || 'No Brand',
                unit_of_measure: product.unit_of_measure
            });
        }
        console.log("Cart item added:", cart[cart.length - 1]);
        console.log("Cart item added unit:", cart[cart.length - 1].unit_of_measure);
        updateTable();
    } catch (error) {
        console.error('API Error:', error);
        alert(error.message || 'An error occurred while adding product.');
    }
    document.getElementById("product_id").value = "";
    document.getElementById("quantity").value = "1";
    if (kilogramInput) kilogramInput.value = "1";
}

// Update the order table display
function updateTable() {
    let table = document.getElementById("orderTable");
    table.innerHTML = "";
    let total = 0;
    
    cart.forEach((item, index) => {
        console.log("Cart item in updateTable:", item);
        console.log(`Cart item ${index} in updateTable unit:`, item.unit_of_measure);
        let row = document.createElement("tr");
        
        // Format the quantity display based on unit
        let displayQuantity;
        if (item.unit_of_measure === 'kg') {
            // For kg, show the exact value entered
            displayQuantity = `${item.quantity} x ${item.kilogram.toFixed(3)}kg`;
        } else if (item.unit_of_measure === 'g') {
            // For grams, show the actual gram value
            displayQuantity = `${item.quantity} x ${item.kilogram}g`;
        } else {
            displayQuantity = `${item.quantity} ${item.unit_of_measure || ''}`;
        }
    
        // Calculate total price for this item
        let itemTotal;
        if (item.unit_of_measure === 'kg') {
            itemTotal = item.price * item.quantity * item.kilogram;
        } else if (item.unit_of_measure === 'g') {
            itemTotal = item.price * item.quantity * (item.kilogram/1000);
        } else {
            itemTotal = item.price * item.quantity;
        }
    
        row.innerHTML = `
            <td>${index + 1}</td>
            <td>${item.brand}</td>
            <td>${item.name}</td>
            <td>₱${parseFloat(item.price).toFixed(2)}</td>
            <td>${displayQuantity}</td>
            <td>₱${itemTotal.toFixed(2)}</td>
            <td>
                <button onclick="removeItem(${index})" class="remove-btn">Remove</button>
            </td>
        `;
        table.appendChild(row);
        total += itemTotal;
    });
    
    document.getElementById("totalAmount").textContent = total.toFixed(2);
    updateTotal();
}

// Remove item from cart
function removeItem(index) {
    cart.splice(index, 1);
    updateTable();
}

// Update total with discount
function updateTotal() {
    let total = parseFloat(document.getElementById("totalAmount")?.innerText) || 0;
    let discount = parseFloat(document.getElementById("discount")?.value) || 0;
    let discountAmount = (total * (discount / 100));
    let netSale = total - discountAmount;
    document.getElementById("payableAmount").innerText = netSale.toFixed(2);
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
        const response = await fetch("save_transactions.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(data)
        });
        const text = await response.text();
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
        alert("Payment processed successfully! Transaction ID: " + result.transaction_id);
        var event = new CustomEvent('newNotification', { detail: 'Transaction completed' });
        document.dispatchEvent(event);
        resetSystem();
    } catch (error) {
        console.error('Transaction Error:', error);
        alert(error.message);
    }
}

// Reset UI and cart
function resetSystem() {
    cart = [];
    document.getElementById("orderTable").innerHTML = "";
    document.getElementById("totalAmount").innerText = "0.00";
    document.getElementById("payableAmount").innerText = "0.00";
    document.getElementById("change").innerText = "0.00";
    document.getElementById("product_id").value = "";
    document.getElementById("quantity").value = "1";
    document.getElementById("kilogram").value = "1";
    document.getElementById("paidAmount").value = "0";
    document.getElementById("discount").value = "0";
}

// Product selection handler (if you have a dropdown with units)
document.getElementById('product_id')?.addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const unit = selectedOption.getAttribute('data-unit');
    const kilogramGroup = document.getElementById('kilogramGroup');
    if (unit === 'kg' || unit === 'g') {
        if (kilogramGroup) kilogramGroup.style.display = 'block';
    } else {
        if (kilogramGroup) kilogramGroup.style.display = 'none';
    }
});

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


