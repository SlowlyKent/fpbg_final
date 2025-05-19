<?php
session_start();
require_once __DIR__ . '/../config/connect.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /views/auth/login.php');
        exit();
    }
}

function getUserRole() {
    return isset($_SESSION['role']) ? $_SESSION['role'] : null;
}

function requireRole($requiredRole) {
    if (getUserRole() !== $requiredRole) {
        header('Location: /permission-denied.php');
        exit();
    }
} 