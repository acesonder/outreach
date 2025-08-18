<?php
/**
 * OUTSINC Configuration File
 * Database connection settings and application constants
 */

// Database connection settings for MAMP
define('DB_HOST', 'localhost');
define('DB_PORT', '8889');
define('DB_NAME', 'outsinc_db');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_CHARSET', 'utf8mb4');

// Application settings
define('SITE_NAME', 'OUTSINC');
define('SITE_URL', 'http://localhost:8000');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 10485760); // 10MB in bytes

// Session settings
define('SESSION_TIMEOUT', 3600); // 1 hour
define('REMEMBER_ME_DURATION', 2592000); // 30 days
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_DURATION', 900); // 15 minutes

// Security settings
define('ENCRYPTION_KEY', 'outsinc-secret-key-change-in-production');
define('PASSWORD_MIN_LENGTH', 8);

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection
try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    // In production, log this error instead of displaying it
    die("Database connection failed: " . $e->getMessage());
}

// Helper functions
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function redirectTo($url, $message = null) {
    if ($message) {
        $_SESSION['flash_message'] = $message;
    }
    header("Location: $url");
    exit;
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_ARGON2ID);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function generateUsername($firstName, $lastName, $dateOfBirth) {
    $firstInitial = strtoupper(substr($firstName, 0, 1));
    $lastInitial = strtoupper(substr($lastName, 0, 1));
    $birthYear = date('y', strtotime($dateOfBirth));
    $random = str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
    
    return $firstInitial . $lastInitial . $birthYear . $random;
}

function logActivity($userId, $action, $details = [], $pdo = null) {
    global $pdo as $globalPdo;
    $db = $pdo ?: $globalPdo;
    
    try {
        $stmt = $db->prepare("
            INSERT INTO audit_log (user_id, action, details, ip_address, user_agent, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $userId,
            $action,
            json_encode($details),
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    } catch (Exception $e) {
        error_log("Failed to log activity: " . $e->getMessage());
    }
}

function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2592000) return floor($time/86400) . ' days ago';
    
    return date('M j, Y', strtotime($datetime));
}
?>
