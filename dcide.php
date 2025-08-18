<?php
/**
 * DCIDE Platform Page - Case Management & Progress Tracking
 */

require_once 'includes/config.php';
require_once 'includes/auth.php';

$currentUser = getCurrentUser();
$isLoggedIn = $currentUser !== null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DCIDE - Case Management & Progress Tracking | OUTSINC</title>
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
                    <?php if ($isLoggedIn): ?>
                    <li class="nav-item">
                        <a href="<?php echo $currentUser['role']; ?>/dashboard.php" class="nav-link">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="index.php?action=logout" class="nav-link">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a href="#" class="nav-link" data-modal="loginModal">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <!-- Hero Section -->
        <section class="hero" style="background: linear-gradient(135deg, var(--accent-green), #388E3C);">
            <div class="container">
                <div style="display: flex; align-items: center; justify-content: center; gap: var(--spacing-lg); margin-bottom: var(--spacing-lg);">
                    <div style="background: rgba(255,255,255,0.2); padding: var(--spacing-lg); border-radius: 50%; font-size: 3rem;">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <div>
                        <h1 style="margin: 0; font-size: 3rem;">DCIDE</h1>
                        <p style="margin: 0; font-size: 1.2rem; opacity: 0.9;">Case Management & Progress Tracking</p>
                    </div>
                </div>
                <p class="hero-subtitle">
                    DCIDE brings structure to chaos. Track client progress from first contact to resolution 
                    with our comprehensive case management system.
                </p>
                <?php if ($isLoggedIn): ?>
                <a href="<?php echo $currentUser['role']; ?>/dashboard.php" class="cta-button">
                    <i class="fas fa-tachometer-alt"></i> Access Dashboard
                </a>
                <?php else: ?>
                <a href="#" class="cta-button" data-modal="loginModal">
                    <i class="fas fa-sign-in-alt"></i> Login to Access
                </a>
                <?php endif; ?>
            </div>
        </section>

        <!-- Features Section -->
        <section class="section">
            <div class="container">
                <h2 class="section-title">Key Features</h2>
                <div class="grid grid-3">
                    <div class="card">
                        <div class="card-icon" style="background: var(--accent-green);">
                            <i class="fas fa-user-friends"></i>
                        </div>
                        <h3 class="card-title">Client Management</h3>
                        <p class="card-description">
                            Comprehensive client profiles with demographics, background, risk indicators, 
                            and detailed case histories.
                        </p>
                    </div>
                    
                    <div class="card">
                        <div class="card-icon" style="background: var(--accent-green);">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <h3 class="card-title">Case Lifecycle</h3>
                        <p class="card-description">
                            Track cases from creation to closure with status updates, priority levels, 
                            and automated workflows.
                        </p>
                    </div>
                    
                    <div class="card">
                        <div class="card-icon" style="background: var(--accent-green);">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3 class="card-title">Progress Tracking</h3>
                        <p class="card-description">
                            Monitor client goals, milestones, and outcomes with visual progress indicators 
                            and detailed reporting.
                        </p>
                    </div>
                    
                    <div class="card">
                        <div class="card-icon" style="background: var(--accent-green);">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <h3 class="card-title">Appointment Scheduling</h3>
                        <p class="card-description">
                            Schedule and manage appointments with automated reminders and 
                            calendar integration.
                        </p>
                    </div>
                    
                    <div class="card">
                        <div class="card-icon" style="background: var(--accent-green);">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h3 class="card-title">Documentation</h3>
                        <p class="card-description">
                            Secure document storage, case notes, and file uploads with 
                            version control and access logging.
                        </p>
                    </div>
                    
                    <div class="card">
                        <div class="card-icon" style="background: var(--accent-green);">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h3 class="card-title">Alert System</h3>
                        <p class="card-description">
                            Automated alerts for high-risk situations, overdue tasks, 
                            and important deadlines.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works Section -->
        <section class="section" style="background: var(--light-gray);">
            <div class="container">
                <h2 class="section-title">How DCIDE Works</h2>
                <div class="grid grid-2">
                    <div>
                        <h3><i class="fas fa-play-circle" style="color: var(--accent-green);"></i> Getting Started</h3>
                        <ol style="line-height: 1.8;">
                            <li><strong>Client Intake:</strong> Complete comprehensive intake forms with all relevant client information</li>
                            <li><strong>Case Creation:</strong> Staff create cases based on client needs and priorities</li>
                            <li><strong>Goal Setting:</strong> Collaborate with clients to establish achievable goals and milestones</li>
                            <li><strong>Action Planning:</strong> Develop detailed action plans with specific tasks and timelines</li>
                        </ol>
                    </div>
                    
                    <div>
                        <h3><i class="fas fa-sync-alt" style="color: var(--accent-green);"></i> Ongoing Management</h3>
                        <ol style="line-height: 1.8;">
                            <li><strong>Regular Check-ins:</strong> Schedule and conduct regular client meetings and assessments</li>
                            <li><strong>Progress Updates:</strong> Track goal completion and update case status as needed</li>
                            <li><strong>Team Collaboration:</strong> Share cases with team members and coordinate services</li>
                            <li><strong>Outcome Tracking:</strong> Monitor long-term outcomes and case resolution</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <!-- User Roles Section -->
        <section class="section">
            <div class="container">
                <h2 class="section-title">User Access Levels</h2>
                <div class="grid grid-4">
                    <div class="card">
                        <div class="card-icon" style="background: var(--accent-coral);">
                            <i class="fas fa-user"></i>
                        </div>
                        <h3 class="card-title">Clients</h3>
                        <ul style="text-align: left; padding-left: var(--spacing-md);">
                            <li>View own cases</li>
                            <li>Track personal progress</li>
                            <li>Update profile information</li>
                            <li>Schedule appointments</li>
                        </ul>
                    </div>
                    
                    <div class="card">
                        <div class="card-icon" style="background: var(--accent-indigo);">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <h3 class="card-title">Staff</h3>
                        <ul style="text-align: left; padding-left: var(--spacing-md);">
                            <li>Create and manage cases</li>
                            <li>Assign tasks and goals</li>
                            <li>Generate reports</li>
                            <li>Access client profiles</li>
                        </ul>
                    </div>
                    
                    <div class="card">
                        <div class="card-icon" style="background: var(--accent-orange);">
                            <i class="fas fa-walking"></i>
                        </div>
                        <h3 class="card-title">Outreach Workers</h3>
                        <ul style="text-align: left; padding-left: var(--spacing-md);">
                            <li>Field case updates</li>
                            <li>Mobile access</li>
                            <li>Quick contact logging</li>
                            <li>Safety alerts</li>
                        </ul>
                    </div>
                    
                    <div class="card">
                        <div class="card-icon" style="background: var(--accent-sky);">
                            <i class="fas fa-user-cog"></i>
                        </div>
                        <h3 class="card-title">Administrators</h3>
                        <ul style="text-align: left; padding-left: var(--spacing-md);">
                            <li>System oversight</li>
                            <li>User management</li>
                            <li>Analytics dashboard</li>
                            <li>Configuration settings</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- Call to Action -->
        <section class="section" style="background: linear-gradient(135deg, var(--accent-green), #388E3C); color: var(--white);">
            <div class="container text-center">
                <h2>Ready to Streamline Your Case Management?</h2>
                <p style="font-size: var(--font-size-lg); margin-bottom: var(--spacing-xl); opacity: 0.9;">
                    Join hundreds of social workers and outreach professionals using DCIDE to provide better client support.
                </p>
                <?php if ($isLoggedIn): ?>
                <a href="<?php echo $currentUser['role']; ?>/dashboard.php" class="cta-button">
                    <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                </a>
                <?php else: ?>
                <a href="#" class="cta-button" data-modal="loginModal">
                    <i class="fas fa-user-plus"></i> Get Started Today
                </a>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer style="background: var(--primary-blue); color: var(--white); padding: var(--spacing-xl) 0;">
        <div class="container text-center">
            <p>&copy; <?php echo date('Y'); ?> OUTSINC - DCIDE Platform. Part of the OUTSINC ecosystem.</p>
            <p style="margin-top: var(--spacing-sm);">
                <a href="index.php" style="color: var(--white); margin: 0 var(--spacing-sm);">Back to OUTSINC</a>
                <a href="#" style="color: var(--white); margin: 0 var(--spacing-sm);">Support</a>
                <a href="#" style="color: var(--white); margin: 0 var(--spacing-sm);">Documentation</a>
            </p>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>