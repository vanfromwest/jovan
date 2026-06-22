<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session_check.php';
require_once __DIR__ . '/../includes/functions.php';

$currentUser = getCurrentUserInfo();
$userRole = $currentUser['role'] ?? null;
?>

<!-- Sidebar Navigation -->
<nav class="sidebar bg-dark d-flex flex-column">
    <!-- Logo Section -->
    <div class="sidebar-header bg-primary text-white p-3 text-center">
        <h5 class="mb-0">
            <i class="bi bi-building"></i> CCSICT
        </h5>
        <small>Faculty Monitoring</small>
    </div>

    <!-- Navigation Menu -->
    <div class="sidebar-menu flex-grow-1">
        <!-- Admin Menu -->
        <?php if ($userRole === 'Admin'): ?>
            <a href="<?php echo SITE_URL; ?>/admin/dashboard.php" class="sidebar-link">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="<?php echo SITE_URL; ?>/admin/faculty_management.php" class="sidebar-link">
                <i class="bi bi-people"></i> Faculty Management
            </a>
            <a href="<?php echo SITE_URL; ?>/admin/pending_users.php" class="sidebar-link">
                <i class="bi bi-hourglass-split"></i> Pending Users
            </a>
            <a href="<?php echo SITE_URL; ?>/admin/announcements.php" class="sidebar-link">
                <i class="bi bi-megaphone"></i> Announcements
            </a>
            <a href="<?php echo SITE_URL; ?>/admin/user_management.php" class="sidebar-link">
                <i class="bi bi-gear"></i> User Management
            </a>
            <a href="<?php echo SITE_URL; ?>/admin/attendance_reports.php" class="sidebar-link">
                <i class="bi bi-file-text"></i> Reports
            </a>
        <!-- Faculty Menu -->
        <?php elseif ($userRole === 'Faculty'): ?>
            <a href="<?php echo SITE_URL; ?>/faculty/dashboard.php" class="sidebar-link">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="<?php echo SITE_URL; ?>/faculty/scan.php" class="sidebar-link">
                <i class="bi bi-qr-code"></i> Scan QR Code
            </a>
            <a href="<?php echo SITE_URL; ?>/faculty/my_qr.php" class="sidebar-link">
                <i class="bi bi-card-image"></i> My QR Code
            </a>
            <a href="<?php echo SITE_URL; ?>/faculty/my_status.php" class="sidebar-link">
                <i class="bi bi-heart-pulse"></i> My Status
            </a>
            <a href="<?php echo SITE_URL; ?>/faculty/attendance.php" class="sidebar-link">
                <i class="bi bi-calendar-check"></i> Attendance
            </a>
            <a href="<?php echo SITE_URL; ?>/faculty/announcements.php" class="sidebar-link">
                <i class="bi bi-megaphone"></i> Announcements
            </a>
            <a href="<?php echo SITE_URL; ?>/faculty/profile.php" class="sidebar-link">
                <i class="bi bi-person"></i> Profile
            </a>
        <!-- Student Menu -->
        <?php elseif ($userRole === 'Student'): ?>
            <a href="<?php echo SITE_URL; ?>/student/dashboard.php" class="sidebar-link">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="<?php echo SITE_URL; ?>/student/faculty_status.php" class="sidebar-link">
                <i class="bi bi-search"></i> Faculty Status
            </a>
            <a href="<?php echo SITE_URL; ?>/student/announcements.php" class="sidebar-link">
                <i class="bi bi-megaphone"></i> Announcements
            </a>
        <?php endif; ?>

        <!-- Monitor (Everyone can access) -->
        <hr class="bg-light">
        <a href="<?php echo SITE_URL; ?>/monitor/live_display.php" class="sidebar-link">
            <i class="bi bi-tv"></i> Live Monitor
        </a>
    </div>

    <!-- Footer Section -->
    <div class="sidebar-footer bg-dark border-top text-white p-3">
        <div class="user-info text-center mb-3">
            <img src="<?php echo SITE_URL . '/' . UPLOAD_DIR . 'profiles/' . ($currentUser['profile_image'] ?? 'default.png'); ?>" 
                 alt="Profile" class="rounded-circle" width="40" height="40">
            <p class="small mb-0 mt-2"><?php echo htmlspecialchars($currentUser['fullname']); ?></p>
            <small class="text-muted"><?php echo $userRole; ?></small>
        </div>
        <a href="<?php echo SITE_URL; ?>/logout.php" class="btn btn-sm btn-danger w-100">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </div>
</nav>
