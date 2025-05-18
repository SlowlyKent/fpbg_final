<?php
session_start();
include('connect.php');

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

// Get the raw POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['action'])) {
    echo json_encode(['success' => false, 'error' => 'No action specified']);
    exit();
}

switch ($data['action']) {
    case 'get':
        // Get unread notifications
        $stmt = $conn->prepare("
            SELECT id, message, type, created_at 
            FROM notifications 
            WHERE user_id = ? AND is_read = 0 
            ORDER BY created_at DESC
        ");
        
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $notifications = [];
        while ($row = $result->fetch_assoc()) {
            $notifications[] = [
                'id' => $row['id'],
                'message' => $row['message'],
                'type' => $row['type'],
                'created_at' => $row['created_at']
            ];
        }
        
        echo json_encode(['success' => true, 'notifications' => $notifications]);
        break;

    case 'markAsRead':
        if (!isset($data['notification_id'])) {
            echo json_encode(['success' => false, 'error' => 'No notification ID provided']);
            exit();
        }
        
        $stmt = $conn->prepare("
            UPDATE notifications 
            SET is_read = 1 
            WHERE id = ? AND user_id = ?
        ");
        
        $stmt->bind_param("ii", $data['notification_id'], $_SESSION['user_id']);
        $success = $stmt->execute();
        
        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to mark notification as read']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        break;
}

$conn->close();
?> 