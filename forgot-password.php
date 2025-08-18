<?php
/**
 * Forgot Password Handler
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

if (empty($username)) {
    redirect('index.php', 'Username is required.', 'error');
}

try {
    $pdo = getDbConnection();
    
    // Get user data
    $stmt = $pdo->prepare("
        SELECT u.user_id, u.username, u.first_name, u.last_name, u.email, u.security_question_id,
               sq.question_text
        FROM users u
        JOIN security_questions sq ON u.security_question_id = sq.question_id
        WHERE u.username = ? AND u.status = 'active'
    ");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if (!$user) {
        // Don't reveal if username exists or not
        redirect('index.php', 'If the username exists, you will see the security question.', 'info');
    }
    
    // Store user info in session for security question verification
    $_SESSION['password_reset_user'] = $user['user_id'];
    $_SESSION['password_reset_username'] = $user['username'];
    
    $securityQuestion = $user['question_text'];
    
} catch (PDOException $e) {
    error_log("Password reset error: " . $e->getMessage());
    redirect('index.php', 'Password reset failed. Please try again later.', 'error');
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
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="row items-center justify-between">
                <div class="col-auto">
                    <a href="index.php" class="navbar-brand">
                        <i class="fas fa-hands-helping"></i>
                        OUTSINC
                    </a>
                </div>
                <div class="col-auto">
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Back to Home
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container py-5">
        <div class="row">
            <div class="col-6 mx-auto">
                <div class="neu-card">
                    <div class="text-center mb-4">
                        <h2>
                            <i class="fas fa-key text-primary"></i>
                            Reset Password
                        </h2>
                        <p class="lead">Answer your security question to reset your password</p>
                    </div>

                    <div class="alert alert-info">
                        <strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?>
                    </div>

                    <form method="POST" action="reset-password.php">
                        <input type="hidden" name="username" value="<?php echo htmlspecialchars($user['username']); ?>">
                        
                        <div class="form-group">
                            <label class="form-label">Security Question</label>
                            <div class="form-control" style="background: var(--light-gray); cursor: not-allowed;">
                                <?php echo htmlspecialchars($securityQuestion); ?>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="security_answer" class="form-label">Your Answer *</label>
                            <input type="text" id="security_answer" name="security_answer" class="form-control" required 
                                   placeholder="Enter your answer exactly as you provided it during registration">
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password" class="form-label">New Password *</label>
                            <input type="password" id="new_password" name="new_password" class="form-control" required>
                            <div id="password-strength" class="mt-1"></div>
                            <small class="text-muted">
                                Password must be at least 8 characters with uppercase, lowercase, number, and special character.
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password" class="form-label">Confirm New Password *</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                        </div>
                        
                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i>
                                Reset Password
                            </button>
                        </div>
                        
                        <div class="text-center">
                            <a href="index.php">Remember your password? Log in here</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="assets/js/main.js"></script>
</body>
</html>