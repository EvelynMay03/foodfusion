<?php
/**
 * Authentication Helper Functions
 * Include this file to use authentication functions across the application
 */

// Check if user is logged in
function isLoggedIn() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

// Get current user ID
function getUserId() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return $_SESSION['user_id'] ?? null;
}

// Get current user data
function getCurrentUser() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isLoggedIn()) {
        return [
            'user_id' => $_SESSION['user_id'] ?? null,
            'email' => $_SESSION['user_email'] ?? null,
            'firstname' => $_SESSION['user_firstname'] ?? null,
            'lastname' => $_SESSION['user_lastname'] ?? null
        ];
    }
    return null;
}

// Require login - redirect to home if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit;
    }
}
?>

