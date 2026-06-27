<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/session_check.php';
require_once '../includes/functions.php';

$pageTitle = 'My QR Code';
requireRole(['Faculty']);

$userId = getCurrentUserId();
$facultyId = getFacultyId($userId);
$qrData = getFacultyQRCode($facultyId);
$qrUrl = '';
$qrExists = false;

if ($qrData && !empty($qrData['qr_path'])) {
    $qrUrl = SITE_URL . '/' . QRCODE_DIR . $qrData['qr_path'];
    $qrExists = file_exists(__DIR__ . '/../' . QRCODE_DIR . $qrData['qr_path']);
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
                <h1 class="h3 mb-4"><i class="bi bi-card-image"></i> My QR Code</h1>

                <div class="dashboard-card">
                    <div class="card-header">Faculty QR Code</div>
                    <div class="card-body text-center">
                        <?php if ($qrData && $qrExists): ?>
                            <p class="text-muted mb-4">Scan this QR code for attendance.</p>
                            <img src="<?php echo htmlspecialchars($qrUrl); ?>" alt="My QR Code" class="img-fluid rounded" style="max-width: 320px;">
                            <p class="mt-3">If the image does not appear, refresh the page or verify the file exists in the <code>qrcodes/</code> folder.</p>
                            <a href="<?php echo htmlspecialchars($qrUrl); ?>" download="my_qr_code.png" class="btn btn-primary mt-2">
                                <i class="bi bi-download"></i> Download QR Code
                            </a>
                            <hr>
                            <div class="text-start">
                                <label class="form-label fw-bold">QR Token (for manual entry):</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control" id="qrTokenInput" value="<?php echo htmlspecialchars($qrData['qr_token']); ?>" readonly>
                                    <button class="btn btn-outline-secondary" type="button" onclick="copyToken()">Copy</button>
                                </div>
                            </div>
                        <?php elseif ($qrData && !$qrExists): ?>
                            <div class="alert alert-warning">QR code file is not found on the server. Please contact the administrator.</div>
                        <?php else: ?>
                            <div class="alert alert-danger">No QR code has been generated for your account yet.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once '../includes/footer.php'; ?>
    <script>
    function copyToken() {
        const inp = document.getElementById('qrTokenInput');
        inp.select();
        navigator.clipboard.writeText(inp.value).catch(function() {});
    }
    </script>
</body>
</html>
