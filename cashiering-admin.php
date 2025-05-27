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
    <script defer src="js/notifications.js"></script>
</head>
<body>
    <div class="notification-container" id="notificationContainer">
        <div class="notification-icon">
            <i class="fa-solid fa-bell"></i>
            <span class="notification-badge" id="notifBadge">0</span>
        </div>
        <div class="notification-dropdown" id="notifDropdown">
            <h4>Notifications</h4>
            <ul id="notifList">
                <!-- Notifications will be dynamically inserted here -->
            </ul>
        </div>
    </div>
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
                <select id="product_id" class="product-select">
                    <option value="">Select a product...</option>
                </select>
            </div>
            
            <div class="input-group">
                <label>Quantity:</label>
                <input type="number" id="quantity" value="1" min="1">
            </div>
            
            <div class="input-group" id="kilogramGroup" style="display: none;">
                <label>Kilogram (kg):</label>
                <input type="number" id="kilogram" value="1" min="0.01" step="0.01">
                <small class="input-help">Enter in grams (e.g., 0.90 for 90g) or kilograms (e.g., 1.5 for 1.5kg)</small>
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
    <script src="cashiering.js"></script>
</body>
</html>
