<?php
/**
 * System Configuration
 * CCSICT Faculty Monitoring System
 */

// Website settings
define('SITE_NAME', 'CCSICT Faculty Monitoring System');
define('SITE_URL', 'http://localhost/jovan');
define('SITE_THEME_COLOR', '#2d5016'); // Dark Green
define('SITE_ACCENT_COLOR', '#ffd700'); // Yellow

// Upload settings
define('UPLOAD_DIR', 'uploads/');
define('QRCODE_DIR', 'qrcodes/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);

// QR Code settings
define('QR_CODE_SIZE', 200);
define('QR_CODE_LEVEL', 'H');

// Session settings
define('SESSION_LIFETIME', 3600); // 1 hour
define('REMEMBER_ME_DURATION', 30 * 24 * 60 * 60); // 30 days

// Status update interval
define('STATUS_AUTO_REFRESH', 5000); // 5 seconds in milliseconds

// System roles
define('ROLE_ADMIN', 'Admin');
define('ROLE_FACULTY', 'Faculty');
define('ROLE_STUDENT', 'Student');

// Account status
define('STATUS_PENDING', 'PENDING');
define('STATUS_APPROVED', 'APPROVED');
define('STATUS_REJECTED', 'REJECTED');

// Faculty status
define('FACULTY_STATUS_IN', 'IN');
define('FACULTY_STATUS_OUT', 'OUT');
define('FACULTY_STATUS_TRAVEL', 'TRAVEL');

// Timezone
date_default_timezone_set('Asia/Manila');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>
