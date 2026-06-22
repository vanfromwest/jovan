<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/session_check.php';
require_once '../includes/functions.php';
require_once '../includes/email_functions.php';

$pageTitle = 'Email Settings';
requireRole(['Admin']);

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['save_settings'])) {
            $settings = [
                'mail_host'       => $_POST['mail_host'] ?? MAIL_HOST,
                'mail_port'       => $_POST['mail_port'] ?? MAIL_PORT,
                'mail_username'   => $_POST['mail_username'] ?? '',
                'mail_encryption' => $_POST['mail_encryption'] ?? 'tls',
                'from_email'      => $_POST['from_email'] ?? '',
                'from_name'       => $_POST['from_name'] ?? SITE_NAME,
            ];

            $pw = $_POST['mail_password'] ?? '';
            if (!empty($pw)) {
                $settings['mail_password'] = $pw;
            }

            $stmt = $conn->prepare("
                INSERT INTO email_config (`key`, `value`, `updated_at`) 
                VALUES (?, ?, NOW())
                ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updated_at` = NOW()
            ");

            foreach ($settings as $key => $value) {
                $stmt->bind_param("ss", $key, $value);
                $stmt->execute();
            }

            $message = 'Email settings saved successfully.';
            $messageType = 'success';
        }

        if (isset($_POST['test_email'])) {
            $result = testMailConnection();
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'danger';
        }
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'danger';
    }
}

$currentSettings = [
    'mail_host'       => MAIL_HOST,
    'mail_port'       => MAIL_PORT,
    'mail_username'   => MAIL_USERNAME,
    'mail_password'   => '',
    'mail_encryption' => MAIL_ENCRYPTION,
    'from_email'      => MAIL_FROM_ADDRESS,
    'from_name'       => MAIL_FROM_NAME,
];

try {
    $stmt = $conn->prepare("SELECT `key`, `value` FROM email_config");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        if (isset($currentSettings[$row['key']])) {
            $currentSettings[$row['key']] = $row['value'];
        }
    }
} catch (Exception $e) {
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
                <h1 class="h3 mb-4"><i class="bi bi-envelope"></i> Email Settings</h1>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-gear"></i> SMTP Configuration
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">SMTP Host</label>
                                            <input type="text" name="mail_host" class="form-control"
                                                   value="<?php echo htmlspecialchars($currentSettings['mail_host']); ?>" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Port</label>
                                            <input type="number" name="mail_port" class="form-control"
                                                   value="<?php echo htmlspecialchars($currentSettings['mail_port']); ?>" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Encryption</label>
                                            <select name="mail_encryption" class="form-select">
                                                <option value="tls" <?php echo $currentSettings['mail_encryption'] === 'tls' ? 'selected' : ''; ?>>TLS</option>
                                                <option value="ssl" <?php echo $currentSettings['mail_encryption'] === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                                <option value="" <?php echo $currentSettings['mail_encryption'] === '' ? 'selected' : ''; ?>>None</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">SMTP Username</label>
                                            <input type="email" name="mail_username" class="form-control"
                                                   value="<?php echo htmlspecialchars($currentSettings['mail_username']); ?>"
                                                   placeholder="your@gmail.com">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">SMTP Password</label>
                                            <input type="password" name="mail_password" class="form-control"
                                                   placeholder="App Password">
                                            <small class="text-muted">For Gmail, use an <a href="https://support.google.com/accounts/answer/185833" target="_blank">App Password</a>.</small>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">From Email Address</label>
                                            <input type="email" name="from_email" class="form-control"
                                                   value="<?php echo htmlspecialchars($currentSettings['from_email']); ?>"
                                                   placeholder="noreply@<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'example.com'); ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">From Name</label>
                                            <input type="text" name="from_name" class="form-control"
                                                   value="<?php echo htmlspecialchars($currentSettings['from_name']); ?>">
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2">
                                        <button type="submit" name="save_settings" class="btn btn-primary">
                                            <i class="bi bi-check"></i> Save Settings
                                        </button>
                                        <button type="submit" name="test_email" class="btn btn-outline-secondary">
                                            <i class="bi bi-send"></i> Send Test Email
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-info-circle"></i> Gmail Setup Guide
                            </div>
                            <div class="card-body">
                                <h6>Using Gmail SMTP</h6>
                                <ol class="small">
                                    <li><strong>SMTP Host:</strong> smtp.gmail.com</li>
                                    <li><strong>Port:</strong> 587</li>
                                    <li><strong>Encryption:</strong> TLS</li>
                                    <li>Enable 2-Factor Authentication on your Google account.</li>
                                    <li>Generate an <a href="https://support.google.com/accounts/answer/185833" target="_blank">App Password</a> and use it as the SMTP password.</li>
                                </ol>
                                <hr>
                                <h6>Email Template</h6>
                                <p class="small text-muted mb-0">Approval emails include the user's name, email, role, and a login button. Rejection emails notify the user their account was declined.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once '../includes/footer.php'; ?>
</body>
</html>
