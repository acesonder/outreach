<?php
/**
 * Logout Handler
 */

require_once 'includes/config.php';

// Start session
startSecureSession();

// Log logout activity
if (isLoggedIn()) {
    logActivity('logout', 'users', $_SESSION['user_id']);
}

// Clear remember me cookie
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Destroy session
session_unset();
session_destroy();

// Redirect to home page
redirect('index.php', 'You have been logged out successfully.', 'info');
?>