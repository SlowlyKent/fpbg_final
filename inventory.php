<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: permission-denied.php');
    exit();
}

$sql = "SELECT * FROM inventory";
$result = $conn->query($sql);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FPBG Stock Inventory</title>
    <link rel="stylesheet" href="inventory.css">
</head>
<body>

<div class="container">
    <aside class="sidebar">
        <h2>FPBG <span>STOCK</span></h2>
        <nav>
            <ul>
                <li><a href="#">Dashboard</a></li>
                <li class="active"><a href="#">Inventory</a></li>
                <li><a href="#">Stock Out</a></li>
                <li><a href="#">Stock In</a></li>
                <li><a href="#">Transaction</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <div class="top-bar">
            <input type="text" placeholder="Search...">
        </div>

        <table>
            <thead>
                <tr>
                    <th>Product ID</th>
                    <th>Product Name</th>
                    <th>Brand</th>
                    <th>Stock Quantity</th>
                    <th>Unit of Measure</th>
                    <th>Category</th>
                    <th>Cost Price</th>
                    <th>Selling Price</th>
                    <th>Stock Status</th>
                    <th>Expiration Date</th>
                </tr>
            </thead>
            <tbody>
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['product_id']); ?></td>
                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                <td><?php echo htmlspecialchars($row['brand']); ?></td>
                <td><?php echo htmlspecialchars($row['stock_quantity']); ?></td>
                <td><?php echo htmlspecialchars($row['unit_of_measure']); ?></td>
                <td><?php echo htmlspecialchars($row['category']); ?></td>
                <td><?php echo htmlspecialchars($row['cost_price']); ?></td>
                <td><?php echo htmlspecialchars($row['selling_price']); ?></td>
                <td><?php echo htmlspecialchars($row['stock_status']); ?></td>
                <td><?php echo htmlspecialchars($row['expiration_date']); ?></td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="10">No products found.</td></tr>
    <?php endif; ?>
</tbody>

        </table>
    </main>
</div>

</body>
</html>
