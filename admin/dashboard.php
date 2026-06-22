<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/session_check.php';
require_once '../includes/functions.php';

$pageTitle = 'Admin Dashboard';

// Require admin role
requireRole(['Admin']);

// Get statistics
$totalUsers = $conn->query("SELECT COUNT(*) as count FROM users WHERE status='APPROVED'")->fetch_assoc()['count'];
$totalFaculty = $conn->query("SELECT COUNT(*) as count FROM faculty")->fetch_assoc()['count'];
$pendingUsers = $conn->query("SELECT COUNT(*) as count FROM users WHERE status='PENDING'")->fetch_assoc()['count'];
$totalStudents = $conn->query("SELECT COUNT(*) as count FROM users WHERE role='Student' AND status='APPROVED'")->fetch_assoc()['count'];

// Recent activity
$recentActivity = $conn->query("
    SELECT * FROM activity_logs
    ORDER BY created_at DESC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// Faculty with latest status
$facultyStatus = getAllFaculty();
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
                <!-- Header -->
                <div class="mb-4">
                    <h1 class="h3">
                        <i class="bi bi-speedometer2"></i> Admin Dashboard
                    </h1>
                    <p class="text-muted">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="dashboard-card">
                            <div class="card-header">
                                <i class="bi bi-people"></i> Total Users
                            </div>
                            <div class="card-body">
                                <h4 class="text-primary"><?php echo $totalUsers; ?></h4>
                                <small class="text-muted">Approved accounts</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="dashboard-card">
                            <div class="card-header">
                                <i class="bi bi-person-check"></i> Faculty Members
                            </div>
                            <div class="card-body">
                                <h4 class="text-success"><?php echo $totalFaculty; ?></h4>
                                <small class="text-muted">Active faculty</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="dashboard-card">
                            <div class="card-header">
                                <i class="bi bi-hourglass-split"></i> Pending Approval
                            </div>
                            <div class="card-body">
                                <h4 class="text-warning"><?php echo $pendingUsers; ?></h4>
                                <small class="text-muted">Awaiting review</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="dashboard-card">
                            <div class="card-header">
                                <i class="bi bi-person-badge"></i> Students
                            </div>
                            <div class="card-body">
                                <h4 class="text-info"><?php echo $totalStudents; ?></h4>
                                <small class="text-muted">Registered students</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="dashboard-card">
                            <div class="card-header">
                                <i class="bi bi-lightning"></i> Quick Actions
                            </div>
                            <div class="card-body">
                                <div class="btn-group" role="group">
                                    <a href="faculty_management.php" class="btn btn-outline-primary">
                                        <i class="bi bi-people"></i> Manage Faculty
                                    </a>
                                    <a href="pending_users.php" class="btn btn-outline-warning">
                                        <i class="bi bi-hourglass-split"></i> Review Pending Accounts
                                    </a>
                                    <a href="announcements.php" class="btn btn-outline-info">
                                        <i class="bi bi-megaphone"></i> Manage Announcements
                                    </a>
                                    <a href="user_management.php" class="btn btn-outline-secondary">
                                        <i class="bi bi-gear"></i> User Management
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Faculty Status -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="dashboard-card">
                            <div class="card-header">
                                <i class="bi bi-building"></i> Faculty Status Overview
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Faculty Name</th>
                                                <th>Department</th>
                                                <th>Status</th>
                                                <th>Activity</th>
                                                <th>Last Updated</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            if (empty($facultyStatus)):
                                                echo '<tr><td colspan="5" class="text-center text-muted">No faculty members found</td></tr>';
                                            else:
                                                foreach (array_slice($facultyStatus, 0, 5) as $faculty):
                                                    $status = getFacultyStatus($faculty['faculty_id']);
                                                    $statusBadge = ($status['status'] === 'IN') 
                                                        ? '<span class="status-badge in"><span class="status-badge-pulse"></span> IN</span>'
                                                        : '<span class="status-badge out"><span class="status-badge-pulse"></span> OUT</span>';
                                                    ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($faculty['fullname']); ?></td>
                                                        <td><?php echo htmlspecialchars(getDepartmentName($faculty['department_id'])); ?></td>
                                                        <td><?php echo $statusBadge; ?></td>
                                                        <td><?php echo !empty($status['activity']) ? htmlspecialchars($status['activity']) : '-'; ?></td>
                                                        <td><?php echo formatDateTime($status['updated_at']); ?></td>
                                                    </tr>
                                                    <?php 
                                                endforeach;
                                            endif;
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                                <a href="faculty_management.php" class="btn btn-sm btn-primary mt-2">View All Faculty</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="dashboard-card">
                            <div class="card-header">
                                <i class="bi bi-clock-history"></i> Recent Activity
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Action</th>
                                                <th>Details</th>
                                                <th>IP Address</th>
                                                <th>Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recentActivity as $log): ?>
                                                <tr>
                                                    <td><small class="badge bg-secondary"><?php echo htmlspecialchars($log['action']); ?></small></td>
                                                    <td><?php echo htmlspecialchars(truncateText($log['details'], 50)); ?></td>
                                                    <td><small><?php echo htmlspecialchars($log['ip_address']); ?></small></td>
                                                    <td><small><?php echo formatDateTime($log['created_at']); ?></small></td>
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
        </div>
    </div>

    <?php require_once '../includes/footer.php'; ?>
    <script>
        const SITE_URL = '<?php echo SITE_URL; ?>';
        const UPLOAD_DIR = '<?php echo UPLOAD_DIR; ?>';
    </script>
</body>
</html>
