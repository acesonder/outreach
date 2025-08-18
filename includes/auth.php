<?php
/**
 * Authentication functions for OUTSINC
 * Handles user login, registration, and session management
 */

require_once 'config.php';
require_once 'database.php';

/**
 * Authenticate user login
 * @param string $username Username
 * @param string $password Password
 * @return array Login result with status and user data
 */
function authenticateUser($username, $password) {
    try {
        // Check if user exists and get their data
        $query = "SELECT user_id, username, email, password_hash, first_name, last_name, role, is_active 
                  FROM users WHERE username = ? OR email = ?";
        $result = executeQuery($query, [$username, $username], 'ss');
        
        if ($result->num_rows === 0) {
            return ['success' => false, 'message' => 'Invalid username or password'];
        }
        
        $user = $result->fetch_assoc();
        
        // Check if account is active
        if (!$user['is_active']) {
            return ['success' => false, 'message' => 'Account is disabled. Please contact support.'];
        }
        
        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Invalid username or password'];
        }
        
        // Start user session
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        
        // Log successful login
        logActivity($user['user_id'], 'login');
        
        return [
            'success' => true,
            'user' => $user,
            'message' => 'Login successful'
        ];
        
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Login failed. Please try again.'];
    }
}

/**
 * Register a new user
 * @param array $userData User registration data
 * @return array Registration result
 */
function registerUser($userData) {
    try {
        // Validate required fields
        $required = ['first_name', 'last_name', 'email', 'password', 'confirm_password', 'date_of_birth', 'security_question', 'security_answer'];
        foreach ($required as $field) {
            if (empty($userData[$field])) {
                return ['success' => false, 'message' => 'Please fill in all required fields'];
            }
        }
        
        // Validate email format
        if (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Please enter a valid email address'];
        }
        
        // Check if passwords match
        if ($userData['password'] !== $userData['confirm_password']) {
            return ['success' => false, 'message' => 'Passwords do not match'];
        }
        
        // Validate password strength
        $passwordValidation = validatePassword($userData['password']);
        if (!$passwordValidation['valid']) {
            return ['success' => false, 'message' => implode(', ', $passwordValidation['errors'])];
        }
        
        // Check if email already exists
        $query = "SELECT user_id FROM users WHERE email = ?";
        $result = executeQuery($query, [$userData['email']], 's');
        if ($result->num_rows > 0) {
            return ['success' => false, 'message' => 'Email address is already registered'];
        }
        
        // Generate unique username
        $username = generateUsername($userData['first_name'], $userData['last_name']);
        
        // Ensure username is unique
        $attempts = 0;
        do {
            $query = "SELECT user_id FROM users WHERE username = ?";
            $result = executeQuery($query, [$username], 's');
            if ($result->num_rows > 0) {
                $username = generateUsername($userData['first_name'], $userData['last_name']);
                $attempts++;
            }
        } while ($result->num_rows > 0 && $attempts < 10);
        
        if ($attempts >= 10) {
            return ['success' => false, 'message' => 'Unable to generate unique username. Please try again.'];
        }
        
        // Hash password
        $passwordHash = password_hash($userData['password'], PASSWORD_DEFAULT);
        
        // Hash security answer
        $securityAnswerHash = password_hash(strtolower(trim($userData['security_answer'])), PASSWORD_DEFAULT);
        
        // Insert new user
        $query = "INSERT INTO users (username, email, password_hash, first_name, last_name, date_of_birth, 
                  phone, security_question, security_answer, role) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'client')";
        
        $params = [
            $username,
            $userData['email'],
            $passwordHash,
            $userData['first_name'],
            $userData['last_name'],
            $userData['date_of_birth'],
            $userData['phone'] ?? null,
            $userData['security_question'],
            $securityAnswerHash
        ];
        
        executeQuery($query, $params, 'sssssssss');
        $userId = getLastInsertId();
        
        // Create client profile
        $profileQuery = "INSERT INTO client_profiles (user_id) VALUES (?)";
        executeQuery($profileQuery, [$userId], 'i');
        
        // Log registration
        logActivity($userId, 'register');
        
        return [
            'success' => true,
            'user_id' => $userId,
            'username' => $username,
            'message' => 'Registration successful! Your username is: ' . $username
        ];
        
    } catch (Exception $e) {
        error_log("Registration error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Registration failed. Please try again.'];
    }
}

/**
 * Check if user is logged in and session is valid
 * @return bool True if user is logged in
 */
function isLoggedIn() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['last_activity'])) {
        return false;
    }
    
    // Check session timeout
    if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
        logout();
        return false;
    }
    
    // Update last activity
    $_SESSION['last_activity'] = time();
    
    return true;
}

/**
 * Get current user information
 * @return array|null User data or null if not logged in
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'user_id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'email' => $_SESSION['email'],
        'first_name' => $_SESSION['first_name'],
        'last_name' => $_SESSION['last_name'],
        'role' => $_SESSION['role']
    ];
}

/**
 * Check if current user has specific role
 * @param string|array $roles Role(s) to check
 * @return bool True if user has role
 */
function hasRole($roles) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $userRole = $_SESSION['role'];
    
    if (is_string($roles)) {
        return $userRole === $roles;
    }
    
    if (is_array($roles)) {
        return in_array($userRole, $roles);
    }
    
    return false;
}

/**
 * Require user to be logged in - redirect if not
 * @param string $redirectUrl URL to redirect to if not logged in
 */
function requireLogin($redirectUrl = 'index.php') {
    if (!isLoggedIn()) {
        header("Location: $redirectUrl?error=login_required");
        exit;
    }
}

/**
 * Require specific role - redirect if not authorized
 * @param string|array $roles Required role(s)
 * @param string $redirectUrl URL to redirect to if not authorized
 */
function requireRole($roles, $redirectUrl = 'index.php') {
    requireLogin($redirectUrl);
    
    if (!hasRole($roles)) {
        header("Location: $redirectUrl?error=access_denied");
        exit;
    }
}

/**
 * Logout user and destroy session
 */
function logout() {
    if (isset($_SESSION['user_id'])) {
        logActivity($_SESSION['user_id'], 'logout');
    }
    
    // Destroy session
    session_destroy();
    
    // Clear session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
}

/**
 * Reset password using security question
 * @param string $username Username
 * @param string $securityAnswer Security question answer
 * @param string $newPassword New password
 * @return array Reset result
 */
function resetPassword($username, $securityAnswer, $newPassword) {
    try {
        // Get user data
        $query = "SELECT user_id, security_answer FROM users WHERE username = ? AND is_active = 1";
        $result = executeQuery($query, [$username], 's');
        
        if ($result->num_rows === 0) {
            return ['success' => false, 'message' => 'Username not found'];
        }
        
        $user = $result->fetch_assoc();
        
        // Verify security answer
        if (!password_verify(strtolower(trim($securityAnswer)), $user['security_answer'])) {
            return ['success' => false, 'message' => 'Security answer is incorrect'];
        }
        
        // Validate new password
        $passwordValidation = validatePassword($newPassword);
        if (!$passwordValidation['valid']) {
            return ['success' => false, 'message' => implode(', ', $passwordValidation['errors'])];
        }
        
        // Update password
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $query = "UPDATE users SET password_hash = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?";
        executeQuery($query, [$passwordHash, $user['user_id']], 'si');
        
        // Log password reset
        logActivity($user['user_id'], 'password_reset');
        
        return ['success' => true, 'message' => 'Password reset successfully'];
        
    } catch (Exception $e) {
        error_log("Password reset error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Password reset failed. Please try again.'];
    }
}

/**
 * Get security question for username
 * @param string $username Username
 * @return array Result with security question
 */
function getSecurityQuestion($username) {
    try {
        $query = "SELECT security_question FROM users WHERE username = ? AND is_active = 1";
        $result = executeQuery($query, [$username], 's');
        
        if ($result->num_rows === 0) {
            return ['success' => false, 'message' => 'Username not found'];
        }
        
        $user = $result->fetch_assoc();
        
        return [
            'success' => true,
            'security_question' => $user['security_question']
        ];
        
    } catch (Exception $e) {
        error_log("Get security question error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Unable to retrieve security question'];
    }
}
?>