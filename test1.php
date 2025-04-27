<?php
session_start();

// Check if the user is logged in and if the user is a staff member
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    // If not logged in or not a staff, redirect to a "permission denied" page or login page
    header("Location: permission-denied.php"); 
    exit();
}

// If the user is a staff member, the page content is displayed
?>

<h1>Welcome, Staff!</h1>
<p>This page is accessible only by staff members.</p>