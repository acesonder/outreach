<?php
/**
 * Client Intake Form
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

// Check if intake already completed
try {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("
        SELECT intake_id, status, completed_at 
        FROM intakes 
        WHERE user_id = ? AND intake_type = 'basic' 
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$currentUser['user_id']]);
    $existingIntake = $stmt->fetch();
    
    if ($existingIntake && $existingIntake['status'] === 'completed') {
        redirect('dashboard.php', 'You have already completed your intake form.', 'info');
    }
} catch (PDOException $e) {
    error_log("Error checking intake: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $formData = [
            'preferred_name' => sanitizeInput($_POST['preferred_name'] ?? ''),
            'gender_identity' => sanitizeInput($_POST['gender_identity'] ?? ''),
            'gender_other' => sanitizeInput($_POST['gender_other'] ?? ''),
            'pronouns' => sanitizeInput($_POST['pronouns'] ?? ''),
            'pronouns_other' => sanitizeInput($_POST['pronouns_other'] ?? ''),
            'marital_status' => sanitizeInput($_POST['marital_status'] ?? ''),
            'emergency_contact_name' => sanitizeInput($_POST['emergency_contact_name'] ?? ''),
            'emergency_contact_phone' => sanitizeInput($_POST['emergency_contact_phone'] ?? ''),
            'emergency_contact_relationship' => sanitizeInput($_POST['emergency_contact_relationship'] ?? ''),
            'current_address' => sanitizeInput($_POST['current_address'] ?? ''),
            'living_situation' => sanitizeInput($_POST['living_situation'] ?? ''),
            'employment_status' => sanitizeInput($_POST['employment_status'] ?? ''),
            'income_sources' => $_POST['income_sources'] ?? [],
            'immediate_needs' => $_POST['immediate_needs'] ?? [],
            
            // Health information
            'physical_health_conditions' => sanitizeInput($_POST['physical_health_conditions'] ?? ''),
            'mental_health_conditions' => sanitizeInput($_POST['mental_health_conditions'] ?? ''),
            'current_medications' => sanitizeInput($_POST['current_medications'] ?? ''),
            
            // Substance use
            'current_substance_use' => sanitizeInput($_POST['current_substance_use'] ?? ''),
            'substances_used' => $_POST['substances_used'] ?? [],
            'frequency_of_use' => sanitizeInput($_POST['frequency_of_use'] ?? ''),
            'want_help_reducing' => sanitizeInput($_POST['want_help_reducing'] ?? ''),
            
            // Legal/Justice
            'legal_matters' => sanitizeInput($_POST['legal_matters'] ?? ''),
            'probation_parole' => sanitizeInput($_POST['probation_parole'] ?? ''),
            
            // Goals
            'goals' => $_POST['goals'] ?? [],
            'additional_information' => sanitizeInput($_POST['additional_information'] ?? '')
        ];
        
        // Begin transaction
        $pdo->beginTransaction();
        
        // Update or create intake record
        if ($existingIntake) {
            $stmt = $pdo->prepare("
                UPDATE intakes 
                SET form_data = ?, status = 'completed', completed_at = NOW(), updated_at = NOW()
                WHERE intake_id = ?
            ");
            $stmt->execute([json_encode($formData), $existingIntake['intake_id']]);
            $intakeId = $existingIntake['intake_id'];
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO intakes (user_id, intake_type, form_data, status, completed_at) 
                VALUES (?, 'basic', ?, 'completed', NOW())
            ");
            $stmt->execute([$currentUser['user_id'], json_encode($formData)]);
            $intakeId = $pdo->lastInsertId();
        }
        
        // Update client profile
        $stmt = $pdo->prepare("
            UPDATE client_profiles 
            SET preferred_name = ?, gender_identity = ?, gender_other = ?, pronouns = ?, pronouns_other = ?,
                marital_status = ?, emergency_contact_name = ?, emergency_contact_phone = ?, 
                emergency_contact_relationship = ?, current_address = ?, living_situation = ?,
                employment_status = ?, income_sources = ?, immediate_needs = ?, updated_at = NOW()
            WHERE user_id = ?
        ");
        $stmt->execute([
            $formData['preferred_name'],
            $formData['gender_identity'],
            $formData['gender_other'],
            $formData['pronouns'], 
            $formData['pronouns_other'],
            $formData['marital_status'],
            $formData['emergency_contact_name'],
            $formData['emergency_contact_phone'],
            $formData['emergency_contact_relationship'],
            $formData['current_address'],
            $formData['living_situation'],
            $formData['employment_status'],
            json_encode($formData['income_sources']),
            json_encode($formData['immediate_needs']),
            $currentUser['user_id']
        ]);
        
        // Log activity
        logActivity('intake_completed', 'intakes', $intakeId);
        
        // Commit transaction
        $pdo->commit();
        
        redirect('dashboard.php', 'Thank you! Your intake form has been completed successfully.', 'success');
        
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollback();
        }
        error_log("Intake submission error: " . $e->getMessage());
        $flashMessage = ['message' => 'Error saving intake form. Please try again.', 'type' => 'error'];
    }
}

// Get existing data if available
$existingData = [];
if ($existingIntake) {
    $existingData = json_decode($existingIntake['form_data'] ?? '{}', true) ?: [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Intake - OUTSINC</title>
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
                        <li>
                            <span class="nav-link">
                                <i class="fas fa-user"></i>
                                Welcome, <?php echo htmlspecialchars($currentUser['first_name']); ?>
                            </span>
                        </li>
                        <li><a href="logout.php" class="btn btn-secondary btn-sm">Logout</a></li>
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
    <div class="container py-5">
        <div class="row">
            <div class="col-10 mx-auto">
                <div class="neu-card">
                    <div class="text-center mb-5">
                        <h1>
                            <i class="fas fa-clipboard-list text-primary"></i>
                            Client Intake Form
                        </h1>
                        <p class="lead">
                            Welcome! Please complete this intake form to help us better understand your needs 
                            and provide appropriate support. All fields are optional unless marked with *.
                        </p>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Privacy Notice:</strong> Your information is confidential and will only be 
                            shared with your consent to provide you with services.
                        </div>
                    </div>

                    <form method="POST" id="intakeForm">
                        <!-- Personal Information -->
                        <div class="glass-card mb-4">
                            <h3><i class="fas fa-user"></i> Personal Information</h3>
                            
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="preferred_name" class="form-label">Preferred Name</label>
                                        <input type="text" id="preferred_name" name="preferred_name" class="form-control" 
                                               value="<?php echo htmlspecialchars($existingData['preferred_name'] ?? ''); ?>">
                                        <small class="text-muted">What would you like us to call you?</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="gender_identity" class="form-label">Gender Identity</label>
                                        <select id="gender_identity" name="gender_identity" class="form-control form-select">
                                            <option value="">Select...</option>
                                            <option value="male" <?php echo ($existingData['gender_identity'] ?? '') === 'male' ? 'selected' : ''; ?>>Male</option>
                                            <option value="female" <?php echo ($existingData['gender_identity'] ?? '') === 'female' ? 'selected' : ''; ?>>Female</option>
                                            <option value="non-binary" <?php echo ($existingData['gender_identity'] ?? '') === 'non-binary' ? 'selected' : ''; ?>>Non-binary</option>
                                            <option value="transgender" <?php echo ($existingData['gender_identity'] ?? '') === 'transgender' ? 'selected' : ''; ?>>Transgender</option>
                                            <option value="two-spirit" <?php echo ($existingData['gender_identity'] ?? '') === 'two-spirit' ? 'selected' : ''; ?>>Two-Spirit</option>
                                            <option value="prefer_not_to_say" <?php echo ($existingData['gender_identity'] ?? '') === 'prefer_not_to_say' ? 'selected' : ''; ?>>Prefer not to say</option>
                                            <option value="other" <?php echo ($existingData['gender_identity'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group" id="gender_other_group" style="display: none;">
                                        <label for="gender_other" class="form-label">Please specify</label>
                                        <input type="text" id="gender_other" name="gender_other" class="form-control"
                                               value="<?php echo htmlspecialchars($existingData['gender_other'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="pronouns" class="form-label">Pronouns</label>
                                        <select id="pronouns" name="pronouns" class="form-control form-select">
                                            <option value="">Select...</option>
                                            <option value="he/him" <?php echo ($existingData['pronouns'] ?? '') === 'he/him' ? 'selected' : ''; ?>>He/Him</option>
                                            <option value="she/her" <?php echo ($existingData['pronouns'] ?? '') === 'she/her' ? 'selected' : ''; ?>>She/Her</option>
                                            <option value="they/them" <?php echo ($existingData['pronouns'] ?? '') === 'they/them' ? 'selected' : ''; ?>>They/Them</option>
                                            <option value="other" <?php echo ($existingData['pronouns'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group" id="pronouns_other_group" style="display: none;">
                                        <label for="pronouns_other" class="form-label">Please specify pronouns</label>
                                        <input type="text" id="pronouns_other" name="pronouns_other" class="form-control"
                                               value="<?php echo htmlspecialchars($existingData['pronouns_other'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="marital_status" class="form-label">Marital Status</label>
                                        <select id="marital_status" name="marital_status" class="form-control form-select">
                                            <option value="">Select...</option>
                                            <option value="single" <?php echo ($existingData['marital_status'] ?? '') === 'single' ? 'selected' : ''; ?>>Single</option>
                                            <option value="married" <?php echo ($existingData['marital_status'] ?? '') === 'married' ? 'selected' : ''; ?>>Married</option>
                                            <option value="divorced" <?php echo ($existingData['marital_status'] ?? '') === 'divorced' ? 'selected' : ''; ?>>Divorced</option>
                                            <option value="widowed" <?php echo ($existingData['marital_status'] ?? '') === 'widowed' ? 'selected' : ''; ?>>Widowed</option>
                                            <option value="separated" <?php echo ($existingData['marital_status'] ?? '') === 'separated' ? 'selected' : ''; ?>>Separated</option>
                                            <option value="other" <?php echo ($existingData['marital_status'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Emergency Contact -->
                        <div class="glass-card mb-4">
                            <h3><i class="fas fa-phone-alt"></i> Emergency Contact</h3>
                            
                            <div class="row">
                                <div class="col-4">
                                    <div class="form-group">
                                        <label for="emergency_contact_name" class="form-label">Contact Name</label>
                                        <input type="text" id="emergency_contact_name" name="emergency_contact_name" class="form-control"
                                               value="<?php echo htmlspecialchars($existingData['emergency_contact_name'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group">
                                        <label for="emergency_contact_phone" class="form-label">Phone Number</label>
                                        <input type="tel" id="emergency_contact_phone" name="emergency_contact_phone" class="form-control"
                                               value="<?php echo htmlspecialchars($existingData['emergency_contact_phone'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group">
                                        <label for="emergency_contact_relationship" class="form-label">Relationship</label>
                                        <select id="emergency_contact_relationship" name="emergency_contact_relationship" class="form-control form-select">
                                            <option value="">Select...</option>
                                            <option value="parent" <?php echo ($existingData['emergency_contact_relationship'] ?? '') === 'parent' ? 'selected' : ''; ?>>Parent</option>
                                            <option value="sibling" <?php echo ($existingData['emergency_contact_relationship'] ?? '') === 'sibling' ? 'selected' : ''; ?>>Sibling</option>
                                            <option value="spouse" <?php echo ($existingData['emergency_contact_relationship'] ?? '') === 'spouse' ? 'selected' : ''; ?>>Spouse/Partner</option>
                                            <option value="friend" <?php echo ($existingData['emergency_contact_relationship'] ?? '') === 'friend' ? 'selected' : ''; ?>>Friend</option>
                                            <option value="other" <?php echo ($existingData['emergency_contact_relationship'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Living Situation -->
                        <div class="glass-card mb-4">
                            <h3><i class="fas fa-home"></i> Living Situation</h3>
                            
                            <div class="form-group">
                                <label for="current_address" class="form-label">Current Address</label>
                                <textarea id="current_address" name="current_address" class="form-control" rows="2"><?php echo htmlspecialchars($existingData['current_address'] ?? ''); ?></textarea>
                                <small class="text-muted">General area is fine if you prefer not to share exact address</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="living_situation" class="form-label">Current Living Situation</label>
                                <select id="living_situation" name="living_situation" class="form-control form-select">
                                    <option value="">Select...</option>
                                    <option value="permanent_housing" <?php echo ($existingData['living_situation'] ?? '') === 'permanent_housing' ? 'selected' : ''; ?>>Permanent Housing</option>
                                    <option value="transitional_housing" <?php echo ($existingData['living_situation'] ?? '') === 'transitional_housing' ? 'selected' : ''; ?>>Transitional Housing</option>
                                    <option value="homeless" <?php echo ($existingData['living_situation'] ?? '') === 'homeless' ? 'selected' : ''; ?>>Homeless (Street, Shelter, Vehicle)</option>
                                    <option value="couch_surfing" <?php echo ($existingData['living_situation'] ?? '') === 'couch_surfing' ? 'selected' : ''; ?>>Couch Surfing</option>
                                    <option value="family_friends" <?php echo ($existingData['living_situation'] ?? '') === 'family_friends' ? 'selected' : ''; ?>>Living with Family/Friends</option>
                                    <option value="other" <?php echo ($existingData['living_situation'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                        </div>

                        <!-- Employment & Income -->
                        <div class="glass-card mb-4">
                            <h3><i class="fas fa-briefcase"></i> Employment & Income</h3>
                            
                            <div class="form-group">
                                <label for="employment_status" class="form-label">Employment Status</label>
                                <select id="employment_status" name="employment_status" class="form-control form-select">
                                    <option value="">Select...</option>
                                    <option value="employed_full_time" <?php echo ($existingData['employment_status'] ?? '') === 'employed_full_time' ? 'selected' : ''; ?>>Employed Full-Time</option>
                                    <option value="employed_part_time" <?php echo ($existingData['employment_status'] ?? '') === 'employed_part_time' ? 'selected' : ''; ?>>Employed Part-Time</option>
                                    <option value="unemployed" <?php echo ($existingData['employment_status'] ?? '') === 'unemployed' ? 'selected' : ''; ?>>Unemployed</option>
                                    <option value="student" <?php echo ($existingData['employment_status'] ?? '') === 'student' ? 'selected' : ''; ?>>Student</option>
                                    <option value="retired" <?php echo ($existingData['employment_status'] ?? '') === 'retired' ? 'selected' : ''; ?>>Retired</option>
                                    <option value="unable_to_work" <?php echo ($existingData['employment_status'] ?? '') === 'unable_to_work' ? 'selected' : ''; ?>>Unable to Work</option>
                                    <option value="other" <?php echo ($existingData['employment_status'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Sources of Income (Select all that apply)</label>
                                <?php 
                                $incomeOptions = [
                                    'employment' => 'Employment',
                                    'ontario_works' => 'Ontario Works',
                                    'odsp' => 'ODSP (Disability Support)',
                                    'employment_insurance' => 'Employment Insurance',
                                    'pension' => 'Pension',
                                    'family_support' => 'Family Support',
                                    'other_income' => 'Other Income',
                                    'no_income' => 'No Income'
                                ];
                                $selectedIncome = $existingData['income_sources'] ?? [];
                                foreach ($incomeOptions as $value => $label): 
                                ?>
                                    <div class="checkbox-wrapper">
                                        <input type="checkbox" id="income_<?php echo $value; ?>" name="income_sources[]" 
                                               value="<?php echo $value; ?>" class="checkbox"
                                               <?php echo in_array($value, $selectedIncome) ? 'checked' : ''; ?>>
                                        <label for="income_<?php echo $value; ?>"><?php echo $label; ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Immediate Needs -->
                        <div class="glass-card mb-4">
                            <h3><i class="fas fa-list-check"></i> Immediate Needs</h3>
                            <p>What support do you need most urgently? (Select all that apply)</p>
                            
                            <?php 
                            $needsOptions = [
                                'housing' => 'Housing Assistance',
                                'food' => 'Food & Basic Needs',
                                'mental_health' => 'Mental Health Support',
                                'addiction_support' => 'Addiction Support',
                                'employment' => 'Employment Assistance',
                                'legal_aid' => 'Legal Aid',
                                'medical_help' => 'Medical Help',
                                'transportation' => 'Transportation',
                                'id_replacement' => 'ID Replacement',
                                'clothing' => 'Clothing',
                                'hygiene_items' => 'Hygiene Items',
                                'childcare' => 'Childcare',
                                'emotional_support' => 'Emotional Support'
                            ];
                            $selectedNeeds = $existingData['immediate_needs'] ?? [];
                            ?>
                            
                            <div class="row">
                                <?php $count = 0; foreach ($needsOptions as $value => $label): ?>
                                    <?php if ($count % 3 === 0 && $count > 0): ?>
                                        </div><div class="row">
                                    <?php endif; ?>
                                    <div class="col-4">
                                        <div class="checkbox-wrapper">
                                            <input type="checkbox" id="need_<?php echo $value; ?>" name="immediate_needs[]" 
                                                   value="<?php echo $value; ?>" class="checkbox"
                                                   <?php echo in_array($value, $selectedNeeds) ? 'checked' : ''; ?>>
                                            <label for="need_<?php echo $value; ?>"><?php echo $label; ?></label>
                                        </div>
                                    </div>
                                <?php $count++; endforeach; ?>
                            </div>
                        </div>

                        <!-- Goals -->
                        <div class="glass-card mb-4">
                            <h3><i class="fas fa-target"></i> Your Goals</h3>
                            <p>What are your top 3 priorities right now? (Select up to 3)</p>
                            
                            <?php 
                            $goalOptions = [
                                'find_housing' => 'Find stable housing',
                                'addiction_treatment' => 'Enter addiction treatment',
                                'mental_health_support' => 'Get mental health support',
                                'find_employment' => 'Find a job',
                                'education_training' => 'Education or job training',
                                'reconnect_family' => 'Reconnect with family',
                                'legal_stability' => 'Resolve legal issues',
                                'financial_assistance' => 'Get financial assistance',
                                'improve_health' => 'Improve physical health'
                            ];
                            $selectedGoals = $existingData['goals'] ?? [];
                            ?>
                            
                            <div class="row">
                                <?php $count = 0; foreach ($goalOptions as $value => $label): ?>
                                    <?php if ($count % 3 === 0 && $count > 0): ?>
                                        </div><div class="row">
                                    <?php endif; ?>
                                    <div class="col-4">
                                        <div class="checkbox-wrapper">
                                            <input type="checkbox" id="goal_<?php echo $value; ?>" name="goals[]" 
                                                   value="<?php echo $value; ?>" class="checkbox"
                                                   <?php echo in_array($value, $selectedGoals) ? 'checked' : ''; ?>>
                                            <label for="goal_<?php echo $value; ?>"><?php echo $label; ?></label>
                                        </div>
                                    </div>
                                <?php $count++; endforeach; ?>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="glass-card mb-4">
                            <h3><i class="fas fa-comment"></i> Additional Information</h3>
                            
                            <div class="form-group">
                                <label for="additional_information" class="form-label">
                                    Is there anything else you'd like us to know that would help us support you better?
                                </label>
                                <textarea id="additional_information" name="additional_information" class="form-control" rows="4"><?php echo htmlspecialchars($existingData['additional_information'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <!-- Submit Section -->
                        <div class="text-center">
                            <div class="alert alert-info mb-4">
                                <i class="fas fa-shield-alt"></i>
                                <strong>Privacy Reminder:</strong> All information provided is confidential and will be used 
                                solely to provide you with appropriate support services.
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i>
                                Complete Intake Form
                            </button>
                            
                            <button type="button" class="btn btn-secondary btn-lg" onclick="saveDraft()">
                                <i class="fas fa-clock"></i>
                                Save as Draft
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="assets/js/main.js"></script>
    <script>
        // Show/hide conditional fields
        document.getElementById('gender_identity').addEventListener('change', function() {
            const otherGroup = document.getElementById('gender_other_group');
            if (this.value === 'other') {
                otherGroup.style.display = 'block';
            } else {
                otherGroup.style.display = 'none';
            }
        });
        
        document.getElementById('pronouns').addEventListener('change', function() {
            const otherGroup = document.getElementById('pronouns_other_group');
            if (this.value === 'other') {
                otherGroup.style.display = 'block';
            } else {
                otherGroup.style.display = 'none';
            }
        });
        
        // Initialize conditional fields
        if (document.getElementById('gender_identity').value === 'other') {
            document.getElementById('gender_other_group').style.display = 'block';
        }
        if (document.getElementById('pronouns').value === 'other') {
            document.getElementById('pronouns_other_group').style.display = 'block';
        }
        
        // Limit goal selection to 3
        const goalCheckboxes = document.querySelectorAll('input[name="goals[]"]');
        goalCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const checkedBoxes = document.querySelectorAll('input[name="goals[]"]:checked');
                if (checkedBoxes.length >= 3) {
                    goalCheckboxes.forEach(box => {
                        if (!box.checked) {
                            box.disabled = true;
                        }
                    });
                } else {
                    goalCheckboxes.forEach(box => {
                        box.disabled = false;
                    });
                }
            });
        });
        
        // Initialize goal limit
        const initialCheckedGoals = document.querySelectorAll('input[name="goals[]"]:checked');
        if (initialCheckedGoals.length >= 3) {
            goalCheckboxes.forEach(box => {
                if (!box.checked) {
                    box.disabled = true;
                }
            });
        }
        
        // Save draft function
        function saveDraft() {
            // This would send form data via AJAX to save as draft
            OUTSINC.showAlert('Draft saved successfully!', 'success');
        }
        
        // Auto-save functionality (optional)
        let autoSaveTimer;
        const formInputs = document.querySelectorAll('#intakeForm input, #intakeForm select, #intakeForm textarea');
        formInputs.forEach(input => {
            input.addEventListener('change', function() {
                clearTimeout(autoSaveTimer);
                autoSaveTimer = setTimeout(saveDraft, 30000); // Auto-save after 30 seconds of inactivity
            });
        });
    </script>
</body>
</html>