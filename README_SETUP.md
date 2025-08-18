# OUTSINC Web Application Setup Instructions

## Overview
OUTSINC (Outreach Someone In Need of Change) is a comprehensive web application built with PHP, MySQL, HTML, CSS, and JavaScript. It provides support services for individuals facing homelessness, addiction, and life challenges.

## System Requirements

### Server Requirements
- **PHP**: 7.4 or higher (PHP 8.0+ recommended)
- **MySQL**: 5.7 or higher (MySQL 8.0+ recommended)
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Operating System**: Linux, Windows, or macOS

### PHP Extensions Required
- PDO and PDO_MySQL
- JSON
- OpenSSL
- Ctype
- Fileinfo
- Session

## Installation Steps

### 1. Environment Setup

#### For LAMP Stack (Linux/Apache/MySQL/PHP):
```bash
# Ubuntu/Debian
sudo apt update
sudo apt install apache2 mysql-server php php-mysql php-json php-openssl

# Enable Apache modules
sudo a2enmod rewrite
sudo systemctl restart apache2

# Start services
sudo systemctl start mysql
sudo systemctl enable mysql apache2
```

#### For XAMPP (Cross-platform):
1. Download XAMPP from https://www.apachefriends.org/
2. Install XAMPP
3. Start Apache and MySQL services

### 2. Database Setup

1. **Create Database User:**
```sql
-- Login to MySQL as root
mysql -u root -p

-- Create database and user
CREATE DATABASE outsinc_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'outsinc_user'@'localhost' IDENTIFIED BY 'your_secure_password_here';
GRANT ALL PRIVILEGES ON outsinc_db.* TO 'outsinc_user'@'localhost';
FLUSH PRIVILEGES;
```

2. **Import Database Schema:**
```bash
mysql -u outsinc_user -p outsinc_db < database/schema.sql
```

### 3. Application Setup

1. **Clone/Download Files:**
```bash
# If using Git
git clone [repository-url] /var/www/html/outreach

# Or extract files to web directory
# Windows XAMPP: C:\xampp\htdocs\outreach
# Linux Apache: /var/www/html/outreach
```

2. **Configure Database Connection:**
Edit `includes/config.php` and update these constants:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'outsinc_db');
define('DB_USER', 'outsinc_user');
define('DB_PASS', 'your_secure_password_here');
```

3. **Set File Permissions:**
```bash
# Linux/macOS
sudo chown -R www-data:www-data /var/www/html/outreach
sudo chmod -R 755 /var/www/html/outreach
sudo chmod -R 777 /var/www/html/outreach/uploads
```

4. **Configure Security:**
- Change `ENCRYPTION_KEY` in `includes/config.php`
- Update database passwords
- Set appropriate file permissions

### 4. Web Server Configuration

#### Apache (.htaccess)
Create `.htaccess` in the application root:
```apache
RewriteEngine On

# Redirect HTTP to HTTPS (optional)
# RewriteCond %{HTTPS} off
# RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Pretty URLs
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^([^/]+)/?$ $1.php [L,QSA]

# Security headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# Hide sensitive files
<Files "config.php">
    Order allow,deny
    Deny from all
</Files>
```

#### Nginx Configuration
Add to your Nginx server block:
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/html/outreach;
    index index.php;

    location / {
        try_files $uri $uri/ $uri.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Security
    location ~ /includes/ {
        deny all;
    }
    
    location ~ /database/ {
        deny all;
    }
}
```

## Configuration

### 1. System Settings
Access the application and configure:
- Site name and branding
- Contact information
- Registration settings
- Email configuration (if implementing)

### 2. Create Admin User
```sql
-- Insert admin user directly into database
INSERT INTO users (username, first_name, last_name, email, password_hash, security_question_id, security_answer_hash, role, status) 
VALUES (
    'ADMIN01', 
    'Admin', 
    'User', 
    'admin@outsinc.org', 
    '$2y$10$your_hashed_password', 
    1, 
    '$2y$10$your_hashed_security_answer', 
    'admin', 
    'active'
);
```

### 3. Default Data
The schema includes default data for:
- Security questions
- System settings
- Supply items
- Basic configuration

## Features

### User Roles
- **Client**: Self-registration, intake forms, case viewing
- **Staff**: Case management, client support
- **Outreach**: Field work, visit logging, supply orders
- **Admin**: Full system administration
- **Service Provider**: Referral management

### Core Modules
- **User Management**: Registration, authentication, profiles
- **Intake System**: Comprehensive client assessment forms
- **Case Management**: Track client progress and goals
- **Messaging**: Internal communication system
- **Appointments**: Scheduling and calendar management
- **Document Management**: File uploads and storage
- **Reporting**: Analytics and progress tracking

### Platform Integration
- **DCIDE**: Case management core
- **LINK**: Referral engine
- **BLES**: Addiction recovery intake
- **ASK**: Crisis support chat
- **ETHAN**: Wellness and learning
- **FOOTPRINT**: Outreach logging

## Security Features

- Password hashing with PHP `password_hash()`
- SQL injection prevention with prepared statements
- XSS protection with input sanitization
- Session management with timeout
- Activity logging and audit trails
- Role-based access control
- CSRF protection (implement tokens as needed)

## Maintenance

### Regular Tasks
1. **Database Backups**:
```bash
mysqldump -u outsinc_user -p outsinc_db > backup_$(date +%Y%m%d).sql
```

2. **Log Rotation**:
Monitor and rotate PHP error logs and application logs

3. **Security Updates**:
Keep PHP, MySQL, and web server updated

4. **File Cleanup**:
Regularly clean uploaded files and temporary data

### Monitoring
- Check database growth and performance
- Monitor user activity and system usage
- Review audit logs for security issues
- Test backup and restore procedures

## Troubleshooting

### Common Issues

1. **Database Connection Failed**:
   - Check database credentials in config.php
   - Verify MySQL service is running
   - Check database user permissions

2. **Permission Denied**:
   - Check file/folder permissions
   - Ensure web server can read/write files
   - Verify uploads directory is writable

3. **Session Issues**:
   - Check PHP session configuration
   - Verify session directory permissions
   - Clear browser cookies if needed

4. **Page Not Found**:
   - Check web server URL rewriting
   - Verify .htaccess file exists and is readable
   - Check file paths and extensions

### Debug Mode
For development, enable debug mode in `includes/config.php`:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

**Important**: Disable debug mode in production!

## Support

For technical support or questions about OUTSINC:
- Review documentation and comments in code files
- Check the database schema for data relationships
- Examine the application logs for errors
- Test functionality in a development environment first

## License

This application is built for OUTSINC (Outreach Someone In Need of Change) and contains proprietary code and configurations specific to their operations.

---

**Note**: This setup guide provides the basic installation steps. Additional configuration may be required based on your specific server environment and requirements.