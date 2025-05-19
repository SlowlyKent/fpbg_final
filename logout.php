<?php
session_start();
// Store the message in a temporary session
session_regenerate_id(true);
$_SESSION = array();
$_SESSION['success_message'] = "Logged out successfully!";
session_write_close();
header("Location: index.php");
exit();
?>
