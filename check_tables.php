<?php
include('connect.php');

try {
    // Tables to check
    $tables = ['stock_transactions', 'notifications'];

    foreach ($tables as $table) {
        $result = $conn->query("DESCRIBE $table");
        if ($result) {
            echo "\n$table table structure:\n";
            echo "------------------------\n";
            while ($row = $result->fetch_assoc()) {
                echo "{$row['Field']} - {$row['Type']} - {$row['Default']}\n";
            }
        } else {
            echo "Error getting table structure for $table: " . $conn->error . "\n";
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

$conn->close();
?> 