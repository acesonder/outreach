<?php
/**
 * User Login Handler
 */

require_once 'includes/config.php';

// Start session
startSecureSession();

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php', 'Invalid request method.', 'error');
}

// Get form data
$username = sanitizeInput($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$rememberMe = isset($_POST['remember_me']);

// Validation
if (empty($username)) {
    redirect('index.php', 'Username is required.', 'error');
}

if (empty($password)) {
    redirect('index.php', 'Password is required.', 'error');
}

try {
    $pdo = getDbConnection();
    
    // Get user data
    $stmt = $pdo->prepare("
        SELECT user_id, username, first_name, last_name, email, password_hash, role, status, last_login
        FROM users 
        WHERE username = ?
    ");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if (!$user) {
        // Log failed login attempt
        logActivity('login_failed', null, null, null, [
            'username' => $username,
            'reason' => 'user_not_found'
        ]);
        
        redirect('index.php', 'Invalid username or password.', 'error');
    }
    
    // Check if account is active
    if ($user['status'] !== 'active') {
        logActivity('login_failed', 'users', $user['user_id'], null, [
            'username' => $username,
            'reason' => 'account_' . $user['status']
        ]);
        
        redirect('index.php', 'Your account is ' . $user['status'] . '. Please contact support.', 'error');
    }
    
    // Verify password
    if (!verifyPassword($password, $user['password_hash'])) {
        logActivity('login_failed', 'users', $user['user_id'], null, [
            'username' => $username,
            'reason' => 'invalid_password'
        ]);
        
        redirect('index.php', 'Invalid username or password.', 'error');
    }
    
    // Login successful - set session variables
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['first_name'] = $user['first_name'];
    $_SESSION['last_name'] = $user['last_name'];
    
    // Handle remember me
    if ($rememberMe) {
        $token = generateToken(64);
        $expires = time() + REMEMBER_ME_DURATION;
        
        // Store token in database (you might want to add a remember_tokens table)
        setcookie('remember_token', $token, $expires, '/', '', false, true);
        
        // For now, just extend session lifetime
        ini_set('session.gc_maxlifetime', REMEMBER_ME_DURATION);
    }
    
    // Update last login timestamp
    $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
    $stmt->execute([$user['user_id']]);
    
    // Log successful login
    logActivity('login_success', 'users', $user['user_id']);
    
    // Determine redirect destination based on role
    $redirectUrl = 'dashboard.php';
    
    // Check if user needs to complete intake
    if ($user['role'] === 'client') {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM intakes 
            WHERE user_id = ? AND status = 'completed'
        ");
        $stmt->execute([$user['user_id']]);
        $completedIntakes = $stmt->fetchColumn();
        
        if ($completedIntakes == 0) {
            $redirectUrl = 'intake.php';
        }
    }
    
    // Welcome back message
    $welcomeMessage = "Welcome back, " . htmlspecialchars($user['first_name']) . "!";
    if ($user['last_login']) {
        $lastLogin = new DateTime($user['last_login']);
        $welcomeMessage .= " Last login: " . $lastLogin->format('M j, Y g:i A');
    }
    
    redirect($redirectUrl, $welcomeMessage, 'success');
    
} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());
    redirect('index.php', 'Login failed. Please try again later.', 'error');
}
?>