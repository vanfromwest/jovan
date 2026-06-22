<?php
/**
 * API: Record Time-Out from QR Scan
 */

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/session_check.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized'
    ]);
    exit();
}

try {
    $facultyId = intval($_POST['faculty_id'] ?? 0);
    $activityId = intval($_POST['activity_id'] ?? 0);
    $location = sanitizeInput($_POST['location'] ?? '');

    if ($facultyId <= 0 || $activityId <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid request data.'
        ]);
        exit();
    }

    $activityStmt = $conn->prepare("SELECT name FROM activities WHERE id = ?");
    $activityStmt->bind_param('i', $activityId);
    $activityStmt->execute();
    $activityResult = $activityStmt->get_result()->fetch_assoc();

    if (!$activityResult) {
        echo json_encode([
            'success' => false,
            'message' => 'Selected activity not found.'
        ]);
        exit();
    }

    $activityName = $activityResult['name'];
    $currentTime = date('H:i:s');
    $today = date('Y-m-d');

    if (!recordTimeOut($facultyId, $today, $currentTime, $activityName, $location)) {
        echo json_encode([
            'success' => false,
            'message' => 'Unable to record time out. Please try again.'
        ]);
        exit();
    }

    updateFacultyStatus($facultyId, FACULTY_STATUS_OUT, $activityName, $location);
    logActivity('QR_SCAN_OUT', 'Time-out scan for faculty ID ' . $facultyId . ' activity: ' . $activityName);

    echo json_encode([
        'success' => true,
        'message' => 'Time Out recorded successfully.',
        'faculty_id' => $facultyId,
        'activity' => $activityName,
        'time' => formatTime($currentTime)
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>