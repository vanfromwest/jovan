<?php
/**
 * API: Update User Details
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
    $fullname = sanitizeInput($_POST['fullname'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $contactNumber = sanitizeInput($_POST['contact_number'] ?? '');
    $departmentId = !empty($_POST['department_id']) ? intval($_POST['department_id']) : null;
    
    if ($userId <= 0) {
        jsonResponse(false, 'Invalid user ID');
    }
    
    if (empty($fullname)) {
        jsonResponse(false, 'Full name is required');
    }
    
    if (empty($email) || !validateEmail($email)) {
        jsonResponse(false, 'Valid email is required');
    }
    
    // Check if email is already used by another user
    global $conn;
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $userId);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        jsonResponse(false, 'Email is already in use');
    }
    
    $result = updateUser($userId, $fullname, $email, $contactNumber, $departmentId);
    
    if ($result) {
        jsonResponse(true, 'User updated successfully');
    } else {
        jsonResponse(false, 'Failed to update user');
    }
    
} catch (Exception $e) {
    jsonResponse(false, 'Error: ' . $e->getMessage());
}
?>
