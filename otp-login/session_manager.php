<?php
/**
 * MedChain Session Management
 * Handles session validation and user authentication checks.
 * This version removes the conflicting code that was causing the "Invalid action" error.
 */

require_once 'otp_config.php';

/**
 * Check if user is authenticated and has valid session.
 * This is the primary function for protecting pages.
 */
function requireAuth($required_role = null) {
    // validateUserSession() starts the session with the correct name.
    $session = validateUserSession();
    
    if (!$session) {
        // If not authenticated, redirect to the login page.
        header('Location: ../otp-login/login_medchain.html');
        exit();
    }
    
    // If a specific role is required, check it.
    if ($required_role && $session['role'] !== $required_role) {
        // If the role does not match, send them away to prevent unauthorized access.
        header('Location: ../otp-login/login_medchain.html');
        exit();
    }
    
    return $session;
}

/**
 * Get current user information from the database.
 */
function getCurrentUser() {
    $session = validateUserSession();
    
    if (!$session) {
        return null;
    }
    
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT user_id, username, email, role, full_name, phone FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $session['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $stmt->close();
            $conn->close();
            return $user;
        }
        
        $stmt->close();
        $conn->close();
        return null;
    } catch (Exception $e) {
        error_log("Failed to get current user: " . $e->getMessage());
        return null;
    }
}

/**
 * Logout user by destroying the session.
 */
function logout() {
    destroyUserSession();
    header('Location: ../otp-login/login_medchain.html');
    exit();
}

// The conflicting code that was here has been removed.
?>
