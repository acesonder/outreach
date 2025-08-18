<?php
/**
 * Forgot Password Handler for OUTSINC
 * Handles password reset using security questions
 */

require_once 'includes/config.php';
require_once 'includes/auth.php';

$step = $_GET['step'] ?? 'username';
$username = $_GET['username'] ?? '';
$errorMessage = '';
$successMessage = '';

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === 'username') {
        // Step 1: Get username and show security question
        $username = trim($_POST['username'] ?? '');
        
        if (empty($username)) {
            $errorMessage = 'Please enter your username.';
        } else {
            $result = getSecurityQuestion($username);
            
            if ($result['success']) {
                header("Location: forgot-password.php?step=security&username=" . urlencode($username));
                exit;
            } else {
                $errorMessage = $result['message'];
            }
        }
    } elseif ($step === 'security') {
        // Step 2: Verify security answer and reset password
        $username = trim($_POST['username'] ?? '');
        $securityAnswer = trim($_POST['security_answer'] ?? '');
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validate inputs
        if (empty($username) || empty($securityAnswer) || empty($newPassword) || empty($confirmPassword)) {
            $errorMessage = 'Please fill in all fields.';
        } elseif ($newPassword !== $confirmPassword) {
            $errorMessage = 'Passwords do not match.';
        } else {
            // Validate password strength
            $passwordValidation = validatePassword($newPassword);
            if (!$passwordValidation['valid']) {
                $errorMessage = implode(', ', $passwordValidation['errors']);
            } else {
                // Attempt password reset
                $result = resetPassword($username, $securityAnswer, $newPassword);
                
                if ($result['success']) {
                    header('Location: index.php?message=password_reset_success');
                    exit;
                } else {
                    $errorMessage = $result['message'];
                }
            }
        }
    }
}

// Get security question for step 2
$securityQuestion = '';
if ($step === 'security' && !empty($username)) {
    $result = getSecurityQuestion($username);
    if ($result['success']) {
        $securityQuestion = $result['security_question'];
    } else {
        header('Location: forgot-password.php?step=username&error=' . urlencode($result['message']));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - OUTSINC</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="header">
        <div class="nav-container">
            <a href="index.php" class="logo">
                <i class="fas fa-heart"></i> OUTSINC
            </a>
            <nav>
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="index.php" class="nav-link">
                            <i class="fas fa-home"></i> Home
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="container" style="max-width: 500px; margin-top: var(--spacing-2xl);">
            <div class="card">
                <h1><i class="fas fa-key"></i> Reset Password</h1>
                
                <?php if ($errorMessage): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo sanitizeOutput($errorMessage); ?>
                </div>
                <?php endif; ?>

                <?php if ($step === 'username'): ?>
                <!-- Step 1: Enter Username -->
                <p>Enter your username to retrieve your security question.</p>
                
                <form method="POST" data-validate>
                    <input type="hidden" name="step" value="username">
                    
                    <div class="form-group">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" id="username" name="username" class="form-input" 
                               value="<?php echo sanitizeOutput($username); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-large" style="width: 100%;">
                            <i class="fas fa-arrow-right"></i> Continue
                        </button>
                    </div>
                </form>

                <?php elseif ($step === 'security'): ?>
                <!-- Step 2: Answer Security Question -->
                <p>Please answer your security question to reset your password.</p>
                
                <div style="background: var(--light-gray); padding: var(--spacing-md); border-radius: var(--radius-md); margin-bottom: var(--spacing-lg);">
                    <strong>Security Question:</strong><br>
                    <?php echo sanitizeOutput($securityQuestion); ?>
                </div>
                
                <form method="POST" data-validate>
                    <input type="hidden" name="step" value="security">
                    <input type="hidden" name="username" value="<?php echo sanitizeOutput($username); ?>">
                    
                    <div class="form-group">
                        <label for="security_answer" class="form-label">Your Answer</label>
                        <input type="text" id="security_answer" name="security_answer" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" id="new_password" name="new_password" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-large" style="width: 100%;">
                            <i class="fas fa-check"></i> Reset Password
                        </button>
                    </div>
                </form>
                <?php endif; ?>
                
                <div class="text-center mt-lg">
                    <a href="index.php">
                        <i class="fas fa-arrow-left"></i> Back to Login
                    </a>
                </div>
            </div>
        </div>
    </main>

    <script src="assets/js/main.js"></script>
</body>
</html>