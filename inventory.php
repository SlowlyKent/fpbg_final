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
            </tr>
                <tr>
                    <td>01</td><td>Nuggets</td><td>Bingo</td><td>10</td><td>grams</td><td>Chicken</td><td>60</td><td>65</td>
                    <td><span class="status outofstock">Out of Stock</span></td><td>08/10/25</td>
                </tr>
                <tr>
                    <td>02</td><td>Tocino</td><td>Virginia</td><td>23</td><td>grams</td><td>Pork</td><td>110</td><td>120</td>
                    <td><span class="status normal">Normal</span></td><td>08/10/25</td>
                </tr>
                <tr>
            </tbody>
        </table>
    </main>
</div>

</body>
</html>
