<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/session_check.php';
require_once '../includes/functions.php';

$pageTitle = 'Student Dashboard';
requireRole(['Student']);

$announcements = getAnnouncements(5);
$facultyList = getAllFaculty();

// Count faculty statuses
$facultyIn = 0;
$facultyOut = 0;
foreach ($facultyList as $f) {
    $status = getFacultyStatus($f['faculty_id']);
    if ($status['status'] === 'IN') $facultyIn++;
    else $facultyOut++;
}
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
                <h1 class="h3 mb-4"><i class="bi bi-speedometer2"></i> Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></h1>

                <!-- Quick Stats -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="dashboard-card">
                            <div class="card-header">Faculty In Office</div>
                            <div class="card-body">
                                <h4 class="text-success"><?php echo $facultyIn; ?></h4>
                                <small class="text-muted">Available now</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="dashboard-card">
                            <div class="card-header">Faculty Away</div>
                            <div class="card-body">
                                <h4 class="text-warning"><?php echo $facultyOut; ?></h4>
                                <small class="text-muted">Outside office</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="dashboard-card">
                            <div class="card-header">Quick Actions</div>
                            <div class="card-body">
                                <a href="faculty_status.php" class="btn btn-primary">
                                    <i class="bi bi-search"></i> View Faculty
                                </a>
                                <a href="<?php echo SITE_URL; ?>/monitor/live_display.php" class="btn btn-info">
                                    <i class="bi bi-tv"></i> Live Monitor
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Announcements -->
                <div class="dashboard-card">
                    <div class="card-header">Recent Announcements</div>
                    <div class="card-body">
                        <?php if (empty($announcements)): ?>
                            <p class="text-muted">No announcements at this time</p>
                        <?php else: ?>
                            <?php foreach ($announcements as $ann): ?>
                                <div class="mb-3 pb-3 border-bottom">
                                    <h6><?php echo htmlspecialchars($ann['title']); ?></h6>
                                    <small class="text-muted"><?php echo formatDateTime($ann['created_at']); ?></small>
                                    <p class="mt-2"><?php echo htmlspecialchars(substr($ann['content'], 0, 100)); ?>...</p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
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
