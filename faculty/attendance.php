<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/session_check.php';
require_once '../includes/functions.php';

$pageTitle = 'My Attendance';
requireRole(['Faculty']);

$userId = getCurrentUserId();
$facultyId = getFacultyId($userId);
$attendance = getFacultyAttendance($facultyId);
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
                <h1 class="h3 mb-4"><i class="bi bi-calendar-check"></i> My Attendance Record</h1>

                <div class="dashboard-card">
                    <div class="card-header">Attendance History</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Time In</th>
                                        <th>Time Out</th>
                                        <th>Activity</th>
                                        <th>Location</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    if (empty($attendance)):
                                        echo '<tr><td colspan="5" class="text-center text-muted">No attendance records</td></tr>';
                                    else:
                                        foreach ($attendance as $att):
                                    ?>
                                            <tr>
                                                <td><?php echo formatDate($att['scan_date']); ?></td>
                                                <td><?php echo $att['time_in'] ? formatTime($att['time_in']) : '-'; ?></td>
                                                <td><?php echo $att['time_out'] ? formatTime($att['time_out']) : '-'; ?></td>
                                                <td><?php echo htmlspecialchars($att['activity_out'] ?? '-'); ?></td>
                                                <td><?php echo htmlspecialchars($att['location_out'] ?? '-'); ?></td>
                                            </tr>
                                    <?php 
                                        endforeach;
                                    endif;
                                    ?>
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
