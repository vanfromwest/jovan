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
    $travelFrom = !empty($_POST['travel_from']) ? sanitizeInput($_POST['travel_from']) : null;
    $travelTo = !empty($_POST['travel_to']) ? sanitizeInput($_POST['travel_to']) : null;
    $travelDays = !empty($_POST['travel_days']) ? intval($_POST['travel_days']) : null;
    
    updateFacultyStatus($facultyId, $status, $activity, $location, $travelFrom, $travelTo, $travelDays);
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
                                    $statusClass = $currentStatus['status'] === 'IN' ? 'in' : ($currentStatus['status'] === 'TRAVEL' ? 'travel' : 'out');
                                    $statusText = $currentStatus['status'] === 'IN' ? 'IN - In Office' : ($currentStatus['status'] === 'TRAVEL' ? 'ON TRAVEL' : 'OUT - Away');
                                    ?>
                                    <span class="status-badge <?php echo $statusClass; ?>">
                                        <span class="status-badge-pulse"></span> <?php echo $statusText; ?>
                                    </span>
                                </h4>
                                <?php if ($currentStatus['status'] === 'TRAVEL'): ?>
                                    <p><strong><i class="bi bi-airplane"></i> Travel Dates:</strong>
                                        <?php echo htmlspecialchars($currentStatus['travel_from']); ?> to <?php echo htmlspecialchars($currentStatus['travel_to']); ?>
                                        (<?php echo intval($currentStatus['travel_days']); ?> day(s))</p>
                                <?php endif; ?>
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
                                        <select class="form-select" id="status" name="status" required onchange="toggleFields()">
                                            <option value="IN">IN - In Office</option>
                                            <option value="OUT" selected>OUT - Away</option>
                                            <option value="TRAVEL">ON TRAVEL</option>
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

                                    <div id="travel-fields" style="display:none;">
                                        <div class="row">
                                            <div class="col-md-5 mb-3">
                                                <label for="travel_from" class="form-label">Travel From</label>
                                                <input type="date" class="form-control" id="travel_from" name="travel_from">
                                            </div>
                                            <div class="col-md-5 mb-3">
                                                <label for="travel_to" class="form-label">Travel To</label>
                                                <input type="date" class="form-control" id="travel_to" name="travel_to" onchange="calculateDays()">
                                            </div>
                                            <div class="col-md-2 mb-3">
                                                <label for="travel_days" class="form-label">Days</label>
                                                <input type="number" class="form-control" id="travel_days" name="travel_days" readonly>
                                            </div>
                                        </div>
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

        function toggleFields() {
            const status = document.getElementById('status').value;
            const activityField = document.getElementById('activity-field');
            const travelFields = document.getElementById('travel-fields');
            const locationField = document.getElementById('location');

            if (status === 'IN') {
                activityField.style.display = 'none';
                travelFields.style.display = 'none';
                locationField.closest('.mb-3').style.display = 'block';
            } else if (status === 'TRAVEL') {
                activityField.style.display = 'block';
                travelFields.style.display = 'block';
                locationField.closest('.mb-3').style.display = 'none';
            } else {
                activityField.style.display = 'block';
                travelFields.style.display = 'none';
                locationField.closest('.mb-3').style.display = 'block';
            }
        }

        function calculateDays() {
            const from = document.getElementById('travel_from').value;
            const to = document.getElementById('travel_to').value;
            if (from && to) {
                const diff = new Date(to) - new Date(from);
                const days = Math.max(0, Math.round(diff / (1000 * 60 * 60 * 24)) + 1);
                document.getElementById('travel_days').value = days;
            }
        }

        // Initialize on page load
        toggleFields();
    </script>
</body>
</html>
