<?php
/**
 * API: Scan QR Code
 */

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/session_check.php';
require_once '../includes/functions.php';

set_time_limit(15);

header('Content-Type: application/json');

// Check authentication
if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized'
    ]);
    exit();
}

try {
    $qrInput = sanitizeInput($_POST['qr_token'] ?? '');

    if (empty($qrInput)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid QR token'
        ]);
        exit();
    }

    // Allow raw token or full URL containing token parameter
    $qrToken = $qrInput;
    $parsedUrl = parse_url($qrInput);
    if (!empty($parsedUrl['query'])) {
        parse_str($parsedUrl['query'], $queryParams);
        if (!empty($queryParams['token'])) {
            $qrToken = sanitizeInput($queryParams['token']);
        }
    } else if (preg_match('/^[a-f0-9]{32,100}$/i', $qrInput)) {
        $qrToken = $qrInput;
    }
    
    if (empty($qrToken)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid QR token'
        ]);
        exit();
    }
    
    // Get faculty
    $faculty = getFacultyByQRToken($qrToken);
    
    if (!$faculty) {
        echo json_encode([
            'success' => false,
            'message' => 'Faculty not found'
        ]);
        exit();
    }
    
    $today = date('Y-m-d');
    $currentTime = date('H:i:s');
    $scanTime = date('Y-m-d H:i:s');
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    
    // Get or create attendance
    $attendance = getOrCreateAttendanceRecord($faculty['id'], $today);
    
    if (empty($attendance['time_in']) || !empty($attendance['time_out'])) {
        // Time In (either no time_in yet, or previous session already timed out)
        if (!empty($attendance['time_out'])) {
            $resetStmt = $conn->prepare("
                UPDATE attendance SET time_out = NULL, activity_out = NULL, location_out = NULL, updated_at = NOW()
                WHERE id = ?
            ");
            $resetStmt->bind_param("i", $attendance['id']);
            $resetStmt->execute();
        }
        recordTimeIn($faculty['id'], $today, $currentTime);
        updateFacultyStatus($faculty['id'], FACULTY_STATUS_IN);
        
        $scanType = 'IN';
        
        $logStmt = $conn->prepare("
            INSERT INTO scan_logs (faculty_id, qr_token, scan_time, scan_type, ip_address)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $logStmt->bind_param("issss", $faculty['id'], $qrToken, $scanTime, $scanType, $ipAddress);
        $logStmt->execute();
        
        logActivity('QR_SCAN_IN', 'Time-in scan for faculty ' . $faculty['fullname']);
        
        echo json_encode([
            'success' => true,
            'message' => 'Time In recorded successfully',
            'scan_type' => 'IN',
            'faculty_id' => $faculty['id'],
            'faculty_name' => $faculty['fullname'],
            'profile_image' => $faculty['profile_image'],
            'time' => formatTime($currentTime)
        ]);
    } else {
        // Time Out
        $scanType = 'OUT';
        
        $logStmt = $conn->prepare("
            INSERT INTO scan_logs (faculty_id, qr_token, scan_time, scan_type, ip_address)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $logStmt->bind_param("issss", $faculty['id'], $qrToken, $scanTime, $scanType, $ipAddress);
        $logStmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'Please select your activity',
            'scan_type' => 'OUT',
            'faculty_id' => $faculty['id'],
            'faculty_name' => $faculty['fullname'],
            'profile_image' => $faculty['profile_image'],
            'time' => formatTime($currentTime)
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
