<?php
/**
 * Client Dashboard for OUTSINC
 * Personal dashboard for registered clients
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';

// Require client login
requireLogin('../index.php');
requireRole('client', '../index.php');

$currentUser = getCurrentUser();

// Handle success messages
$successMessage = '';
if (isset($_GET['message'])) {
    switch ($_GET['message']) {
        case 'intake_completed':
            $successMessage = 'Intake form completed successfully! Thank you for providing this information.';
            break;
    }
}

// Get client profile data
$clientProfile = [];
try {
    $query = "SELECT * FROM client_profiles WHERE user_id = ?";
    $result = executeQuery($query, [$currentUser['user_id']], 'i');
    if ($result->num_rows > 0) {
        $clientProfile = $result->fetch_assoc();
    }
} catch (Exception $e) {
    error_log("Error fetching client profile: " . $e->getMessage());
}

// Get intake form completion status
$intakeCompleted = false;
try {
    $query = "SELECT completion_status FROM intake_forms WHERE user_id = ? AND form_type = 'Basic'";
    $result = executeQuery($query, [$currentUser['user_id']], 'i');
    if ($result->num_rows > 0) {
        $intake = $result->fetch_assoc();
        $intakeCompleted = $intake['completion_status'] === 'Completed';
    }
} catch (Exception $e) {
    error_log("Error checking intake status: " . $e->getMessage());
}

// Get client's cases
$cases = [];
try {
    $query = "SELECT c.*, CONCAT(u.first_name, ' ', u.last_name) as worker_name 
              FROM cases c 
              LEFT JOIN users u ON c.assigned_worker_id = u.user_id 
              WHERE c.client_id = ? 
              ORDER BY c.created_at DESC";
    $result = executeQuery($query, [$currentUser['user_id']], 'i');
    while ($row = $result->fetch_assoc()) {
        $cases[] = $row;
    }
} catch (Exception $e) {
    error_log("Error fetching cases: " . $e->getMessage());
}

// Get upcoming appointments
$appointments = [];
try {
    $query = "SELECT a.*, CONCAT(u.first_name, ' ', u.last_name) as staff_name 
              FROM appointments a 
              JOIN users u ON a.staff_id = u.user_id 
              WHERE a.client_id = ? AND a.appointment_date >= CURDATE() 
              ORDER BY a.appointment_date, a.appointment_time";
    $result = executeQuery($query, [$currentUser['user_id']], 'i');
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
} catch (Exception $e) {
    error_log("Error fetching appointments: " . $e->getMessage());
}

// Get recent messages
$messages = [];
try {
    $query = "SELECT m.*, CONCAT(u.first_name, ' ', u.last_name) as sender_name 
              FROM messages m 
              JOIN users u ON m.sender_id = u.user_id 
              WHERE m.recipient_id = ? 
              ORDER BY m.created_at DESC 
              LIMIT 5";
    $result = executeQuery($query, [$currentUser['user_id']], 'i');
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
} catch (Exception $e) {
    error_log("Error fetching messages: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard - OUTSINC</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="header">
        <div class="nav-container">
            <a href="../index.php" class="logo">
                <i class="fas fa-heart"></i> OUTSINC
            </a>
            <nav>
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="intake.php" class="nav-link">
                            <i class="fas fa-clipboard-list"></i> Intake Form
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="profile.php" class="nav-link">
                            <i class="fas fa-user"></i> Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../index.php?action=logout" class="nav-link">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </nav>
            
            <button class="mobile-menu-toggle">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </header>

    <main>
        <div class="container" style="margin-top: var(--spacing-xl);">
            <!-- Welcome Section -->
            <div class="section">
                <h1>Welcome back, <?php echo sanitizeOutput($currentUser['first_name']); ?>!</h1>
                <p>Here's an overview of your account and available services.</p>
            </div>

            <!-- Success Message -->
            <?php if ($successMessage): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo sanitizeOutput($successMessage); ?>
            </div>
            <?php endif; ?>

            <!-- Intake Status Alert -->
            <?php if (!$intakeCompleted): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Action Required:</strong> Please complete your intake form to help us serve you better.
                <a href="intake.php" class="btn btn-small btn-primary" style="margin-left: var(--spacing-md);">
                    <i class="fas fa-clipboard-list"></i> Complete Intake
                </a>
            </div>
            <?php endif; ?>

            <!-- Dashboard Grid -->
            <div class="grid grid-3">
                <!-- Quick Actions -->
                <div class="card">
                    <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
                    <div style="display: flex; flex-direction: column; gap: var(--spacing-sm);">
                        <a href="intake.php" class="btn btn-primary">
                            <i class="fas fa-clipboard-list"></i> 
                            <?php echo $intakeCompleted ? 'Update' : 'Complete'; ?> Intake Form
                        </a>
                        <a href="profile.php" class="btn btn-secondary">
                            <i class="fas fa-user-edit"></i> Edit Profile
                        </a>
                        <a href="#" class="btn btn-secondary" data-modal="messageModal">
                            <i class="fas fa-envelope"></i> Send Message
                        </a>
                        <a href="#" class="btn btn-secondary">
                            <i class="fas fa-calendar-plus"></i> Request Appointment
                        </a>
                    </div>
                </div>

                <!-- My Cases -->
                <div class="card">
                    <h3><i class="fas fa-folder-open"></i> My Cases</h3>
                    <?php if (empty($cases)): ?>
                    <p style="color: var(--dark-gray); margin: var(--spacing-md) 0;">
                        No cases yet. Your case worker will create cases as needed.
                    </p>
                    <?php else: ?>
                    <div style="max-height: 200px; overflow-y: auto;">
                        <?php foreach (array_slice($cases, 0, 3) as $case): ?>
                        <div style="padding: var(--spacing-sm); border-bottom: 1px solid var(--medium-gray); margin-bottom: var(--spacing-sm);">
                            <strong><?php echo sanitizeOutput($case['case_title']); ?></strong>
                            <div style="display: flex; gap: var(--spacing-sm); margin-top: var(--spacing-xs);">
                                <span class="badge badge-<?php echo strtolower($case['priority_level']) === 'high' ? 'danger' : (strtolower($case['priority_level']) === 'medium' ? 'warning' : 'info'); ?>">
                                    <?php echo sanitizeOutput($case['priority_level']); ?>
                                </span>
                                <span class="badge badge-primary">
                                    <?php echo sanitizeOutput($case['status']); ?>
                                </span>
                            </div>
                            <?php if ($case['worker_name']): ?>
                            <small style="color: var(--dark-gray);">Worker: <?php echo sanitizeOutput($case['worker_name']); ?></small>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($cases) > 3): ?>
                    <a href="cases.php" class="btn btn-small btn-secondary">View All Cases</a>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- Profile Completion -->
                <div class="card">
                    <h3><i class="fas fa-user-check"></i> Profile Status</h3>
                    <?php
                    $profileFields = [
                        'preferred_name', 'gender_identity', 'pronouns', 
                        'emergency_contact_name', 'living_situation', 'employment_status'
                    ];
                    $completedFields = 0;
                    foreach ($profileFields as $field) {
                        if (!empty($clientProfile[$field])) {
                            $completedFields++;
                        }
                    }
                    $completionPercentage = ($completedFields / count($profileFields)) * 100;
                    ?>
                    <div style="margin: var(--spacing-md) 0;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: var(--spacing-xs);">
                            <span>Completion</span>
                            <span><strong><?php echo round($completionPercentage); ?>%</strong></span>
                        </div>
                        <div style="background: var(--medium-gray); height: 10px; border-radius: 5px; overflow: hidden;">
                            <div style="background: var(--primary-teal); height: 100%; width: <?php echo $completionPercentage; ?>%; transition: width 0.3s ease;"></div>
                        </div>
                    </div>
                    <p style="font-size: var(--font-size-sm); color: var(--dark-gray);">
                        Complete your profile to help us provide better support.
                    </p>
                    <a href="profile.php" class="btn btn-small btn-primary">Update Profile</a>
                </div>
            </div>

            <!-- Two Column Layout -->
            <div class="grid grid-2" style="margin-top: var(--spacing-xl);">
                <!-- Upcoming Appointments -->
                <div class="card">
                    <h3><i class="fas fa-calendar-alt"></i> Upcoming Appointments</h3>
                    <?php if (empty($appointments)): ?>
                    <p style="color: var(--dark-gray);">No upcoming appointments scheduled.</p>
                    <?php else: ?>
                    <div style="max-height: 300px; overflow-y: auto;">
                        <?php foreach ($appointments as $appointment): ?>
                        <div style="padding: var(--spacing-sm); border-bottom: 1px solid var(--medium-gray); margin-bottom: var(--spacing-sm);">
                            <div style="display: flex; justify-content: space-between; align-items: start;">
                                <div>
                                    <strong><?php echo sanitizeOutput($appointment['appointment_type']); ?></strong>
                                    <div style="color: var(--dark-gray); font-size: var(--font-size-sm);">
                                        <i class="fas fa-user"></i> <?php echo sanitizeOutput($appointment['staff_name']); ?>
                                    </div>
                                    <?php if ($appointment['location']): ?>
                                    <div style="color: var(--dark-gray); font-size: var(--font-size-sm);">
                                        <i class="fas fa-map-marker-alt"></i> <?php echo sanitizeOutput($appointment['location']); ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-weight: 600;"><?php echo formatDate($appointment['appointment_date']); ?></div>
                                    <div style="color: var(--dark-gray); font-size: var(--font-size-sm);">
                                        <?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Recent Messages -->
                <div class="card">
                    <h3><i class="fas fa-envelope"></i> Recent Messages</h3>
                    <?php if (empty($messages)): ?>
                    <p style="color: var(--dark-gray);">No messages yet.</p>
                    <?php else: ?>
                    <div style="max-height: 300px; overflow-y: auto;">
                        <?php foreach ($messages as $message): ?>
                        <div style="padding: var(--spacing-sm); border-bottom: 1px solid var(--medium-gray); margin-bottom: var(--spacing-sm);">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: var(--spacing-xs);">
                                <strong style="font-size: var(--font-size-sm);">
                                    From: <?php echo sanitizeOutput($message['sender_name']); ?>
                                </strong>
                                <span style="font-size: var(--font-size-sm); color: var(--dark-gray);">
                                    <?php echo formatDate($message['created_at'], 'short'); ?>
                                </span>
                            </div>
                            <?php if ($message['subject']): ?>
                            <div style="font-weight: 600; margin-bottom: var(--spacing-xs);">
                                <?php echo sanitizeOutput($message['subject']); ?>
                            </div>
                            <?php endif; ?>
                            <p style="margin: 0; font-size: var(--font-size-sm); color: var(--dark-gray);">
                                <?php echo sanitizeOutput(substr($message['message_body'], 0, 100)); ?>
                                <?php if (strlen($message['message_body']) > 100): ?>...<?php endif; ?>
                            </p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <a href="messages.php" class="btn btn-small btn-secondary">View All Messages</a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Platform Access -->
            <div class="section">
                <h2 class="section-title">Access Our Platforms</h2>
                <div class="grid grid-3">
                    <?php 
                    $clientPlatforms = [
                        'dcide' => $platforms['dcide'],
                        'link' => $platforms['link'], 
                        'ask' => $platforms['ask'],
                        'ethan' => $platforms['ethan']
                    ];
                    foreach ($clientPlatforms as $key => $platform): 
                    ?>
                    <div class="card platform-card" data-platform="<?php echo $key; ?>">
                        <div class="card-icon" style="background-color: <?php echo $platform['color']; ?>">
                            <i class="<?php echo $platform['icon']; ?>"></i>
                        </div>
                        <h3 class="card-title"><?php echo $platform['name']; ?></h3>
                        <p class="card-description"><?php echo $platform['description']; ?></p>
                        <a href="../platforms/<?php echo $key; ?>.php" class="btn btn-primary">
                            <i class="fas fa-external-link-alt"></i> Access Platform
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Message Modal -->
    <div id="messageModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Send Message to Staff</h3>
                <button class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="send_message.php" method="POST" data-validate>
                <div class="form-group">
                    <label for="message_subject" class="form-label">Subject</label>
                    <input type="text" id="message_subject" name="subject" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label for="message_body" class="form-label">Message</label>
                    <textarea id="message_body" name="message_body" class="form-textarea" rows="5" required></textarea>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-large" style="width: 100%;">
                        <i class="fas fa-paper-plane"></i> Send Message
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>