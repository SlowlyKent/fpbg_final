let products = {
    "4904550592571": { name: "Flash Disk 8GB", price: 50.00 },
    "1234567890123": { name: "Anti-Rust Pan", price: 145.00 }
};
let cart = [];

function addProduct() {
    var barcode = document.getElementById("barcode").value;
    var quantity = parseInt(document.getElementById("quantity").value);

    if (barcode === ''|| quantity <= 0){
        alert('Please enter valid barcode and quantity.');
        return;
    }

     var xhr = new XMLHttpRequest();
    xhr.open('POST', 'fetch-product.php', true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.onload = function () {
        if (this.status == 200) {
            var product = JSON.parse(this.responseText);

            if (product) {
                addProductToTable(product, quantity);
                updateTotal();
            } else {
                alert('Product not found!');
            }
        }
    };
    xhr.send('barcode=' + barcode);
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
    var orders = []; 

    var discount = document.getElementById('discount').value;
    var paidAmount = document.getElementById('paidAmount').value;
    var totalAmount = document.getElementById('payableAmount').textContent;

    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'process-payment.php', true);
    xhr.setRequestHeader('Content-type', 'application/json');
    xhr.onload = function () {
        if (this.status == 200) {
            var response = JSON.parse(this.responseText);
            if (response.status == 'success') {
                alert('Payment successful!');
                resetSystem();
            } else {
                alert('Payment failed.');
            }
        }
    };
    xhr.send(JSON.stringify({
        orders: orders,
        discount: discount,
        paidAmount: paidAmount,
        totalAmount: totalAmount
    }));
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