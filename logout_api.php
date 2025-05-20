<?php
header('Content-Type: application/json');
include 'connect.php';
require_once 'auth_middleware.php';

// Get the Authorization header
$headers = getallheaders();
$auth_header = isset($headers['Authorization']) ? $headers['Authorization'] : '';

if (empty($auth_header) || !preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
    echo json_encode([
        'success' => false,
        'message' => 'No API key provided'
    ]);
    exit();
}

$api_key = $matches[1];

// Invalidate the API key
if (invalidateApiKey($api_key)) {
    // Clear session
    session_start();
    session_destroy();
    
    echo json_encode([
        'success' => true,
        'message' => 'Logged out successfully'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to logout'
    ]);
}

$conn->close();
?> 