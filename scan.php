<?php
require_once 'config/config.php';
require_once 'includes/session_check.php';
require_once 'includes/functions.php';

requireLogin();

// Handle time-out form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'record_timeout') {
    $facultyId = intval($_POST['faculty_id'] ?? 0);
    $activityId = intval($_POST['activity_id'] ?? 0);
    $location = sanitizeInput($_POST['location'] ?? '');
    $qrToken = sanitizeInput($_POST['qr_token'] ?? '');

    if ($facultyId <= 0 || $activityId <= 0 || empty($qrToken)) {
        $_SESSION['scan_error'] = 'Invalid request data.';
        header('Location: index.php');
        exit();
    }

    $activityStmt = $conn->prepare("SELECT name FROM activities WHERE id = ?");
    $activityStmt->bind_param('i', $activityId);
    $activityStmt->execute();
    $activityResult = $activityStmt->get_result()->fetch_assoc();

    if (!$activityResult) {
        $_SESSION['scan_error'] = 'Selected activity not found.';
        header('Location: index.php');
        exit();
    }

    $activityName = $activityResult['name'];
    $today = date('Y-m-d');
    $currentTime = date('H:i:s');

    if (!recordTimeOut($facultyId, $today, $currentTime, $activityName, $location)) {
        $_SESSION['scan_error'] = 'Unable to record time out. Please try again.';
        header('Location: index.php');
        exit();
    }

    updateFacultyStatus($facultyId, FACULTY_STATUS_OUT, $activityName, $location);
    logActivity('QR_SCAN_OUT', 'Time-out scan for faculty ID ' . $facultyId . ' activity: ' . $activityName);

    $stmt = $conn->prepare("
        INSERT INTO scan_logs (faculty_id, qr_token, scan_time, scan_type, ip_address)
        VALUES (?, ?, ?, ?, ?)
    ");
    $scanType = 'OUT';
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    $scanTime = date('Y-m-d H:i:s');
    $stmt->bind_param("issss", $facultyId, $qrToken, $scanTime, $scanType, $ipAddress);
    $stmt->execute();

    $faculty = getFacultyByQRToken($qrToken);
    $name = $faculty ? $faculty['fullname'] : 'Faculty';
    $_SESSION['scan_success'] = $name . ' - Time Out: ' . formatTime($currentTime) . ' (' . $activityName . ')';

    if (getCurrentUserRole() === 'Faculty') {
        header('Location: faculty/dashboard.php?scan=success');
    } else {
        header('Location: index.php');
    }
    exit();
}

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

    $_SESSION['scan_success'] = $faculty['fullname'] . ' - Time In: ' . formatTime($currentTime);

    if (getCurrentUserRole() === 'Faculty') {
        header('Location: faculty/dashboard.php?scan=success');
    } else {
        header('Location: index.php');
    }
    exit();
}

// This is a time-out scan - show activity selection page
$activities = getAllActivities();
$pageTitle = 'Select Activity - Time Out';
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>
<body>
    <div class="main-content">
        <div class="content-wrapper" style="margin-left: 0; padding-top: 40px;">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="dashboard-card">
                            <div class="card-header">
                                <i class="bi bi-box-arrow-right"></i> Time Out - <?php echo htmlspecialchars($faculty['fullname']); ?>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">Select your activity to record time-out.</p>
                                <p><strong>Time:</strong> <?php echo formatTime($currentTime); ?></p>
                                <form method="post" action="scan.php">
                                    <input type="hidden" name="action" value="record_timeout">
                                    <input type="hidden" name="faculty_id" value="<?php echo $faculty['id']; ?>">
                                    <input type="hidden" name="qr_token" value="<?php echo htmlspecialchars($qrToken); ?>">
                                    <div class="mb-3">
                                        <label class="form-label">Activity</label>
                                        <select class="form-select" name="activity_id" required>
                                            <option value="">-- Select Activity --</option>
                                            <?php foreach ($activities as $activity): ?>
                                                <option value="<?php echo $activity['id']; ?>"><?php echo htmlspecialchars($activity['name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Location</label>
                                        <input type="text" class="form-control" name="location" placeholder="e.g., Room 204">
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle"></i> Record Time Out
                                    </button>
                                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
?>
