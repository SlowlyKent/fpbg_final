<?php

use Dba\Connection;

$servername = "localhost";
$username = "root";
$password = "";
$database = "fpbg_final"; 

$conn = new mysqli($servername, $username, $password, $database);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "Connection Successful!";
}

$error = '';

?>
