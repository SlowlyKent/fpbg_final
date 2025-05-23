<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Permission denied']);
    exit();
}

include('connect.php');

// Function to check expiration dates and create notifications
function checkExpirationDates($conn) {
    // Get current date
    $currentDate = date('Y-m-d');
    
    // First check if there are any products
    $check_products = $conn->prepare("SELECT COUNT(*) as total FROM products");
    if (!$check_products->execute()) {
        return ['success' => false, 'message' => 'Error checking products'];
    }
    $total_products = $check_products->get_result()->fetch_assoc()['total'];
    
    if ($total_products === 0) {
        return ['success' => true, 'message' => 'No products found in the system'];
    }
    
    // Check if there are any products with expiration dates
    $check_expirations = $conn->prepare("
        SELECT COUNT(*) as total 
        FROM products 
        WHERE expiration_date IS NOT NULL
    ");
    if (!$check_expirations->execute()) {
        return ['success' => false, 'message' => 'Error checking expiration dates'];
    }
    $total_with_expiration = $check_expirations->get_result()->fetch_assoc()['total'];
    
    if ($total_with_expiration === 0) {
        return ['success' => true, 'message' => 'No products with expiration dates found'];
    }
    
    // Get products that will expire within 30 days or have already expired
    $stmt = $conn->prepare("
        SELECT product_id, product_name, expiration_date 
        FROM products 
        WHERE expiration_date IS NOT NULL 
        AND expiration_date <= DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY)
        AND expiration_date >= CURRENT_DATE
    ");
    
    if (!$stmt->execute()) {
        return ['success' => false, 'message' => 'Error checking expirations'];
    }
    
    $result = $stmt->get_result();
    $notificationsCreated = false;
    
    while ($product = $result->fetch_assoc()) {
        $daysUntilExpiration = (strtotime($product['expiration_date']) - strtotime($currentDate)) / (60 * 60 * 24);
        
        // Create notification based on expiration timeline
        if ($daysUntilExpiration <= 7) {
            // Critical warning for products expiring within 7 days
            $message = "URGENT: {$product['product_name']} will expire in " . round($daysUntilExpiration) . " days!";
            $type = "danger";
        } else {
            // Warning for products expiring within 30 days
            $message = "WARNING: {$product['product_name']} will expire in " . round($daysUntilExpiration) . " days.";
            $type = "warning";
        }
        
        // Insert notification
        $notif_stmt = $conn->prepare("
            INSERT INTO notifications (user_id, message, type, is_read, created_at) 
            VALUES (?, ?, ?, 0, NOW())
        ");
        $notif_stmt->bind_param("iss", $_SESSION['user_id'], $message, $type);
        $notif_stmt->execute();
        $notificationsCreated = true;
    }
    
    // Check for expired products
    $expired_stmt = $conn->prepare("
        SELECT product_id, product_name, expiration_date 
        FROM products 
        WHERE expiration_date < CURRENT_DATE
    ");
    
    if (!$expired_stmt->execute()) {
        return ['success' => false, 'message' => 'Error checking expired products'];
    }
    
    $expired_result = $expired_stmt->get_result();
    
    while ($expired_product = $expired_result->fetch_assoc()) {
        // Create notification for expired products
        $message = "ALERT: {$expired_product['product_name']} has expired on {$expired_product['expiration_date']}!";
        $type = "danger";
        
        $notif_stmt = $conn->prepare("
            INSERT INTO notifications (user_id, message, type, is_read, created_at) 
            VALUES (?, ?, ?, 0, NOW())
        ");
        $notif_stmt->bind_param("iss", $_SESSION['user_id'], $message, $type);
        $notif_stmt->execute();
        $notificationsCreated = true;
    }
    
    if (!$notificationsCreated) {
        return ['success' => true, 'message' => 'No products are expiring soon or have expired'];
    }
    
    return ['success' => true, 'message' => 'New expiration notifications have been created'];
}

// Run the expiration check and return the result
$result = checkExpirationDates($conn);

// Close the database connection
$conn->close();

// Return JSON response
header('Content-Type: application/json');
echo json_encode($result);
?> 