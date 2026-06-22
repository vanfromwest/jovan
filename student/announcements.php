<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/session_check.php';
require_once '../includes/functions.php';

$pageTitle = 'Announcements';
requireRole(['Student']);

$announcements = getAnnouncements(20);
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
                <h1 class="h3 mb-4"><i class="bi bi-megaphone"></i> Announcements</h1>

                <div class="dashboard-card">
                    <div class="card-header">Latest Announcements</div>
                    <div class="card-body">
                        <?php if (empty($announcements)): ?>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> No announcements available at this time.
                            </div>
                        <?php else: ?>
                            <?php foreach ($announcements as $ann): ?>
                                <div class="mb-4 pb-4 border-bottom">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h5><?php echo htmlspecialchars($ann['title']); ?></h5>
                                            <small class="text-muted">
                                                <i class="bi bi-person"></i> <?php echo htmlspecialchars($ann['fullname']); ?> |
                                                <i class="bi bi-clock"></i> <?php echo formatDateTime($ann['created_at']); ?>
                                            </small>
                                        </div>
                                    </div>
                                    <p class="mt-2"><?php echo htmlspecialchars($ann['content']); ?></p>
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
