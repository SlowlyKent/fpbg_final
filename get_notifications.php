<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

include('connect.php');

// Get unread notification count
$count_stmt = $conn->prepare("
    SELECT COUNT(*) as unread_count 
    FROM notifications 
    WHERE user_id = ? AND is_read = 0
");
$count_stmt->bind_param("i", $_SESSION['user_id']);
$count_stmt->execute();
$unread_count = $count_stmt->get_result()->fetch_assoc()['unread_count'];

// Get recent notifications (limit to 10)
$notif_stmt = $conn->prepare("
    SELECT id, message, type, is_read, created_at 
    FROM notifications 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 10
");
$notif_stmt->bind_param("i", $_SESSION['user_id']);
$notif_stmt->execute();
$result = $notif_stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    // Format the date
    $created_at = new DateTime($row['created_at']);
    $row['created_at'] = $created_at->format('M d, Y H:i');
    $notifications[] = $row;
}

// Close the database connection
$conn->close();

// Return JSON response
header('Content-Type: application/json');
echo json_encode([
    'unread_count' => $unread_count,
    'notifications' => $notifications
]);
?>
