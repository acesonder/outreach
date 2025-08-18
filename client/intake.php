<?php
/**
 * Client Intake Form for OUTSINC
 * Comprehensive intake process for new clients
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';

// Require client login
requireLogin('../index.php');
requireRole('client', '../index.php');

$currentUser = getCurrentUser();
$isWelcome = isset($_GET['welcome']) && $_GET['welcome'] == '1';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $formData = $_POST;
        
        // Remove CSRF and submit button from data
        unset($formData['submit']);
        
        // Save intake form data as JSON
        $query = "INSERT INTO intake_forms (user_id, form_type, form_data, completion_status, completed_at) 
                  VALUES (?, 'Basic', ?, 'Completed', NOW())
                  ON DUPLICATE KEY UPDATE 
                  form_data = VALUES(form_data), 
                  completion_status = 'Completed',
                  completed_at = NOW()";
        
        executeQuery($query, [$currentUser['user_id'], json_encode($formData)], 'is');
        
        // Update client profile with basic information
        $profileQuery = "UPDATE client_profiles SET 
                         preferred_name = ?, gender_identity = ?, pronouns = ?,
                         emergency_contact_name = ?, emergency_contact_phone = ?, 
                         emergency_contact_relationship = ?, current_address = ?,
                         living_situation = ?, employment_status = ?, income_source = ?,
                         has_medical_conditions = ?, medical_conditions = ?,
                         has_disabilities = ?, disabilities = ?,
                         has_mental_health_concerns = ?, mental_health_concerns = ?,
                         substance_use_current = ?, substances_used = ?, 
                         wants_substance_support = ?,
                         updated_at = CURRENT_TIMESTAMP
                         WHERE user_id = ?";
        
        $params = [
            $formData['preferred_name'] ?? '',
            $formData['gender_identity'] ?? '',
            $formData['pronouns'] ?? '',
            $formData['emergency_contact_name'] ?? '',
            $formData['emergency_contact_phone'] ?? '',
            $formData['emergency_contact_relationship'] ?? '',
            $formData['current_address'] ?? '',
            $formData['living_situation'] ?? '',
            $formData['employment_status'] ?? '',
            $formData['income_source'] ?? '',
            isset($formData['has_medical_conditions']),
            $formData['medical_conditions'] ?? '',
            isset($formData['has_disabilities']),
            $formData['disabilities'] ?? '',
            isset($formData['has_mental_health_concerns']),
            $formData['mental_health_concerns'] ?? '',
            isset($formData['substance_use_current']),
            $formData['substances_used'] ?? '',
            isset($formData['wants_substance_support']),
            $currentUser['user_id']
        ];
        
        executeQuery($profileQuery, $params, 'ssssssssssississsissi');
        
        // Log activity
        logActivity($currentUser['user_id'], 'complete_intake', 'intake_forms', null);
        
        header('Location: dashboard.php?message=intake_completed');
        exit;
        
    } catch (Exception $e) {
        error_log("Intake form error: " . $e->getMessage());
        $errorMessage = "Failed to save intake form. Please try again.";
    }
}

// Get existing intake data if available
$existingData = [];
try {
    $query = "SELECT form_data FROM intake_forms WHERE user_id = ? AND form_type = 'Basic'";
    $result = executeQuery($query, [$currentUser['user_id']], 'i');
    if ($result->num_rows > 0) {
        $intakeRow = $result->fetch_assoc();
        $existingData = json_decode($intakeRow['form_data'], true) ?: [];
    }
} catch (Exception $e) {
    error_log("Error fetching intake data: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Intake - OUTSINC</title>
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
                        <a href="../index.php?action=logout" class="nav-link">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="container" style="max-width: 800px; margin-top: var(--spacing-xl);">
            <?php if ($isWelcome): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                Welcome to OUTSINC! Please complete this intake form to get started. All fields are optional, 
                but providing more information helps us serve you better.
            </div>
            <?php endif; ?>

            <?php if (isset($errorMessage)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo sanitizeOutput($errorMessage); ?>
            </div>
            <?php endif; ?>

            <div class="card">
                <h1>Client Intake Form</h1>
                <p>Hello <?php echo sanitizeOutput($currentUser['first_name']); ?>! This form helps us understand your needs and preferences.</p>

                <form method="POST" data-validate>
                    <!-- Personal Information -->
                    <h3><i class="fas fa-user"></i> Personal Information</h3>
                    
                    <div class="grid grid-2">
                        <div class="form-group">
                            <label for="preferred_name" class="form-label">Preferred Name</label>
                            <input type="text" id="preferred_name" name="preferred_name" class="form-input" 
                                   value="<?php echo sanitizeOutput($existingData['preferred_name'] ?? ''); ?>"
                                   placeholder="How would you like to be called?">
                        </div>
                        
                        <div class="form-group">
                            <label for="gender_identity" class="form-label">Gender Identity</label>
                            <select id="gender_identity" name="gender_identity" class="form-select">
                                <option value="">Select if you'd like to share</option>
                                <option value="Male" <?php echo ($existingData['gender_identity'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo ($existingData['gender_identity'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                                <option value="Non-binary" <?php echo ($existingData['gender_identity'] ?? '') === 'Non-binary' ? 'selected' : ''; ?>>Non-binary</option>
                                <option value="Two-Spirit" <?php echo ($existingData['gender_identity'] ?? '') === 'Two-Spirit' ? 'selected' : ''; ?>>Two-Spirit</option>
                                <option value="Transgender" <?php echo ($existingData['gender_identity'] ?? '') === 'Transgender' ? 'selected' : ''; ?>>Transgender</option>
                                <option value="Prefer not to say" <?php echo ($existingData['gender_identity'] ?? '') === 'Prefer not to say' ? 'selected' : ''; ?>>Prefer not to say</option>
                                <option value="Other" <?php echo ($existingData['gender_identity'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="pronouns" class="form-label">Pronouns</label>
                        <select id="pronouns" name="pronouns" class="form-select">
                            <option value="">Select your pronouns</option>
                            <option value="He/Him" <?php echo ($existingData['pronouns'] ?? '') === 'He/Him' ? 'selected' : ''; ?>>He/Him</option>
                            <option value="She/Her" <?php echo ($existingData['pronouns'] ?? '') === 'She/Her' ? 'selected' : ''; ?>>She/Her</option>
                            <option value="They/Them" <?php echo ($existingData['pronouns'] ?? '') === 'They/Them' ? 'selected' : ''; ?>>They/Them</option>
                            <option value="Other" <?php echo ($existingData['pronouns'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>

                    <!-- Contact Information -->
                    <h3><i class="fas fa-address-book"></i> Contact & Emergency Information</h3>
                    
                    <div class="form-group">
                        <label for="current_address" class="form-label">Current Address</label>
                        <textarea id="current_address" name="current_address" class="form-textarea" rows="3"
                                  placeholder="Where are you currently staying?"><?php echo sanitizeOutput($existingData['current_address'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="grid grid-3">
                        <div class="form-group">
                            <label for="emergency_contact_name" class="form-label">Emergency Contact Name</label>
                            <input type="text" id="emergency_contact_name" name="emergency_contact_name" class="form-input"
                                   value="<?php echo sanitizeOutput($existingData['emergency_contact_name'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="emergency_contact_phone" class="form-label">Emergency Contact Phone</label>
                            <input type="tel" id="emergency_contact_phone" name="emergency_contact_phone" class="form-input"
                                   value="<?php echo sanitizeOutput($existingData['emergency_contact_phone'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="emergency_contact_relationship" class="form-label">Relationship</label>
                            <select id="emergency_contact_relationship" name="emergency_contact_relationship" class="form-select">
                                <option value="">Select relationship</option>
                                <option value="Parent" <?php echo ($existingData['emergency_contact_relationship'] ?? '') === 'Parent' ? 'selected' : ''; ?>>Parent</option>
                                <option value="Sibling" <?php echo ($existingData['emergency_contact_relationship'] ?? '') === 'Sibling' ? 'selected' : ''; ?>>Sibling</option>
                                <option value="Spouse" <?php echo ($existingData['emergency_contact_relationship'] ?? '') === 'Spouse' ? 'selected' : ''; ?>>Spouse</option>
                                <option value="Friend" <?php echo ($existingData['emergency_contact_relationship'] ?? '') === 'Friend' ? 'selected' : ''; ?>>Friend</option>
                                <option value="Other" <?php echo ($existingData['emergency_contact_relationship'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                    </div>

                    <!-- Living Situation -->
                    <h3><i class="fas fa-home"></i> Living & Employment Situation</h3>
                    
                    <div class="grid grid-2">
                        <div class="form-group">
                            <label for="living_situation" class="form-label">Current Living Situation</label>
                            <select id="living_situation" name="living_situation" class="form-select">
                                <option value="">Select your situation</option>
                                <option value="Permanent Housing" <?php echo ($existingData['living_situation'] ?? '') === 'Permanent Housing' ? 'selected' : ''; ?>>Permanent Housing</option>
                                <option value="Transitional Housing" <?php echo ($existingData['living_situation'] ?? '') === 'Transitional Housing' ? 'selected' : ''; ?>>Transitional Housing</option>
                                <option value="Homeless (Street, Shelter, Vehicle)" <?php echo ($existingData['living_situation'] ?? '') === 'Homeless (Street, Shelter, Vehicle)' ? 'selected' : ''; ?>>Homeless (Street, Shelter, Vehicle)</option>
                                <option value="Couch Surfing" <?php echo ($existingData['living_situation'] ?? '') === 'Couch Surfing' ? 'selected' : ''; ?>>Couch Surfing</option>
                                <option value="Living with Family/Friends" <?php echo ($existingData['living_situation'] ?? '') === 'Living with Family/Friends' ? 'selected' : ''; ?>>Living with Family/Friends</option>
                                <option value="Other" <?php echo ($existingData['living_situation'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="employment_status" class="form-label">Employment Status</label>
                            <select id="employment_status" name="employment_status" class="form-select">
                                <option value="">Select your status</option>
                                <option value="Employed Full-Time" <?php echo ($existingData['employment_status'] ?? '') === 'Employed Full-Time' ? 'selected' : ''; ?>>Employed Full-Time</option>
                                <option value="Employed Part-Time" <?php echo ($existingData['employment_status'] ?? '') === 'Employed Part-Time' ? 'selected' : ''; ?>>Employed Part-Time</option>
                                <option value="Unemployed" <?php echo ($existingData['employment_status'] ?? '') === 'Unemployed' ? 'selected' : ''; ?>>Unemployed</option>
                                <option value="Student" <?php echo ($existingData['employment_status'] ?? '') === 'Student' ? 'selected' : ''; ?>>Student</option>
                                <option value="Retired" <?php echo ($existingData['employment_status'] ?? '') === 'Retired' ? 'selected' : ''; ?>>Retired</option>
                                <option value="Other" <?php echo ($existingData['employment_status'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="income_source" class="form-label">Source of Income</label>
                        <select id="income_source" name="income_source" class="form-select">
                            <option value="">Select your income source</option>
                            <option value="Employment" <?php echo ($existingData['income_source'] ?? '') === 'Employment' ? 'selected' : ''; ?>>Employment</option>
                            <option value="Disability Support" <?php echo ($existingData['income_source'] ?? '') === 'Disability Support' ? 'selected' : ''; ?>>Disability Support</option>
                            <option value="Social Assistance" <?php echo ($existingData['income_source'] ?? '') === 'Social Assistance' ? 'selected' : ''; ?>>Social Assistance</option>
                            <option value="Pension" <?php echo ($existingData['income_source'] ?? '') === 'Pension' ? 'selected' : ''; ?>>Pension</option>
                            <option value="No Income" <?php echo ($existingData['income_source'] ?? '') === 'No Income' ? 'selected' : ''; ?>>No Income</option>
                            <option value="Other" <?php echo ($existingData['income_source'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>

                    <!-- Health Information -->
                    <h3><i class="fas fa-heartbeat"></i> Health & Wellness</h3>
                    
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: var(--spacing-sm);">
                            <input type="checkbox" name="has_medical_conditions" value="1" 
                                   <?php echo isset($existingData['has_medical_conditions']) ? 'checked' : ''; ?>>
                            I have medical conditions that affect my daily life
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label for="medical_conditions" class="form-label">Medical Conditions (if any)</label>
                        <textarea id="medical_conditions" name="medical_conditions" class="form-textarea" rows="3"
                                  placeholder="Please describe any medical conditions, medications, or health concerns"><?php echo sanitizeOutput($existingData['medical_conditions'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: var(--spacing-sm);">
                            <input type="checkbox" name="has_disabilities" value="1"
                                   <?php echo isset($existingData['has_disabilities']) ? 'checked' : ''; ?>>
                            I have a disability or require accommodations
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label for="disabilities" class="form-label">Disabilities or Accommodations Needed</label>
                        <textarea id="disabilities" name="disabilities" class="form-textarea" rows="3"
                                  placeholder="Please describe any disabilities or accommodations you need"><?php echo sanitizeOutput($existingData['disabilities'] ?? ''); ?></textarea>
                    </div>

                    <!-- Mental Health -->
                    <h3><i class="fas fa-brain"></i> Mental Health & Substance Use</h3>
                    
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: var(--spacing-sm);">
                            <input type="checkbox" name="has_mental_health_concerns" value="1"
                                   <?php echo isset($existingData['has_mental_health_concerns']) ? 'checked' : ''; ?>>
                            I have mental health concerns or receive mental health services
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label for="mental_health_concerns" class="form-label">Mental Health Information</label>
                        <textarea id="mental_health_concerns" name="mental_health_concerns" class="form-textarea" rows="3"
                                  placeholder="Please share any mental health information that would help us support you"><?php echo sanitizeOutput($existingData['mental_health_concerns'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: var(--spacing-sm);">
                            <input type="checkbox" name="substance_use_current" value="1"
                                   <?php echo isset($existingData['substance_use_current']) ? 'checked' : ''; ?>>
                            I currently use substances (alcohol, drugs, etc.)
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label for="substances_used" class="form-label">Substances Used (if applicable)</label>
                        <textarea id="substances_used" name="substances_used" class="form-textarea" rows="3"
                                  placeholder="If comfortable sharing, please list substances you use"><?php echo sanitizeOutput($existingData['substances_used'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: var(--spacing-sm);">
                            <input type="checkbox" name="wants_substance_support" value="1"
                                   <?php echo isset($existingData['wants_substance_support']) ? 'checked' : ''; ?>>
                            I would like support with substance use
                        </label>
                    </div>

                    <!-- Submit -->
                    <div class="form-group mt-xl">
                        <button type="submit" name="submit" class="btn btn-primary btn-large" style="width: 100%;">
                            <i class="fas fa-save"></i> Save Intake Form
                        </button>
                    </div>
                    
                    <div class="text-center mt-md">
                        <p><small>All information is kept confidential and secure. You can update this information at any time.</small></p>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script src="../assets/js/main.js"></script>
</body>
</html>