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
                                    $statusClass = $status['status'] === 'IN' ? 'in' : ($status['status'] === 'TRAVEL' ? 'travel' : 'out');
                                    $statusText = $status['status'] === 'IN' ? 'IN - In Office' : ($status['status'] === 'TRAVEL' ? 'ON TRAVEL' : 'OUT - Away');
                                    ?>
                                    <span class="status-badge <?php echo $statusClass; ?>">
                                        <span class="status-badge-pulse"></span> <?php echo $statusText; ?>
                                    </span>
                                </h2>
                                
                                <?php if ($status['status'] === 'TRAVEL'): ?>
                                    <p class="text-muted"><strong><i class="bi bi-airplane"></i> Travel Dates:</strong>
                                        <?php echo htmlspecialchars($status['travel_from']); ?> to <?php echo htmlspecialchars($status['travel_to']); ?>
                                        (<?php echo intval($status['travel_days']); ?> day(s))</p>
                                <?php endif; ?>
                                
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
                                <a href="my_qr.php" class="btn btn-primary w-100 mb-2">
                                    <i class="bi bi-qr-code"></i> My QR Code
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

                <!-- Pinned Announcements -->
                <?php $pinnedAnnouncements = getPinnedAnnouncements(5, true); ?>
                <div class="row mb-4" id="pinned-announcements-row">
                    <div class="col-md-12">
                        <div class="dashboard-card">
                            <div class="card-header">
                                <i class="bi bi-pin-fill"></i> Pinned Announcements
                                <small class="ms-auto refresh-indicator" id="announcement-refresh-indicator" title="Auto-refreshes every 30s"></small>
                            </div>
                            <div class="card-body" id="announcements-container">
                                <?php if (empty($pinnedAnnouncements)): ?>
                                    <p class="text-muted mb-0" id="no-announcements-msg">No pinned announcements yet</p>
                                <?php else: ?>
                                    <?php foreach ($pinnedAnnouncements as $ann): ?>
                                        <div class="mb-3 pb-3 border-bottom announcement-pinned announcement-item">
                                            <h6><?php echo htmlspecialchars($ann['title']); ?>
                                                <span class="pinned-badge"><i class="bi bi-pin-fill"></i> Pinned</span>
                                            </h6>
                                            <small class="text-muted">
                                                <i class="bi bi-person"></i> <?php echo htmlspecialchars($ann['fullname']); ?> |
                                                <i class="bi bi-clock"></i> <?php echo formatDateTime($ann['created_at']); ?>
                                            </small>
                                            <p class="mt-2 mb-0"><?php echo htmlspecialchars(substr($ann['content'], 0, 150)); ?>...</p>
                                        </div>
                                    <?php endforeach; ?>
                                    <a href="announcements.php" class="btn btn-sm btn-primary mt-2">
                                        <i class="bi bi-megaphone"></i> View All Announcements
                                    </a>
                                <?php endif; ?>
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
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('announcements-container');
        const indicator = document.getElementById('announcement-refresh-indicator');
        if (!container) return;

        function refreshAnnouncements() {
            if (indicator) indicator.innerHTML = '<i class="bi bi-arrow-repeat spin"></i>';

            fetch(SITE_URL + '/api/announcements.php?type=pinned&t=' + Date.now())
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const items = container.querySelectorAll('.announcement-item');
                        const currentCount = items.length;
                        const newCount = data.count;

                        if (newCount > currentCount) {
                            container.style.transition = 'background-color 0.5s';
                            container.style.backgroundColor = 'rgba(255, 215, 0, 0.15)';
                            setTimeout(() => { container.style.backgroundColor = ''; }, 1500);
                        }

                        if (data.count === 0) {
                            container.innerHTML = '<p class="text-muted mb-0" id="no-announcements-msg">No pinned announcements yet</p>';
                        } else {
                            let html = '';
                            data.announcements.forEach(function(ann) {
                                html += '<div class="mb-3 pb-3 border-bottom announcement-pinned announcement-item">';
                                html += '<h6>' + escapeHtml(ann.title) + ' <span class="pinned-badge"><i class="bi bi-pin-fill"></i> Pinned</span></h6>';
                                html += '<small class="text-muted"><i class="bi bi-person"></i> ' + escapeHtml(ann.fullname) + ' | <i class="bi bi-clock"></i> ' + formatDateStr(ann.created_at) + '</small>';
                                html += '<p class="mt-2 mb-0">' + escapeHtml(truncateText(ann.content, 150)) + '...</p>';
                                html += '</div>';
                            });
                            html += '<a href="announcements.php" class="btn btn-sm btn-primary mt-2"><i class="bi bi-megaphone"></i> View All Announcements</a>';
                            container.innerHTML = html;
                        }
                    }
                    if (indicator) indicator.innerHTML = '<i class="bi bi-check-circle-fill text-success" style="font-size:12px;"></i>';
                })
                .catch(function() {
                    if (indicator) indicator.innerHTML = '<i class="bi bi-exclamation-circle-fill text-danger" style="font-size:12px;"></i>';
                });
        }

        function escapeHtml(str) {
            if (!str) return '';
            var d = document.createElement('div');
            d.appendChild(document.createTextNode(str));
            return d.innerHTML;
        }

        function truncateText(str, max) {
            if (!str) return '';
            return str.length > max ? str.substring(0, max) : str;
        }

        function formatDateStr(dateStr) {
            if (!dateStr) return '';
            var d = new Date(dateStr.replace(' ', 'T'));
            if (isNaN(d.getTime())) return dateStr;
            var opts = { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' };
            return d.toLocaleDateString('en-US', opts);
        }

        setInterval(refreshAnnouncements, 30000);
    });
    </script>
</body>
</html>
