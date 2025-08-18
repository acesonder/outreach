<?php
/**
 * Placeholder page - Cases
 */

require_once 'includes/config.php';
startSecureSession();
requireLogin();

$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cases - OUTSINC</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
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
                        <li><a href="dashboard.php" class="nav-link">Dashboard</a></li>
                        <li><a href="cases.php" class="nav-link active">Cases</a></li>
                        <li><a href="logout.php" class="btn btn-secondary btn-sm">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row">
            <div class="col-12">
                <div class="neu-card text-center">
                    <i class="fas fa-folder-open fa-4x mb-4" style="color: var(--dcide-green);"></i>
                    <h2><?php echo $currentUser['role'] === 'client' ? 'My Cases' : 'Case Management'; ?></h2>
                    <p class="lead">The case management system is coming soon!</p>
                    <p>This feature will allow you to track progress, set goals, and manage support plans.</p>
                    <a href="dashboard.php" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i>
                        Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>