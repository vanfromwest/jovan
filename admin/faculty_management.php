<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/session_check.php';
require_once '../includes/functions.php';

$pageTitle = 'Faculty Management';
requireRole(['Admin']);

$deleted = isset($_GET['deleted']) ? true : false;
$error = sanitizeInput($_GET['error'] ?? '');

$faculty = getAllFaculty();
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3"><i class="bi bi-people"></i> Faculty Management</h1>
                    <a href="add_faculty.php" class="btn btn-primary">
                        <i class="bi bi-plus"></i> Add Faculty
                    </a>
                </div>

                <?php if ($deleted): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle"></i> Faculty member deleted successfully!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle"></i> <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <?php foreach ($faculty as $f): 
                        $status = getFacultyStatus($f['faculty_id']);
                        $statusClass = ($status['status'] === 'IN') ? 'in' : 'out';
                    ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="dashboard-card">
                                <div class="card-body text-center">
                                    <img src="<?php echo SITE_URL . '/' . UPLOAD_DIR . 'profiles/' . ($f['profile_image'] ?? 'default.png'); ?>" 
                                         class="rounded-circle mb-3" width="80" height="80" alt="<?php echo $f['fullname']; ?>">
                                    
                                    <h5><?php echo htmlspecialchars($f['fullname']); ?></h5>
                                    <p class="text-muted"><?php echo htmlspecialchars($f['position'] ?? 'Faculty'); ?></p>
                                    
                                    <span class="status-badge <?php echo $statusClass; ?> mb-3">
                                        <span class="status-badge-pulse"></span>
                                        <?php echo $status['status'] === 'IN' ? 'IN' : 'OUT'; ?>
                                    </span>
                                    
                                    <div class="mt-3">
                                        <a href="edit_faculty.php?id=<?php echo $f['faculty_id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <a href="delete_faculty.php?id=<?php echo $f['faculty_id']; ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Are you sure?')">
                                            <i class="bi bi-trash"></i> Delete
                                        </a>
                                    </div>
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
    </script>
</body>
</html>
