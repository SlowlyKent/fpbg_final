let products = {
    "4904550592571": { name: "Flash Disk 8GB", price: 50.00 },
    "1234567890123": { name: "Anti-Rust Pan", price: 145.00 }
};

let cart = [];

function addProduct() {
    const barcode = document.getElementById("barcode").value.trim();
    const quantity = parseInt(document.getElementById("quantity").value);

    if (!barcode || !products[barcode]) {
        alert("Product not found!");
        return;
    }

    if (isNaN(quantity) || quantity <= 0) {
        alert("Quantity must be at least 1");
        return;
    }

    const existingProductIndex = cart.findIndex(item => item.barcode === barcode);

    if (existingProductIndex !== -1) {
        cart[existingProductIndex].quantity += quantity;
    } else {
        cart.push({ ...products[barcode], quantity, barcode });
    }

    updateTable();
    document.getElementById("barcode").value = "";
    document.getElementById("quantity").value = "1";
    document.getElementById("barcode").focus();
}

function updateTable() {
    const table = document.getElementById("orderTable");
    table.innerHTML = "";
    let total = 0;

    cart.forEach((item, index) => {
        const row = `
            <tr>
                <td>${index + 1}</td>
                <td>${item.name}</td>
                <td>₱${item.price.toFixed(2)}</td>
                <td>${item.quantity}</td>
                <td>₱${(item.price * item.quantity).toFixed(2)}</td>
                <td><button onclick="removeItem(${index})">Remove</button></td>
            </tr>
        `;
        table.innerHTML += row;
        total += item.price * item.quantity;
    });

    document.getElementById("totalAmount").innerText = total.toFixed(2);
    updateTotal();
}

function removeItem(index) {
    cart.splice(index, 1);
    updateTable();
}

function updateTotal() {
    const discount = parseFloat(document.getElementById("discount").value) || 0;
    const total = parseFloat(document.getElementById("totalAmount").innerText) || 0;
    const discountedTotal = total - (total * (discount / 100));
    document.getElementById("payableAmount").innerText = discountedTotal.toFixed(2);
    updateChange();
}

function updateChange() {
    const total = parseFloat(document.getElementById("payableAmount").innerText) || 0;
    const paid = parseFloat(document.getElementById("paidAmount").value) || 0;
    const change = paid - total;
    document.getElementById("change").innerText = (change >= 0 ? change : 0).toFixed(2);
}

function processPayment() {
    const change = document.getElementById("change").innerText;
    const payable = document.getElementById("payableAmount").innerText;

    if (parseFloat(change) < 0 || payable === "0.00") {
        alert("Payment not complete or no items added!");
        return;
    }

    alert(`Payment Successful! Change: ₱${change}`);
    resetSystem();
}

function resetSystem() {
    cart = [];
    document.getElementById("barcode").value = "";
    document.getElementById("quantity").value = "1";
    document.getElementById("orderTable").innerHTML = "";
    document.getElementById("totalAmount").innerText = "0.00";
    document.getElementById("discount").value = "0";
    document.getElementById("paidAmount").value = "0";
    document.getElementById("payableAmount").innerText = "0.00";
    document.getElementById("change").innerText = "0.00";
}
