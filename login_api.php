<?php
header('Content-Type: application/json');
include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['username']) || !isset($input['password'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Username and password are required'
        ]);
        exit();
    }

    $username = trim($input['username']);
    $password = $input['password'];
    
    $sql = "SELECT user_id, username, password, role FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error'
        ]);
        exit();
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $stored_hash = $row['password'];

        if (password_verify($password, $stored_hash)) {
            // Generate a secure API key
            $api_key = bin2hex(random_bytes(32));
            
            // Store API key in database
            $update_stmt = $conn->prepare("UPDATE users SET api_key = ? WHERE user_id = ?");
            $update_stmt->bind_param("si", $api_key, $row['user_id']);
            
            if (!$update_stmt->execute()) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to generate API key'
                ]);
                exit();
            }
            
            // Store in session
            session_start();
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['api_key'] = $api_key;

            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user_id' => $row['user_id'],
                    'username' => $row['username'],
                    'role' => $row['role'],
                    'api_key' => $api_key
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Incorrect password'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Username not found'
        ]);
    }

    $stmt->close();
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}

$conn->close();
?> 