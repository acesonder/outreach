-- OUTSINC Database Schema
-- Database for Outreach Someone In Need of Change platform

CREATE DATABASE IF NOT EXISTS outsinc_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE outsinc_db;

-- Users table for authentication and basic info
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(20),
    date_of_birth DATE,
    password_hash VARCHAR(255) NOT NULL,
    security_question_id INT NOT NULL,
    security_answer_hash VARCHAR(255) NOT NULL,
    role ENUM('client', 'staff', 'outreach', 'admin', 'service_provider') DEFAULT 'client',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- Security questions for password recovery
CREATE TABLE security_questions (
    question_id INT AUTO_INCREMENT PRIMARY KEY,
    question_text VARCHAR(255) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE
);

-- Insert default security questions
INSERT INTO security_questions (question_text) VALUES
('What was the name of your first pet?'),
('What street did you grow up on?'),
('What was your mother\'s maiden name?'),
('What was the name of your elementary school?'),
('What city were you born in?'),
('What is your favorite color?'),
('What was the make of your first car?'),
('What is your favorite food?'),
('What month were you born?'),
('What is your favorite season?');

-- Consent and document tracking
CREATE TABLE consents (
    consent_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    consent_type ENUM('platform', 'sharing', 'advocacy', 'treatment') NOT NULL,
    consent_text TEXT,
    is_granted BOOLEAN DEFAULT FALSE,
    granted_at TIMESTAMP NULL,
    revoked_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Client profiles and extended information
CREATE TABLE client_profiles (
    profile_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    preferred_name VARCHAR(100),
    gender_identity ENUM('male', 'female', 'non-binary', 'transgender', 'two-spirit', 'prefer_not_to_say', 'other') DEFAULT 'prefer_not_to_say',
    gender_other VARCHAR(100),
    pronouns ENUM('he/him', 'she/her', 'they/them', 'other') DEFAULT 'they/them',
    pronouns_other VARCHAR(50),
    marital_status ENUM('single', 'married', 'divorced', 'widowed', 'separated', 'other'),
    emergency_contact_name VARCHAR(200),
    emergency_contact_phone VARCHAR(20),
    emergency_contact_relationship VARCHAR(100),
    current_address TEXT,
    living_situation ENUM('permanent_housing', 'transitional_housing', 'homeless', 'couch_surfing', 'family_friends', 'other'),
    employment_status ENUM('employed_full_time', 'employed_part_time', 'unemployed', 'student', 'retired', 'unable_to_work', 'other'),
    income_sources JSON, -- Array of income sources
    immediate_needs JSON, -- Array of immediate needs
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Intake forms and assessments
CREATE TABLE intakes (
    intake_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    intake_type ENUM('basic', 'advanced', 'drug_assessment', 'housing_assessment', 'needs_assessment') NOT NULL,
    status ENUM('started', 'in_progress', 'completed', 'pending_review') DEFAULT 'started',
    form_data JSON, -- Store all form responses
    completed_at TIMESTAMP NULL,
    reviewed_by INT NULL,
    reviewed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(user_id) ON DELETE SET NULL
);

-- Cases for case management
CREATE TABLE cases (
    case_id INT AUTO_INCREMENT PRIMARY KEY,
    case_title VARCHAR(255) NOT NULL,
    client_id INT NOT NULL,
    assigned_worker_id INT,
    case_type ENUM('housing', 'employment', 'mental_health', 'addiction_recovery', 'basic_needs', 'legal_support', 'medical_support', 'other') NOT NULL,
    priority_level ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    status ENUM('new', 'open', 'in_progress', 'pending', 'resolved', 'closed') DEFAULT 'new',
    description TEXT,
    goals TEXT,
    date_opened DATE NOT NULL,
    date_closed DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_worker_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- Case notes and updates
CREATE TABLE case_notes (
    note_id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NOT NULL,
    author_id INT NOT NULL,
    note_type ENUM('progress', 'contact', 'incident', 'referral', 'general') DEFAULT 'general',
    note_content TEXT NOT NULL,
    is_confidential BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES cases(case_id) ON DELETE CASCADE,
    FOREIGN KEY (author_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Tasks and appointments
CREATE TABLE tasks (
    task_id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT,
    assigned_to INT,
    created_by INT NOT NULL,
    task_title VARCHAR(255) NOT NULL,
    task_description TEXT,
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    due_date DATE,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES cases(case_id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(user_id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Appointments scheduling
CREATE TABLE appointments (
    appointment_id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    staff_id INT,
    appointment_type ENUM('intake', 'follow_up', 'assessment', 'counseling', 'group_session', 'other') NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    duration_minutes INT DEFAULT 60,
    status ENUM('scheduled', 'confirmed', 'arrived', 'no_show', 'cancelled', 'completed') DEFAULT 'scheduled',
    location VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (staff_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- Messages and communication
CREATE TABLE messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    recipient_id INT,
    case_id INT,
    subject VARCHAR(255),
    message_content TEXT NOT NULL,
    message_type ENUM('direct', 'case_note', 'system', 'broadcast') DEFAULT 'direct',
    is_read BOOLEAN DEFAULT FALSE,
    is_urgent BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (recipient_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (case_id) REFERENCES cases(case_id) ON DELETE CASCADE
);

-- Document storage and management
CREATE TABLE documents (
    document_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    case_id INT,
    document_name VARCHAR(255) NOT NULL,
    document_type ENUM('id', 'medical', 'consent', 'assessment', 'referral', 'other') NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT,
    mime_type VARCHAR(100),
    uploaded_by INT NOT NULL,
    is_confidential BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (case_id) REFERENCES cases(case_id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Service providers and referrals
CREATE TABLE service_providers (
    provider_id INT AUTO_INCREMENT PRIMARY KEY,
    organization_name VARCHAR(255) NOT NULL,
    contact_person VARCHAR(200),
    phone VARCHAR(20),
    email VARCHAR(255),
    address TEXT,
    services_offered JSON, -- Array of services
    operating_hours JSON, -- Operating schedule
    website VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Referrals tracking
CREATE TABLE referrals (
    referral_id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NOT NULL,
    provider_id INT NOT NULL,
    referred_by INT NOT NULL,
    referral_type VARCHAR(100) NOT NULL,
    urgency ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    status ENUM('pending', 'accepted', 'declined', 'completed', 'cancelled') DEFAULT 'pending',
    referral_notes TEXT,
    follow_up_date DATE,
    outcome_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES cases(case_id) ON DELETE CASCADE,
    FOREIGN KEY (provider_id) REFERENCES service_providers(provider_id) ON DELETE CASCADE,
    FOREIGN KEY (referred_by) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Outreach visits and logs
CREATE TABLE outreach_visits (
    visit_id INT AUTO_INCREMENT PRIMARY KEY,
    outreach_worker_id INT NOT NULL,
    client_id INT,
    visit_date DATE NOT NULL,
    visit_time TIME,
    location VARCHAR(255),
    visit_type ENUM('street_outreach', 'home_visit', 'shelter_visit', 'community_event', 'other') NOT NULL,
    services_provided JSON, -- Array of services provided
    visit_outcome ENUM('successful_engagement', 'follow_up_required', 'client_declined', 'referral_made', 'other'),
    risk_assessment ENUM('low', 'moderate', 'high'),
    visit_notes TEXT,
    follow_up_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (outreach_worker_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- Supply orders and harm reduction
CREATE TABLE supply_orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT,
    outreach_worker_id INT NOT NULL,
    order_date DATE NOT NULL,
    distribution_location VARCHAR(255),
    status ENUM('pending', 'prepared', 'distributed', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES users(user_id) ON DELETE SET NULL,
    FOREIGN KEY (outreach_worker_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Supply items
CREATE TABLE supply_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    item_name VARCHAR(100) NOT NULL,
    item_category ENUM('harm_reduction', 'hygiene', 'food', 'clothing', 'medical', 'other') NOT NULL,
    item_description TEXT,
    is_active BOOLEAN DEFAULT TRUE
);

-- Supply order items (junction table)
CREATE TABLE supply_order_items (
    order_id INT NOT NULL,
    item_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    notes VARCHAR(255),
    PRIMARY KEY (order_id, item_id),
    FOREIGN KEY (order_id) REFERENCES supply_orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES supply_items(item_id) ON DELETE CASCADE
);

-- Insert some basic supply items
INSERT INTO supply_items (item_name, item_category, item_description) VALUES
('Clean Syringes', 'harm_reduction', 'Sterile needles for safe injection'),
('Naloxone Kit', 'harm_reduction', 'Overdose reversal medication'),
('Safe Injection Kit', 'harm_reduction', 'Complete kit for safer drug use'),
('Condoms', 'harm_reduction', 'Protection for safer sex'),
('Alcohol Wipes', 'hygiene', 'Cleaning wipes for injection sites'),
('Soap', 'hygiene', 'Basic hygiene soap'),
('Toothbrush', 'hygiene', 'Dental hygiene'),
('Toothpaste', 'hygiene', 'Dental hygiene'),
('Socks', 'clothing', 'Clean socks'),
('Water Bottle', 'other', 'Hydration');

-- Incidents and safety tracking
CREATE TABLE incidents (
    incident_id INT AUTO_INCREMENT PRIMARY KEY,
    reported_by INT NOT NULL,
    client_id INT,
    incident_type ENUM('overdose', 'violence', 'theft', 'harassment', 'mental_health_crisis', 'other') NOT NULL,
    incident_date DATE NOT NULL,
    incident_time TIME,
    location VARCHAR(255),
    description TEXT NOT NULL,
    severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    status ENUM('reported', 'investigating', 'resolved', 'escalated') DEFAULT 'reported',
    follow_up_required BOOLEAN DEFAULT FALSE,
    resolution_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (reported_by) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- System settings and configuration
CREATE TABLE system_settings (
    setting_id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    updated_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(user_id) ON DELETE SET NULL
);

-- Insert default system settings
INSERT INTO system_settings (setting_key, setting_value, setting_type, description) VALUES
('site_name', 'OUTSINC', 'string', 'Name of the application'),
('maintenance_mode', 'false', 'boolean', 'Enable/disable maintenance mode'),
('max_file_upload_size', '10485760', 'integer', 'Maximum file upload size in bytes (10MB)'),
('session_timeout', '3600', 'integer', 'Session timeout in seconds'),
('enable_registration', 'true', 'boolean', 'Allow new user registration'),
('welcome_message', 'Welcome to OUTSINC - Outreach Someone In Need of Change', 'string', 'Welcome message for new users');

-- Audit log for tracking changes
CREATE TABLE audit_log (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(100),
    record_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- Create indexes for better performance
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_cases_client_id ON cases(client_id);
CREATE INDEX idx_cases_assigned_worker_id ON cases(assigned_worker_id);
CREATE INDEX idx_cases_status ON cases(status);
CREATE INDEX idx_messages_recipient_id ON messages(recipient_id);
CREATE INDEX idx_messages_is_read ON messages(is_read);
CREATE INDEX idx_appointments_client_id ON appointments(client_id);
CREATE INDEX idx_appointments_date ON appointments(appointment_date);
CREATE INDEX idx_tasks_assigned_to ON tasks(assigned_to);
CREATE INDEX idx_tasks_due_date ON tasks(due_date);
CREATE INDEX idx_outreach_visits_worker_id ON outreach_visits(outreach_worker_id);
CREATE INDEX idx_outreach_visits_date ON outreach_visits(visit_date);