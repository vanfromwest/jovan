<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/session_check.php';
require_once '../includes/functions.php';

$pageTitle = 'Faculty Dashboard';
requireRole(['Faculty']);

$userId = getCurrentUserId();
$facultyId = getFacultyId($userId);
$status = getFacultyStatus($facultyId);
$recentAttendance = getFacultyAttendance($facultyId, null, null);
?>
<!DOCTYPE html>
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
        <?php require_once '../includes/sidebar.php'; ?>
        <div class="content-wrapper">
            <div class="container-fluid">
                <h1 class="h3 mb-4"><i class="bi bi-speedometer2"></i> Faculty Dashboard</h1>

                <!-- Current Status -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="dashboard-card">
                            <div class="card-header">
                                <i class="bi bi-heart-pulse"></i> Current Status
                            </div>
                            <div class="card-body text-center">
                                <h2 class="mb-3">
                                    <?php 
                                    $statusClass = $status['status'] === 'IN' ? 'in' : 'out';
                                    $statusText = $status['status'] === 'IN' ? 'IN' : 'OUT';
                                    ?>
                                    <span class="status-badge <?php echo $statusClass; ?>">
                                        <span class="status-badge-pulse"></span> <?php echo $statusText; ?>
                                    </span>
                                </h2>
                                
                                <?php if ($status['activity']): ?>
                                    <p class="text-muted">Activity: <strong><?php echo htmlspecialchars($status['activity']); ?></strong></p>
                                <?php endif; ?>
                                
                                <?php if ($status['location']): ?>
                                    <p class="text-muted">Location: <strong><?php echo htmlspecialchars($status['location']); ?></strong></p>
                                <?php endif; ?>
                                
                                <small class="text-muted">Last Updated: <?php echo formatDateTime($status['updated_at']); ?></small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="dashboard-card">
                            <div class="card-header">
                                <i class="bi bi-lightning"></i> Quick Actions
                            </div>
                            <div class="card-body">
                                <a href="scan.php" class="btn btn-primary w-100 mb-2">
                                    <i class="bi bi-qr-code"></i> Scan QR Code
                                </a>
                                <a href="my_status.php" class="btn btn-info w-100 mb-2">
                                    <i class="bi bi-gear"></i> Update My Status
                                </a>
                                <a href="attendance.php" class="btn btn-secondary w-100">
                                    <i class="bi bi-calendar-check"></i> View Attendance
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Attendance -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <i class="bi bi-clock-history"></i> Recent Attendance
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Time In</th>
                                        <th>Time Out</th>
                                        <th>Activity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($recentAttendance, 0, 5) as $att): ?>
                                        <tr>
                                            <td><?php echo formatDate($att['scan_date']); ?></td>
                                            <td><?php echo $att['time_in'] ? formatTime($att['time_in']) : '-'; ?></td>
                                            <td><?php echo $att['time_out'] ? formatTime($att['time_out']) : '-'; ?></td>
                                            <td><?php echo $att['activity_out'] ? htmlspecialchars($att['activity_out']) : '-'; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once '../includes/footer.php'; ?>
    <script>
        const SITE_URL = '<?php echo SITE_URL; ?>';
        const UPLOAD_DIR = '<?php echo UPLOAD_DIR; ?>';
    </script>
</body>
</html>
