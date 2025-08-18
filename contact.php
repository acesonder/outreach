<?php
/**
 * Contact Form Handler
 */

require_once 'includes/config.php';

// Start session
startSecureSession();

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php#contact', 'Invalid request method.', 'error');
}

// Get form data
$name = sanitizeInput($_POST['name'] ?? '');
$email = sanitizeInput($_POST['email'] ?? '');
$message = sanitizeInput($_POST['message'] ?? '');

// Validation
$errors = [];

if (empty($name)) {
    $errors[] = 'Name is required.';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Valid email address is required.';
}

if (empty($message)) {
    $errors[] = 'Message is required.';
}

if (!empty($errors)) {
    $_SESSION['contact_errors'] = $errors;
    redirect('index.php#contact', 'Please correct the errors and try again.', 'error');
}

try {
    $pdo = getDbConnection();
    
    // Insert contact message (you might want to create a contacts table)
    $stmt = $pdo->prepare("
        INSERT INTO messages (sender_id, recipient_id, subject, message_content, message_type, created_at) 
        VALUES (NULL, 1, ?, ?, 'contact_form', NOW())
    ");
    
    $subject = "Contact Form: Message from " . $name;
    $fullMessage = "Name: " . $name . "\n";
    $fullMessage .= "Email: " . $email . "\n\n";
    $fullMessage .= "Message:\n" . $message;
    
    $stmt->execute([$subject, $fullMessage]);
    
    // Log contact form submission
    logActivity('contact_form_submitted', null, null, null, [
        'name' => $name,
        'email' => $email
    ]);
    
    // In a real application, you might want to:
    // 1. Send email notification to admin
    // 2. Send confirmation email to sender
    // 3. Store in a dedicated contacts table
    
    redirect('index.php#contact', 'Thank you for your message! We will get back to you soon.', 'success');
    
} catch (PDOException $e) {
    error_log("Contact form error: " . $e->getMessage());
    redirect('index.php#contact', 'Message sending failed. Please try again later.', 'error');
}
?>