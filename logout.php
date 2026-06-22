<?php
require_once 'config/config.php';
require_once 'includes/session_check.php';

// Logout the user
logout();

// Redirect to home
header('Location: index.php');
exit();
?>
