<?php
/**
 * Dashboard - Role-based landing page after login
 */

require_once 'includes/config.php';

// Start session and require login
startSecureSession();
requireLogin();

$currentUser = getCurrentUser();
if (!$currentUser) {
    redirect('index.php', 'Session expired. Please log in again.', 'warning');
}

// Get flash message
$flashMessage = getFlashMessage();

try {
    $pdo = getDbConnection();
    
    // Get user's profile data
    $stmt = $pdo->prepare("
        SELECT cp.*, u.email, u.phone 
        FROM client_profiles cp 
        JOIN users u ON cp.user_id = u.user_id 
        WHERE cp.user_id = ?
    ");
    $stmt->execute([$currentUser['user_id']]);
    $profile = $stmt->fetch();
    
    // Get intake status
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as completed_intakes 
        FROM intakes 
        WHERE user_id = ? AND status = 'completed'
    ");
    $stmt->execute([$currentUser['user_id']]);
    $intakeStatus = $stmt->fetch();
    
    // Get user's cases
    $stmt = $pdo->prepare("
        SELECT case_id, case_title, case_type, priority_level, status, date_opened,
               CONCAT(u.first_name, ' ', u.last_name) as assigned_worker
        FROM cases c
        LEFT JOIN users u ON c.assigned_worker_id = u.user_id
        WHERE c.client_id = ?
        ORDER BY c.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$currentUser['user_id']]);
    $cases = $stmt->fetchAll();
    
    // Get upcoming appointments
    $stmt = $pdo->prepare("
        SELECT a.appointment_id, a.appointment_type, a.appointment_date, a.appointment_time,
               a.location, a.status, CONCAT(u.first_name, ' ', u.last_name) as staff_name
        FROM appointments a
        LEFT JOIN users u ON a.staff_id = u.user_id
        WHERE a.client_id = ? AND a.appointment_date >= CURDATE()
        ORDER BY a.appointment_date, a.appointment_time
        LIMIT 5
    ");
    $stmt->execute([$currentUser['user_id']]);
    $appointments = $stmt->fetchAll();
    
    // Get recent messages
    $stmt = $pdo->prepare("
        SELECT m.message_id, m.subject, m.message_content, m.created_at, m.is_read,
               CONCAT(u.first_name, ' ', u.last_name) as sender_name
        FROM messages m
        JOIN users u ON m.sender_id = u.user_id
        WHERE m.recipient_id = ?
        ORDER BY m.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$currentUser['user_id']]);
    $messages = $stmt->fetchAll();
    
    // Get tasks assigned to user
    $stmt = $pdo->prepare("
        SELECT t.task_id, t.task_title, t.task_description, t.priority, t.status, t.due_date,
               c.case_title
        FROM tasks t
        LEFT JOIN cases c ON t.case_id = c.case_id
        WHERE t.assigned_to = ? AND t.status != 'completed'
        ORDER BY t.due_date ASC
        LIMIT 5
    ");
    $stmt->execute([$currentUser['user_id']]);
    $tasks = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $profile = null;
    $intakeStatus = ['completed_intakes' => 0];
    $cases = [];
    $appointments = [];
    $messages = [];
    $tasks = [];
}

// Dashboard data based on user role
$dashboardData = [];
switch ($currentUser['role']) {
    case 'client':
        $dashboardData = [
            'welcome_message' => "Welcome back, " . htmlspecialchars($currentUser['first_name']) . "!",
            'show_intake_reminder' => $intakeStatus['completed_intakes'] == 0,
            'platform_access' => ['ETHAN', 'ASK', 'LINK'],
            'primary_actions' => [
                ['title' => 'Complete Intake', 'url' => 'intake.php', 'icon' => 'fas fa-clipboard-list', 'color' => 'primary'],
                ['title' => 'View My Cases', 'url' => 'cases.php', 'icon' => 'fas fa-folder-open', 'color' => 'success'],
                ['title' => 'Send Message', 'url' => 'messages.php?action=compose', 'icon' => 'fas fa-envelope', 'color' => 'info'],
                ['title' => 'Book Appointment', 'url' => 'appointments.php?action=book', 'icon' => 'fas fa-calendar-plus', 'color' => 'warning']
            ]
        ];
        break;
        
    case 'staff':
        $dashboardData = [
            'welcome_message' => "Welcome, " . htmlspecialchars($currentUser['first_name']) . " (Staff)",
            'platform_access' => ['DCIDE', 'LINK', 'ASK'],
            'primary_actions' => [
                ['title' => 'Create New Case', 'url' => 'cases.php?action=create', 'icon' => 'fas fa-plus-circle', 'color' => 'primary'],
                ['title' => 'Client List', 'url' => 'clients.php', 'icon' => 'fas fa-users', 'color' => 'success'],
                ['title' => 'Pending Intakes', 'url' => 'intakes.php?status=pending', 'icon' => 'fas fa-clipboard-check', 'color' => 'warning'],
                ['title' => 'Schedule Appointments', 'url' => 'appointments.php', 'icon' => 'fas fa-calendar', 'color' => 'info']
            ]
        ];
        break;
        
    case 'outreach':
        $dashboardData = [
            'welcome_message' => "Welcome, " . htmlspecialchars($currentUser['first_name']) . " (Outreach)",
            'platform_access' => ['FOOTPRINT', 'DCIDE', 'ASK'],
            'primary_actions' => [
                ['title' => 'Log Visit', 'url' => 'visits.php?action=log', 'icon' => 'fas fa-map-marker-alt', 'color' => 'primary'],
                ['title' => 'Order Supplies', 'url' => 'supplies.php?action=order', 'icon' => 'fas fa-boxes', 'color' => 'success'],
                ['title' => 'Report Incident', 'url' => 'incidents.php?action=report', 'icon' => 'fas fa-exclamation-triangle', 'color' => 'danger'],
                ['title' => 'Client Check-ins', 'url' => 'checkins.php', 'icon' => 'fas fa-user-check', 'color' => 'info']
            ]
        ];
        break;
        
    case 'admin':
        $dashboardData = [
            'welcome_message' => "Welcome, " . htmlspecialchars($currentUser['first_name']) . " (Administrator)",
            'platform_access' => ['DCIDE', 'LINK', 'ASK', 'ETHAN', 'BLES', 'FOOTPRINT'],
            'primary_actions' => [
                ['title' => 'User Management', 'url' => 'admin/users.php', 'icon' => 'fas fa-users-cog', 'color' => 'primary'],
                ['title' => 'System Reports', 'url' => 'admin/reports.php', 'icon' => 'fas fa-chart-bar', 'color' => 'success'],
                ['title' => 'Settings', 'url' => 'admin/settings.php', 'icon' => 'fas fa-cogs', 'color' => 'info'],
                ['title' => 'Audit Logs', 'url' => 'admin/audit.php', 'icon' => 'fas fa-history', 'color' => 'warning']
            ]
        ];
        break;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - OUTSINC</title>
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
                    <ul class="navbar-nav d-flex items-center">
                        <li><a href="dashboard.php" class="nav-link active">Dashboard</a></li>
                        
                        <?php if (in_array($currentUser['role'], ['staff', 'admin'])): ?>
                            <li><a href="clients.php" class="nav-link">Clients</a></li>
                            <li><a href="cases.php" class="nav-link">Cases</a></li>
                        <?php endif; ?>
                        
                        <?php if ($currentUser['role'] === 'client'): ?>
                            <li><a href="cases.php" class="nav-link">My Cases</a></li>
                        <?php endif; ?>
                        
                        <li><a href="messages.php" class="nav-link">Messages</a></li>
                        <li><a href="appointments.php" class="nav-link">Appointments</a></li>
                        
                        <li class="dropdown">
                            <a href="#" class="nav-link dropdown-toggle">
                                <i class="fas fa-user"></i>
                                <?php echo htmlspecialchars($currentUser['first_name']); ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="profile.php" class="dropdown-link">My Profile</a></li>
                                <li><a href="settings.php" class="dropdown-link">Settings</a></li>
                                <?php if ($currentUser['role'] === 'admin'): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a href="admin/" class="dropdown-link">Admin Panel</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a href="logout.php" class="dropdown-link">Logout</a></li>
                            </ul>
                        </li>
                        
                        <li>
                            <label class="theme-switch">
                                <input type="checkbox" id="theme-toggle">
                                <span class="slider">
                                    <i class="fas fa-sun"></i>
                                    <i class="fas fa-moon"></i>
                                </span>
                            </label>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Alert Container -->
    <div id="alert-container" class="alert-container">
        <?php if ($flashMessage): ?>
            <div class="alert alert-<?php echo $flashMessage['type']; ?>">
                <?php echo $flashMessage['message']; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Main Content -->
    <div class="container py-4">
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="hero" style="padding: 2rem 0; border-radius: var(--radius-xl);">
                    <div class="hero-content">
                        <h1><?php echo $dashboardData['welcome_message']; ?></h1>
                        <p>
                            Username: <strong><?php echo htmlspecialchars($currentUser['username']); ?></strong> | 
                            Role: <strong><?php echo ucfirst($currentUser['role']); ?></strong> |
                            Last Login: <strong><?php echo $currentUser['last_login'] ? formatDate($currentUser['last_login'], 'M j, Y g:i A') : 'First time'; ?></strong>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <?php if (isset($dashboardData['show_intake_reminder']) && $dashboardData['show_intake_reminder']): ?>
            <!-- Intake Reminder -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-warning">
                        <h4><i class="fas fa-clipboard-list"></i> Complete Your Intake</h4>
                        <p>Welcome to OUTSINC! To get started and receive the best support possible, please complete your intake form.</p>
                        <a href="intake.php" class="btn btn-primary">
                            <i class="fas fa-arrow-right"></i>
                            Start Intake Form
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
                <div class="platform-grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));">
                    <?php foreach ($dashboardData['primary_actions'] as $action): ?>
                        <div class="glass-card text-center animate-on-scroll">
                            <i class="<?php echo $action['icon']; ?> fa-3x mb-3" style="color: var(--<?php echo $action['color'] === 'primary' ? 'primary-teal' : ($action['color'] === 'success' ? 'dcide-green' : ($action['color'] === 'info' ? 'ask-blue' : ($action['color'] === 'warning' ? 'bles-orange' : 'danger'))); ?>);"></i>
                            <h5><?php echo $action['title']; ?></h5>
                            <a href="<?php echo $action['url']; ?>" class="btn btn-<?php echo $action['color']; ?>">
                                Go <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Dashboard Content Grid -->
        <div class="row">
            <!-- Left Column -->
            <div class="col-8">
                <!-- My Cases / Recent Cases -->
                <div class="neu-card mb-4">
                    <h4>
                        <i class="fas fa-folder-open"></i>
                        <?php echo $currentUser['role'] === 'client' ? 'My Cases' : 'Recent Cases'; ?>
                    </h4>
                    
                    <?php if (empty($cases)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-folder fa-3x mb-3" style="color: var(--medium-gray);"></i>
                            <p>No cases found.</p>
                            <?php if (in_array($currentUser['role'], ['staff', 'admin'])): ?>
                                <a href="cases.php?action=create" class="btn btn-primary">
                                    <i class="fas fa-plus"></i>
                                    Create New Case
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Case</th>
                                        <th>Type</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Opened</th>
                                        <?php if ($currentUser['role'] === 'client'): ?>
                                            <th>Worker</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cases as $case): ?>
                                        <tr>
                                            <td>
                                                <a href="cases.php?id=<?php echo $case['case_id']; ?>">
                                                    <?php echo htmlspecialchars($case['case_title']); ?>
                                                </a>
                                            </td>
                                            <td>
                                                <span class="badge badge-secondary">
                                                    <?php echo ucwords(str_replace('_', ' ', $case['case_type'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?php 
                                                    echo $case['priority_level'] === 'critical' ? 'danger' : 
                                                        ($case['priority_level'] === 'high' ? 'warning' : 
                                                        ($case['priority_level'] === 'medium' ? 'info' : 'secondary')); 
                                                ?>">
                                                    <?php echo ucfirst($case['priority_level']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?php 
                                                    echo $case['status'] === 'resolved' ? 'success' : 
                                                        ($case['status'] === 'in_progress' ? 'primary' : 'secondary'); 
                                                ?>">
                                                    <?php echo ucwords(str_replace('_', ' ', $case['status'])); ?>
                                                </span>
                                            </td>
                                            <td><?php echo formatDate($case['date_opened'], 'M j, Y'); ?></td>
                                            <?php if ($currentUser['role'] === 'client'): ?>
                                                <td><?php echo htmlspecialchars($case['assigned_worker'] ?: 'Unassigned'); ?></td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="text-center">
                            <a href="cases.php" class="btn btn-secondary">
                                View All Cases <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Tasks -->
                <?php if (!empty($tasks)): ?>
                    <div class="neu-card mb-4">
                        <h4><i class="fas fa-tasks"></i> My Tasks</h4>
                        
                        <div class="task-list">
                            <?php foreach ($tasks as $task): ?>
                                <div class="glass-card mb-2">
                                    <div class="row items-center">
                                        <div class="col-1">
                                            <input type="checkbox" class="checkbox" onchange="toggleTask(<?php echo $task['task_id']; ?>)">
                                        </div>
                                        <div class="col-8">
                                            <h6><?php echo htmlspecialchars($task['task_title']); ?></h6>
                                            <?php if ($task['task_description']): ?>
                                                <p class="text-muted mb-0"><?php echo htmlspecialchars($task['task_description']); ?></p>
                                            <?php endif; ?>
                                            <?php if ($task['case_title']): ?>
                                                <small class="text-info">Case: <?php echo htmlspecialchars($task['case_title']); ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-2">
                                            <span class="badge badge-<?php 
                                                echo $task['priority'] === 'urgent' ? 'danger' : 
                                                    ($task['priority'] === 'high' ? 'warning' : 'info'); 
                                            ?>">
                                                <?php echo ucfirst($task['priority']); ?>
                                            </span>
                                        </div>
                                        <div class="col-1">
                                            <?php if ($task['due_date']): ?>
                                                <small class="text-muted">
                                                    <?php echo formatDate($task['due_date'], 'M j'); ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right Column -->
            <div class="col-4">
                <!-- Upcoming Appointments -->
                <div class="glass-card mb-4">
                    <h5><i class="fas fa-calendar-alt"></i> Upcoming Appointments</h5>
                    
                    <?php if (empty($appointments)): ?>
                        <div class="text-center py-3">
                            <i class="fas fa-calendar fa-2x mb-2" style="color: var(--medium-gray);"></i>
                            <p>No upcoming appointments.</p>
                            <a href="appointments.php?action=book" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i>
                                Book Appointment
                            </a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($appointments as $appointment): ?>
                            <div class="appointment-item mb-3 p-3" style="border-left: 4px solid var(--primary-teal);">
                                <h6><?php echo ucwords(str_replace('_', ' ', $appointment['appointment_type'])); ?></h6>
                                <p class="mb-1">
                                    <i class="fas fa-calendar"></i>
                                    <?php echo formatDate($appointment['appointment_date'], 'M j, Y'); ?>
                                    at <?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?>
                                </p>
                                <?php if ($appointment['location']): ?>
                                    <p class="mb-1">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?php echo htmlspecialchars($appointment['location']); ?>
                                    </p>
                                <?php endif; ?>
                                <?php if ($appointment['staff_name']): ?>
                                    <p class="mb-0">
                                        <i class="fas fa-user"></i>
                                        <?php echo htmlspecialchars($appointment['staff_name']); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="text-center">
                            <a href="appointments.php" class="btn btn-secondary btn-sm">
                                View All <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Recent Messages -->
                <div class="glass-card mb-4">
                    <h5><i class="fas fa-envelope"></i> Recent Messages</h5>
                    
                    <?php if (empty($messages)): ?>
                        <div class="text-center py-3">
                            <i class="fas fa-envelope fa-2x mb-2" style="color: var(--medium-gray);"></i>
                            <p>No messages.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($messages as $message): ?>
                            <div class="message-item mb-3 p-3 <?php echo $message['is_read'] ? '' : 'unread'; ?>" 
                                 style="border-left: 4px solid <?php echo $message['is_read'] ? 'var(--medium-gray)' : 'var(--primary-teal)'; ?>;">
                                <h6><?php echo htmlspecialchars($message['subject'] ?: 'No Subject'); ?></h6>
                                <p class="mb-1"><?php echo htmlspecialchars(substr($message['message_content'], 0, 100)) . '...'; ?></p>
                                <small class="text-muted">
                                    From: <?php echo htmlspecialchars($message['sender_name']); ?> |
                                    <?php echo formatDate($message['created_at'], 'M j, g:i A'); ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="text-center">
                            <a href="messages.php" class="btn btn-secondary btn-sm">
                                View All <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Platform Access -->
                <div class="glass-card">
                    <h5><i class="fas fa-apps"></i> Platform Access</h5>
                    
                    <div class="platform-links">
                        <?php foreach ($dashboardData['platform_access'] as $platform): ?>
                            <a href="<?php echo strtolower($platform); ?>.php" class="btn btn-secondary w-100 mb-2">
                                <i class="fas fa-external-link-alt"></i>
                                Launch <?php echo $platform; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="assets/js/main.js"></script>
    
    <script>
        function toggleTask(taskId) {
            // AJAX call to toggle task completion
            fetch('api/toggle-task.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ task_id: taskId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    OUTSINC.showAlert('Task updated successfully!', 'success');
                    // Optionally remove the task from the list or update its appearance
                } else {
                    OUTSINC.showAlert('Error updating task.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                OUTSINC.showAlert('Network error.', 'error');
            });
        }
    </script>
    
    <!-- Additional CSS for dashboard -->
    <style>
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }
        
        .table th,
        .table td {
            padding: 0.75rem;
            border-bottom: 1px solid var(--medium-gray);
            text-align: left;
        }
        
        .table th {
            font-weight: 600;
            background: var(--light-gray);
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            font-weight: 500;
            border-radius: var(--radius-sm);
            text-transform: uppercase;
        }
        
        .badge-primary { background: var(--primary-teal); color: white; }
        .badge-secondary { background: var(--medium-gray); color: var(--dark-gray); }
        .badge-success { background: var(--dcide-green); color: white; }
        .badge-danger { background: #F44336; color: white; }
        .badge-warning { background: var(--bles-orange); color: white; }
        .badge-info { background: var(--ask-blue); color: white; }
        
        .task-list .glass-card {
            padding: 1rem;
            transition: all var(--transition-fast);
        }
        
        .task-list .glass-card:hover {
            transform: translateX(5px);
        }
        
        .appointment-item,
        .message-item {
            border-radius: var(--radius-md);
            background: rgba(255, 255, 255, 0.05);
            transition: all var(--transition-fast);
        }
        
        .appointment-item:hover,
        .message-item:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .message-item.unread {
            background: rgba(0, 150, 136, 0.1);
        }
        
        .platform-links .btn {
            margin-bottom: 0.5rem;
        }
        
        .nav-link.active {
            color: var(--primary-teal) !important;
            font-weight: 600;
        }
    </style>
</body>
</html>