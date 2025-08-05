<?php
/**
 * Hospital Dashboard Authentication Check
 * Ensures only authenticated hospital users can access the dashboard
 */

require_once '../otp-login/session_manager.php';

// Require authentication with hospital role
$user = requireAuth('hospital');

// Make user data available to the dashboard
$current_user = getCurrentUser();
?>
