<?php
include('connect.php');

try {
    // Drop and recreate tables
    $queries = [
        // Drop existing tables
        "DROP TABLE IF EXISTS stock_transactions",
        "DROP TABLE IF EXISTS notifications",
        
        // Create stock_transactions table
        "CREATE TABLE stock_transactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            transaction_id VARCHAR(50) NOT NULL,
            product_id VARCHAR(50) NOT NULL,
            quantity DECIMAL(10,2) NOT NULL,
            transaction_type ENUM('stock_in', 'stock_out') NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (transaction_id) REFERENCES transactions(transaction_id)
        )",

        // Create notifications table
        "CREATE TABLE notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            message TEXT NOT NULL,
            type VARCHAR(20) NOT NULL,
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )"
    ];

    foreach ($queries as $query) {
        if (!$conn->query($query)) {
            throw new Exception("Error executing query: " . $conn->error . "\n" . $query);
        }
    }

    echo "Tables recreated successfully!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

$conn->close();
?> 