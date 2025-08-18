<?php
/**
 * OUTSINC - Outreach Someone In Need of Change
 * Main Landing Page
 */

require_once 'includes/config.php';

// Start session
startSecureSession();

// Get current user if logged in
$currentUser = getCurrentUser();

// Get flash message
$flashMessage = getFlashMessage();

// Get system settings
try {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ('site_name', 'welcome_message', 'enable_registration')");
    $stmt->execute();
    $settings = [];
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (PDOException $e) {
    error_log("Error fetching settings: " . $e->getMessage());
    $settings = [
        'site_name' => 'OUTSINC',
        'welcome_message' => 'Welcome to OUTSINC - Outreach Someone In Need of Change',
        'enable_registration' => 'true'
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="OUTSINC - Outreach Someone In Need of Change. Comprehensive support platform for individuals facing homelessness, addiction, and life challenges.">
    <meta name="keywords" content="outreach, support, case management, addiction recovery, housing assistance, mental health">
    <title><?php echo htmlspecialchars($settings['site_name']); ?> - Outreach Someone In Need of Change</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/styles.css">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Preload critical resources -->
    <link rel="preload" href="assets/css/styles.css" as="style">
    <link rel="preload" href="assets/js/main.js" as="script">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="row items-center justify-between">
                <div class="col-auto">
                    <a href="index.php" class="navbar-brand">
                        <i class="fas fa-hands-helping"></i>
                        <?php echo htmlspecialchars($settings['site_name']); ?>
                    </a>
                </div>
                
                <div class="col-auto">
                    <ul class="navbar-nav d-flex items-center">
                        <li><a href="#home" class="nav-link">Home</a></li>
                        <li><a href="#about" class="nav-link">About Us</a></li>
                        <li><a href="#platforms" class="nav-link">Platforms</a></li>
                        <li><a href="#services" class="nav-link">Services</a></li>
                        <li><a href="#contact" class="nav-link">Contact</a></li>
                        
                        <?php if ($currentUser): ?>
                            <li class="dropdown">
                                <a href="#" class="nav-link dropdown-toggle">
                                    <i class="fas fa-user"></i>
                                    <?php echo htmlspecialchars($currentUser['first_name']); ?>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a href="dashboard.php" class="dropdown-link">Dashboard</a></li>
                                    <li><a href="profile.php" class="dropdown-link">Profile</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a href="logout.php" class="dropdown-link">Logout</a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li><a href="#" class="btn btn-secondary btn-sm" onclick="OUTSINC.openModal('loginModal')">Login</a></li>
                            <?php if ($settings['enable_registration'] === 'true'): ?>
                                <li><a href="#" class="btn btn-primary btn-sm" onclick="OUTSINC.openModal('registerModal')">Get Help</a></li>
                            <?php endif; ?>
                        <?php endif; ?>
                        
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
                <?php echo htmlspecialchars($flashMessage['message']); ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="container">
            <div class="hero-content text-center">
                <h1 class="fade-in-up">
                    Outreach Someone <br>
                    <span style="color: var(--primary-teal);">In Need of Change</span>
                </h1>
                <p class="fade-in-up" style="animation-delay: 0.2s;">
                    A comprehensive digital platform connecting individuals to support services, 
                    case management, and resources for lasting positive change.
                </p>
                <div class="fade-in-up" style="animation-delay: 0.4s;">
                    <?php if (!$currentUser): ?>
                        <a href="#" class="btn btn-primary btn-lg" onclick="OUTSINC.openModal('registerModal')">
                            <i class="fas fa-user-plus"></i>
                            Get Started Today
                        </a>
                        <a href="#about" class="btn btn-secondary btn-lg">
                            <i class="fas fa-info-circle"></i>
                            Learn More
                        </a>
                    <?php else: ?>
                        <a href="dashboard.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-tachometer-alt"></i>
                            Go to Dashboard
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2>About OUTSINC</h2>
                    <p class="lead">
                        OUTSINC stands for "Outreach Someone In Need of Change," highlighting our commitment 
                        to meet people where they are and help them move toward positive life changes.
                    </p>
                </div>
            </div>
            
            <div class="row">
                <div class="col-4">
                    <div class="neu-card text-center animate-on-scroll">
                        <div class="platform-icon dcide mb-3">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h4>Our Mission</h4>
                        <p>
                            To provide comprehensive, compassionate outreach and essential supports for 
                            individuals facing homelessness, substance-use challenges, and acute life crises.
                        </p>
                    </div>
                </div>
                
                <div class="col-4">
                    <div class="neu-card text-center animate-on-scroll">
                        <div class="platform-icon ask mb-3">
                            <i class="fas fa-eye"></i>
                        </div>
                        <h4>Our Vision</h4>
                        <p>
                            A community where no one falls through the cracks—where everyone has reliable 
                            access to basic needs and the supports that foster lasting stability.
                        </p>
                    </div>
                </div>
                
                <div class="col-4">
                    <div class="neu-card text-center animate-on-scroll">
                        <div class="platform-icon ethan mb-3">
                            <i class="fas fa-star"></i>
                        </div>
                        <h4>Our Values</h4>
                        <p>
                            Dignity & respect, self-determination, accountability & transparency, 
                            collaboration, and advocacy for those who need it most.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Platforms Section -->
    <section id="platforms" class="py-5" style="background: var(--light-gray);">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2>Our Integrated Platforms</h2>
                    <p class="lead">
                        OUTSINC connects multiple specialized platforms to provide comprehensive support
                    </p>
                </div>
            </div>
            
            <div class="platform-grid">
                <div class="platform-card dcide animate-on-scroll">
                    <div class="platform-icon dcide">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <h4>DCIDE</h4>
                    <p><strong>Driving Change Inspiring Development Everywhere</strong></p>
                    <p>Comprehensive case management system for tracking client progress, setting goals, and coordinating care across multiple services.</p>
                    <a href="dcide.php" class="btn btn-success">Learn More</a>
                </div>
                
                <div class="platform-card link animate-on-scroll">
                    <div class="platform-icon link">
                        <i class="fas fa-link"></i>
                    </div>
                    <h4>LINK</h4>
                    <p><strong>Lead Individuals to New Knowledge</strong></p>
                    <p>Smart referral engine connecting clients to community resources, treatment programs, and support services.</p>
                    <a href="link.php" class="btn btn-primary" style="background: var(--link-indigo);">Learn More</a>
                </div>
                
                <div class="platform-card bles animate-on-scroll">
                    <div class="platform-icon bles">
                        <i class="fas fa-home"></i>
                    </div>
                    <h4>BLES</h4>
                    <p><strong>Breaking Life's Endless Struggles</strong></p>
                    <p>Specialized intake and advocacy platform for addiction recovery, helping access treatment beds and support programs.</p>
                    <a href="bles.php" class="btn btn-warning">Learn More</a>
                </div>
                
                <div class="platform-card ask animate-on-scroll">
                    <div class="platform-icon ask">
                        <i class="fas fa-comments"></i>
                    </div>
                    <h4>ASK</h4>
                    <p><strong>Access Support Knowledge</strong></p>
                    <p>Real-time messaging and crisis support platform providing immediate assistance and resource navigation.</p>
                    <a href="ask.php" class="btn btn-primary" style="background: var(--ask-blue);">Learn More</a>
                </div>
                
                <div class="platform-card ethan animate-on-scroll">
                    <div class="platform-icon ethan">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h4>ETHAN</h4>
                    <p><strong>Everything That's Human And Normal</strong></p>
                    <p>Wellness and personal development platform offering learning tools, reflection, and growth tracking.</p>
                    <a href="ethan.php" class="btn btn-primary" style="background: var(--ethan-coral);">Learn More</a>
                </div>
                
                <div class="platform-card footprint animate-on-scroll">
                    <div class="platform-icon footprint">
                        <i class="fas fa-map-marked-alt"></i>
                    </div>
                    <h4>FOOTPRINT</h4>
                    <p><strong>Field Operations & Tracking</strong></p>
                    <p>Outreach logging and incident reporting system for tracking field activities and community impact.</p>
                    <a href="footprint.php" class="btn btn-primary" style="background: var(--footprint-brown);">Learn More</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2>Our Services</h2>
                    <p class="lead">Comprehensive support across multiple areas of need</p>
                </div>
            </div>
            
            <div class="row">
                <div class="col-6 col-md-4 mb-4">
                    <div class="glass-card text-center animate-on-scroll">
                        <i class="fas fa-home fa-3x mb-3" style="color: var(--dcide-green);"></i>
                        <h5>Housing Support</h5>
                        <p>Emergency shelter, transitional housing, and permanent housing assistance.</p>
                    </div>
                </div>
                
                <div class="col-6 col-md-4 mb-4">
                    <div class="glass-card text-center animate-on-scroll">
                        <i class="fas fa-heart fa-3x mb-3" style="color: var(--ethan-coral);"></i>
                        <h5>Mental Health</h5>
                        <p>Counseling, crisis intervention, and connections to mental health services.</p>
                    </div>
                </div>
                
                <div class="col-6 col-md-4 mb-4">
                    <div class="glass-card text-center animate-on-scroll">
                        <i class="fas fa-prescription-bottle fa-3x mb-3" style="color: var(--bles-orange);"></i>
                        <h5>Addiction Recovery</h5>
                        <p>Harm reduction, detox referrals, and recovery support programs.</p>
                    </div>
                </div>
                
                <div class="col-6 col-md-4 mb-4">
                    <div class="glass-card text-center animate-on-scroll">
                        <i class="fas fa-briefcase fa-3x mb-3" style="color: var(--link-indigo);"></i>
                        <h5>Employment</h5>
                        <p>Job training, resume assistance, and employment placement services.</p>
                    </div>
                </div>
                
                <div class="col-6 col-md-4 mb-4">
                    <div class="glass-card text-center animate-on-scroll">
                        <i class="fas fa-balance-scale fa-3x mb-3" style="color: var(--ask-blue);"></i>
                        <h5>Legal Aid</h5>
                        <p>Legal assistance, court support, and advocacy services.</p>
                    </div>
                </div>
                
                <div class="col-6 col-md-4 mb-4">
                    <div class="glass-card text-center animate-on-scroll">
                        <i class="fas fa-utensils fa-3x mb-3" style="color: var(--footprint-brown);"></i>
                        <h5>Basic Needs</h5>
                        <p>Food assistance, clothing, hygiene supplies, and emergency support.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-5" style="background: var(--light-gray);">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2>Get In Touch</h2>
                    <p class="lead">Ready to get started or need more information?</p>
                </div>
            </div>
            
            <div class="row">
                <div class="col-8 mx-auto">
                    <div class="neu-card">
                        <div class="row">
                            <div class="col-6">
                                <h4>Contact Information</h4>
                                <div class="mb-3">
                                    <i class="fas fa-map-marker-alt text-primary"></i>
                                    <strong>Address:</strong><br>
                                    310 Division Street<br>
                                    Cobourg, Ontario
                                </div>
                                <div class="mb-3">
                                    <i class="fas fa-phone text-primary"></i>
                                    <strong>Phone:</strong><br>
                                    <a href="tel:+1-555-0123">(555) 123-4567</a>
                                </div>
                                <div class="mb-3">
                                    <i class="fas fa-envelope text-primary"></i>
                                    <strong>Email:</strong><br>
                                    <a href="mailto:info@outsinc.org">info@outsinc.org</a>
                                </div>
                                <div class="mb-3">
                                    <i class="fas fa-clock text-primary"></i>
                                    <strong>Hours:</strong><br>
                                    Monday - Friday: 9:00 AM - 5:00 PM<br>
                                    24/7 Crisis Support Available
                                </div>
                            </div>
                            <div class="col-6">
                                <h4>Quick Contact</h4>
                                <form id="contactForm" method="POST" action="contact.php">
                                    <div class="form-group">
                                        <label for="contact_name" class="form-label">Name</label>
                                        <input type="text" id="contact_name" name="name" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="contact_email" class="form-label">Email</label>
                                        <input type="email" id="contact_email" name="email" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="contact_message" class="form-label">Message</label>
                                        <textarea id="contact_message" name="message" class="form-control" rows="4" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane"></i>
                                        Send Message
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-4" style="background: var(--primary-blue); color: var(--white);">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($settings['site_name']); ?>. All rights reserved.</p>
                    <p>
                        <a href="privacy.php" style="color: var(--primary-teal);">Privacy Policy</a> |
                        <a href="terms.php" style="color: var(--primary-teal);">Terms of Service</a> |
                        <a href="#contact" style="color: var(--primary-teal);">Contact Us</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Login Modal -->
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Login to OUTSINC</h3>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <form id="loginForm" method="POST" action="login.php">
                <div class="form-group">
                    <label for="login_username" class="form-label">Username</label>
                    <input type="text" id="login_username" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="login_password" class="form-label">Password</label>
                    <input type="password" id="login_password" name="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <div class="checkbox-wrapper">
                        <input type="checkbox" id="remember_me" name="remember_me" class="checkbox">
                        <label for="remember_me">Remember me</label>
                    </div>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-sign-in-alt"></i>
                        Login
                    </button>
                </div>
                <div class="text-center">
                    <a href="#" onclick="OUTSINC.closeModal('loginModal'); OUTSINC.openModal('forgotPasswordModal');">
                        Forgot your password?
                    </a>
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