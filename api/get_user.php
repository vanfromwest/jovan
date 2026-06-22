<?php
/**
 * API: Get User Details
 */

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/session_check.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Check if admin
if (!isLoggedIn() || getCurrentUserRole() !== 'Admin') {
    jsonResponse(false, 'Unauthorized');
}

try {
    $userId = intval($_GET['user_id'] ?? $_POST['user_id'] ?? 0);
    
    if ($userId <= 0) {
        jsonResponse(false, 'Invalid user ID');
    }
    
    $user = getUserById($userId);
    
    if (!$user) {
        jsonResponse(false, 'User not found');
    }
    
    // Don't send password hash
    unset($user['password']);
    
    jsonResponse(true, 'User retrieved successfully', $user);
    
} catch (Exception $e) {
    jsonResponse(false, 'Error: ' . $e->getMessage());
}
?>
