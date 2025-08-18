<?php
/**
 * OUTSINC Demo Mode - Shows the interface without database
 */

// Demo mode settings
$demoMode = true;
$settings = [
    'site_name' => 'OUTSINC',
    'welcome_message' => 'Welcome to OUTSINC - Outreach Someone In Need of Change',
    'enable_registration' => 'true'
];

$currentUser = null;
$flashMessage = null;

// Mock security questions for demo
$mockSecurityQuestions = [
    ['question_id' => 1, 'question_text' => 'What was the name of your first pet?'],
    ['question_id' => 2, 'question_text' => 'What street did you grow up on?'],
    ['question_id' => 3, 'question_text' => 'What city were you born in?'],
    ['question_id' => 4, 'question_text' => 'What is your favorite color?'],
    ['question_id' => 5, 'question_text' => 'What month were you born?']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="OUTSINC - Outreach Someone In Need of Change. Comprehensive support platform for individuals facing homelessness, addiction, and life challenges.">
    <meta name="keywords" content="outreach, support, case management, addiction recovery, housing assistance, mental health">
    <title><?php echo htmlspecialchars($settings['site_name']); ?> - Outreach Someone In Need of Change</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/styles.css">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Demo Mode Notice -->
    <div style="background: #FFC107; color: #000; text-align: center; padding: 10px; font-weight: bold;">
        <i class="fas fa-exclamation-triangle"></i>
        DEMO MODE - Database not connected. This shows the interface design and functionality.
    </div>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="row items-center justify-between">
                <div class="col-auto">
                    <a href="demo.php" class="navbar-brand">
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
                        
                        <li><a href="#" class="btn btn-secondary btn-sm" onclick="OUTSINC.openModal('loginModal')">Login</a></li>
                        <li><a href="#" class="btn btn-primary btn-sm" onclick="OUTSINC.openModal('registerModal')">Get Help</a></li>
                        
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
                    <a href="#" class="btn btn-primary btn-lg" onclick="OUTSINC.openModal('registerModal')">
                        <i class="fas fa-user-plus"></i>
                        Get Started Today
                    </a>
                    <a href="#about" class="btn btn-secondary btn-lg">
                        <i class="fas fa-info-circle"></i>
                        Learn More
                    </a>
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
                    <a href="#" class="btn btn-success">Learn More</a>
                </div>
                
                <div class="platform-card link animate-on-scroll">
                    <div class="platform-icon link">
                        <i class="fas fa-link"></i>
                    </div>
                    <h4>LINK</h4>
                    <p><strong>Lead Individuals to New Knowledge</strong></p>
                    <p>Smart referral engine connecting clients to community resources, treatment programs, and support services.</p>
                    <a href="#" class="btn btn-primary" style="background: var(--link-indigo);">Learn More</a>
                </div>
                
                <div class="platform-card bles animate-on-scroll">
                    <div class="platform-icon bles">
                        <i class="fas fa-home"></i>
                    </div>
                    <h4>BLES</h4>
                    <p><strong>Breaking Life's Endless Struggles</strong></p>
                    <p>Specialized intake and advocacy platform for addiction recovery, helping access treatment beds and support programs.</p>
                    <a href="#" class="btn btn-warning">Learn More</a>
                </div>
                
                <div class="platform-card ask animate-on-scroll">
                    <div class="platform-icon ask">
                        <i class="fas fa-comments"></i>
                    </div>
                    <h4>ASK</h4>
                    <p><strong>Access Support Knowledge</strong></p>
                    <p>Real-time messaging and crisis support platform providing immediate assistance and resource navigation.</p>
                    <a href="#" class="btn btn-primary" style="background: var(--ask-blue);">Learn More</a>
                </div>
                
                <div class="platform-card ethan animate-on-scroll">
                    <div class="platform-icon ethan">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h4>ETHAN</h4>
                    <p><strong>Everything That's Human And Normal</strong></p>
                    <p>Wellness and personal development platform offering learning tools, reflection, and growth tracking.</p>
                    <a href="#" class="btn btn-primary" style="background: var(--ethan-coral);">Learn More</a>
                </div>
                
                <div class="platform-card footprint animate-on-scroll">
                    <div class="platform-icon footprint">
                        <i class="fas fa-map-marked-alt"></i>
                    </div>
                    <h4>FOOTPRINT</h4>
                    <p><strong>Field Operations & Tracking</strong></p>
                    <p>Outreach logging and incident reporting system for tracking field activities and community impact.</p>
                    <a href="#" class="btn btn-primary" style="background: var(--footprint-brown);">Learn More</a>
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
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    Demo mode - Form submissions are not processed
                                </div>
                                <form>
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
                                    <button type="button" class="btn btn-primary" onclick="OUTSINC.showAlert('Demo mode - Message not sent', 'info')">
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
                        <a href="#" style="color: var(--primary-teal);">Privacy Policy</a> |
                        <a href="#" style="color: var(--primary-teal);">Terms of Service</a> |
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
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                Demo mode - Login functionality is not available
            </div>
            <form>
                <div class="form-group">
                    <label for="login_username" class="form-label">Username</label>
                    <input type="text" id="login_username" name="username" class="form-control" value="JOHNDOE9001" readonly>
                </div>
                <div class="form-group">
                    <label for="login_password" class="form-label">Password</label>
                    <input type="password" id="login_password" name="password" class="form-control" value="••••••••" readonly>
                </div>
                <div class="form-group">
                    <button type="button" class="btn btn-primary w-100" onclick="OUTSINC.showAlert('Demo mode - Login not available', 'info')">
                        <i class="fas fa-sign-in-alt"></i>
                        Login (Demo)
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Registration Modal -->
    <div id="registerModal" class="modal">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h3 class="modal-title">Get Help - Register for OUTSINC</h3>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                Demo mode - Registration functionality is not available
            </div>
            <form>
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label for="reg_first_name" class="form-label">First Name *</label>
                            <input type="text" id="reg_first_name" name="first_name" class="form-control" value="John">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="reg_last_name" class="form-label">Last Name *</label>
                            <input type="text" id="reg_last_name" name="last_name" class="form-control" value="Doe">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="reg_dob" class="form-label">Date of Birth *</label>
                    <input type="date" id="reg_dob" name="date_of_birth" class="form-control" value="1990-01-01">
                </div>
                
                <div class="form-group">
                    <label for="reg_security_question" class="form-label">Security Question *</label>
                    <select id="reg_security_question" name="security_question_id" class="form-control form-select">
                        <option value="">Choose a security question...</option>
                        <?php foreach ($mockSecurityQuestions as $question): ?>
                            <option value="<?php echo $question['question_id']; ?>" <?php echo $question['question_id'] == 1 ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($question['question_text']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="reg_security_answer" class="form-label">Security Answer *</label>
                    <input type="text" id="reg_security_answer" name="security_answer" class="form-control" value="Buddy">
                </div>
                
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label for="reg_password" class="form-label">Password *</label>
                            <input type="password" id="reg_password" name="password" class="form-control" value="SecurePass123!">
                            <div id="password-strength" class="mt-1"></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="reg_confirm_password" class="form-label">Confirm Password *</label>
                            <input type="password" id="reg_confirm_password" name="confirm_password" class="form-control" value="SecurePass123!">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="checkbox-wrapper">
                        <input type="checkbox" id="terms_agreement" name="terms_agreement" class="checkbox" checked>
                        <label for="terms_agreement">
                            I agree to the <a href="#" target="_blank">Terms of Service</a> and 
                            <a href="#" target="_blank">Privacy Policy</a> *
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="button" class="btn btn-primary w-100" onclick="OUTSINC.showAlert('Demo mode - Registration not available', 'info')">
                        <i class="fas fa-user-plus"></i>
                        Create Account (Demo)
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="assets/js/main.js"></script>
    
    <!-- Additional CSS for theme toggle -->
    <style>
        .theme-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 30px;
            margin-left: 1rem;
        }

        .theme-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--neu-light);
            border-radius: 30px;
            transition: 0.3s;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 5px;
            box-shadow: 
                inset 4px 4px 8px var(--neu-shadow-dark),
                inset -4px -4px 8px var(--neu-shadow-light);
        }

        .slider i {
            font-size: 14px;
            transition: 0.3s;
        }

        .slider .fa-sun {
            color: #FFD700;
        }

        .slider .fa-moon {
            color: #4A5568;
        }

        input:checked + .slider {
            background: var(--primary-blue);
        }

        input:checked + .slider .fa-sun {
            opacity: 0.3;
        }

        input:checked + .slider .fa-moon {
            opacity: 1;
            color: #E2E8F0;
        }

        input:not(:checked) + .slider .fa-sun {
            opacity: 1;
        }

        input:not(:checked) + .slider .fa-moon {
            opacity: 0.3;
        }

        .w-100 {
            width: 100%;
        }
    </style>
</body>
</html>