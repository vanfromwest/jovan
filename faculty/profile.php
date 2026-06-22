<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/session_check.php';
require_once '../includes/functions.php';

$pageTitle = 'Profile';
requireRole(['Faculty']);

$userId = getCurrentUserId();
$user = getCurrentUserInfo();
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
                <h1 class="h3 mb-4"><i class="bi bi-person"></i> My Profile</h1>

                <div class="row">
                    <div class="col-md-4">
                        <div class="dashboard-card">
                            <div class="card-body text-center">
                                <img src="<?php echo SITE_URL . '/' . UPLOAD_DIR . 'profiles/' . ($user['profile_image'] ?? 'default.png'); ?>" 
                                     class="rounded-circle mb-3" width="150" height="150" alt="<?php echo htmlspecialchars($user['fullname']); ?>">
                                <h5><?php echo htmlspecialchars($user['fullname']); ?></h5>
                                <p class="text-muted"><?php echo htmlspecialchars($user['role']); ?></p>
                                <small class="text-muted">Member since <?php echo formatDate($user['created_at']); ?></small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="dashboard-card">
                            <div class="card-header">Profile Information</div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>Email:</strong>
                                        <p><?php echo htmlspecialchars($user['email']); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Contact Number:</strong>
                                        <p><?php echo htmlspecialchars($user['contact_number'] ?? 'N/A'); ?></p>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>Department:</strong>
                                        <p><?php echo getDepartmentName($user['department_id']); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Account Status:</strong>
                                        <p>
                                            <span class="badge bg-success"><?php echo htmlspecialchars($user['status']); ?></span>
                                        </p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <strong>Last Login:</strong>
                                        <p><?php echo $user['last_login'] ? formatDateTime($user['last_login']) : 'Never'; ?></p>
                                    </div>
                                </div>
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
    </script>
</body>
</html>
