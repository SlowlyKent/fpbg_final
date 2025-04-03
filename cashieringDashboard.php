<?php
session_start();
include 'connect.php'; // Database connection

// Check if the user is logged in and has 'Staff' role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Staff') {
    header("Location: index.php"); // Redirect to login if not Staff
    exit();
}

// Fetch products from the database
$products = $conn->query("SELECT * FROM products");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashiering Dashboard</title>
    <link rel="stylesheet" href="styles.css"> <!-- Add your styles here -->
</head>
<body>
    <h1>Cashiering Dashboard</h1>
    <p>Welcome, <?php echo $_SESSION['username']; ?> (Staff)</p>
    
    <h2>Product List</h2>
    <table border="1">
        <tr>
            <th>Product Name</th>
            <th>Price</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $products->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['product_name']; ?></td>
            <td><?php echo number_format($row['price'], 2); ?></td>
            <td>
                <form action="process_sale.php" method="POST">
                    <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                    <input type="number" name="quantity" value="1" min="1" required>
                    <button type="submit">Add to Sale</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    
    <br>
    <a href="logout.php">Logout</a>
</body>
</html>
