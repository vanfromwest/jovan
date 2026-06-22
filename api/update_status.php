<?php
/**
 * API: Update Faculty Status
 */

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/session_check.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Check authentication
if (!isLoggedIn() || getCurrentUserRole() !== 'Faculty') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized'
    ]);
    exit();
}

try {
    $facultyId = intval($_POST['faculty_id'] ?? 0);
    $status = sanitizeInput($_POST['status'] ?? '');
    $activity = sanitizeInput($_POST['activity'] ?? null);
    $location = sanitizeInput($_POST['location'] ?? null);
    
    // Verify faculty ownership
    $verifyStmt = $conn->prepare("
        SELECT id FROM faculty WHERE id = ? AND user_id = ?
    ");
    
    $userId = getCurrentUserId();
    $verifyStmt->bind_param("ii", $facultyId, $userId);
    $verifyStmt->execute();
    
    if ($verifyStmt->get_result()->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized'
        ]);
        exit();
    }
    
    // Update status
    $result = updateFacultyStatus($facultyId, $status, $activity, $location);
    
    if ($result) {
        logActivity('STATUS_UPDATE', "Faculty status updated to $status", $userId);
        
        echo json_encode([
            'success' => true,
            'message' => 'Status updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update status'
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
