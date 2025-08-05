<?php
/**
 * MedChain Global Configuration
 *
 * This file contains all the core settings, database connections,
 * and helper functions for the MedChain application.
 */

// --- Start Session ---
// A single, consistent session name
session_name('MedChainSession');
if (session_status() == PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 86400, // 24 hours
        'gc_maxlifetime' => 86400,
    ]);
}

// --- Error Reporting (for development) ---
// Comment these out in a production environment
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- Database Credentials ---
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'medchain_db'); // Ensure this matches your database name

// --- SMTP Mailer Credentials ---
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_AUTH', true);
define('SMTP_USERNAME', 'shrikantchavan0003@gmail.com'); // Your Gmail address
define('SMTP_PASSWORD', 'szgtvcvdxsbftxrr');     // Your Gmail App Password
define('SMTP_SECURE', 'ssl'); // or 'tls'
define('SMTP_PORT', 465); // 465 for SSL, 587 for TLS
define('FROM_EMAIL', 'noreply@medchain.com'); // The "from" email address

// --- Security Settings ---
define('LOGIN_ATTEMPT_LIMIT', 10); // Max failed attempts
define('LOGIN_LOCKOUT_PERIOD', '15 MINUTE'); // Lockout duration

/**
 * Establishes and returns a database connection.
 * @return mysqli
 * @throws Exception
 */
function getDBConnection() {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    return $conn;
}

/**
 * Sanitizes user input to prevent XSS.
 * @param string $data
 * @return string
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Validates an email address format.
 * @param string $email
 * @return bool
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Logs a login attempt to the database.
 * @param string $email
 * @param string $ip_address
 * @param bool $success
 * @param string|null $failure_reason
 */
function logLoginAttempt($email, $ip_address, $success, $failure_reason = null) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("INSERT INTO login_attempts (email, ip_address, success, failure_reason) VALUES (?, ?, ?, ?)");
        $success_int = $success ? 1 : 0;
        $stmt->bind_param("ssis", $email, $ip_address, $success_int, $failure_reason);
        $stmt->execute();
        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        error_log("Failed to log login attempt: " . $e->getMessage());
    }
}

/**
 * Checks if an IP address is currently locked out.
 * @param string $ip_address
 * @return bool
 */
function isIPLockedOut($ip_address) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT COUNT(*) as failed_attempts FROM login_attempts WHERE ip_address = ? AND success = 0 AND attempt_time > (NOW() - INTERVAL " . LOGIN_LOCKOUT_PERIOD . ")");
        $stmt->bind_param("s", $ip_address);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $conn->close();
        return $result['failed_attempts'] >= LOGIN_ATTEMPT_LIMIT;
    } catch (Exception $e) {
        error_log("IP lockout check failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Creates a secure user session.
 * @param int $user_id
 * @param string $role
 * @param string $full_name
 * @return string|false
 */
function createUserSession($user_id, $role, $full_name) {
    try {
        // Generate a secure, random session ID
        $session_id = bin2hex(random_bytes(32));
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $conn = getDBConnection();
        // Deactivate any old sessions for this user
        $stmt = $conn->prepare("UPDATE user_sessions SET is_active = 0 WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        // Insert new session record
        $stmt = $conn->prepare("INSERT INTO user_sessions (session_id, user_id, role, ip_address, user_agent, expires_at) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sissss", $session_id, $user_id, $role, $ip_address, $user_agent, $expires_at);
        $stmt->execute();
        $stmt->close();
        $conn->close();

        // Set session variables
        $_SESSION['session_id'] = $session_id;
        $_SESSION['user_id'] = $user_id;
        $_SESSION['role'] = $role;
        $_SESSION['full_name'] = $full_name;

        return $session_id;
    } catch (Exception $e) {
        error_log("Failed to create session: " . $e->getMessage());
        return false;
    }
}

/**
 * Validates the current user session from the database.
 * @return array|null
 */
function validateUserSession() {
    if (isset($_SESSION['session_id'])) {
        try {
            $conn = getDBConnection();
            $stmt = $conn->prepare("SELECT * FROM user_sessions WHERE session_id = ? AND is_active = 1 AND expires_at > NOW()");
            $stmt->bind_param("s", $_SESSION['session_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 1) {
                return $result->fetch_assoc();
            }
            $stmt->close();
            $conn->close();
        } catch (Exception $e) {
            error_log("Session validation failed: " . $e->getMessage());
        }
    }
    // If validation fails, destroy the session
    destroyUserSession();
    return null;
}

/**
 * Destroys the current user session.
 */
function destroyUserSession() {
    if (isset($_SESSION['session_id'])) {
        try {
            $conn = getDBConnection();
            $stmt = $conn->prepare("UPDATE user_sessions SET is_active = 0 WHERE session_id = ?");
            $stmt->bind_param("s", $_SESSION['session_id']);
            $stmt->execute();
            $stmt->close();
            $conn->close();
        } catch (Exception $e) {
            error_log("Session destruction failed: " . $e->getMessage());
        }
    }
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }
    session_destroy();
}

/**
 * Returns the redirect URL based on user role.
 * @param string $role
 * @return string
 */
function getRoleRedirectURL($role) {
    switch ($role) {
        case 'admin':
            return '../admin/index.html';
        case 'manufacturer':
            return '../manufacturer/manufacturer_dashboard.php';
        case 'distributor':
            return '../distributor/distributor.php';
        case 'pharmacist':
            return '../pharmacist/pharmacist.php';
        case 'patient':
            return '../patient/index.php';
        case 'hospital':
            return '../hospital/hospital.php';
        default:
            return '../index.html';
    }
}
?>
