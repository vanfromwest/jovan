<?php
require_once 'config/config.php';
require_once 'includes/session_check.php';
require_once 'includes/functions.php';

// Require login
requireLogin();

// Get user role
$userRole = getCurrentUserRole();

// Redirect to appropriate dashboard
if ($userRole === 'Admin') {
    header('Location: admin/dashboard.php');
} elseif ($userRole === 'Faculty') {
    header('Location: faculty/dashboard.php');
} else {
    header('Location: student/dashboard.php');
}
exit();
?>
