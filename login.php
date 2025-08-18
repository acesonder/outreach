<?php
/**
 * Login processing for OUTSINC
 * Handles user authentication
 */

require_once 'includes/config.php';
require_once 'includes/auth.php';

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Get form data
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// Validate input
if (empty($username) || empty($password)) {
    header('Location: index.php?error=invalid_credentials');
    exit;
}

try {
    // Attempt authentication
    $result = authenticateUser($username, $password);
    
    if ($result['success']) {
        // Redirect based on user role
        $user = $result['user'];
        
        switch ($user['role']) {
            case 'admin':
                $redirectUrl = 'admin/dashboard.php';
                break;
            case 'staff':
            case 'outreach':
                $redirectUrl = 'staff/dashboard.php';
                break;
            case 'service_provider':
                $redirectUrl = 'provider/dashboard.php';
                break;
            case 'client':
            default:
                $redirectUrl = 'client/dashboard.php';
                break;
        }
        
        // Check if user needs to complete intake
        if ($user['role'] === 'client') {
            $query = "SELECT COUNT(*) as completed FROM intake_forms 
                      WHERE user_id = ? AND completion_status = 'Completed'";
            $result = executeQuery($query, [$user['user_id']], 'i');
            $intakeData = $result->fetch_assoc();
            
            if ($intakeData['completed'] == 0) {
                $redirectUrl = 'client/intake.php?welcome=1';
            }
        }
        
        header("Location: $redirectUrl");
        exit;
        
    } else {
        // Login failed
        header('Location: index.php?error=login_failed&message=' . urlencode($result['message']));
        exit;
    }
    
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    header('Location: index.php?error=system_error');
    exit;
}
?>