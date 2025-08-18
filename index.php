<?php
/**
 * OUTSINC - Outreach Someone In Need of Change
 * Main landing page and navigation hub
 */

require_once 'includes/config.php';
require_once 'includes/auth.php';

// Check if user is logged in
$currentUser = getCurrentUser();
$isLoggedIn = $currentUser !== null;

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    logout();
    header('Location: index.php?message=logged_out');
    exit;
}

// Handle error messages
$errorMessage = '';
$successMessage = '';

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'login_required':
            $errorMessage = 'Please log in to access this feature.';
            break;
        case 'access_denied':
            $errorMessage = 'You do not have permission to access this resource.';
            break;
    }
}

if (isset($_GET['message'])) {
    switch ($_GET['message']) {
        case 'logged_out':
            $successMessage = 'You have been logged out successfully.';
            break;
        case 'registration_success':
            $successMessage = 'Registration successful! Please log in to continue.';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="OUTSINC - Outreach Someone In Need of Change. Comprehensive platform for case management, client intake, and community support services.">
    <meta name="keywords" content="outreach, case management, social services, client support, community resources">
    <meta name="author" content="OUTSINC Team">
    
    <title>OUTSINC - Outreach Someone In Need of Change</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
</head>
<body>
    <!-- Header and Navigation -->
    <header class="header">
        <div class="nav-container">
            <a href="index.php" class="logo">
                <i class="fas fa-heart"></i> OUTSINC
            </a>
            
            <nav>
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="#home" class="nav-link">
                            <i class="fas fa-home"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#services" class="nav-link">
                            <i class="fas fa-hands-helping"></i> Services
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#platforms" class="nav-link">
                            <i class="fas fa-th-large"></i> Platforms
                        </a>
                        <div class="dropdown">
                            <?php foreach ($platforms as $key => $platform): ?>
                            <a href="<?php echo $key; ?>.php" class="dropdown-item">
                                <i class="<?php echo $platform['icon']; ?>"></i>
                                <?php echo $platform['name']; ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a href="#about" class="nav-link">
                            <i class="fas fa-info-circle"></i> About
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#contact" class="nav-link">
                            <i class="fas fa-envelope"></i> Contact
                        </a>
                    </li>
                    <?php if ($isLoggedIn): ?>
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="?action=logout" class="nav-link">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a href="#" class="nav-link" data-modal="loginModal">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link" data-modal="registerModal">
                            <i class="fas fa-user-plus"></i> Register
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            
            <button class="mobile-menu-toggle">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </header>

    <!-- Alert Messages -->
    <?php if ($errorMessage): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <?php echo sanitizeOutput($errorMessage); ?>
    </div>
    <?php endif; ?>

    <?php if ($successMessage): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?php echo sanitizeOutput($successMessage); ?>
    </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main>
        <!-- Hero Section -->
        <section id="home" class="hero">
            <div class="container">
                <h1 class="hero-title">Welcome to OUTSINC</h1>
                <p class="hero-subtitle">
                    Outreach Someone In Need of Change - Your comprehensive platform for community support, 
                    case management, and connecting people with the resources they need.
                </p>
                <?php if (!$isLoggedIn): ?>
                <a href="#" class="cta-button" data-modal="registerModal">
                    <i class="fas fa-rocket"></i> Get Started Today
                </a>
                <?php else: ?>
                <a href="dashboard.php" class="cta-button">
                    <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                </a>
                <?php endif; ?>
            </div>
        </section>

        <!-- Services Section -->
        <section id="services" class="section">
            <div class="container">
                <h2 class="section-title">Our Services</h2>
                <div class="grid grid-3">
                    <div class="card">
                        <div class="card-icon">
                            <i class="fas fa-home"></i>
                        </div>
                        <h3 class="card-title">Housing Assistance</h3>
                        <p class="card-description">
                            Help finding shelter, transitional housing, and permanent housing solutions.
                        </p>
                    </div>
                    
                    <div class="card">
                        <div class="card-icon">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <h3 class="card-title">Employment Support</h3>
                        <p class="card-description">
                            Job search assistance, skills training, and career development resources.
                        </p>
                    </div>
                    
                    <div class="card">
                        <div class="card-icon">
                            <i class="fas fa-brain"></i>
                        </div>
                        <h3 class="card-title">Mental Health</h3>
                        <p class="card-description">
                            Counseling services, support groups, and mental health resources.
                        </p>
                    </div>
                    
                    <div class="card">
                        <div class="card-icon">
                            <i class="fas fa-heart-broken"></i>
                        </div>
                        <h3 class="card-title">Addiction Recovery</h3>
                        <p class="card-description">
                            Substance abuse treatment, recovery programs, and ongoing support.
                        </p>
                    </div>
                    
                    <div class="card">
                        <div class="card-icon">
                            <i class="fas fa-gavel"></i>
                        </div>
                        <h3 class="card-title">Legal Aid</h3>
                        <p class="card-description">
                            Legal assistance, advocacy, and support navigating the justice system.
                        </p>
                    </div>
                    
                    <div class="card">
                        <div class="card-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <h3 class="card-title">Financial Support</h3>
                        <p class="card-description">
                            Emergency financial assistance, budgeting help, and benefit applications.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Platforms Section -->
        <section id="platforms" class="section">
            <div class="container">
                <h2 class="section-title">Our Integrated Platforms</h2>
                <div class="grid grid-3">
                    <?php foreach ($platforms as $key => $platform): ?>
                    <div class="card platform-card" data-platform="<?php echo $key; ?>">
                        <div class="card-icon" style="background-color: <?php echo $platform['color']; ?>">
                            <i class="<?php echo $platform['icon']; ?>"></i>
                        </div>
                        <h3 class="card-title"><?php echo $platform['name']; ?></h3>
                        <p class="card-description"><?php echo $platform['description']; ?></p>
                        <a href="<?php echo $key; ?>.php" class="btn btn-primary">
                            <i class="fas fa-external-link-alt"></i> Learn More
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section id="about" class="section">
            <div class="container">
                <h2 class="section-title">About OUTSINC</h2>
                <div class="grid grid-2">
                    <div>
                        <h3>Our Mission</h3>
                        <p>
                            To provide comprehensive, compassionate outreach and essential supports for individuals 
                            facing homelessness, substance-use challenges, and acute life crises. We believe in 
                            meeting people where they are and walking alongside them on their journey to stability.
                        </p>
                        
                        <h3>Our Vision</h3>
                        <p>
                            A community where no one falls through the cracks—where everyone has reliable access 
                            to basic needs and the supports that foster lasting stability and positive change.
                        </p>
                    </div>
                    
                    <div>
                        <h3>Core Values</h3>
                        <ul style="list-style: none; padding: 0;">
                            <li style="margin-bottom: 1rem;">
                                <i class="fas fa-check-circle" style="color: var(--primary-teal); margin-right: 0.5rem;"></i>
                                <strong>Dignity & Respect:</strong> Treating every person with compassion
                            </li>
                            <li style="margin-bottom: 1rem;">
                                <i class="fas fa-check-circle" style="color: var(--primary-teal); margin-right: 0.5rem;"></i>
                                <strong>Self-Determination:</strong> Supporting client-led decision making
                            </li>
                            <li style="margin-bottom: 1rem;">
                                <i class="fas fa-check-circle" style="color: var(--primary-teal); margin-right: 0.5rem;"></i>
                                <strong>Collaboration:</strong> Working together to multiply impact
                            </li>
                            <li style="margin-bottom: 1rem;">
                                <i class="fas fa-check-circle" style="color: var(--primary-teal); margin-right: 0.5rem;"></i>
                                <strong>Advocacy:</strong> Amplifying client voices in policy discussions
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section id="contact" class="section">
            <div class="container">
                <h2 class="section-title">Get In Touch</h2>
                <div class="grid grid-3">
                    <div class="card">
                        <div class="card-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <h3 class="card-title">Phone</h3>
                        <p class="card-description">
                            24/7 Crisis Line<br>
                            <strong>(555) 123-4567</strong>
                        </p>
                    </div>
                    
                    <div class="card">
                        <div class="card-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h3 class="card-title">Email</h3>
                        <p class="card-description">
                            General Inquiries<br>
                            <strong>info@outsinc.org</strong>
                        </p>
                    </div>
                    
                    <div class="card">
                        <div class="card-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h3 class="card-title">Location</h3>
                        <p class="card-description">
                            310 Division Street<br>
                            <strong>Cobourg, ON</strong>
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer style="background: var(--primary-blue); color: var(--white); padding: var(--spacing-xl) 0; margin-top: var(--spacing-2xl);">
        <div class="container text-center">
            <p>&copy; <?php echo date('Y'); ?> OUTSINC - Outreach Someone In Need of Change. All rights reserved.</p>
            <p style="margin-top: var(--spacing-sm);">
                <a href="#" style="color: var(--white); margin: 0 var(--spacing-sm);">Privacy Policy</a>
                <a href="#" style="color: var(--white); margin: 0 var(--spacing-sm);">Terms of Service</a>
                <a href="#" style="color: var(--white); margin: 0 var(--spacing-sm);">Accessibility</a>
            </p>
        </div>
    </footer>

    <!-- Login Modal -->
    <?php if (!$isLoggedIn): ?>
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Login to OUTSINC</h3>
                <button class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="login.php" method="POST" data-validate>
                <div class="form-group">
                    <label for="login_username" class="form-label">Username or Email</label>
                    <input type="text" id="login_username" name="username" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label for="login_password" class="form-label">Password</label>
                    <input type="password" id="login_password" name="password" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-large" style="width: 100%;">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </div>
                
                <div class="text-center">
                    <a href="#" data-modal="forgotPasswordModal">Forgot your password?</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Register Modal -->
    <div id="registerModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Register for OUTSINC</h3>
                <button class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="register.php" method="POST" data-validate>
                <div class="grid grid-2">
                    <div class="form-group">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" id="first_name" name="first_name" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" id="last_name" name="last_name" class="form-input" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" id="email" name="email" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label for="phone" class="form-label">Phone Number (Optional)</label>
                    <input type="tel" id="phone" name="phone" class="form-input">
                </div>
                
                <div class="form-group">
                    <label for="date_of_birth" class="form-label">Date of Birth</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label for="security_question" class="form-label">Security Question</label>
                    <select id="security_question" name="security_question" class="form-select" required>
                        <option value="">Choose a security question...</option>
                        <?php foreach ($security_questions as $question): ?>
                        <option value="<?php echo sanitizeOutput($question); ?>">
                            <?php echo sanitizeOutput($question); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="security_answer" class="form-label">Security Answer</label>
                    <input type="text" id="security_answer" name="security_answer" class="form-input" required>
                </div>
                
                <div class="grid grid-2">
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" name="password" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-input" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: var(--spacing-sm);">
                        <input type="checkbox" name="terms_consent" required>
                        I agree to the <a href="#" target="_blank">Terms of Service</a> and 
                        <a href="#" target="_blank">Privacy Policy</a>
                    </label>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-large" style="width: 100%;">
                        <i class="fas fa-user-plus"></i> Create Account
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div id="forgotPasswordModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Reset Password</h3>
                <button class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="forgot-password.php" method="POST" data-validate>
                <div class="form-group">
                    <label for="forgot_username" class="form-label">Username</label>
                    <input type="text" id="forgot_username" name="username" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-large" style="width: 100%;">
                        <i class="fas fa-key"></i> Get Security Question
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- JavaScript -->
    <script src="assets/js/main.js"></script>
</body>
</html>