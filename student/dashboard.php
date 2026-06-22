<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/session_check.php';
require_once '../includes/functions.php';

$pageTitle = 'Student Dashboard';
requireRole(['Student']);

$announcements = getAnnouncements(5, true);
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
                    <div class="card-header">
                        <i class="bi bi-megaphone"></i> Latest Announcements
                        <small class="ms-auto refresh-indicator" id="announcement-refresh-indicator" title="Auto-refreshes every 30s"></small>
                    </div>
                    <div class="card-body" id="announcements-container">
                        <?php if (empty($announcements)): ?>
                            <p class="text-muted" id="no-announcements-msg">No announcements at this time</p>
                        <?php else: ?>
                            <?php foreach ($announcements as $ann): ?>
                                <div class="mb-3 pb-3 border-bottom announcement-item <?php echo $ann['is_pinned'] ? 'announcement-pinned' : ''; ?>">
                                    <h6><?php echo htmlspecialchars($ann['title']); ?>
                                        <?php if ($ann['is_pinned']): ?>
                                            <span class="pinned-badge"><i class="bi bi-pin-fill"></i> Pinned</span>
                                        <?php endif; ?>
                                    </h6>
                                    <small class="text-muted">
                                        <i class="bi bi-person"></i> <?php echo htmlspecialchars($ann['fullname']); ?> |
                                        <?php echo formatDateTime($ann['created_at']); ?>
                                    </small>
                                    <p class="mt-2"><?php echo htmlspecialchars(substr($ann['content'], 0, 100)); ?>...</p>
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

            fetch(SITE_URL + '/api/announcements.php?type=all&t=' + Date.now())
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
                            container.innerHTML = '<p class="text-muted" id="no-announcements-msg">No announcements at this time</p>';
                        } else {
                            let html = '';
                            data.announcements.forEach(function(ann) {
                                var pinnedClass = ann.is_pinned ? ' announcement-pinned' : '';
                                var badgeHtml = ann.is_pinned ? ' <span class="pinned-badge"><i class="bi bi-pin-fill"></i> Pinned</span>' : '';
                                html += '<div class="mb-3 pb-3 border-bottom announcement-item' + pinnedClass + '">';
                                html += '<h6>' + escapeHtml(ann.title) + badgeHtml + '</h6>';
                                html += '<small class="text-muted"><i class="bi bi-person"></i> ' + escapeHtml(ann.fullname) + ' | ' + formatDateStr(ann.created_at) + '</small>';
                                html += '<p class="mt-2">' + escapeHtml(truncateText(ann.content, 100)) + '...</p>';
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
