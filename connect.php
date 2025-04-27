<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "fpbg_final";// database

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = '';

?>
