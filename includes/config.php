<?php
/**
 * Database Configuration for OUTSINC
 * 
 * This file contains database connection settings.
 * Modify these settings according to your server configuration.
 */

// Database connection settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'outsinc_db');
define('DB_USER', 'outsinc_user');
define('DB_PASS', 'outsinc_password');
define('DB_CHARSET', 'utf8mb4');

// Application settings
define('SITE_NAME', 'OUTSINC');
define('SITE_URL', 'http://localhost/outreach');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 10485760); // 10MB in bytes

// Session settings
define('SESSION_TIMEOUT', 3600); // 1 hour
define('REMEMBER_ME_DURATION', 2592000); // 30 days

// Security settings
define('ENCRYPTION_KEY', 'your-secret-encryption-key-here-change-this');
define('PASSWORD_MIN_LENGTH', 8);

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone setting
date_default_timezone_set('America/Toronto');

/**
 * Create database connection
 */
function getDbConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        die("Database connection failed. Please try again later.");
    }
}

/**
 * Start secure session
 */
function startSecureSession() {
    // Configure session security
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
    
    session_start();
    
    // Regenerate session ID periodically for security
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > 1800) {
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
    
    // Check for session timeout
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        session_unset();
        session_destroy();
        return false;
    }
    $_SESSION['last_activity'] = time();
    
    return true;
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user information
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT user_id, username, first_name, last_name, email, role, last_login FROM users WHERE user_id = ? AND status = 'active'");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error fetching user: " . $e->getMessage());
        return null;
    }
}

/**
 * Log user activity for audit trail
 */
function logActivity($action, $table_name = null, $record_id = null, $old_values = null, $new_values = null) {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("
            INSERT INTO audit_log (user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $user_id = $_SESSION['user_id'] ?? null;
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $stmt->execute([
            $user_id,
            $action,
            $table_name,
            $record_id,
            $old_values ? json_encode($old_values) : null,
            $new_values ? json_encode($new_values) : null,
            $ip_address,
            $user_agent
        ]);
    } catch (PDOException $e) {
        error_log("Error logging activity: " . $e->getMessage());
    }
}

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate secure random token
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Hash password securely
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generate username from name and birth year
 */
function generateUsername($firstName, $lastName, $dateOfBirth) {
    $firstName = strtoupper(substr($firstName, 0, 3));
    $lastName = strtoupper(substr($lastName, 0, 3));
    $year = substr($dateOfBirth, 2, 2); // Last 2 digits of birth year
    $month = substr($dateOfBirth, 5, 2); // Month
    
    return $firstName . $lastName . $year . $month;
}

/**
 * Check if username exists
 */
function usernameExists($username) {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        error_log("Error checking username: " . $e->getMessage());
        return true; // Assume exists to be safe
    }
}

/**
 * Redirect with message
 */
function redirect($url, $message = null, $type = 'info') {
    if ($message) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    header("Location: $url");
    exit;
}

/**
 * Get and clear flash message
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

/**
 * Format date for display
 */
function formatDate($date, $format = 'Y-m-d') {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

/**
 * Calculate age from date of birth
 */
function calculateAge($dateOfBirth) {
    if (empty($dateOfBirth)) return null;
    $dob = new DateTime($dateOfBirth);
    $now = new DateTime();
    return $now->diff($dob)->y;
}

/**
 * Get user role permissions
 */
function getUserPermissions($role) {
    $permissions = [
        'client' => ['view_own_profile', 'edit_own_profile', 'view_own_cases', 'send_messages'],
        'staff' => ['view_clients', 'edit_clients', 'create_cases', 'edit_cases', 'view_all_cases', 'send_messages', 'create_appointments'],
        'outreach' => ['view_clients', 'create_cases', 'edit_own_cases', 'log_visits', 'order_supplies', 'send_messages'],
        'admin' => ['*'], // All permissions
        'service_provider' => ['view_referrals', 'update_referrals', 'send_messages']
    ];
    
    return $permissions[$role] ?? [];
}

/**
 * Check if user has permission
 */
function hasPermission($permission) {
    $user = getCurrentUser();
    if (!$user) return false;
    
    $permissions = getUserPermissions($user['role']);
    return in_array('*', $permissions) || in_array($permission, $permissions);
}

/**
 * Require permission or redirect
 */
function requirePermission($permission, $redirect_url = 'index.php') {
    if (!hasPermission($permission)) {
        redirect($redirect_url, 'You do not have permission to access this page.', 'error');
    }
}

/**
 * Require login or redirect
 */
function requireLogin($redirect_url = 'login.php') {
    if (!isLoggedIn()) {
        redirect($redirect_url, 'Please log in to continue.', 'warning');
    }
}
?>