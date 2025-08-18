-- OUTSINC Complete Database Schema
USE outsinc_db;

-- Users table with role-based access
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(20) UNIQUE NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    date_of_birth DATE,
    password_hash VARCHAR(255) NOT NULL,
    security_question_id INT NOT NULL,
    security_answer_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    INDEX idx_username (username),
    INDEX idx_created_at (created_at)
);

-- Roles table
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name ENUM('anonymous', 'client', 'staff', 'admin', 'service') NOT NULL,
    description TEXT,
    permissions JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- User roles junction table
CREATE TABLE user_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    role_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    assigned_by INT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id),
    UNIQUE KEY unique_user_role (user_id, role_id)
);

-- Security questions
CREATE TABLE security_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question TEXT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Consents table for PIPEDA/PHIPA compliance
CREATE TABLE consents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    consent_type ENUM('platform', 'data_sharing', 'communication', 'research') NOT NULL,
    consent_text TEXT NOT NULL,
    is_granted BOOLEAN NOT NULL,
    granted_at TIMESTAMP NULL,
    revoked_at TIMESTAMP NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_consent (user_id, consent_type)
);

-- Documents table
CREATE TABLE documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    document_type ENUM('consent', 'id', 'release', 'assessment', 'other') NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    is_signed BOOLEAN DEFAULT FALSE,
    signed_at TIMESTAMP NULL,
    uploaded_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id),
    INDEX idx_user_docs (user_id),
    INDEX idx_signed (is_signed, signed_at)
);

-- Client profiles
CREATE TABLE client_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    alias VARCHAR(100),
    contact_phone VARCHAR(20),
    contact_email VARCHAR(255),
    emergency_contact_name VARCHAR(200),
    emergency_contact_phone VARCHAR(20),
    preferred_language VARCHAR(50) DEFAULT 'English',
    accessibility_needs JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_profile (user_id)
);

-- Intakes table
CREATE TABLE intakes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    staff_id INT,
    status ENUM('not_started', 'in_progress', 'completed', 'on_hold') DEFAULT 'not_started',
    progress_percentage DECIMAL(5,2) DEFAULT 0.00,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    last_updated_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (staff_id) REFERENCES users(id),
    FOREIGN KEY (last_updated_by) REFERENCES users(id),
    INDEX idx_user_intake (user_id),
    INDEX idx_status (status)
);

-- Assessments table with traffic-light scoring
CREATE TABLE assessments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    intake_id INT NOT NULL,
    domain VARCHAR(100) NOT NULL,
    questions JSON NOT NULL,
    responses JSON,
    risk_level ENUM('green', 'yellow', 'red') DEFAULT 'green',
    score INT DEFAULT 0,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (intake_id) REFERENCES intakes(id) ON DELETE CASCADE,
    INDEX idx_intake_assessment (intake_id),
    INDEX idx_risk_level (risk_level)
);

-- Tasks table
CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    assigned_to INT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    due_date DATE,
    completed_at TIMESTAMP NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_user_tasks (user_id),
    INDEX idx_assigned_tasks (assigned_to),
    INDEX idx_status_priority (status, priority)
);

-- Appointments table
CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    staff_id INT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    duration_minutes INT DEFAULT 60,
    location VARCHAR(255),
    appointment_type ENUM('phone', 'office', 'home_visit', 'virtual') DEFAULT 'office',
    status ENUM('scheduled', 'confirmed', 'completed', 'cancelled', 'no_show') DEFAULT 'scheduled',
    arrival_status ENUM('scheduled', 'arrived', 'late', 'no_show') DEFAULT 'scheduled',
    staff_name VARCHAR(200),
    notes TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (staff_id) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_user_appointments (user_id),
    INDEX idx_staff_appointments (staff_id),
    INDEX idx_appointment_date (appointment_date)
);

-- Messages table
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    recipient_id INT NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message_text TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    sender_name VARCHAR(200),
    recipient_name VARCHAR(200),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_recipient_messages (recipient_id),
    INDEX idx_sender_messages (sender_id)
);

-- Goals table
CREATE TABLE goals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    target_date DATE,
    progress_percentage DECIMAL(5,2) DEFAULT 0.00,
    status ENUM('active', 'in_progress', 'completed', 'on_hold', 'cancelled') DEFAULT 'active',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_user_goals (user_id)
);

-- Referrals table
CREATE TABLE referrals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    referring_staff_id INT NOT NULL,
    service_type VARCHAR(100) NOT NULL,
    partner_organization VARCHAR(255),
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    warm_handoff_status ENUM('pending', 'confirmed', 'failed') DEFAULT 'pending',
    referral_date DATE NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (referring_staff_id) REFERENCES users(id),
    INDEX idx_user_referrals (user_id),
    INDEX idx_staff_referrals (referring_staff_id)
);

-- Audit log for compliance
CREATE TABLE audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    details JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_audit (user_id),
    INDEX idx_action_audit (action),
    INDEX idx_created_audit (created_at)
);

-- Insert default security questions
INSERT INTO security_questions (question) VALUES
('What was the name of your first pet?'),
('What street did you grow up on?'),
('What was your mother''s maiden name?'),
('What was the name of your first school?'),
('What is your favorite color?'),
('What was the make of your first car?'),
('What city were you born in?');

-- Insert default roles
INSERT INTO roles (name, description, permissions) VALUES
('anonymous', 'Anonymous user with limited access', '{"view_public": true}'),
('client', 'Service recipient with access to personal dashboard', '{"view_own_data": true, "update_own_profile": true, "complete_assessments": true}'),
('staff', 'Case manager with client management capabilities', '{"view_client_data": true, "manage_cases": true, "create_tasks": true, "view_reports": true}'),
('admin', 'System administrator with full access', '{"full_access": true}'),
('service', 'Service provider with limited client interaction', '{"view_assigned_clients": true, "update_service_records": true}');

-- Create demo users with proper password hashes (will be updated with real hashes)
INSERT INTO users (username, first_name, last_name, date_of_birth, password_hash, security_question_id, security_answer_hash) VALUES
('ADMIN001', 'System', 'Administrator', '1990-01-01', 'temp_hash', 1, 'temp_hash'),
('STAFF001', 'Jane', 'Smith', '1985-05-15', 'temp_hash', 2, 'temp_hash'),
('CLIENT001', 'John', 'Doe', '1992-03-20', 'temp_hash', 3, 'temp_hash');

-- Assign roles to demo users
INSERT INTO user_roles (user_id, role_id) 
SELECT u.id, r.id 
FROM users u, roles r 
WHERE (u.username = 'ADMIN001' AND r.name = 'admin')
   OR (u.username = 'STAFF001' AND r.name = 'staff')
   OR (u.username = 'CLIENT001' AND r.name = 'client');

-- Create client profile for demo client
INSERT INTO client_profiles (user_id, contact_phone, contact_email) 
SELECT id, '555-123-4567', 'john.doe@example.com'
FROM users 
WHERE username = 'CLIENT001';

-- Create demo consent records
INSERT INTO consents (user_id, consent_type, consent_text, is_granted, granted_at, ip_address) 
SELECT u.id, 'platform', 'I consent to the collection and use of my personal information for the purpose of receiving support services through the OUTSINC platform.', TRUE, NOW(), '127.0.0.1'
FROM users u 
WHERE u.username = 'CLIENT001';

-- Create demo signed document
INSERT INTO documents (user_id, document_type, file_name, file_path, file_size, mime_type, is_signed, signed_at, uploaded_by) 
SELECT u.id, 'consent', 'consent_form.pdf', '/uploads/consent_form.pdf', 1024, 'application/pdf', TRUE, NOW(), u.id
FROM users u 
WHERE u.username = 'CLIENT001';

-- Create demo intake
INSERT INTO intakes (user_id, status, progress_percentage, started_at) 
SELECT id, 'in_progress', 25.00, NOW()
FROM users 
WHERE username = 'CLIENT001';

-- Create demo assessment
INSERT INTO assessments (intake_id, domain, questions, responses, risk_level, score, completed_at) 
SELECT i.id, 'basic_info', '{}', '{"preferred_name": "John", "pronouns": "he/him", "phone": "555-123-4567"}', 'green', 3, NOW()
FROM intakes i
JOIN users u ON i.user_id = u.id
WHERE u.username = 'CLIENT001';

COMMIT;
