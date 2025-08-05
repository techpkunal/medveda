<?php
// config.php - Centralized configuration for MedChain Blockchain System
// This file contains all configuration settings to make the system portable

// Database Configuration
// Change these settings according to your environment
define('DB_HOST', 'localhost');
define('DB_NAME', 'medchain_db');
define('DB_USER', 'root');
define('DB_PASS', ''); // Default XAMPP password is blank

// Application Settings
define('APP_NAME', 'MedChain');
define('APP_VERSION', '1.0.0');

// Path Configuration (automatically detects the correct paths)
define('BASE_PATH', dirname(__FILE__));
define('API_PATH', BASE_PATH . '/api');
define('ASSETS_PATH', BASE_PATH . '/assets');

// URL Configuration (automatically detects the correct base URL)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script_name = $_SERVER['SCRIPT_NAME'] ?? '';
$base_url = $protocol . '://' . $host . dirname(dirname($script_name));
define('BASE_URL', rtrim($base_url, '/'));

// Security Settings
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds
define('PASSWORD_MIN_LENGTH', 6);

// Error Reporting (set to 0 for production, 1 for development)
define('DEBUG_MODE', 0);

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Function to get database connection
function getDatabaseConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            if (DEBUG_MODE) {
                die("Database Connection Error: " . $e->getMessage());
            } else {
                die("Database connection failed. Please check your configuration.");
            }
        }
    }
    
    return $pdo;
}

// Function to get asset URL
function getAssetUrl($asset_path) {
    return BASE_URL . '/assets/' . ltrim($asset_path, '/');
}

// Function to get API URL
function getApiUrl($endpoint) {
    return BASE_URL . '/api/' . ltrim($endpoint, '/');
}

// Function to redirect with proper base URL
function redirectTo($path) {
    $url = BASE_URL . '/' . ltrim($path, '/');
    header("Location: $url");
    exit;
}

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    
    // Check session timeout
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        session_unset();
        session_destroy();
        session_start();
    }
    $_SESSION['last_activity'] = time();
}
?>
