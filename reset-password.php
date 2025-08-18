<?php
/**
 * Reset Password Handler
 */

require_once 'includes/config.php';

// Start session
startSecureSession();

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php', 'Invalid request method.', 'error');
}

// Check if user went through forgot password flow
if (!isset($_SESSION['password_reset_user']) || !isset($_SESSION['password_reset_username'])) {
    redirect('index.php', 'Invalid password reset session.', 'error');
}

// Get form data
$username = sanitizeInput($_POST['username'] ?? '');
$securityAnswer = sanitizeInput($_POST['security_answer'] ?? '');
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// Validation
$errors = [];

if (empty($securityAnswer)) {
    $errors[] = 'Security answer is required.';
}

if (empty($newPassword)) {
    $errors[] = 'New password is required.';
} elseif (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
    $errors[] = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long.';
} elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/', $newPassword)) {
    $errors[] = 'Password must contain uppercase, lowercase, number, and special character.';
}

if ($newPassword !== $confirmPassword) {
    $errors[] = 'Passwords do not match.';
}

if ($username !== $_SESSION['password_reset_username']) {
    $errors[] = 'Invalid username.';
}

if (!empty($errors)) {
    $_SESSION['form_errors'] = $errors;
    redirect('forgot-password.php', 'Please correct the errors and try again.', 'error');
}

try {
    $pdo = getDbConnection();
    
    // Get user data and verify security answer
    $stmt = $pdo->prepare("
        SELECT user_id, username, first_name, security_answer_hash 
        FROM users 
        WHERE user_id = ? AND username = ? AND status = 'active'
    ");
    $stmt->execute([$_SESSION['password_reset_user'], $username]);
    $user = $stmt->fetch();
    
    if (!$user) {
        redirect('index.php', 'Invalid password reset session.', 'error');
    }
    
    // Verify security answer
    if (!verifyPassword(strtolower(trim($securityAnswer)), $user['security_answer_hash'])) {
        logActivity('password_reset_failed', 'users', $user['user_id'], null, [
            'username' => $username,
            'reason' => 'incorrect_security_answer'
        ]);
        
        redirect('forgot-password.php', 'Incorrect security answer. Please try again.', 'error');
    }
    
    // Update password
    $newPasswordHash = hashPassword($newPassword);
    
    $stmt = $pdo->prepare("
        UPDATE users 
        SET password_hash = ?, updated_at = NOW() 
        WHERE user_id = ?
    ");
    $stmt->execute([$newPasswordHash, $user['user_id']]);
    
    // Log successful password reset
    logActivity('password_reset_success', 'users', $user['user_id'], null, [
        'username' => $username
    ]);
    
    // Clear password reset session
    unset($_SESSION['password_reset_user'], $_SESSION['password_reset_username']);
    
    // Auto-login the user
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = 'client'; // Default role, will be updated on next login
    
    // Update last login
    $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
    $stmt->execute([$user['user_id']]);
    
    redirect('dashboard.php', 'Password reset successful! You are now logged in.', 'success');
    
} catch (PDOException $e) {
    error_log("Password reset error: " . $e->getMessage());
    redirect('forgot-password.php', 'Password reset failed. Please try again later.', 'error');
}
?>