<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/session_check.php';
require_once '../includes/functions.php';

$pageTitle = 'Announcements';
requireRole(['Admin', 'Faculty']);

$announcements = getAnnouncements(20);

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    deleteAnnouncement(intval($_POST['delete_id']));
    header('Location: announcements.php?deleted=1');
    exit();
}

// Handle creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'])) {
    $title = sanitizeInput($_POST['title']);
    $content = sanitizeInput($_POST['content']);
    $priority = sanitizeInput($_POST['priority'] ?? 'MEDIUM');
    $expirationDate = !empty($_POST['expiration_date']) ? sanitizeInput($_POST['expiration_date']) : null;
    $isPinned = isset($_POST['is_pinned']) ? 1 : 0;
    
    if (!empty($title) && !empty($content)) {
        createAnnouncement($title, $content, getCurrentUserId(), $isPinned, $priority, $expirationDate);
        header('Location: announcements.php?created=1');
        exit();
    }
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
                <h1 class="h3 mb-4"><i class="bi bi-megaphone"></i> Announcements</h1>

                <!-- Create Announcement Form -->
                <div class="dashboard-card mb-4">
                    <div class="card-header">
                        <i class="bi bi-plus"></i> Create New Announcement
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            <div class="mb-3">
                                <label for="content" class="form-label">Content</label>
                                <textarea class="form-control" id="content" name="content" rows="4" required></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="priority" class="form-label">Priority</label>
                                    <select class="form-select" id="priority" name="priority">
                                        <option value="LOW">Low</option>
                                        <option value="MEDIUM" selected>Medium</option>
                                        <option value="HIGH">High</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="expiration_date" class="form-label">Expiration Date</label>
                                    <input type="date" class="form-control" id="expiration_date" name="expiration_date">
                                </div>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="is_pinned" name="is_pinned">
                                <label class="form-check-label" for="is_pinned">Pin this announcement (Admin only)</label>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send"></i> Post Announcement
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Announcements List -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <i class="bi bi-list"></i> Recent Announcements
                    </div>
                    <div class="card-body">
                        <?php if (empty($announcements)): ?>
                            <div class="alert alert-info">No announcements yet</div>
                        <?php else: ?>
                            <?php foreach ($announcements as $ann): ?>
                                <div class="mb-4 pb-4 border-bottom">
                                    <h5><?php echo htmlspecialchars($ann['title']); ?>
                                        <?php if ($ann['is_pinned']): ?>
                                            <span class="pinned-badge"><i class="bi bi-pin-fill"></i> Pinned</span>
                                        <?php endif; ?>
                                        <?php if (!empty($ann['priority']) && $ann['priority'] !== 'MEDIUM'): ?>
                                            <span class="badge bg-<?php echo $ann['priority'] === 'HIGH' ? 'danger' : ($ann['priority'] === 'LOW' ? 'secondary' : 'warning'); ?> ms-2">
                                                <?php echo htmlspecialchars($ann['priority']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </h5>
                                    <p class="text-muted">
                                        By: <?php echo htmlspecialchars($ann['fullname']); ?> | 
                                        <?php echo formatDateTime($ann['created_at']); ?>
                                        <?php if (!empty($ann['expiration_date'])): ?>
                                            | Expires: <?php echo formatDateTime($ann['expiration_date']); ?>
                                        <?php endif; ?>
                                    </p>
                                    <p><?php echo htmlspecialchars(substr($ann['content'], 0, 200)); ?>...</p>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="delete_id" value="<?php echo $ann['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this announcement?')">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </form>
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
