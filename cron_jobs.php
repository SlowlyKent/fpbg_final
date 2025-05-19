<?php
// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include necessary files
require_once('connect.php');
require_once('check_expiration.php');

// Log function for debugging
function logMessage($message) {
    $logFile = 'cron_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

try {
    // Start session with admin user context
    session_start();
    $_SESSION['user_id'] = 1; // Assuming admin user has ID 1
    
    // Log start of cron job
    logMessage("Starting daily expiration check...");
    
    // Check expiration dates
    if (checkExpirationDates($conn)) {
        logMessage("Expiration check completed successfully.");
    } else {
        logMessage("Error during expiration check.");
    }
    
    // Clean up old notifications (optional, keeping last 30 days)
    $cleanup_stmt = $conn->prepare("
        DELETE FROM notifications 
        WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    
    if ($cleanup_stmt->execute()) {
        logMessage("Old notifications cleaned up successfully.");
    } else {
        logMessage("Error cleaning up old notifications.");
    }
    
} catch (Exception $e) {
    logMessage("Error: " . $e->getMessage());
} finally {
    // Close database connection
    if (isset($conn)) {
        $conn->close();
    }
    logMessage("Cron job completed.");
}
?> 