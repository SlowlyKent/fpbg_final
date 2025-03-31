<?php
include 'connect.php'; // Ensure database connection

$sql = "SELECT * FROM inventory";
$result = $conn->query($sql);
?>

<h2>Inventory Management</h2>
<table border="1">
    <tr>
        <th>Product ID</th>
        <th>Product Name</th>
        <th>Stock Quantity</th>
        <th>Unit of Measure</th>
        <th>Category</th>
        <th>Cost Price</th>
        <th>Selling Price</th>
        <th>Stock Status</th>
        <th>Expiration Date</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?php echo $row['product_id']; ?></td>
            <td><?php echo $row['product_name']; ?></td>
            <td><?php echo $row['stock_quantity']; ?></td>
            <td><?php echo $row['unit_of_measure']; ?></td>
            <td><?php echo $row['category']; ?></td>
            <td>₱<?php echo number_format($row['cost_price'], 2); ?></td>
            <td>₱<?php echo number_format($row['selling_price'], 2); ?></td>
            <td><?php echo $row['stock_status']; ?></td>
            <td><?php echo $row['expiration_date'] ? $row['expiration_date'] : 'N/A'; ?></td>
        </tr>
    <?php } ?>
</table>
