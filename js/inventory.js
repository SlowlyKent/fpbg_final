document.addEventListener('DOMContentLoaded', function() {
    // Initialize edit buttons
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            editProduct(productId);
        });
    });

    // Initialize delete buttons
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            deleteProduct(productId);
        });
    });
});

function editProduct(productId) {
    // First fetch the product data
    fetch('inventory_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'get_product',
            product_id: productId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showEditModal(data.product);
        } else {
            alert('Error fetching product details: ' + data.error);
        }
    })
    .catch(error => console.error('Error:', error));
}

function showEditModal(product) {
    // Create modal HTML
    const modalHtml = `
        <div class="modal-overlay" id="editModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Edit Product</h2>
                    <button class="close-modal">&times;</button>
                </div>
                <form id="editProductForm">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="product_name">
                                <i class="fas fa-box"></i> Product Name
                            </label>
                            <input type="text" id="product_name" name="product_name" value="${product.product_name}" required>
                        </div>
                        <div class="form-group">
                            <label for="brand">
                                <i class="fas fa-tag"></i> Brand
                            </label>
                            <input type="text" id="brand" name="brand" value="${product.brand}" required>
                        </div>
                        <div class="form-group">
                            <label for="stock_quantity">
                                <i class="fas fa-cubes"></i> Stock Quantity
                            </label>
                            <input type="number" id="stock_quantity" name="stock_quantity" value="${product.stock_quantity}" required>
                        </div>
                        <div class="form-group">
                            <label for="unit_of_measure">
                                <i class="fas fa-ruler"></i> Unit of Measure
                            </label>
                            <input type="text" id="unit_of_measure" name="unit_of_measure" value="${product.unit_of_measure}" required>
                        </div>
                        <div class="form-group">
                            <label for="category">
                                <i class="fas fa-folder"></i> Category
                            </label>
                            <input type="text" id="category" name="category" value="${product.category}" required>
                        </div>
                        <div class="form-group">
                            <label for="cost_price">
                                <i class="fas fa-tags"></i> Cost Price
                            </label>
                            <input type="number" step="0.01" id="cost_price" name="cost_price" value="${product.cost_price}" required>
                        </div>
                        <div class="form-group">
                            <label for="selling_price">
                                <i class="fas fa-money-bill-wave"></i> Selling Price
                            </label>
                            <input type="number" step="0.01" id="selling_price" name="selling_price" value="${product.selling_price}" required>
                        </div>
                        <div class="form-group">
                            <label for="expiration_date">
                                <i class="fas fa-calendar"></i> Expiration Date
                            </label>
                            <input type="date" id="expiration_date" name="expiration_date" value="${product.expiration_date}" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="save-btn">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                        <button type="button" class="cancel-btn">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    `;

    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalHtml);

    // Add event listeners
    const modal = document.getElementById('editModal');
    const form = document.getElementById('editProductForm');

    // Close modal on clicking close button or outside
    document.querySelector('.close-modal').addEventListener('click', () => {
        modal.remove();
    });

    document.querySelector('.cancel-btn').addEventListener('click', () => {
        modal.remove();
    });

    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.remove();
        }
    });

    // Handle form submission
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        
        const formData = {
            product_name: form.product_name.value,
            brand: form.brand.value,
            stock_quantity: parseInt(form.stock_quantity.value),
            unit_of_measure: form.unit_of_measure.value,
            category: form.category.value,
            cost_price: parseFloat(form.cost_price.value),
            selling_price: parseFloat(form.selling_price.value),
            expiration_date: form.expiration_date.value
        };

        updateProduct(product.product_id, formData);
    });
}

function updateProduct(productId, productData) {
    fetch('inventory_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'update',
            product_id: productId,
            product_data: productData
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal and refresh page
            document.getElementById('editModal').remove();
            window.location.reload();
        } else {
            alert('Error updating product: ' + data.error);
        }
    })
    .catch(error => console.error('Error:', error));
}

function deleteProduct(productId) {
    if (!confirm('Are you sure you want to delete this product?')) {
        return;
    }

    fetch('inventory_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'delete',
            product_id: productId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert('Error deleting product: ' + data.error);
        }
    })
    .catch(error => console.error('Error:', error));
} 