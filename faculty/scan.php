<?php
require_once '../config/config.php';
require_once '../includes/session_check.php';
require_once '../includes/functions.php';

requireRole(['Admin', 'Faculty']);

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Scan QR Code - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <style>
        @media (max-width: 768px) {
            #qr-reader {
                height: 350px !important;
            }
            .content-wrapper {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <?php require_once '../includes/sidebar.php'; ?>
        <div class="content-wrapper">
            <div class="container-fluid">
                <h1 class="h3 mb-4"><i class="bi bi-qr-code"></i> QR Code Scanner</h1>

                <div class="dashboard-card">
                    <div class="card-header">Scan QR Code for Attendance</div>
                    <div class="card-body">
                        <p class="text-muted">Use your phone or QR scanner to scan a faculty QR code.</p>
                        <div id="qr-reader" style="width: 100%; height: 400px; border: 2px solid #ddd; border-radius: 10px; margin: 20px 0; display:flex; align-items:center; justify-content:center; background:#f8f9fa;">
                            <?php if ($qrExists): ?>
                                <div class="text-center">
                                    <p class="text-muted mb-3" style="font-size:0.95rem;">Your faculty QR code is shown here. Use it with another device or press Start Camera to scan.</p>
                                    <img id="qr-preview" src="<?php echo htmlspecialchars($qrUrl); ?>" alt="Faculty QR Code" class="img-fluid rounded" style="max-height: 340px; max-width: 100%;">
                                </div>
                            <?php else: ?>
                                <div class="text-center text-muted">
                                    <p>No QR preview is available yet.</p>
                                    <p>Please generate your faculty QR code first or use manual token input.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <input type="hidden" id="qr_result">
                        <div id="qr-status" class="text-center text-muted mb-3">Initializing camera...</div>
                        <div id="camera_controls" class="mb-3" style="display:none;">
                            <label class="form-label">Choose Camera</label>
                            <div class="input-group mb-2">
                                <select id="camera_select" class="form-select"></select>
                                <button class="btn btn-success" type="button" id="start_camera_btn">Start Camera</button>
                                <button class="btn btn-secondary" type="button" id="stop_camera_btn" style="display:none;">Stop Camera</button>
                            </div>
                            <small class="form-text text-muted d-block mb-2">If the scanner fails, try selecting a different camera or allow camera permissions in your browser settings.</small>
                            
                            <div class="alert alert-info mb-2" id="mobile-notice" style="display:none;">
                                <i class="bi bi-info-circle"></i> <strong>Mobile Device:</strong> Make sure to allow camera permissions when prompted by your browser.
                            </div>
                        </div>
                        <div class="mb-3 mt-3">
                            <label class="form-label">Manual QR token / URL</label>
                            <div class="input-group">
                                <input type="text" id="manual_token" class="form-control" placeholder="Paste QR token or full scan URL here">
                                <button class="btn btn-primary" type="button" onclick="submitManualToken()">Submit</button>
                            </div>
                            <small class="form-text text-muted">If camera scanning fails, paste your faculty QR token or full QR scan URL and submit manually.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once '../includes/footer.php'; ?>
    <script src="<?php echo SITE_URL; ?>/assets/js/ajax.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/libs/html5-qrcode.min.js"></script>
    <script>
        const SITE_URL = '<?php echo SITE_URL; ?>';
        const UPLOAD_DIR = '<?php echo UPLOAD_DIR; ?>';
        
        function showError(message) {
            alert(message);
        }
        
        function extractQRToken(decodedText) {
            const trimmedText = decodedText.trim();

            try {
                const url = new URL(trimmedText);
                const token = url.searchParams.get('token');
                if (token) {
                    return token;
                }
            } catch (e) {
                // Not a full URL. Try raw token fallback.
            }

            // If the QR code contains only the token, use it directly.
            if (/^[a-f0-9]{100}$/i.test(trimmedText)) {
                return trimmedText;
            }

            return null;
        }

        function setScanStatus(message, type = 'info') {
            const statusEl = document.getElementById('qr-status');
            statusEl.textContent = message;
            statusEl.className = 'text-center mb-3 ' + (type === 'error' ? 'text-danger' : 'text-muted');
        }

        function onScanSuccess(decodedText, decodedResult) {
            const currentTime = Date.now();
            
            // Debounce: ignore scans too close together
            if (currentTime - lastScanTime < SCAN_DEBOUNCE_MS) {
                return;
            }
            
            lastScanTime = currentTime;
            console.log('QR Code Scanned:', decodedText);
            setScanStatus('QR code detected. Processing...');

            const token = extractQRToken(decodedText);
            if (token) {
                console.log('Token extracted:', token);
                document.getElementById('qr_result').value = token;
                // Stop scanner immediately
                if (html5Qrcode) {
                    html5Qrcode.stop().catch(() => {});
                }
                // Send scan data immediately
                scanQRCode(token);
            } else {
                console.error('No valid QR token found in scanned data');
                setScanStatus('Invalid QR code format. Please scan a valid faculty QR code.', 'error');
                showError('Invalid QR code format. Please scan a valid faculty QR code.');
            }
        }

        function onScanFailure(error) {
            // Silently ignore scan failures - they happen constantly during normal scanning
            // Only log to console for debugging
            console.debug('Scan attempt:', error.message || 'No QR code detected');
        }

        function submitManualToken() {
            const rawValue = document.getElementById('manual_token').value;
            const token = extractQRToken(rawValue);

            if (token) {
                document.getElementById('qr_result').value = token;
                scanQRCode(token);
            } else {
                showError('Please enter a valid QR token or scan URL.');
            }
        }

        let html5Qrcode = null;
        let lastScanTime = 0;
        const SCAN_DEBOUNCE_MS = 1000; // Prevent duplicate scans within 1 second

        async function enumerateCameras() {
            try {
                const devices = await Html5Qrcode.getCameras();
                const sel = document.getElementById('camera_select');
                sel.innerHTML = '';
                devices.forEach(d => {
                    const opt = document.createElement('option');
                    opt.value = d.id;
                    opt.textContent = d.label || ('Camera ' + (sel.options.length + 1));
                    sel.appendChild(opt);
                });

                if (devices.length > 0) {
                    console.log('Found', devices.length, 'camera(s)');
                    document.getElementById('camera_controls').style.display = 'block';
                    setScanStatus('Camera(s) found. Click "Start Camera" to begin.');
                } else {
                    setScanStatus('No camera devices found. Please enable camera access in your browser settings.', 'error');
                    showError('No cameras detected. Please check permissions: Settings → Privacy → Camera');
                }
            } catch (e) {
                console.error('Could not enumerate cameras:', e);
                setScanStatus('Camera access denied. Check browser permissions.', 'error');
                showError('Camera permission denied. Please:\n1. Refresh the page\n2. Allow camera access when prompted\n3. Check browser settings: Privacy → Camera');
            }
        }

        function startCamera() {
            const sel = document.getElementById('camera_select');
            const camId = sel.value;
            if (!camId) { 
                showError('Please select a camera'); 
                return; 
            }

            if (html5Qrcode) {
                html5Qrcode.stop().catch(() => {});
            }

            html5Qrcode = new Html5Qrcode('qr-reader');
            setScanStatus('Starting camera...');

            html5Qrcode.start(
                { deviceId: { exact: camId } },
                { fps: 30, qrbox: { width: 300, height: 300 }, aspectRatio: 1.0 },
                onScanSuccess,
                onScanFailure
            ).then(() => {
                console.log('Camera started successfully');
                setScanStatus('✓ Camera active. Point at QR code to scan.');
                document.getElementById('start_camera_btn').style.display = 'none';
                document.getElementById('stop_camera_btn').style.display = 'inline-block';
            }).catch(err => {
                console.error('Camera start failed:', err);
                const errorMsg = (err.message || err).toString();
                
                // Provide specific error messages
                let userMsg = 'Camera failed to start. ';
                if (errorMsg.includes('permission')) {
                    userMsg = 'Camera permission denied. Please:\n1. Refresh the page\n2. Click "Allow" when prompted for camera access\n3. Check browser settings: Privacy & Security → Camera';
                } else if (errorMsg.includes('not found')) {
                    userMsg = 'No camera found on this device.';
                } else if (errorMsg.includes('NotAllowedError')) {
                    userMsg = 'Camera access not allowed. Please enable camera in browser settings and try again.';
                } else if (errorMsg.includes('NotFoundError')) {
                    userMsg = 'No camera device available.';
                }
                
                setScanStatus(userMsg, 'error');
                showError(userMsg);
            });
        }

        function stopCamera() {
            if (html5Qrcode) {
                html5Qrcode.stop().then(() => {
                    setScanStatus('Camera stopped.');
                    document.getElementById('start_camera_btn').style.display = 'inline-block';
                    document.getElementById('stop_camera_btn').style.display = 'none';
                }).catch(err => {
                    console.error('Stop camera error:', err);
                });
            }
        }

        document.getElementById('start_camera_btn').addEventListener('click', startCamera);
        document.getElementById('stop_camera_btn').addEventListener('click', stopCamera);

        // Detect if on mobile device
        function isMobileDevice() {
            return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        }

        // Dynamic loader for html5-qrcode only if the local file is missing.
        function loadScriptSrc(url) {
            return new Promise(function(resolve, reject) {
                const s = document.createElement('script');
                s.src = url;
                s.async = true;
                s.onload = function() { console.log('Loaded script:', url); resolve(); };
                s.onerror = function(e) { console.warn('Script load error:', url, e); reject(e); };
                document.head.appendChild(s);
            });
        }

        document.addEventListener('DOMContentLoaded', async function() {
            // Setup event listeners now that DOM is ready
            document.getElementById('start_camera_btn').addEventListener('click', startCamera);
            document.getElementById('stop_camera_btn').addEventListener('click', stopCamera);
            
            const isMobile = isMobileDevice();
            
            // Show mobile notice if on mobile
            if (isMobile) {
                document.getElementById('mobile-notice').style.display = 'block';
            }
            
            setScanStatus('Loading QR library...');

            if (typeof Html5Qrcode === 'undefined') {
                const cdns = [
                    'https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.7/minified/html5-qrcode.min.js',
                    'https://unpkg.com/html5-qrcode/minified/html5-qrcode.min.js'
                ];

                let loaded = false;
                for (const url of cdns) {
                    try {
                        await loadScriptSrc(url);
                        if (typeof Html5Qrcode !== 'undefined') {
                            loaded = true;
                            break;
                        }
                    } catch (e) {
                        console.warn('Failed to load from', url);
                    }
                }

                if (!loaded) {
                    setScanStatus('QR library failed to load. Please check your internet connection or use manual QR token input.', 'error');
                    return;
                }
            }

            // Library loaded; enumerate cameras
            await enumerateCameras();

            // Auto-start camera on mobile after short delay
            if (isMobile) {
                setTimeout(() => {
                    const camSelect = document.getElementById('camera_select');
                    if (camSelect && camSelect.options.length > 0) {
                        setScanStatus('Starting camera on mobile device...');
                        startCamera();
                    } else {
                        setScanStatus('No camera found. Please check browser permissions.', 'error');
                    }
                }, 500);
            }
        });
    </script>
</body>
</html>
