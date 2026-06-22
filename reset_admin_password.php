<?php
/**
 * Admin Password Reset Script
 * Safely resets the admin password to default
 */

require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

$message = '';
$messageType = '';

// Generate new password hash
$defaultPassword = 'sonic123';
$passwordHash = password_hash($defaultPassword, PASSWORD_BCRYPT, ['cost' => 10]);

// Update admin password
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = 'adminsonic@ccsict.com'");

if ($stmt) {
    $stmt->bind_param("s", $passwordHash);
    if ($stmt->execute()) {
        $message = 'Admin password has been reset to: sonic123';
        $messageType = 'success';
    } else {
        $message = 'Error updating password: ' . $stmt->error;
        $messageType = 'error';
    }
    $stmt->close();
} else {
    $message = 'Database error: ' . $conn->error;
    $messageType = 'error';
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Admin Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #2d5016 0%, #1a3a0a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            max-width: 500px;
            text-align: center;
        }
        .container h1 {
            color: #2d5016;
            margin-bottom: 30px;
        }
        .credentials-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: left;
        }
        .credentials-box p {
            margin: 5px 0;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="bi bi-key"></i> Admin Password Reset</h1>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'danger'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($messageType === 'success'): ?>
            <div class="credentials-box">
                <strong>Login Credentials:</strong>
                <p><strong>Email:</strong> adminsonic@ccsict.com</p>
                <p><strong>Password:</strong> sonic123</p>
            </div>

            <div class="alert alert-warning">
                <strong>Important:</strong> Change this password immediately after logging in!
            </div>

            <a href="login.php" class="btn btn-primary btn-lg">
                <i class="bi bi-box-arrow-in-right"></i> Go to Login
            </a>
        <?php endif; ?>
    </div>
</body>
</html>
