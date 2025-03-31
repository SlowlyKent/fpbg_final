<?php
// Include the JWT library
require_once 'vendor/autoload.php';
use \Firebase\JWT\JWT;
use Firebase\JWT\Key;

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Database connection
$conn = new mysqli("localhost", "root", "", "fpbg_final");

if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
}

// Secret key for JWT
define('SECRET_KEY', 'your-secret-key-here');

// Function to authenticate API request with JWT
function authenticate() {
    $headers = getallheaders();
    if (isset($headers['Authorization'])) {
        $token = str_replace('Bearer ', '', $headers['Authorization']);
        try {
            $decoded = JWT::decode($token, SECRET_KEY, array('HS256'));
            return (object) $decoded;
        } catch (Exception $e) {
            echo json_encode(["error" => "Access denied. Invalid token."]);
            exit;
        }
    } else {
        echo json_encode(["error" => "Authorization token not found."]);
        exit;
    }
}

// Function to validate POST data
function validate_post_data($data, $required_fields) {
    foreach ($required_fields as $field) {
        if (!isset($data[$field])) {
            echo json_encode(["error" => "Missing required field: " . $field]);
            exit;
        }
    }
}

// Handle requests
$method = $_SERVER["REQUEST_METHOD"];
$input = json_decode(file_get_contents("php://input"), true);

switch ($method) {
    case "POST":
        if (isset($_GET["action"]) && $_GET["action"] == "login") {
            // Login action (Generate JWT token)
            validate_post_data($input, ['username', 'password']);
            
            $username = $input['username'];
            $password = $input['password'];

            $stmt = $conn->prepare("SELECT * FROM user_final WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 0) {
                echo json_encode(["error" => "Invalid credentials"]);
                exit;
            }

            $user = $result->fetch_assoc();
            if (!password_verify($password, $user['password'])) {
                echo json_encode(["error" => "Invalid credentials"]);
                exit;
            }

            // Generate JWT
            $payload = array(
                "user_id" => $user['user_id'],
                "username" => $user['username'],
                "role" => $user['role'],
                "iat" => time(), // Issued at: time when the token was generated
                "exp" => time() + (60 * 60) // Expiration time (1 hour)
            );
            $jwt = JWT::encode($payload, SECRET_KEY);

            echo json_encode(["token" => $jwt]);
        }
        break;

    case "GET":
        // Example of role-based access control (RBAC)
        $user = authenticate(); // Authenticate the user
        if ($user->role !== 'admin') {
            echo json_encode(["error" => "Insufficient permissions. Admin role required."]);
            exit;
        }

        // Example: Fetch all products (only accessible by admin)
        $stmt = $conn->prepare("SELECT * FROM products");
        $stmt->execute();
        $result = $stmt->get_result();
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        break;

    default:
        echo json_encode(["error" => "Invalid request method"]);
}

// Function to generate JWT (Not used in your current flow but added for example purposes)
function generateJWT($userId, $role) {
    $secretKey = "your_secret_key";  // Change this to a strong, secure key
    $issuedAt = time();
    $expirationTime = $issuedAt + 3600; // Token valid for 1 hour

    $payload = [
        "iss" => "yourdomain.com",
        "iat" => $issuedAt,
        "exp" => $expirationTime,
        "sub" => $userId,
        "role" => $role
    ];

    return JWT::encode($payload, $secretKey, 'HS256');
}

// Example usage (you can remove this if not needed):
// $token = generateJWT(1, "admin");
// echo "JWT Token: " . $token;

// Function to verify JWT
function verifyJWT($token) {
    $secretKey = "your_secret_key"; // Use the same key as in generateJWT()

    try {
        $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
        return (array) $decoded;
    } catch (Exception $e) {
        return ["error" => "Invalid token: " . $e->getMessage()];
    }
}

// Example: Protecting an API route
// In the authenticate function above, this will protect routes by validating the JWT

$conn->close();
?>
