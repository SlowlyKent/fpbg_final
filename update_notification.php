<?php
session_start();
include('connect.php');

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['notification_id'])) {
    echo json_encode(['error' => 'Notification ID required']);
    exit();
}

$notification_id = $data['notification_id'];
$user_id = $_SESSION['user_id'];

// Update notification to mark as read
$sql = "UPDATE notifications SET is_read = 1 
        WHERE id = ? AND user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $notification_id, $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Failed to update notification']);
}
?>
