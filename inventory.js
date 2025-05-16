document.addEventListener("DOMContentLoaded", function () {
    document.getElementById('notificationContainer').style.display = 'flex';
});

function toggleNotifications() {
    var dropdown = document.getElementById("notifDropdown");
    dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
}

document.addEventListener("click", function (event) {
    var dropdown = document.getElementById("notifDropdown");
    var icon = document.querySelector(".notification-icon");
    if (dropdown && icon && !dropdown.contains(event.target) && !icon.contains(event.target)) {
        dropdown.style.display = "none";
    }
});

function editProduct(productId) {
    // Redirect to an edit page or show a modal for editing
    window.location.href = `edit_product.php?id=${productId}`;
}

function deleteProduct(productId) {
    if (confirm("Are you sure you want to delete this product?")) {
        fetch('delete_product.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `product_id=${productId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Product deleted successfully");
                window.location.reload(); // Refresh the page to reflect changes
            } else {
                alert("Error deleting product: " + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
}

