<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

// Get JSON data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!isset($data['notification_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Notification ID not provided']);
    exit();
}

include('connect.php');

// Mark notification as read
$stmt = $conn->prepare("
    UPDATE notifications 
    SET is_read = 1 
    WHERE id = ? AND user_id = ?
");
$stmt->bind_param("ii", $data['notification_id'], $_SESSION['user_id']);
$success = $stmt->execute();

// Close the database connection
$conn->close();

// Return JSON response
header('Content-Type: application/json');
echo json_encode(['success' => $success]);
?> 