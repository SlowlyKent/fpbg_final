<?php
function verifyApiKey() {
    global $conn;
    
    // Get the Authorization header
    $headers = getallheaders();
    $auth_header = isset($headers['Authorization']) ? $headers['Authorization'] : '';
    
    // Check if Authorization header exists and has the correct format
    if (empty($auth_header) || !preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'No API key provided'
        ]);
        exit();
    }

    $api_key = $matches[1];
    
    // Verify API key in database
    $stmt = $conn->prepare("
        SELECT user_id, username, role 
        FROM users 
        WHERE api_key = ?
    ");
    $stmt->bind_param("s", $api_key);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Invalid API key'
        ]);
        exit();
    }
    
    // Get user data
    $user = $result->fetch_assoc();
    
    // Start session and store user data
    session_start();
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['api_key'] = $api_key;

    return true;
}

// Function to check if user has admin role
function requireAdmin() {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Admin access required'
        ]);
        exit();
    }
    return true;
}

// Function to check if user has staff role
function requireStaff() {
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Staff access required'
        ]);
        exit();
    }
    return true;
}

// Function to invalidate API key (for logout)
function invalidateApiKey($api_key) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE users SET api_key = NULL WHERE api_key = ?");
    $stmt->bind_param("s", $api_key);
    return $stmt->execute();
}
?> 