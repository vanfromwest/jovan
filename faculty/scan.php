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
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/style.css'); ?>">
    <style>
        #qr-reader {
            width: 100%;
            min-height: 400px;
            border-radius: 12px;
            overflow: hidden;
            background: #000;
            position: relative;
        }
        #qr-reader video {
            display: block;
            width: 100%;
            height: auto;
            object-fit: cover;
        }
        #qr-reader img[alt="Faculty QR Code"] {
            display: block;
            max-width: 260px;
            margin: 40px auto;
        }

        .scanner-container {
            position: relative;
            overflow: hidden;
            border-radius: 12px;
            background: #111;
        }

        .scan-frame {
            display: none;
        }

        .scan-line { display: none; }

        .scanning-badge {
            position: absolute;
            bottom: 16px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 20;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 16px;
            border-radius: 20px;
            background: rgba(0,0,0,0.6);
            color: #fff;
            font-size: 13px;
            font-weight: 500;
            transition: opacity 0.3s;
        }
        .scanning-badge.hidden { opacity: 0; pointer-events: none; }
        .scanning-pulse {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #28a745;
            animation: scanningPulse 1.2s ease-in-out infinite;
        }
        @keyframes scanningPulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%      { opacity: 0.4; transform: scale(0.7); }
        }

        .tap-scan-btn {
            position: absolute;
            inset: 0;
            z-index: 15;
            background: transparent;
            border: none;
            cursor: pointer;
            display: none;
        }
        .tap-scan-btn.visible { display: block; }
        .tap-scan-flash {
            position: absolute;
            inset: 0;
            z-index: 25;
            background: #fff;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.1s;
        }
        .tap-scan-flash.active { opacity: 0.5; }

        .cam-status {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
        }
        .cam-status .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .cam-status .dot.active   { background: #28a745; box-shadow: 0 0 6px rgba(40,167,69,.6); }
        .cam-status .dot.inactive { background: #6c757d; }
        .cam-status .dot.error    { background: #dc3545; box-shadow: 0 0 6px rgba(220,53,69,.6); }
        .cam-status .dot.warning  { background: #ffc107; box-shadow: 0 0 6px rgba(255,193,7,.6); }

        .view-toggle .btn {
            border-radius: 20px;
            padding: 4px 16px;
            font-size: 13px;
        }

        /* Success overlay */
        .scan-overlay {
            position: fixed;
            inset: 0;
            z-index: 1060;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(0,0,0,0.55);
            backdrop-filter: blur(4px);
            animation: fadeIn .25s ease;
        }
        .scan-overlay.show {
            display: flex;
        }
        .scan-overlay-card {
            background: #fff;
            border-radius: 20px;
            padding: 40px 48px;
            max-width: 420px;
            width: 90%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,.3);
            animation: popUp .35s cubic-bezier(.34,1.56,.64,1);
        }
        .scan-overlay-card .checkmark-wrap {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #d4edda;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            animation: popIn .4s cubic-bezier(.34,1.56,.64,1) .1s both;
        }
        .scan-overlay-card .checkmark-wrap i {
            font-size: 44px;
            color: #28a745;
        }
        .scan-overlay-card .scan-faculty-name {
            font-size: 22px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 4px;
        }
        .scan-overlay-card .scan-type-badge {
            display: inline-block;
            padding: 4px 18px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 14px;
            letter-spacing: .5px;
            margin-bottom: 8px;
        }
        .scan-type-badge.in  { background: #d4edda; color: #155724; }
        .scan-type-badge.out { background: #f8d7da; color: #721c24; }
        .scan-overlay-card .scan-time {
            color: #888;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .scan-overlay-card .btn {
            border-radius: 20px;
            padding: 8px 28px;
            font-weight: 600;
        }

        .scan-overlay-card .checkmark-wrap.error {
            background: #f8d7da;
        }
        .scan-overlay-card .checkmark-wrap.error i {
            color: #dc3545;
        }

        @keyframes fadeIn { from { opacity:0 } to { opacity:1 } }
        @keyframes popUp  { from { opacity:0; transform:scale(.85) translateY(20px) } to { opacity:1; transform:scale(1) translateY(0) } }
        @keyframes popIn  { from { opacity:0; transform:scale(0) } to { opacity:1; transform:scale(1) } }

        .scanner-container:fullscreen {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #000;
            border-radius: 0;
            padding: 0;
        }
        .scanner-container:fullscreen #qr-reader {
            max-width: 100vw;
            max-height: 100vh;
            border-radius: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .scanner-container:fullscreen #qr-reader video {
            width: 100vw;
            height: 100vh;
            object-fit: contain;
        }
        .scanner-container:fullscreen .scan-frame { display: none; }
        .scanner-container:fullscreen .scan-line { display: none; }
            box-shadow: 0 0 20px rgba(255, 215, 0, 0.7);
        }
        .scanner-container:fullscreen .fs-topbar {
            display: flex;
        }
        .fs-topbar {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 9999;
            padding: 16px 20px;
            background: linear-gradient(180deg, rgba(0,0,0,.7) 0%, transparent 100%);
            align-items: center;
            justify-content: space-between;
            pointer-events: none;
        }
        .fs-topbar > * {
            pointer-events: auto;
        }
        .fs-topbar .fs-status {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #fff;
            font-size: 13px;
            font-weight: 500;
        }
        .fs-topbar .fs-status .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .fs-topbar .fs-status .dot.active   { background: #28a745; box-shadow: 0 0 8px rgba(40,167,69,.8); }
        .fs-topbar .fs-status .dot.inactive { background: #6c757d; }
        .fs-topbar .fs-status .dot.error    { background: #dc3545; box-shadow: 0 0 8px rgba(220,53,69,.8); }
        .fs-topbar .btn-exit-fs {
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.2);
            color: #fff;
            border-radius: 8px;
            padding: 6px 14px;
            font-size: 13px;
            cursor: pointer;
            transition: background .2s;
        }
        .fs-topbar .btn-exit-fs:hover {
            background: rgba(255,255,255,0.22);
        }
        .fs-hint {
            display: none;
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%);
            color: rgba(255,255,255,0.35);
            font-size: 12px;
            z-index: 9999;
            pointer-events: none;
            text-align: center;
            letter-spacing: .3px;
            background: rgba(0,0,0,0.3);
            padding: 6px 16px;
            border-radius: 20px;
        }

        @media (max-width: 768px) {
            #qr-reader { min-height: 320px; }
            .scan-overlay-card { padding: 28px 24px; }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <?php require_once '../includes/sidebar.php'; ?>
        <div class="content-wrapper">
            <div class="container-fluid">

                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h1 class="h3 mb-0"><i class="bi bi-qr-code"></i> QR Code Scanner</h1>
                </div>

                <div id="scannerView">
                    <div class="dashboard-card">
                        <div class="card-header"><i class="bi bi-camera-video"></i> Point camera at a QR code</div>
                        <div class="card-body p-0">
                            <div class="scanner-container" id="scannerContainer">
                                <div id="qr-reader">
                                    <video id="qr-video" autoplay playsinline muted></video>
                                    <div class="text-center text-muted py-5" id="loadingPlaceholder">
                                        <i class="bi bi-camera" style="font-size:3rem;display:block;margin-bottom:12px;"></i>
                                        <p>Starting camera...</p>
                                    </div>
                                </div>
                                <div class="scanning-badge" id="scanningBadge">
                                    <span class="scanning-pulse"></span>
                                    <span>Scanning...</span>
                                </div>
                                <button class="tap-scan-btn" id="tapScanBtn" title="Tap to scan"></button>
                                <div class="tap-scan-flash" id="tapScanFlash"></div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
                        <div class="cam-status" id="camStatus">
                            <span class="dot inactive" id="camDot"></span>
                            <span id="camStatusText">Initializing camera...</span>
                        </div>
                        <div class="d-flex gap-3" id="qualityIndicators" style="display:none;">
                            <span class="cam-status" id="lightingIndicator">
                                <span class="dot inactive" id="lightingDot"></span>
                                <span id="lightingText">Lighting</span>
                            </span>
                            <span class="cam-status" id="stabilityIndicator">
                                <span class="dot inactive" id="stabilityDot"></span>
                                <span id="stabilityText">Steady</span>
                            </span>
                        </div>
                        <select id="camera_select" class="form-select form-select-sm" style="width:auto;min-width:200px;"></select>
                        <button class="btn btn-success btn-sm" type="button" id="start_camera_btn" style="display:none;">
                            <i class="bi bi-play-fill"></i> Start
                        </button>
                        <button class="btn btn-secondary btn-sm" type="button" id="stop_camera_btn" style="display:none;">
                            <i class="bi bi-stop-fill"></i> Stop
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" type="button" id="fullscreen_btn" title="Toggle fullscreen">
                            <i class="bi bi-arrows-fullscreen"></i>
                        </button>
                    </div>

                    <div class="fs-topbar" id="fsTopbar">
                        <div class="fs-status">
                            <span class="dot inactive" id="fsCamDot"></span>
                            <span id="fsCamStatus">Camera active</span>
                        </div>
                        <button class="btn-exit-fs" id="fsExitBtn"><i class="bi bi-fullscreen-exit"></i> Exit fullscreen</button>
                    </div>
                    <div class="fs-hint" id="fsHint">Press <kbd>Esc</kbd> to exit fullscreen</div>

                    <div class="alert alert-info mb-2 py-2 px-3" id="mobile-notice" style="display:none;font-size:13px;">
                        <i class="bi bi-info-circle"></i> Make sure to allow camera permissions when prompted.
                    </div>

                    <div class="accordion mb-4" id="manualAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#manualCollapse">
                                    <small><i class="bi bi-keyboard"></i> Or enter token manually</small>
                                </button>
                            </h2>
                            <div id="manualCollapse" class="accordion-collapse collapse" data-bs-parent="#manualAccordion">
                                <div class="accordion-body py-2">
                                    <div class="input-group input-group-sm">
                                        <input type="text" id="manual_token" class="form-control" placeholder="Paste QR token or scan URL">
                                        <button class="btn btn-primary" type="button" onclick="submitManualToken()">Submit</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" id="qr_result">
                </div>

            </div>
        </div>
    </div>

    <!-- Scan Success Overlay -->
    <div class="scan-overlay" id="scanOverlay">
        <div class="scan-overlay-card">
            <div class="checkmark-wrap" id="scanIconWrap">
                <i class="bi bi-check-circle-fill" id="scanIcon"></i>
                <img id="scanProfileImg" class="d-none" style="width:80px;height:80px;border-radius:50%;object-fit:cover;">
            </div>
            <div class="scan-faculty-name" id="scanFacultyName"></div>
            <div class="scan-type-badge" id="scanTypeBadge"></div>
            <div class="scan-time" id="scanTimestamp"></div>
            <div id="timeoutActivitySection" class="d-none mt-3 text-start">
                <hr>
                <label class="form-label fw-bold">Activity</label>
                <select id="activitySelect" class="form-select form-select-sm mb-2"></select>
                <label class="form-label fw-bold">Location</label>
                <input type="text" id="timeoutLocation" class="form-control form-control-sm mb-2" placeholder="e.g., Room 204">
                <button class="btn btn-primary btn-sm w-100" id="submitTimeoutBtn">
                    <i class="bi bi-check-circle"></i> Record Time Out
                </button>
            </div>
            <button class="btn btn-success mt-2" id="scanAgainBtn" onclick="dismissScanOverlay()">
                <i class="bi bi-qr-code"></i> Scan Again
            </button>
        </div>
    </div>

    <?php require_once '../includes/footer.php'; ?>
    <script src="<?php echo SITE_URL; ?>/assets/libs/jsqr.min.js?v=<?php echo filemtime(__DIR__ . '/../assets/libs/jsqr.min.js'); ?>"></script>
    <script>
        const SITE_URL = '<?php echo SITE_URL; ?>';
        const UPLOAD_DIR = '<?php echo UPLOAD_DIR; ?>';
        let mediaStream = null;
        let animationId = null;
        let scanCanvas = null;
        let scanCtx = null;
        let lastScanTime = 0;
        const SCAN_DEBOUNCE_MS = 1000;
        let scanRestartTimer = null;
        let scanIdleTimer = null;
        let isScanning = false;
        let scanStartTime = 0;

        // ── Helpers ──────────────────────────────────────────

        function showError(msg) { alert(msg); }

        function extractQRToken(decodedText) {
            const t = decodedText.trim();
            try {
                const url = new URL(t);
                const token = url.searchParams.get('token');
                if (token) return token;
            } catch (_) {}
            if (/^[a-f0-9]{32,100}$/i.test(t)) return t;
            return null;
        }

        function setCamStatus(text, state) {
            const dot = document.getElementById('camDot');
            const el = document.getElementById('camStatusText');
            el.textContent = text;
            dot.className = 'dot ' + state;
        }

        function toggleScannerUI(running) {
            document.getElementById('start_camera_btn').style.display = running ? 'none' : 'inline-block';
            document.getElementById('stop_camera_btn').style.display  = running ? 'inline-block' : 'none';
        }

        function setScanningBadge(visible) {
            const badge = document.getElementById('scanningBadge');
            if (badge) badge.classList.toggle('hidden', !visible);
        }

        // ── Idle timeout ────────────────────────────────────

        let scanIdleStage = 0;

        function resetScanIdleTimer() {
            clearTimeout(scanIdleTimer);
            scanIdleStage = 0;
            scanStartTime = Date.now();
            scanIdleTimer = setTimeout(scanIdleTick, 8000);
        }

        function scanIdleTick() {
            if (!mediaStream || isScanning) return;
            scanIdleStage++;
            if (scanIdleStage === 1) {
                setCamStatus('Hold QR code steady in the center of the frame', 'inactive');
                scanIdleTimer = setTimeout(scanIdleTick, 7000);
            } else {
                setCamStatus('Camera not detecting QR — try manual entry below', 'error');
                document.getElementById('manualCollapse').classList.add('show');
            }
        }

        function clearScanIdleTimer() {
            clearTimeout(scanIdleTimer);
            scanIdleTimer = null;
            scanIdleStage = 0;
        }

        // ── Quality analysis ────────────────────────────────

        let prevFrameData = null;
        let qualityCheckCounter = 0;
        const QUALITY_CHECK_INTERVAL = 30;

        function showQualityIndicators(show) {
            document.getElementById('qualityIndicators').style.display = show ? '' : 'none';
        }

        function setIndicator(id, state, text) {
            const dot = document.getElementById(id + 'Dot');
            const label = document.getElementById(id + 'Text');
            dot.className = 'dot ' + state;
            label.textContent = text;
        }

        function analyzeFrameQuality(imageData) {
            const data = imageData.data;
            const w = imageData.width;
            const h = imageData.height;
            const totalPixels = w * h;
            let sumLum = 0;
            let darkPixels = 0;
            let brightPixels = 0;

            // Sample pixels in a grid (every 8th pixel) for performance
            for (let y = 0; y < h; y += 8) {
                for (let x = 0; x < w; x += 8) {
                    const idx = (y * w + x) * 4;
                    const lum = 0.299 * data[idx] + 0.587 * data[idx + 1] + 0.114 * data[idx + 2];
                    sumLum += lum;
                    if (lum < 40) darkPixels++;
                    else if (lum > 200) brightPixels++;
                }
            }

            const sampled = Math.ceil(h / 8) * Math.ceil(w / 8);
            const avgLum = sumLum / sampled;
            const darkRatio = darkPixels / sampled;
            const brightRatio = brightPixels / sampled;

            // Lighting assessment
            if (darkRatio > 0.6) {
                setIndicator('lighting', 'error', 'Too dark — increase light');
            } else if (brightRatio > 0.6) {
                setIndicator('lighting', 'warning', 'Too bright — reduce glare');
            } else if (avgLum < 60) {
                setIndicator('lighting', 'warning', 'Low light');
            } else if (avgLum > 180) {
                setIndicator('lighting', 'warning', 'Bright');
            } else {
                setIndicator('lighting', 'active', 'Good lighting');
            }

            // Stability / blur assessment via frame comparison
            if (prevFrameData) {
                let diffSum = 0;
                let diffCount = 0;
                for (let y = 0; y < h; y += 12) {
                    for (let x = 0; x < w; x += 12) {
                        const idx = (y * w + x) * 4;
                        const curr = 0.299 * data[idx] + 0.587 * data[idx + 1] + 0.114 * data[idx + 2];
                        const prev = prevFrameData[y * w + x] || 0;
                        diffSum += Math.abs(curr - prev);
                        diffCount++;
                    }
                }
                const avgDiff = diffSum / diffCount;

                if (avgDiff > 25) {
                    setIndicator('stability', 'error', 'Hold steady — camera moving');
                } else if (avgDiff > 10) {
                    setIndicator('stability', 'warning', 'Steady...');
                } else {
                    setIndicator('stability', 'active', 'Stable');
                }
            } else {
                setIndicator('stability', 'inactive', 'Analyzing...');
            }

            // Store current luminance values for next comparison
            prevFrameData = new Float32Array(totalPixels);
            for (let i = 0; i < totalPixels; i++) {
                const idx = i * 4;
                prevFrameData[i] = 0.299 * data[idx] + 0.587 * data[idx + 1] + 0.114 * data[idx + 2];
            }
        }

        // ── Tap to scan ────────────────────────────────────

        function doTapScan() {
            if (!mediaStream || isScanning) return;
            const video = document.getElementById('qr-video');
            if (!video || video.readyState < 2 || !video.videoWidth || !video.videoHeight) return;

            const flash = document.getElementById('tapScanFlash');
            flash.classList.add('active');
            setTimeout(function() { flash.classList.remove('active'); }, 150);

            const c = document.createElement('canvas');
            c.width = video.videoWidth;
            c.height = video.videoHeight;
            const ctx = c.getContext('2d');
            ctx.drawImage(video, 0, 0, c.width, c.height);

            const imageData = ctx.getImageData(0, 0, c.width, c.height);
            const code = jsQR(imageData.data, imageData.width, imageData.height);

            if (code && code.data) {
                const now = Date.now();
                if (now - lastScanTime >= SCAN_DEBOUNCE_MS) {
                    lastScanTime = now;
                    resetScanIdleTimer();
                    handleScanResult(code.data);
                }
            } else {
                setCamStatus('No QR found — try adjusting distance or lighting', 'warning');
            }
        }

        function showTapScanBtn(visible) {
            document.getElementById('tapScanBtn').classList.toggle('visible', visible);
        }

        // ── Overlay ──────────────────────────────────────────

        let scanFacultyId = null;

        function showScanOverlay(type, facultyName, scanType, time, profileImg) {
            const overlay = document.getElementById('scanOverlay');
            const wrap = document.getElementById('scanIconWrap');
            const icon = document.getElementById('scanIcon');
            const nameEl = document.getElementById('scanFacultyName');
            const badgeEl = document.getElementById('scanTypeBadge');
            const timeEl = document.getElementById('scanTimestamp');
            const profileEl = document.getElementById('scanProfileImg');
            const activitySection = document.getElementById('timeoutActivitySection');
            const scanAgainBtn = document.getElementById('scanAgainBtn');

            if (type === 'success') {
                wrap.className = 'checkmark-wrap';
                icon.className = 'bi bi-check-circle-fill d-inline';
                profileEl.className = 'd-none';
                if (profileImg) {
                    icon.className = 'bi bi-check-circle-fill d-none';
                    profileEl.className = '';
                    profileEl.onerror = function() {
                        icon.className = 'bi bi-check-circle-fill d-inline';
                        profileEl.className = 'd-none';
                    };
                    profileEl.src = SITE_URL + '/' + UPLOAD_DIR + 'profiles/' + profileImg;
                }
                nameEl.textContent = facultyName || 'Scan Successful';
                badgeEl.textContent = scanType === 'OUT' ? 'TIME OUT' : 'TIME IN';
                badgeEl.className = 'scan-type-badge ' + (scanType === 'OUT' ? 'out' : 'in');
                timeEl.textContent = time ? 'Scanned at ' + time : '';
            } else {
                wrap.className = 'checkmark-wrap error';
                icon.className = 'bi bi-exclamation-circle-fill';
                profileEl.className = 'd-none';
                nameEl.textContent = facultyName || 'Scan Failed';
                badgeEl.textContent = '';
                timeEl.textContent = '';
            }
            overlay.classList.add('show');
            setScanningBadge(false);
            clearTimeout(scanRestartTimer);

            if (type === 'success' && scanType === 'OUT') {
                activitySection.classList.remove('d-none');
                scanAgainBtn.classList.add('d-none');
                loadActivities();
            } else {
                activitySection.classList.add('d-none');
                scanAgainBtn.classList.remove('d-none');
                scanRestartTimer = setTimeout(dismissScanOverlay, 3500);
            }
        }

        function loadActivities() {
            fetch(SITE_URL + '/api/get_activities.php')
                .then(function(r) { return r.json(); })
                .then(function(resp) {
                    if (resp.success) {
                        var sel = document.getElementById('activitySelect');
                        sel.innerHTML = '<option value="">-- Select Activity --</option>';
                        resp.data.forEach(function(a) {
                            var opt = document.createElement('option');
                            opt.value = a.id;
                            opt.textContent = a.name;
                            sel.appendChild(opt);
                        });
                    }
                });
        }

        document.addEventListener('click', function(e) {
            if (e.target && e.target.id === 'submitTimeoutBtn') {
                var facultyId = scanFacultyId;
                var activityId = document.getElementById('activitySelect').value;
                var location = document.getElementById('timeoutLocation').value.trim();
                if (!activityId) { alert('Please select an activity.'); return; }
                var btn = e.target;
                btn.disabled = true;
                btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Processing...';
                fetch(SITE_URL + '/api/record_timeout.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'faculty_id=' + encodeURIComponent(facultyId) + '&activity_id=' + encodeURIComponent(activityId) + '&location=' + encodeURIComponent(location)
                })
                .then(function(r) { return r.json(); })
                .then(function(resp) {
                    if (resp.success) {
                        var badgeEl = document.getElementById('scanTypeBadge');
                        var timeEl = document.getElementById('scanTimestamp');
                        badgeEl.textContent = 'TIME OUT';
                        badgeEl.className = 'scan-type-badge out';
                        timeEl.textContent = 'Recorded at ' + resp.time + (resp.activity ? ' (' + resp.activity + ')' : '');
                        document.getElementById('timeoutActivitySection').classList.add('d-none');
                        document.getElementById('scanAgainBtn').classList.remove('d-none');
                        scanRestartTimer = setTimeout(dismissScanOverlay, 3000);
                    } else {
                        alert(resp.message || 'Failed to record time out.');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="bi bi-check-circle"></i> Record Time Out';
                    }
                })
                .catch(function() {
                    alert('Network error.');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-check-circle"></i> Record Time Out';
                });
            }
        });

        function dismissScanOverlay() {
            document.getElementById('scanOverlay').classList.remove('show');
            clearTimeout(scanRestartTimer);
            clearScanIdleTimer();
            setScanningBadge(true);
            const sel = document.getElementById('camera_select');
            if (sel && sel.options.length > 0 && sel.value && !isScanning) {
                setTimeout(startCamera, 300);
            }
        }

        // ── Frame capture & decode ──────────────────────────

        let frameCount = 0;
        const FRAME_SKIP = 3;

        function captureFrame() {
            try {
                if (!mediaStream || !scanCanvas || !scanCtx) {
                    animationId = requestAnimationFrame(captureFrame);
                    return;
                }

                const video = document.getElementById('qr-video');
                if (!video || video.readyState < 2 || !video.videoWidth || !video.videoHeight) {
                    animationId = requestAnimationFrame(captureFrame);
                    return;
                }

                scanCanvas.width = Math.min(video.videoWidth, 1280);
                scanCanvas.height = Math.min(video.videoHeight, 720);
                scanCtx.drawImage(video, 0, 0, scanCanvas.width, scanCanvas.height);

                frameCount++;

                if (frameCount % FRAME_SKIP !== 0) {
                    animationId = requestAnimationFrame(captureFrame);
                    return;
                }

                const imageData = scanCtx.getImageData(0, 0, scanCanvas.width, scanCanvas.height);

                if (frameCount % (FRAME_SKIP * 10) === 0) {
                    analyzeFrameQuality(imageData);
                }

                const code = jsQR(imageData.data, imageData.width, imageData.height);

                if (code && code.data) {
                    const now = Date.now();
                    if (now - lastScanTime >= SCAN_DEBOUNCE_MS) {
                        lastScanTime = now;
                        resetScanIdleTimer();
                        handleScanResult(code.data);
                        return;
                    }
                }
            } catch (e) {
                console.error('captureFrame error:', e);
            }

            animationId = requestAnimationFrame(captureFrame);
        }

        function handleScanResult(decodedText) {
            isScanning = true;
            stopCamera();

            const token = extractQRToken(decodedText);
            if (!token) {
                showScanOverlay('error', 'Invalid QR format');
                isScanning = false;
                return;
            }
            document.getElementById('qr_result').value = token;
            setCamStatus('Processing...', 'inactive');
            scanQRCode(token, function(resp) {
                isScanning = false;
                if (resp && resp.success) {
                    scanFacultyId = resp.faculty_id;
                    showScanOverlay('success', resp.faculty_name, resp.scan_type, resp.time, resp.profile_image);
                } else {
                    const msg = (resp && resp.message) || 'Scan failed';
                    showScanOverlay('error', msg);
                }
            }, true);
        }

        function submitManualToken() {
            const raw = document.getElementById('manual_token').value;
            const token = extractQRToken(raw);
            if (!token) { showError('Enter a valid QR token or scan URL.'); return; }
            document.getElementById('qr_result').value = token;
            setCamStatus('Processing...', 'inactive');
            scanQRCode(token, function(resp) {
                if (resp && resp.success) {
                    scanFacultyId = resp.faculty_id;
                    showScanOverlay('success', resp.faculty_name, resp.scan_type, resp.time, resp.profile_image);
                } else {
                    showScanOverlay('error', (resp && resp.message) || 'Scan failed');
                }
            }, true);
        }

        // ── Camera ──────────────────────────────────────────

        async function enumerateCameras() {
            try {
                const devices = await navigator.mediaDevices.enumerateDevices();
                const videoDevices = devices.filter(d => d.kind === 'videoinput');
                const sel = document.getElementById('camera_select');
                sel.innerHTML = '';
                videoDevices.forEach(d => {
                    const opt = document.createElement('option');
                    opt.value = d.deviceId;
                    opt.textContent = d.label || ('Camera ' + (sel.options.length + 1));
                    sel.appendChild(opt);
                });
                if (videoDevices.length > 0) {
                    if (videoDevices.length === 1) {
                        sel.style.display = 'none';
                    } else {
                        sel.style.display = '';
                    }
                    toggleScannerUI(false);
                    setCamStatus('Camera ready', 'inactive');
                    if (videoDevices.length === 1) {
                        for (let i = 0; i < sel.options.length; i++) {
                            if (sel.options[i].value === videoDevices[0].deviceId) {
                                sel.selectedIndex = i;
                                break;
                            }
                        }
                        setTimeout(startCamera, 400);
                    }
                } else {
                    setCamStatus('No camera found. Use manual token entry below.', 'error');
                }
            } catch (e) {
                console.error('Enumerate error:', e);
                setCamStatus('Camera access denied. Check browser permissions.', 'error');
            }
        }

        function startCamera() {
            const sel = document.getElementById('camera_select');
            const camId = sel.value;
            if (!camId) { showError('No camera selected'); return; }

            stopCamera();
            setCamStatus('Starting camera...', 'inactive');
            document.getElementById('loadingPlaceholder').style.display = 'block';

            navigator.mediaDevices.getUserMedia({
                video: { deviceId: { exact: camId }, width: { ideal: 1280 }, height: { ideal: 720 } }
            }).then(function(stream) {
                mediaStream = stream;
                const video = document.getElementById('qr-video');
                video.srcObject = stream;
                video.setAttribute('playsinline', '');
                video.play();

                document.getElementById('loadingPlaceholder').style.display = 'none';
                toggleScannerUI(true);
                setCamStatus('Scanner active — point at a QR code', 'active');

                scanCanvas = document.createElement('canvas');
                scanCtx = scanCanvas.getContext('2d');
                prevFrameData = null;
                resetScanIdleTimer();
                showQualityIndicators(true);
                setScanningBadge(true);
                animationId = requestAnimationFrame(captureFrame);
                console.log('QR scanner started successfully');
            }).catch(function(err) {
                const msg = err.message || err.toString();
                let userMsg = 'Camera failed to start.';
                if (msg.includes('permission') || msg.includes('NotAllowedError')) {
                    userMsg = 'Camera permission denied. Refresh and allow camera access.';
                } else if (msg.includes('NotFoundError')) {
                    userMsg = 'No camera available on this device.';
                }
                document.getElementById('loadingPlaceholder').style.display = 'none';
                setCamStatus(userMsg, 'error');
                showError(userMsg);
                toggleScannerUI(false);
            });
        }

        function stopCamera() {
            clearScanIdleTimer();
            showQualityIndicators(false);
            setScanningBadge(false);
            if (animationId) {
                cancelAnimationFrame(animationId);
                animationId = null;
            }
            if (mediaStream) {
                mediaStream.getTracks().forEach(function(t) { t.stop(); });
                mediaStream = null;
            }
            const video = document.getElementById('qr-video');
            if (video) video.srcObject = null;
            scanCanvas = null;
            scanCtx = null;
            toggleScannerUI(false);
            setCamStatus('Camera stopped', 'inactive');
        }

        // ── Fullscreen ──────────────────────────────────────

        function toggleFullscreen() {
            const container = document.getElementById('scannerContainer');
            const btn = document.getElementById('fullscreen_btn');
            if (!document.fullscreenElement) {
                container.requestFullscreen().catch(function() {});
                btn.classList.add('active');
            } else {
                document.exitFullscreen().catch(function() {});
                btn.classList.remove('active');
            }
        }

        function syncFsStatus() {
            const mainDot = document.getElementById('camDot');
            const mainText = document.getElementById('camStatusText');
            const fsDot = document.getElementById('fsCamDot');
            const fsText = document.getElementById('fsCamStatus');
            if (fsDot && fsText) {
                fsDot.className = 'dot ' + (mainDot ? mainDot.className.replace('dot ', '') : 'inactive');
                fsText.textContent = mainText ? mainText.textContent : '';
            }
        }

        document.addEventListener('fullscreenchange', function() {
            const btn = document.getElementById('fullscreen_btn');
            const topbar = document.getElementById('fsTopbar');
            const hint = document.getElementById('fsHint');
            if (document.fullscreenElement) {
                btn.classList.add('active');
                btn.querySelector('i').className = 'bi bi-fullscreen-exit';
                topbar.style.display = 'flex';
                hint.style.display = 'block';
                syncFsStatus();
            } else {
                btn.classList.remove('active');
                btn.querySelector('i').className = 'bi bi-arrows-fullscreen';
                topbar.style.display = 'none';
                hint.style.display = 'none';
            }
        });

        setInterval(function() {
            if (document.fullscreenElement) syncFsStatus();
        }, 500);

        // ── Init ────────────────────────────────────────────

        document.addEventListener('DOMContentLoaded', async function() {
            const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
            if (isMobile) document.getElementById('mobile-notice').style.display = 'block';

            document.getElementById('start_camera_btn').addEventListener('click', startCamera);
            document.getElementById('stop_camera_btn').addEventListener('click', stopCamera);
            document.getElementById('fullscreen_btn').addEventListener('click', toggleFullscreen);
            document.getElementById('fsExitBtn').addEventListener('click', toggleFullscreen);
            document.getElementById('tapScanBtn').addEventListener('click', doTapScan);
            document.getElementById('qr-reader').addEventListener('click', function(e) {
                if (e.target === this) doTapScan();
            });


            if (typeof jsQR === 'undefined') {
                try {
                    await new Promise(function(resolve, reject) {
                        const s = document.createElement('script');
                        s.src = SITE_URL + '/assets/libs/jsqr.min.js';
                        s.async = true;
                        s.onload = resolve;
                        s.onerror = reject;
                        document.head.appendChild(s);
                    });
                } catch (_) {
                    setCamStatus('QR library failed to load. Use manual entry.', 'error');
                    return;
                }
            }

            setCamStatus('Initializing...', 'inactive');
            await enumerateCameras();
            if (isMobile && document.getElementById('camera_select').options.length > 0) {
                document.getElementById('start_camera_btn').addEventListener('click', function startOnClick() {
                    this.removeEventListener('click', startOnClick);
                    startCamera();
                }, { once: true });
                startCamera();
            }
        });
    </script>
</body>
</html>
