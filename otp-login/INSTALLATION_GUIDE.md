# MedChain OTP Authentication Integration - Installation Guide

## Overview
This guide will help you integrate the secure OTP authentication system with your existing MedChain blockchain application.

## Prerequisites
- XAMPP with Apache and MySQL running
- PHP 7.4 or higher
- Composer (for PHPMailer dependencies)
- Gmail account with App Password for email sending

## Installation Steps

### Step 1: Database Setup
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Select your `medchain_db` database
3. Go to the "SQL" tab
4. Copy and paste the contents of `medchain_otp_extension.sql`
5. Click "Go" to execute the SQL commands

**What this does:**
- Adds OTP fields to the existing users table
- Creates user_profiles table for additional user information
- Creates user_sessions table for session management
- Creates login_attempts table for security tracking
- Updates existing users with sample email addresses

### Step 2: Email Configuration
1. Open `otp-login/otp_config.php`
2. Update the following constants with your Gmail credentials:
   ```php
   define('SMTP_USERNAME', 'your-email@gmail.com');
   define('SMTP_PASSWORD', 'your-app-password');
   define('FROM_EMAIL', 'your-email@gmail.com');
   ```

**To get Gmail App Password:**
1. Go to Google Account settings
2. Enable 2-Factor Authentication
3. Generate an App Password for "Mail"
4. Use this 16-character password in the config

### Step 3: Install Dependencies
1. Navigate to the `otp-login` folder
2. Run: `composer install`
3. This will install PHPMailer for email functionality

### Step 4: Update Dashboard URLs
The system automatically redirects users to role-specific dashboards:
- Distributors → `/distributor/dashboard.php`
- Pharmacists → `/pharmacist/dashboard.php`
- Patients → `/patient/dashboard.php`

### Step 5: Test the Integration

#### Testing Registration:
1. Go to: `http://localhost/blockchain/otp-login/register_medchain.html`
2. Select a role (distributor, pharmacist, or patient)
3. Enter your email address
4. Check your email for the OTP code
5. Complete the registration form
6. You should be redirected to the appropriate dashboard

#### Testing Login:
1. Go to: `http://localhost/blockchain/otp-login/login_medchain.html`
2. Select your role
3. Enter your registered email
4. Check email for OTP code
5. Enter the code to login
6. You should be redirected to your dashboard

## Security Features

### OTP Security:
- 6-digit random OTP codes
- 10-minute expiration time
- One-time use (cleared after successful verification)
- IP-based rate limiting (5 attempts per 30 minutes)

### Session Management:
- Secure session tokens
- 24-hour session timeout
- Database-stored sessions
- Automatic cleanup of expired sessions

### Role-Based Access:
- Each dashboard requires specific role authentication
- Automatic redirection based on user role
- Session validation on every page load

## Database Schema Changes

### Extended Users Table:
```sql
ALTER TABLE users 
ADD COLUMN email VARCHAR(255) UNIQUE AFTER full_name,
ADD COLUMN otp VARCHAR(10) DEFAULT NULL AFTER email,
ADD COLUMN otp_expiry DATETIME DEFAULT NULL AFTER otp,
ADD COLUMN phone VARCHAR(20) DEFAULT NULL AFTER otp_expiry,
ADD COLUMN is_verified BOOLEAN DEFAULT FALSE AFTER phone,
ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER is_verified,
ADD COLUMN last_login TIMESTAMP NULL DEFAULT NULL AFTER created_at;
```

### New Tables:
- `user_profiles` - Extended user information
- `user_sessions` - Active user sessions
- `login_attempts` - Security audit trail

## File Structure
```
blockchain/
├── otp-login/
│   ├── otp_config.php              # Central configuration
│   ├── send_otp_medchain.php       # OTP sending API
│   ├── verify_otp_medchain.php     # OTP verification API
│   ├── session_manager.php         # Session management
│   ├── login_medchain.html         # Login page
│   ├── register_medchain.html      # Registration page
│   ├── medchain_otp_extension.sql  # Database schema
│   └── vendor/                     # PHPMailer dependencies
├── distributor/
│   ├── auth_check.php              # Authentication wrapper
│   └── dashboard.php               # Authenticated dashboard
├── pharmacist/
│   ├── auth_check.php              # Authentication wrapper
│   └── dashboard.php               # Authenticated dashboard
└── patient/
    ├── auth_check.php              # Authentication wrapper
    └── dashboard.php               # Authenticated dashboard
```

## Troubleshooting

### Common Issues:

1. **Email not sending:**
   - Check Gmail App Password
   - Verify SMTP settings in otp_config.php
   - Check PHP error logs

2. **Database errors:**
   - Ensure medchain_db exists
   - Run the SQL extension script
   - Check database connection settings

3. **Session issues:**
   - Clear browser cookies
   - Check session table in database
   - Verify PHP session configuration

4. **Permission errors:**
   - Ensure proper file permissions
   - Check Apache error logs
   - Verify PHP extensions are enabled

### Debug Mode:
To enable debug mode, add this to the top of any PHP file:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## Security Recommendations

1. **Change default encryption key** in otp_config.php
2. **Use HTTPS** in production
3. **Set strong database passwords**
4. **Regular security updates**
5. **Monitor login attempts table** for suspicious activity

## Support
If you encounter any issues:
1. Check the browser console for JavaScript errors
2. Check PHP error logs
3. Verify database connections
4. Test email functionality separately

The system is now ready for secure OTP-based authentication across all MedChain roles!
