<?php
/**
 * API: Approve User
 */

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/session_check.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Check if admin
if (!isLoggedIn() || getCurrentUserRole() !== 'Admin') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized'
    ]);
    exit();
}

try {
    $userId = intval($_POST['user_id'] ?? 0);
    
    if ($userId <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid user ID'
        ]);
        exit();
    }
    
    $result = approveUser($userId);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'User account approved successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to approve user'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
