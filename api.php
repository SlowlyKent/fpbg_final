<?php
// Set JSON headers
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

// Database connection
$conn = new mysqli("localhost", "root", "", "fpbg_final");
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Get request method and input data
$method = $_SERVER["REQUEST_METHOD"];
$input = json_decode(file_get_contents("php://input"), true);

switch ($method) {
    case "GET": // Fetch all products or a single product
        if (isset($_GET["product_id"])) {
            $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
            $stmt->bind_param("i", $_GET["product_id"]);
        } else {
            $stmt = $conn->prepare("SELECT * FROM products");
        }
        $stmt->execute();
        $result = $stmt->get_result();
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        break;

    case "POST": // Create a new product
        if (!isset($input["product_name"], $input["stock_quantity"], $input["unit_of_measure"], 
                    $input["category"], $input["cost_price"], $input["selling_price"], 
                    $input["stock_status"], $input["expiration_date"])) {
            echo json_encode(["error" => "Missing required fields"]);
            exit;
        }
        $stmt = $conn->prepare("INSERT INTO products (product_name, stock_quantity, unit_of_measure, 
                                category, cost_price, selling_price, stock_status, expiration_date) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sissddss", $input["product_name"], $input["stock_quantity"], 
                          $input["unit_of_measure"], $input["category"], 
                          $input["cost_price"], $input["selling_price"], 
                          $input["stock_status"], $input["expiration_date"]);
        echo json_encode(["success" => $stmt->execute()]);
        break;

    case "PUT": // Update a product
        if (!isset($input["product_id"], $input["product_name"], $input["stock_quantity"], 
                    $input["unit_of_measure"], $input["category"], $input["cost_price"], 
                    $input["selling_price"], $input["stock_status"], $input["expiration_date"])) {
            echo json_encode(["error" => "Missing required fields"]);
            exit;
        }
        $stmt = $conn->prepare("UPDATE products SET product_name = ?, stock_quantity = ?, 
                                unit_of_measure = ?, category = ?, cost_price = ?, 
                                selling_price = ?, stock_status = ?, expiration_date = ? 
                                WHERE product_id = ?");
        $stmt->bind_param("sissddssi", $input["product_name"], $input["stock_quantity"], 
                          $input["unit_of_measure"], $input["category"], 
                          $input["cost_price"], $input["selling_price"], 
                          $input["stock_status"], $input["expiration_date"], 
                          $input["product_id"]);
        echo json_encode(["success" => $stmt->execute()]);
        break;

    case "DELETE": // Delete a product
        if (!isset($input["product_id"])) {
            echo json_encode(["error" => "Missing required field: product_id"]);
            exit;
        }
        $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $input["product_id"]);
        echo json_encode(["success" => $stmt->execute()]);
        break;

    default:
        echo json_encode(["error" => "Invalid request method"]);
}

$conn->close();
?>
