<?php
/**
 * Pharmacist Dashboard Authentication Check
 * Ensures only authenticated pharmacists can access the dashboard
 */

require_once '../otp-login/session_manager.php';

// Require authentication with pharmacist role
$user = requireAuth('pharmacist');

// Make user data available to the dashboard
$current_user = getCurrentUser();
?>
