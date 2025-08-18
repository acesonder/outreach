<?php
/**
 * Registration processing for OUTSINC
 * Handles new user registration
 */

require_once 'includes/config.php';
require_once 'includes/auth.php';

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Get form data
$userData = [
    'first_name' => trim($_POST['first_name'] ?? ''),
    'last_name' => trim($_POST['last_name'] ?? ''),
    'email' => trim($_POST['email'] ?? ''),
    'phone' => trim($_POST['phone'] ?? ''),
    'date_of_birth' => $_POST['date_of_birth'] ?? '',
    'security_question' => $_POST['security_question'] ?? '',
    'security_answer' => trim($_POST['security_answer'] ?? ''),
    'password' => $_POST['password'] ?? '',
    'confirm_password' => $_POST['confirm_password'] ?? '',
    'terms_consent' => isset($_POST['terms_consent'])
];

// Validate consent
if (!$userData['terms_consent']) {
    header('Location: index.php?error=consent_required');
    exit;
}

try {
    // Attempt registration
    $result = registerUser($userData);
    
    if ($result['success']) {
        // Registration successful
        $username = $result['username'];
        
        // Log the registration
        logActivity($result['user_id'], 'register', 'users', $result['user_id']);
        
        // Redirect to login with success message
        header("Location: index.php?message=registration_success&username=" . urlencode($username));
        exit;
        
    } else {
        // Registration failed
        header('Location: index.php?error=registration_failed&message=' . urlencode($result['message']));
        exit;
    }
    
} catch (Exception $e) {
    error_log("Registration error: " . $e->getMessage());
    header('Location: index.php?error=system_error');
    exit;
}
?>