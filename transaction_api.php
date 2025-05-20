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
    case "GET": // Fetch all transactions or a single transaction
        if (isset($_GET["transaction_id"])) {
            $stmt = $conn->prepare("SELECT * FROM stock_transactions WHERE transaction_id = ?");
            $stmt->bind_param("s", $_GET["transaction_id"]);
        } else {
            $stmt = $conn->prepare("SELECT * FROM stock_transactions ORDER BY created_at DESC");
        }
        $stmt->execute();
        $result = $stmt->get_result();
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        break;

    case "POST": // Create a new transaction
        if (!isset($input["transaction_id"], $input["product_id"], $input["quantity"], $input["transaction_type"])) {
            echo json_encode(["error" => "Missing required fields"]);
            exit;
        }

        try {
            // Insert into stock_transactions table
            $stmt = $conn->prepare("INSERT INTO stock_transactions (transaction_id, product_id, quantity, transaction_type) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssds", 
                $input["transaction_id"],
                $input["product_id"],
                $input["quantity"],
                $input["transaction_type"]
            );
            
            if ($stmt->execute()) {
                // Update product stock based on transaction type
                if ($input["transaction_type"] == "stock_in") {
                    $update_stock = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE product_id = ?");
                } else {
                    $update_stock = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?");
                }
                $update_stock->bind_param("ds", $input["quantity"], $input["product_id"]);
                $update_stock->execute();

                echo json_encode([
                    "success" => true,
                    "transaction_id" => $input["transaction_id"]
                ]);
            } else {
                throw new Exception("Failed to create transaction");
            }

        } catch (Exception $e) {
            echo json_encode([
                "error" => "Transaction failed: " . $e->getMessage()
            ]);
        }
        break;

    case "PUT": // Update a transaction
        if (!isset($input["transaction_id"], $input["quantity"])) {
            echo json_encode(["error" => "Missing required fields"]);
            exit;
        }

        try {
            // Get the current transaction details
            $stmt = $conn->prepare("SELECT quantity, transaction_type, product_id FROM stock_transactions WHERE transaction_id = ?");
            $stmt->bind_param("s", $input["transaction_id"]);
            $stmt->execute();
            $result = $stmt->get_result();
            $current = $result->fetch_assoc();

            if (!$current) {
                throw new Exception("Transaction not found");
            }

            // Calculate the difference in quantity
            $quantity_diff = $input["quantity"] - $current["quantity"];

            // Update the transaction
            $stmt = $conn->prepare("UPDATE stock_transactions SET quantity = ? WHERE transaction_id = ?");
            $stmt->bind_param("ds", $input["quantity"], $input["transaction_id"]);
            
            if ($stmt->execute()) {
                // Update product stock based on the difference
                if ($current["transaction_type"] == "stock_in") {
                    $update_stock = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE product_id = ?");
                } else {
                    $update_stock = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?");
                }
                $update_stock->bind_param("ds", $quantity_diff, $current["product_id"]);
                $update_stock->execute();

                echo json_encode(["success" => true]);
            } else {
                throw new Exception("Failed to update transaction");
            }

        } catch (Exception $e) {
            echo json_encode([
                "error" => "Update failed: " . $e->getMessage()
            ]);
        }
        break;

    case "DELETE": // Delete a transaction
        if (!isset($input["transaction_id"])) {
            echo json_encode(["error" => "Missing required field: transaction_id"]);
            exit;
        }

        try {
            // Get the transaction details first
            $stmt = $conn->prepare("SELECT quantity, transaction_type, product_id FROM stock_transactions WHERE transaction_id = ?");
            $stmt->bind_param("s", $input["transaction_id"]);
            $stmt->execute();
            $result = $stmt->get_result();
            $transaction = $result->fetch_assoc();

            if (!$transaction) {
                throw new Exception("Transaction not found");
            }

            // Delete the transaction
            $stmt = $conn->prepare("DELETE FROM stock_transactions WHERE transaction_id = ?");
            $stmt->bind_param("s", $input["transaction_id"]);
            
            if ($stmt->execute()) {
                // Reverse the stock change
                if ($transaction["transaction_type"] == "stock_in") {
                    $update_stock = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?");
                } else {
                    $update_stock = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE product_id = ?");
                }
                $update_stock->bind_param("ds", $transaction["quantity"], $transaction["product_id"]);
                $update_stock->execute();

                echo json_encode(["success" => true]);
            } else {
                throw new Exception("Failed to delete transaction");
            }

        } catch (Exception $e) {
            echo json_encode([
                "error" => "Delete failed: " . $e->getMessage()
            ]);
        }
        break;

    default:
        echo json_encode(["error" => "Invalid request method"]);
}

$conn->close();
?> 