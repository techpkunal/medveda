<?php
// api/db_connect.php - Improved database connection using centralized config

// Include the centralized configuration
require_once dirname(__DIR__) . '/config.php';

// Get database connection using the centralized function
$pdo = getDatabaseConnection();

// Set error reporting based on debug mode
if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}
?>