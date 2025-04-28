let orders= []

let cart = [];

function addProduct() {
    let barcode = document.getElementById("barcode").value;
    let quantity = parseInt(document.getElementById("quantity").value);

    if (!products[barcode]) {
        alert("Product not found!");
        return;
    }

    if (quantity <= 0) {
        alert("Quantity must be at least 1");
        return;
    }

    let productIndex = cart.findIndex(item => item.barcode === barcode);

    if (productIndex !== -1) {
        cart[productIndex].quantity += quantity;
    } else {
        cart.push({ ...products[barcode], quantity, barcode });
    }

    updateTable();
    document.getElementById("barcode").value = "";
    document.getElementById("quantity").value = "1";
}

function updateTable() {
    let table = document.getElementById("orderTable");
    table.innerHTML = "";
    let total = 0;
    
    cart.forEach((item, index) => {
        let row = `<tr>
            <td>${index + 1}</td>
            <td>${item.name}</td>
            <td>$${item.price.toFixed(2)}</td>
            <td>${item.quantity}</td>
            <td>$${(item.price * item.quantity).toFixed(2)}</td>
            <td><button onclick="removeItem(${index})">Remove</button></td>
        </tr>`;
        total += item.price * item.quantity;
        table.innerHTML += row;
    });

    document.getElementById("totalAmount").innerText = total.toFixed(2);
    updateTotal();
}

function removeItem(index) {
    cart.splice(index, 1);
    updateTable();
}

function updateTotal() {
    let discount = parseFloat(document.getElementById("discount").value);
    let total = parseFloat(document.getElementById("totalAmount").innerText);
    let discountedTotal = total - (total * (discount / 100));
    document.getElementById("payableAmount").innerText = discountedTotal.toFixed(2);
}

function updateChange() {
    let total = parseFloat(document.getElementById("payableAmount").innerText);
    let paid = parseFloat(document.getElementById("paidAmount").value);
    let change = paid - total;
    document.getElementById("change").innerText = change.toFixed(2);
}

function processPayment() {
    alert("Payment Successful! Change: $" + document.getElementById("change").innerText);
    resetSystem();
}

function resetSystem() {
    cart = [];
    document.getElementById("orderTable").innerHTML = "";
    document.getElementById("totalAmount").innerText = "0.00";
    document.getElementById("discount").value = "0";
    document.getElementById("paidAmount").value = "0";
    document.getElementById("change").innerText = "0.00";
    document.getElementById("payableAmount").innerText = "0.00";
}