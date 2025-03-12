<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "fpbg_final"; // Change to your new database

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}



?>
