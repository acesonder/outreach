<?php
/**
 * OUTSINC Configuration File
 * Application settings and constants
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Application constants
define('APP_NAME', 'OUTSINC');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost');  // Update for production

// Security settings
define('PASSWORD_MIN_LENGTH', 8);
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes

// File upload settings
define('UPLOAD_MAX_SIZE', 5242880); // 5MB in bytes
define('UPLOAD_PATH', 'uploads/');
define('ALLOWED_FILE_TYPES', ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif']);

// Platform information
$platforms = [
    'outsinc' => [
        'name' => 'OUTSINC',
        'description' => 'Central hub for outreach and case management',
        'color' => '#003366',
        'icon' => 'fas fa-home'
    ],
    'dcide' => [
        'name' => 'DCIDE',
        'description' => 'Case management and progress tracking',
        'color' => '#43A047',
        'icon' => 'fas fa-clipboard-check'
    ],
    'link' => [
        'name' => 'LINK',
        'description' => 'Referral network and community connections',
        'color' => '#3F51B5',
        'icon' => 'fas fa-link'
    ],
    'bles' => [
        'name' => 'BLES',
        'description' => 'Addiction recovery and support platform',
        'color' => '#FB8C00',
        'icon' => 'fas fa-heart'
    ],
    'ask' => [
        'name' => 'ASK',
        'description' => 'Real-time chat and support system',
        'color' => '#29B6F6',
        'icon' => 'fas fa-comments'
    ],
    'ethan' => [
        'name' => 'ETHAN',
        'description' => 'Analytics and outcome tracking',
        'color' => '#FF6F61',
        'icon' => 'fas fa-chart-line'
    ],
    'footprint' => [
        'name' => 'FOOTPRINT',
        'description' => 'Impact tracking and sustainability',
        'color' => '#8D6E63',
        'icon' => 'fas fa-shoe-prints'
    ]
];

// Security questions for password recovery
$security_questions = [
    "What was the name of your first pet?",
    "What was the make of your first car?",
    "What elementary school did you attend?",
    "What is the name of the town where you were born?",
    "What was your maternal grandmother's maiden name?",
    "What was the name of your first employer?",
    "What was your childhood nickname?",
    "What is the name of your favorite childhood friend?",
    "What was the street name you lived on in third grade?",
    "What was your favorite food as a child?"
];

/**
 * Sanitize output for HTML display
 * @param string $data Raw data
 * @return string Sanitized data
 */
function sanitizeOutput($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Generate a random username
 * @param string $firstName First name
 * @param string $lastName Last name
 * @return string Generated username
 */
function generateUsername($firstName, $lastName) {
    $firstPart = strtoupper(substr($firstName, 0, 3));
    $lastPart = strtoupper(substr($lastName, 0, 3));
    $numbers = sprintf("%04d", rand(1000, 9999));
    return $firstPart . $lastPart . $numbers;
}

/**
 * Validate password strength
 * @param string $password Password to validate
 * @return array Validation result with status and message
 */
function validatePassword($password) {
    $errors = [];
    
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        $errors[] = "Password must be at least " . PASSWORD_MIN_LENGTH . " characters long";
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    }
    
    if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
        $errors[] = "Password must contain at least one special character";
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * Format date for display
 * @param string $date Date string
 * @param string $format Output format
 * @return string Formatted date
 */
function formatDate($date, $format = 'M j, Y') {
    if (empty($date) || $date === '0000-00-00') {
        return 'Not set';
    }
    return date($format, strtotime($date));
}

/**
 * Get user role display name
 * @param string $role User role
 * @return string Display name
 */
function getRoleDisplayName($role) {
    $roles = [
        'client' => 'Client',
        'staff' => 'Staff Member',
        'outreach' => 'Outreach Worker',
        'admin' => 'Administrator',
        'service_provider' => 'Service Provider'
    ];
    
    return $roles[$role] ?? 'Unknown Role';
}

/**
 * Log user activity for audit trail
 * @param int $userId User ID
 * @param string $action Action performed
 * @param string $tableName Table affected
 * @param int $recordId Record ID affected
 * @param array $oldValues Old values (for updates)
 * @param array $newValues New values
 */
function logActivity($userId, $action, $tableName = null, $recordId = null, $oldValues = null, $newValues = null) {
    try {
        require_once 'database.php';
        
        $query = "INSERT INTO audit_log (user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $userId,
            $action,
            $tableName,
            $recordId,
            $oldValues ? json_encode($oldValues) : null,
            $newValues ? json_encode($newValues) : null,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        executeQuery($query, $params, 'ississss');
        
    } catch (Exception $e) {
        error_log("Failed to log activity: " . $e->getMessage());
    }
}

/**
 * Check if user has permission for specific action
 * @param string $userRole User's role
 * @param string $action Action to check
 * @param string $resource Resource being accessed
 * @return bool True if permitted
 */
function hasPermission($userRole, $action, $resource = null) {
    $permissions = [
        'admin' => ['*'], // Admin has all permissions
        'staff' => ['view_cases', 'create_cases', 'edit_cases', 'view_clients', 'create_appointments', 'send_messages'],
        'outreach' => ['view_cases', 'create_cases', 'view_clients', 'create_appointments'],
        'service_provider' => ['view_assigned_cases', 'view_referrals'],
        'client' => ['view_own_profile', 'edit_own_profile', 'view_own_cases', 'view_messages']
    ];
    
    $userPermissions = $permissions[$userRole] ?? [];
    
    return in_array('*', $userPermissions) || in_array($action, $userPermissions);
}

// Error reporting for development - disable in production
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'error.log');
?>