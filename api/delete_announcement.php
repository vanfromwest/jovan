<?php
/**
 * API: Delete Announcement
 */

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/session_check.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Check if logged in
if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized'
    ]);
    exit();
}

try {
    $announcementId = intval($_POST['announcement_id'] ?? 0);
    $userId = getCurrentUserId();
    
    if ($announcementId <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid announcement ID'
        ]);
        exit();
    }
    
    // Verify ownership or admin
    $verifyStmt = $conn->prepare("
        SELECT created_by FROM announcements WHERE id = ?
    ");
    
    $verifyStmt->bind_param("i", $announcementId);
    $verifyStmt->execute();
    $result = $verifyStmt->get_result()->fetch_assoc();
    
    if (!$result || ($result['created_by'] != $userId && getCurrentUserRole() !== 'Admin')) {
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized'
        ]);
        exit();
    }
    
    $deleteResult = deleteAnnouncement($announcementId);
    
    if ($deleteResult) {
        logActivity('ANNOUNCEMENT_DELETE', 'Announcement ' . $announcementId . ' deleted');
        
        echo json_encode([
            'success' => true,
            'message' => 'Announcement deleted successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to delete announcement'
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
