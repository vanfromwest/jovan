<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/session_check.php';
require_once '../includes/functions.php';

$pageTitle = 'Announcements';
requireRole(['Faculty']);

$userId = getCurrentUserId();
$postMessage = '';
$postError = '';

// Handle announcement creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $title = sanitizeInput($_POST['title'] ?? '');
    $content = sanitizeInput($_POST['content'] ?? '');
    
    if (empty($title) || empty($content)) {
        $postError = 'Title and content are required.';
    } else {
        if (createAnnouncement($title, $content, $userId)) {
            $postMessage = 'Announcement posted successfully!';
            logActivity('ANNOUNCEMENT_CREATE', 'Faculty posted announcement: ' . $title);
        } else {
            $postError = 'Failed to post announcement. Please try again.';
        }
    }
}

// Handle announcement deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $announcementId = intval($_POST['announcement_id'] ?? 0);
    
    // Verify the announcement belongs to the faculty member
    $stmt = $conn->prepare("SELECT created_by FROM announcements WHERE id = ?");
    $stmt->bind_param("i", $announcementId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if ($result && $result['created_by'] == $userId) {
        deleteAnnouncement($announcementId);
        $postMessage = 'Announcement deleted successfully!';
        logActivity('ANNOUNCEMENT_DELETE', 'Faculty deleted announcement ID: ' . $announcementId);
    } else {
        $postError = 'You can only delete your own announcements.';
    }
}

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

                <?php if (!empty($postMessage)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle"></i> <?php echo $postMessage; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($postError)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle"></i> <?php echo $postError; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Post Announcement Form -->
                <div class="dashboard-card mb-4">
                    <div class="card-header">
                        <i class="bi bi-plus-circle"></i> Post an Announcement
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="create">
                            <div class="mb-3">
                                <label for="title" class="form-label">Announcement Title</label>
                                <input type="text" class="form-control" id="title" name="title" placeholder="Enter announcement title" required>
                            </div>
                            <div class="mb-3">
                                <label for="content" class="form-label">Message</label>
                                <textarea class="form-control" id="content" name="content" rows="4" placeholder="Enter your announcement message" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-send"></i> Post Announcement
                            </button>
                        </form>
                    </div>
                </div>

                <!-- All Announcements -->
                <div class="dashboard-card">
                    <div class="card-header">All Announcements</div>
                    <div class="card-body">
                        <?php if (empty($announcements)): ?>
                            <div class="alert alert-info">No announcements at this time</div>
                        <?php else: ?>
                            <?php foreach ($announcements as $ann): ?>
                                <div class="mb-4 pb-4 border-bottom <?php echo $ann['is_pinned'] ? 'announcement-pinned' : ''; ?>">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h5><?php echo htmlspecialchars($ann['title']); ?>
                                                <?php if ($ann['is_pinned']): ?>
                                                    <span class="pinned-badge"><i class="bi bi-pin-fill"></i> Pinned</span>
                                                <?php endif; ?>
                                            </h5>
                                            <small class="text-muted">
                                                By: <?php echo htmlspecialchars($ann['fullname']); ?> | 
                                                <?php echo formatDateTime($ann['created_at']); ?>
                                            </small>
                                        </div>
                                        <?php if ($ann['created_by'] == $userId): ?>
                                            <form method="POST" action="" style="display: inline;">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="announcement_id" value="<?php echo $ann['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this announcement?')">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </form>
                                        <?php endif; ?>
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
