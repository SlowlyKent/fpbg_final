<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff')) {
    header('Location: index.php');
    exit();
}

$role = $_SESSION['role'];


if ($role !== 'admin' && $role !== 'staff') {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashiering</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h2>FPBG STOCK</h2>
    <ul>
        <?php if ($role === 'admin'): ?>
            <li><a href="dashboard.php">Dashboard</a></li>
        <?php endif; ?>
        <li><a href="cashiering.php">Cashiering</a></li>
    </ul>
    <a href="logout.php" class="logout-btn">Logout</a>
</div>

<!-- Main Content -->
<div class="main-content">
    <h1>Cashiering</h1>
    <form action="process_transaction.php" method="POST">
        <label for="product_id">Product ID:</label>
        <input type="text" id="product_id" name="product_id" required>
        
        <label for="quantity">Quantity:</label>
        <input type="number" id="quantity" name="quantity" required>
        
        <label for="payment">Payment Amount:</label>
        <input type="number" id="payment" name="payment" required>
        
        <button type="submit" class="transaction-btn">Process Transaction</button>
    </form>
</div>

</body>
</html>
