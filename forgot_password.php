<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

// Forgot password - placeholder
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-container">
    <div class="login-box">
        <div class="login-header">
            <h1><i class="bi bi-building"></i> CCSICT</h1>
            <p>Faculty Monitoring System</p>
        </div>

        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> <strong>Password Recovery</strong>
            <p>Please contact the system administrator to reset your password.</p>
            <p>Email: <strong>admin@ccsict.com</strong></p>
        </div>

        <a href="login.php" class="btn btn-primary w-100">
            <i class="bi bi-arrow-left"></i> Back to Login
        </a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
