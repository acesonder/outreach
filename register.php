<?php
/**
 * User Registration Handler
 */

require_once 'includes/config.php';

// Start session
startSecureSession();

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php', 'Invalid request method.', 'error');
}

// Get form data
$firstName = sanitizeInput($_POST['first_name'] ?? '');
$lastName = sanitizeInput($_POST['last_name'] ?? '');
$email = sanitizeInput($_POST['email'] ?? '');
$phone = sanitizeInput($_POST['phone'] ?? '');
$dateOfBirth = sanitizeInput($_POST['date_of_birth'] ?? '');
$securityQuestionId = (int)($_POST['security_question_id'] ?? 0);
$securityAnswer = sanitizeInput($_POST['security_answer'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';
$termsAgreement = isset($_POST['terms_agreement']);

// Validation
$errors = [];

if (empty($firstName)) {
    $errors[] = 'First name is required.';
}

if (empty($lastName)) {
    $errors[] = 'Last name is required.';
}

if (empty($dateOfBirth)) {
    $errors[] = 'Date of birth is required.';
} else {
    $dob = new DateTime($dateOfBirth);
    $now = new DateTime();
    $age = $now->diff($dob)->y;
    if ($age < 16) {
        $errors[] = 'You must be at least 16 years old to register.';
    }
}

if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
}

if ($securityQuestionId < 1) {
    $errors[] = 'Please select a security question.';
}

if (empty($securityAnswer)) {
    $errors[] = 'Security answer is required.';
}

if (empty($password)) {
    $errors[] = 'Password is required.';
} elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
    $errors[] = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long.';
} elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/', $password)) {
    $errors[] = 'Password must contain uppercase, lowercase, number, and special character.';
}

if ($password !== $confirmPassword) {
    $errors[] = 'Passwords do not match.';
}

if (!$termsAgreement) {
    $errors[] = 'You must agree to the Terms of Service and Privacy Policy.';
}

if (!empty($errors)) {
    $_SESSION['form_errors'] = $errors;
    $_SESSION['form_data'] = $_POST;
    redirect('index.php', 'Please correct the errors and try again.', 'error');
}

try {
    $pdo = getDbConnection();
    
    // Generate username
    $username = generateUsername($firstName, $lastName, $dateOfBirth);
    
    // Make sure username is unique
    $counter = 1;
    $originalUsername = $username;
    while (usernameExists($username)) {
        $username = $originalUsername . sprintf('%02d', $counter);
        $counter++;
    }
    
    // Hash password and security answer
    $passwordHash = hashPassword($password);
    $securityAnswerHash = hashPassword(strtolower(trim($securityAnswer)));
    
    // Begin transaction
    $pdo->beginTransaction();
    
    // Insert user
    $stmt = $pdo->prepare("
        INSERT INTO users (username, first_name, last_name, email, phone, date_of_birth, 
                          password_hash, security_question_id, security_answer_hash, role, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'client', 'active')
    ");
    
    $stmt->execute([
        $username,
        $firstName,
        $lastName,
        $email ?: null,
        $phone ?: null,
        $dateOfBirth,
        $passwordHash,
        $securityQuestionId,
        $securityAnswerHash
    ]);
    
    $userId = $pdo->lastInsertId();
    
    // Create client profile
    $stmt = $pdo->prepare("
        INSERT INTO client_profiles (user_id, immediate_needs) 
        VALUES (?, ?)
    ");
    $stmt->execute([$userId, json_encode([])]);
    
    // Log registration
    logActivity('user_registered', 'users', $userId, null, [
        'username' => $username,
        'first_name' => $firstName,
        'last_name' => $lastName
    ]);
    
    // Commit transaction
    $pdo->commit();
    
    // Auto-login the user
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $username;
    $_SESSION['role'] = 'client';
    
    // Update last login
    $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
    $stmt->execute([$userId]);
    
    // Show welcome message with username
    $welcomeMessage = "Welcome to OUTSINC! Your username is: <strong>$username</strong> - please write this down. You'll need it to log in.";
    redirect('intake.php', $welcomeMessage, 'success');
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollback();
    }
    
    error_log("Registration error: " . $e->getMessage());
    
    // Check for duplicate username error
    if ($e->getCode() == 23000) {
        redirect('index.php', 'Username already exists. Please try again.', 'error');
    } else {
        redirect('index.php', 'Registration failed. Please try again later.', 'error');
    }
}
?>