<?php
/**
 * API: Delete User Account
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
    $userId = intval($_POST['user_id'] ?? 0);
    
    if ($userId <= 0) {
        jsonResponse(false, 'Invalid user ID');
    }
    
    // Don't allow deleting yourself
    if ($userId === getCurrentUserId()) {
        jsonResponse(false, 'Cannot delete your own account');
    }
    
    $user = getUserById($userId);
    
    if (!$user) {
        jsonResponse(false, 'User not found');
    }
    
    $result = deleteUser($userId);
    
    if ($result) {
        jsonResponse(true, 'User account deleted successfully');
    } else {
        jsonResponse(false, 'Failed to delete user account');
    }
    
} catch (Exception $e) {
    jsonResponse(false, 'Error: ' . $e->getMessage());
}
?>
