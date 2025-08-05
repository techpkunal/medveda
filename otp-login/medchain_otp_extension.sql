-- MedChain OTP Authentication Extension
-- This script adds OTP functionality to the existing MedChain users table

USE medchain_db;

-- Add OTP fields to existing users table
ALTER TABLE users 
ADD COLUMN email VARCHAR(255) UNIQUE AFTER full_name,
ADD COLUMN otp VARCHAR(10) DEFAULT NULL AFTER email,
ADD COLUMN otp_expiry DATETIME DEFAULT NULL AFTER otp,
ADD COLUMN phone VARCHAR(20) DEFAULT NULL AFTER otp_expiry,
ADD COLUMN is_verified BOOLEAN DEFAULT FALSE AFTER phone,
ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER is_verified,
ADD COLUMN last_login TIMESTAMP NULL DEFAULT NULL AFTER created_at;

-- Create index for faster email lookups
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);

-- Create user_profiles table for additional user information
CREATE TABLE user_profiles (
    profile_id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    address TEXT DEFAULT NULL,
    license_number VARCHAR(100) DEFAULT NULL,
    organization_name VARCHAR(255) DEFAULT NULL,
    specialization VARCHAR(255) DEFAULT NULL,
    profile_image VARCHAR(255) DEFAULT NULL,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (profile_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_profile (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create user_sessions table for session management
CREATE TABLE user_sessions (
    session_id VARCHAR(128) NOT NULL,
    user_id INT(11) NOT NULL,
    role VARCHAR(50) NOT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    PRIMARY KEY (session_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_sessions_user_id (user_id),
    INDEX idx_user_sessions_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create login_attempts table for security
CREATE TABLE login_attempts (
    attempt_id INT(11) NOT NULL AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    success BOOLEAN DEFAULT FALSE,
    failure_reason VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (attempt_id),
    INDEX idx_login_attempts_email (email),
    INDEX idx_login_attempts_ip (ip_address),
    INDEX idx_login_attempts_time (attempt_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert some sample data for testing (optional)
-- Update existing users with email addresses
UPDATE users SET email = 'admin@medchain.com' WHERE username = 'admin_user';
UPDATE users SET email = 'manufacturer@medchain.com' WHERE username = 'manufacturer_a';
UPDATE users SET email = 'distributor@medchain.com' WHERE username = 'distributor_x';
UPDATE users SET email = 'pharmacist@medchain.com' WHERE username = 'pharmacist_y';
UPDATE users SET email = 'chickya@medchain.com' WHERE username = 'chickya';
UPDATE users SET email = 'gautam@medchain.com' WHERE username = 'GAUTAM';
