# OUTSINC System Documentation

## Overview

OUTSINC (Outreach Someone In Need of Change) is a comprehensive web application designed to support individuals in need through integrated case management, client intake, referrals, and analytics. The system is built using only core web technologies: PHP, MySQL, HTML, CSS, and JavaScript without external frameworks or package managers.

## Architecture

### Technology Stack
- **Backend**: PHP 7.4+ with MySQLi
- **Database**: MySQL 5.7+ or MariaDB
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Icons**: FontAwesome 6.4.0 (CDN)
- **Server**: Apache or Nginx (LAMP/LEMP stack)

### File Structure
```
outreach/
├── index.php                 # Main landing page
├── login.php                 # Login processing
├── register.php              # Registration processing
├── dcide.php                 # DCIDE platform page
├── database/
│   └── schema.sql            # Database structure
├── includes/
│   ├── config.php           # Application configuration
│   ├── database.php         # Database connection and utilities
│   └── auth.php             # Authentication functions
├── assets/
│   ├── css/
│   │   └── styles.css       # Main stylesheet
│   └── js/
│       └── main.js          # JavaScript functionality
├── client/
│   ├── dashboard.php        # Client dashboard
│   └── intake.php           # Client intake form
├── staff/
│   └── (future staff files)
├── admin/
│   └── (future admin files)
├── provider/
│   └── (future provider files)
└── platforms/
    └── (future platform pages)
```

## Database Schema

### Core Tables

#### users
Primary user authentication and basic information
- `user_id`: Auto-increment primary key
- `username`: Unique system-generated username
- `email`: User email address (unique)
- `password_hash`: Bcrypt hashed password
- `role`: User role (client, staff, outreach, admin, service_provider)
- `security_question`/`security_answer`: Password recovery

#### client_profiles
Extended client information
- `user_id`: Foreign key to users table
- `preferred_name`, `gender_identity`, `pronouns`
- Contact and emergency information
- Living situation and employment status
- Health and substance use information

#### cases
Case management core
- `case_id`: Auto-increment primary key
- `client_id`: Foreign key to users table
- `assigned_worker_id`: Foreign key to users table
- `case_type`: Housing, Employment, Mental Health, etc.
- `priority_level`: Low, Medium, High, Critical
- `status`: New, Open, In Progress, Pending, Resolved, Closed

#### intake_forms
Flexible form storage using JSON
- `user_id`: Foreign key to users table
- `form_type`: Basic, Advanced, Housing, etc.
- `form_data`: JSON field for flexible data storage
- `completion_status`: Not Started, In Progress, Completed

## Features Implemented

### User Authentication
- Secure registration with auto-generated usernames
- Password strength validation (8+ chars, number, special character)
- Security question-based password recovery
- Role-based access control
- Session management with timeout

### Client Management
- Comprehensive intake forms with optional fields
- Profile completion tracking
- Dashboard with personalized information
- Case viewing and status tracking

### Responsive Design
- Mobile-first CSS approach
- Modern UI with animations and transitions
- Accessibility considerations
- FontAwesome icons for visual consistency

### Security Features
- SQL injection prevention using prepared statements
- XSS protection with output sanitization
- CSRF protection (recommended for production)
- Password hashing with PHP's password_hash()
- Input validation and sanitization

## Installation Instructions

### Prerequisites
1. Web server (Apache/Nginx)
2. PHP 7.4 or higher with MySQLi extension
3. MySQL 5.7+ or MariaDB
4. mod_rewrite enabled (for Apache)

### Setup Steps

1. **Download and Extract**
   ```bash
   git clone <repository-url>
   cd outreach
   ```

2. **Database Setup**
   ```bash
   mysql -u root -p < database/schema.sql
   ```

3. **Configure Database**
   Edit `includes/database.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USERNAME', 'your_username');
   define('DB_PASSWORD', 'your_password');
   define('DB_NAME', 'outsinc_db');
   ```

4. **Set File Permissions**
   ```bash
   chmod 644 *.php
   chmod 755 assets/ includes/ client/ -R
   chmod 600 includes/database.php
   ```

5. **Web Server Configuration**
   
   **Apache (.htaccess)**
   ```apache
   RewriteEngine On
   RewriteCond %{HTTPS} off
   RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   
   # Security headers
   Header always set X-Content-Type-Options nosniff
   Header always set X-Frame-Options DENY
   Header always set X-XSS-Protection "1; mode=block"
   ```

   **Nginx**
   ```nginx
   server {
       listen 80;
       server_name your-domain.com;
       root /path/to/outreach;
       index index.php;
       
       location ~ \.php$ {
           fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
           fastcgi_index index.php;
           include fastcgi_params;
           fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
       }
   }
   ```

6. **SSL Certificate**
   - Obtain SSL certificate (Let's Encrypt recommended)
   - Configure HTTPS redirects

### Default Admin Account
- Username: `admin`
- Password: `password` (change immediately)
- Email: `admin@outsinc.org`

## Configuration Options

### Application Settings (`includes/config.php`)
- `APP_URL`: Base application URL
- `PASSWORD_MIN_LENGTH`: Minimum password length
- `SESSION_TIMEOUT`: Session timeout in seconds
- `UPLOAD_MAX_SIZE`: Maximum file upload size

### Security Settings
- `MAX_LOGIN_ATTEMPTS`: Maximum failed login attempts
- `LOGIN_LOCKOUT_TIME`: Lockout duration in seconds
- Error reporting (disable in production)

## User Roles and Permissions

### Client
- Complete intake forms
- View own cases and appointments
- Send messages to staff
- Update profile information

### Staff
- Create and manage cases
- View client profiles (with consent)
- Schedule appointments
- Send/receive messages

### Outreach Worker
- Field access to case information
- Quick contact logging
- Mobile-optimized interface
- Safety alert system

### Service Provider
- View assigned referrals
- Update referral status
- Limited client information access

### Administrator
- Full system access
- User management
- System configuration
- Analytics and reporting

## Customization

### Adding New Platforms
1. Create platform page: `platforms/newplatform.php`
2. Add to `$platforms` array in `includes/config.php`
3. Update navigation menus

### Custom Form Fields
1. Modify `client/intake.php` for frontend
2. Update database schema if needed
3. Adjust validation in form processing

### Styling Customization
- Modify CSS variables in `assets/css/styles.css`
- Platform-specific colors defined in `:root`
- Responsive breakpoints at 768px and 480px

## Maintenance

### Regular Tasks
- Database backups (automated recommended)
- Log file rotation
- Security updates
- Performance monitoring

### Monitoring
- Check error logs regularly
- Monitor disk space usage
- Database performance tuning
- SSL certificate renewal

### Backup Strategy
```bash
# Database backup
mysqldump -u username -p outsinc_db > backup_$(date +%Y%m%d).sql

# File backup
tar -czf outreach_backup_$(date +%Y%m%d).tar.gz /path/to/outreach
```

## Security Considerations

### Production Checklist
- [ ] Change default admin password
- [ ] Disable error reporting
- [ ] Configure HTTPS
- [ ] Set up firewall rules
- [ ] Configure database user with minimal privileges
- [ ] Enable security headers
- [ ] Set up automated backups
- [ ] Configure log monitoring

### Data Protection
- All sensitive data encrypted at rest
- Secure session management
- GDPR/PIPEDA compliance considerations
- Regular security audits recommended

## Troubleshooting

### Common Issues

**Database Connection Errors**
- Check database credentials in `includes/database.php`
- Verify MySQL service is running
- Check firewall settings

**Permission Denied**
- Verify file permissions are set correctly
- Check web server user ownership
- Ensure include paths are accessible

**Session Issues**
- Check session.save_path permissions
- Verify session timeout settings
- Clear browser cache

**CSS/JS Not Loading**
- Check file paths in HTML
- Verify file permissions
- Check web server configuration

## Future Enhancements

### Planned Features
- Advanced reporting dashboard
- Mobile application integration
- Document upload system
- Calendar synchronization
- API endpoints for external integrations

### Scalability Considerations
- Database query optimization
- Caching implementation
- Load balancing for high traffic
- CDN integration for static assets

## Support and Documentation

### Resources
- System administrator guide
- User training materials
- API documentation (when available)
- Troubleshooting guides

### Contact Information
- Technical Support: tech@outsinc.org
- User Support: support@outsinc.org
- Emergency Contact: emergency@outsinc.org

---

*This documentation is maintained as part of the OUTSINC project. Last updated: <?php echo date('Y-m-d'); ?>*