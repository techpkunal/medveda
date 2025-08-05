<?php
/**
 * Distributor Dashboard Authentication Check
 * Ensures only authenticated distributors can access the dashboard
 */

require_once '../otp-login/session_manager.php';

// Require authentication with distributor role
$user = requireAuth('distributor');

// Redirect authenticated distributors to the distributor page
if ($user && $user['role'] === 'distributor') {
    header('Location: distributor.php');
    exit();
}

// Make user data available to the dashboard
$current_user = getCurrentUser();
?>
