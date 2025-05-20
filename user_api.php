<?php
header('Content-Type: application/json');
include 'connect.php';
require_once 'auth_middleware.php';

// Verify API key for all requests
verifyApiKey();

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Get all users (admin only)
        requireAdmin();
        
        $sql = "SELECT user_id, username, role FROM users";
        $result = $conn->query($sql);
        
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $users
        ]);
        break;

    case 'POST':
        // Create new user (admin only)
        requireAdmin();
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['username']) || !isset($input['password']) || !isset($input['role'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Missing required fields'
            ]);
            exit();
        }

        $username = trim($input['username']);
        $password = $input['password'];
        $role = $input['role'];

        // Check if username exists
        $check = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Username already exists'
            ]);
            exit();
        }

        // Hash password and create user
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $hashed_password, $role);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'User created successfully',
                'user_id' => $conn->insert_id
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to create user'
            ]);
        }
        break;

    case 'PUT':
        // Update user (admin only)
        requireAdmin();
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['user_id']) || !isset($input['username'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Missing required fields'
            ]);
            exit();
        }

        $user_id = $input['user_id'];
        $username = trim($input['username']);
        $new_password = isset($input['password']) ? $input['password'] : null;

        if ($new_password) {
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE users SET username = ?, password = ? WHERE user_id = ?");
            $stmt->bind_param("ssi", $username, $hashed_password, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET username = ? WHERE user_id = ?");
            $stmt->bind_param("si", $username, $user_id);
        }

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'User updated successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to update user'
            ]);
        }
        break;

    case 'DELETE':
        // Delete user (admin only)
        requireAdmin();
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['user_id'])) {
            echo json_encode([
                'success' => false,
                'message' => 'User ID is required'
            ]);
            exit();
        }

        $user_id = $input['user_id'];
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to delete user'
            ]);
        }
        break;

    default:
        echo json_encode([
            'success' => false,
            'message' => 'Invalid request method'
        ]);
}

$conn->close();
?> 