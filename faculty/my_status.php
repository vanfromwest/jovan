<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/session_check.php';
require_once '../includes/functions.php';

$pageTitle = 'My Status';
requireRole(['Faculty']);

$userId = getCurrentUserId();
$facultyId = getFacultyId($userId);
$currentStatus = getFacultyStatus($facultyId);
$activities = getAllActivities();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = sanitizeInput($_POST['status'] ?? 'OUT');
    $activity = sanitizeInput($_POST['activity'] ?? '');
    $location = sanitizeInput($_POST['location'] ?? '');
    
    updateFacultyStatus($facultyId, $status, $activity, $location);
    logActivity('STATUS_UPDATE', "Faculty status updated to $status - $activity");
    
    $_SESSION['success_message'] = 'Status updated successfully!';
    header('Location: my_status.php');
    exit();
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
                <h1 class="h3 mb-4"><i class="bi bi-heart-pulse"></i> Update My Status</h1>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success">
                        <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="dashboard-card">
                            <div class="card-header">Current Status</div>
                            <div class="card-body">
                                <h4 class="mb-3">
                                    <?php 
                                    $statusClass = $currentStatus['status'] === 'IN' ? 'in' : 'out';
                                    $statusText = $currentStatus['status'] === 'IN' ? 'IN' : 'OUT';
                                    ?>
                                    <span class="status-badge <?php echo $statusClass; ?>">
                                        <span class="status-badge-pulse"></span> <?php echo $statusText; ?>
                                    </span>
                                </h4>
                                <?php if ($currentStatus['activity']): ?>
                                    <p><strong>Activity:</strong> <?php echo htmlspecialchars($currentStatus['activity']); ?></p>
                                <?php endif; ?>
                                <?php if ($currentStatus['location']): ?>
                                    <p><strong>Location:</strong> <?php echo htmlspecialchars($currentStatus['location']); ?></p>
                                <?php endif; ?>
                                <small class="text-muted">Last Updated: <?php echo formatDateTime($currentStatus['updated_at']); ?></small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="dashboard-card">
                            <div class="card-header">Update Status</div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status *</label>
                                        <select class="form-select" id="status" name="status" required onchange="toggleActivityField()">
                                            <option value="IN">IN - In Office</option>
                                            <option value="OUT" selected>OUT - Away</option>
                                        </select>
                                    </div>

                                    <div class="mb-3" id="activity-field">
                                        <label for="activity" class="form-label">Activity</label>
                                        <select class="form-select" id="activity" name="activity">
                                            <option value="">-- Select Activity --</option>
                                            <?php foreach ($activities as $act): ?>
                                                <option value="<?php echo htmlspecialchars($act['name']); ?>">
                                                    <?php echo htmlspecialchars($act['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="location" class="form-label">Location / Room</label>
                                        <input type="text" class="form-control" id="location" name="location" 
                                               placeholder="e.g., Laboratory 2, Meeting Room, etc.">
                                    </div>

                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-check"></i> Update Status
                                    </button>
                                </form>
                            </div>
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

        function toggleActivityField() {
            const status = document.getElementById('status').value;
            const activityField = document.getElementById('activity-field');
            if (status === 'IN') {
                activityField.style.display = 'none';
            } else {
                activityField.style.display = 'block';
            }
        }

        // Initialize on page load
        toggleActivityField();
    </script>
</body>
</html>
