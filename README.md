# OUTSINC - Outreach Someone In Need of Change

A comprehensive web application for case management, client intake, and community support services. Built with pure PHP, MySQL, HTML, CSS, and JavaScript - no external frameworks or dependencies required.

## 🌟 Features

- **Multi-Role User System**: Clients, Staff, Outreach Workers, Service Providers, and Administrators
- **Comprehensive Intake Forms**: Flexible form system with optional fields and JSON storage
- **Case Management**: Full case lifecycle tracking with status updates and progress monitoring
- **Responsive Design**: Modern UI with animations, mobile-first approach
- **Security**: Secure authentication, SQL injection prevention, XSS protection
- **Platform Integration**: Multiple specialized platforms (DCIDE, LINK, BLES, ASK, ETHAN, Footprint)

## 🚀 Quick Start

### Prerequisites
- PHP 7.4+ with MySQLi extension
- MySQL 5.7+ or MariaDB
- Web server (Apache/Nginx)

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd outreach
   ```

2. **Set up the database**
   ```bash
   mysql -u root -p < database/schema.sql
   ```

3. **Configure database connection**
   Edit `includes/database.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USERNAME', 'your_username');
   define('DB_PASSWORD', 'your_password');
   define('DB_NAME', 'outsinc_db');
   ```

4. **Set permissions**
   ```bash
   chmod 644 *.php
   chmod 755 assets/ includes/ client/ -R
   chmod 600 includes/database.php
   ```

5. **Access the application**
   - Navigate to your domain/server
   - Default admin login: `admin` / `password` (change immediately)

## 📱 User Roles

### Client
- Complete intake forms
- View personal cases and appointments
- Send messages to staff
- Track personal progress

### Staff & Outreach Workers
- Create and manage cases
- Access client profiles (with consent)
- Schedule appointments
- Field data entry and updates

### Administrators
- Full system oversight
- User management
- Analytics and reporting
- System configuration

## 🏗️ Architecture

### File Structure
```
outreach/
├── index.php              # Main landing page
├── login.php              # Authentication processing
├── register.php           # User registration
├── database/
│   └── schema.sql         # Database structure
├── includes/
│   ├── config.php        # App configuration
│   ├── database.php      # DB connection
│   └── auth.php          # Authentication
├── assets/
│   ├── css/styles.css    # Main stylesheet
│   └── js/main.js        # JavaScript functionality
├── client/
│   ├── dashboard.php     # Client dashboard
│   └── intake.php        # Intake forms
└── platforms/
    └── *.php             # Platform pages
```

### Technology Stack
- **Backend**: PHP with MySQLi (no frameworks)
- **Database**: MySQL with proper indexing
- **Frontend**: Responsive HTML5/CSS3 with CSS Grid/Flexbox
- **JavaScript**: Vanilla ES6+ (no libraries except FontAwesome icons)
- **Security**: Prepared statements, password hashing, input sanitization

## 🔧 Platforms

### DCIDE - Case Management
Comprehensive case tracking from intake to resolution with progress monitoring and team collaboration.

### LINK - Referral Network
Smart referral system connecting clients to community services with status tracking.

### BLES - Addiction Recovery
Specialized platform for addiction support with recovery-focused intake and resources.

### ASK - Communication Hub
Real-time messaging and support system for client-staff communication.

### ETHAN - Analytics Platform
Data tracking and outcome monitoring with visual dashboards and reporting.

### FOOTPRINT - Impact Tracking
Sustainability and long-term impact measurement tools.

## 🔒 Security Features

- SQL injection prevention with prepared statements
- XSS protection via output sanitization
- Password strength validation and secure hashing
- Role-based access control
- Session management with timeouts
- Security question-based password recovery
- Audit logging for all critical actions

## 📊 Database Design

- **Normalized structure** with proper foreign keys
- **JSON storage** for flexible form data
- **Audit trail** for all user actions
- **Optimized indexes** for performance
- **GDPR/PIPEDA compliance** considerations

## 🎨 Design Features

- **CSS Custom Properties** for consistent theming
- **Responsive Grid System** for all screen sizes
- **Smooth Animations** and hover effects
- **Accessibility** considerations throughout
- **Progressive Enhancement** approach
- **Print-friendly** styles

## 📖 Documentation

See `SYSTEM_DOCUMENTATION.md` for comprehensive technical documentation including:
- Detailed architecture overview
- Database schema explanations
- Security implementation details
- Deployment instructions
- Troubleshooting guides

## 🤝 Contributing

This project follows these principles:
- **No external dependencies** (PHP/MySQL/HTML/CSS/JS only)
- **Security first** approach
- **Accessibility** considerations
- **Clean, readable code** with inline documentation
- **Mobile-first** responsive design

## 📧 Support

- Technical Documentation: See `SYSTEM_DOCUMENTATION.md`
- System Requirements: PHP 7.4+, MySQL 5.7+
- Browser Support: Modern browsers (Chrome, Firefox, Safari, Edge)

## 📄 License

[Add your license information here]

---

**OUTSINC** - Because everyone deserves support, and no one should fall through the cracks.