-- OUTSINC Database Schema
-- Database for Outreach Someone In Need of Change platform
-- Created for LAMP/LEMP stack deployment

CREATE DATABASE IF NOT EXISTS outsinc_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE outsinc_db;

-- Users table for authentication and role management
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    date_of_birth DATE,
    phone VARCHAR(20),
    role ENUM('client', 'staff', 'outreach', 'admin', 'service_provider') DEFAULT 'client',
    security_question VARCHAR(255),
    security_answer VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Client profiles with detailed information
CREATE TABLE client_profiles (
    profile_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    preferred_name VARCHAR(50),
    gender_identity VARCHAR(50),
    pronouns VARCHAR(20),
    emergency_contact_name VARCHAR(100),
    emergency_contact_phone VARCHAR(20),
    emergency_contact_relationship VARCHAR(50),
    current_address TEXT,
    living_situation VARCHAR(100),
    employment_status VARCHAR(50),
    income_source VARCHAR(100),
    has_medical_conditions BOOLEAN DEFAULT FALSE,
    medical_conditions TEXT,
    has_disabilities BOOLEAN DEFAULT FALSE,
    disabilities TEXT,
    has_mental_health_concerns BOOLEAN DEFAULT FALSE,
    mental_health_concerns TEXT,
    substance_use_current BOOLEAN DEFAULT FALSE,
    substances_used TEXT,
    wants_substance_support BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Cases table for case management
CREATE TABLE cases (
    case_id INT AUTO_INCREMENT PRIMARY KEY,
    case_title VARCHAR(200) NOT NULL,
    client_id INT NOT NULL,
    assigned_worker_id INT,
    case_type ENUM('Housing', 'Employment', 'Mental Health', 'Addiction Recovery', 'Basic Needs', 'Legal Support', 'Medical Support', 'Other') NOT NULL,
    priority_level ENUM('Low', 'Medium', 'High', 'Critical') DEFAULT 'Medium',
    status ENUM('New', 'Open', 'In Progress', 'Pending', 'Resolved', 'Closed') DEFAULT 'New',
    description TEXT,
    date_opened DATE NOT NULL,
    date_closed DATE NULL,
    initial_assessment_notes TEXT,
    safety_concerns BOOLEAN DEFAULT FALSE,
    safety_notes TEXT,
    consent_for_services BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_worker_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- Goals associated with cases
CREATE TABLE goals (
    goal_id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NOT NULL,
    goal_title VARCHAR(200) NOT NULL,
    goal_description TEXT,
    target_date DATE,
    progress_status ENUM('Not Started', 'In Progress', 'Completed', 'On Hold') DEFAULT 'Not Started',
    completion_percentage INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES cases(case_id) ON DELETE CASCADE
);

-- Tasks for case action plans
CREATE TABLE tasks (
    task_id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NOT NULL,
    task_title VARCHAR(200) NOT NULL,
    task_description TEXT,
    assigned_to INT,
    due_date DATE,
    completion_status ENUM('Pending', 'In Progress', 'Completed', 'Cancelled') DEFAULT 'Pending',
    priority ENUM('Low', 'Medium', 'High') DEFAULT 'Medium',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES cases(case_id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(user_id) ON DELETE SET NULL
);

-- Intake forms and assessments
CREATE TABLE intake_forms (
    intake_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    form_type ENUM('Basic', 'Advanced', 'Housing', 'Substance_Use', 'Mental_Health') NOT NULL,
    form_data JSON,
    completion_status ENUM('Not Started', 'In Progress', 'Completed') DEFAULT 'Not Started',
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Referrals to external services
CREATE TABLE referrals (
    referral_id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    case_id INT,
    service_provider VARCHAR(200) NOT NULL,
    service_type VARCHAR(100) NOT NULL,
    referral_reason TEXT,
    referral_status ENUM('Pending', 'Accepted', 'Completed', 'Declined', 'No Show') DEFAULT 'Pending',
    urgency_level ENUM('Low', 'Medium', 'High', 'Emergency') DEFAULT 'Medium',
    referred_by INT NOT NULL,
    contact_made BOOLEAN DEFAULT FALSE,
    follow_up_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (case_id) REFERENCES cases(case_id) ON DELETE SET NULL,
    FOREIGN KEY (referred_by) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Messages for communication
CREATE TABLE messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    recipient_id INT NOT NULL,
    case_id INT,
    subject VARCHAR(200),
    message_body TEXT NOT NULL,
    message_type ENUM('Direct', 'Case Note', 'System Alert', 'Appointment Reminder') DEFAULT 'Direct',
    is_read BOOLEAN DEFAULT FALSE,
    is_urgent BOOLEAN DEFAULT FALSE,
    parent_message_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (recipient_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (case_id) REFERENCES cases(case_id) ON DELETE CASCADE,
    FOREIGN KEY (parent_message_id) REFERENCES messages(message_id) ON DELETE CASCADE
);

-- Appointments and scheduling
CREATE TABLE appointments (
    appointment_id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    staff_id INT NOT NULL,
    case_id INT,
    appointment_type VARCHAR(100) NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    duration_minutes INT DEFAULT 60,
    location VARCHAR(200),
    status ENUM('Scheduled', 'Confirmed', 'Completed', 'Cancelled', 'No Show') DEFAULT 'Scheduled',
    notes TEXT,
    reminder_sent BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (staff_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (case_id) REFERENCES cases(case_id) ON DELETE SET NULL
);

-- Documents and file uploads
CREATE TABLE documents (
    document_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    case_id INT,
    document_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    document_type ENUM('ID', 'Medical', 'Consent', 'Assessment', 'Referral', 'Other') DEFAULT 'Other',
    uploaded_by INT NOT NULL,
    is_confidential BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (case_id) REFERENCES cases(case_id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(user_id) ON DELETE CASCADE
);

-- System audit log
CREATE TABLE audit_log (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50),
    record_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- Insert default admin user
INSERT INTO users (username, email, password_hash, first_name, last_name, role) 
VALUES ('admin', 'admin@outsinc.org', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System', 'Administrator', 'admin');

-- Create indexes for better performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_cases_client ON cases(client_id);
CREATE INDEX idx_cases_status ON cases(status);
CREATE INDEX idx_cases_priority ON cases(priority_level);
CREATE INDEX idx_messages_recipient ON messages(recipient_id);
CREATE INDEX idx_messages_sender ON messages(sender_id);
CREATE INDEX idx_appointments_date ON appointments(appointment_date);
CREATE INDEX idx_documents_user ON documents(user_id);