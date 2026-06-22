<?php
require_once 'config/config.php';
require_once 'includes/session_check.php';
require_once 'includes/functions.php';

requireLogin();

// Get QR token from URL
$qrToken = sanitizeInput($_GET['token'] ?? '');

if (empty($qrToken)) {
    $_SESSION['scan_error'] = 'Invalid QR code';
    header('Location: index.php');
    exit();
}

// Get faculty info
$faculty = getFacultyByQRToken($qrToken);

if (!$faculty) {
    $_SESSION['scan_error'] = 'Faculty not found';
    header('Location: index.php');
    exit();
}

// Get current date
$today = date('Y-m-d');
$currentTime = date('H:i:s');

// Get or create attendance record
$attendance = getOrCreateAttendanceRecord($faculty['id'], $today);

// Determine scan type
if (empty($attendance['time_in'])) {
    // This is a time-in scan
    recordTimeIn($faculty['id'], $today, $currentTime);
    updateFacultyStatus($faculty['id'], FACULTY_STATUS_IN);
    
    logActivity('QR_SCAN_IN', 'Faculty ' . $faculty['fullname'] . ' scanned QR code for time-in', getCurrentUserId());
    
    $_SESSION['scan_success'] = $faculty['fullname'] . ' - Time In: ' . formatTime($currentTime);
} else {
    // This is a time-out scan - need activity selection
    $_SESSION['temp_faculty_id'] = $faculty['id'];
    $_SESSION['temp_time_out'] = $currentTime;
    header('Location: faculty/scan_out.php');
    exit();
}

// Log the scan event
$stmt = $conn->prepare("
    INSERT INTO scan_logs (faculty_id, qr_token, scan_time, scan_type, ip_address)
    VALUES (?, ?, ?, ?, ?)
");

$scanType = 'IN';
$ipAddress = $_SERVER['REMOTE_ADDR'];
$scanTime = date('Y-m-d H:i:s');

$stmt->bind_param("issss", $faculty['id'], $qrToken, $scanTime, $scanType, $ipAddress);
$stmt->execute();

// Redirect based on user role
if (getCurrentUserRole() === 'Faculty') {
    header('Location: faculty/dashboard.php?scan=success');
} else {
    header('Location: index.php');
}
exit();
?>
