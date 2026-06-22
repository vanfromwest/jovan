<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/session_check.php';
require_once '../includes/functions.php';

$pageTitle = 'Faculty Status';
requireRole(['Student']);

$facultyList = getAllFaculty();
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
                <h1 class="h3 mb-4"><i class="bi bi-search"></i> Faculty Availability</h1>

                <!-- Search -->
                <div class="dashboard-card mb-4">
                    <div class="card-header">Search Faculty</div>
                    <div class="card-body">
                        <input type="text" class="form-control" id="faculty-search" placeholder="Search by name or department...">
                    </div>
                </div>

                <!-- Faculty List -->
                <div class="row">
                    <?php foreach ($facultyList as $faculty):
                        $status = getFacultyStatus($faculty['faculty_id']);
                        $statusClass = $status['status'] === 'IN' ? 'in' : 'out';
                    ?>
                        <div class="col-md-6 col-lg-4 mb-4 faculty-item" 
                             data-faculty-name="<?php echo strtolower($faculty['fullname']); ?>"
                             data-faculty-id="<?php echo $faculty['faculty_id']; ?>">
                            <div class="dashboard-card h-100">
                                <div class="card-body text-center">
                                    <img src="<?php echo SITE_URL . '/' . UPLOAD_DIR . 'profiles/' . ($faculty['profile_image'] ?? 'default.png'); ?>" 
                                         class="rounded-circle mb-3" width="80" height="80" alt="<?php echo $faculty['fullname']; ?>">
                                    
                                    <h5><?php echo htmlspecialchars($faculty['fullname']); ?></h5>
                                    <p class="text-muted"><?php echo htmlspecialchars($faculty['position'] ?? 'Faculty'); ?></p>
                                    
                                    <span class="status-badge <?php echo $statusClass; ?> mb-3">
                                        <span class="status-badge-pulse"></span>
                                        <?php echo $status['status'] === 'IN' ? 'IN' : 'OUT'; ?>
                                    </span>
                                    
                                    <?php if ($status['activity']): ?>
                                        <p class="small mt-2">
                                            <i class="bi bi-info-circle"></i>
                                            <?php echo htmlspecialchars($status['activity']); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <?php require_once '../includes/footer.php'; ?>
    <script>
        const SITE_URL = '<?php echo SITE_URL; ?>';
        const UPLOAD_DIR = '<?php echo UPLOAD_DIR; ?>';

        $(document).ready(function() {
            $('#faculty-search').on('keyup', function() {
                const query = $(this).val().toLowerCase();
                $('.faculty-item').each(function() {
                    const name = $(this).data('faculty-name');
                    if (query === '' || name.includes(query)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });
        });
    </script>
</body>
</html>
