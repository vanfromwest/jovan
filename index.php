<?php
require_once 'config/config.php';
require_once 'includes/session_check.php';
require_once 'includes/functions.php';

$pageTitle = 'Home';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #2d5016 0%, #1a3a0a 100%);
            color: white;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .hero-section {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 60px 20px;
        }

        .hero-content h1 {
            font-size: 52px;
            font-weight: bold;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .hero-content p {
            font-size: 20px;
            margin-bottom: 30px;
            opacity: 0.9;
        }

        .hero-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-hero {
            padding: 12px 30px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s ease;
        }

        .btn-hero.primary {
            background-color: #ffd700;
            color: #2d5016;
            border: none;
        }

        .btn-hero.primary:hover {
            background-color: #ffcc00;
            color: #2d5016;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(255, 215, 0, 0.3);
        }

        .btn-hero.secondary {
            background-color: transparent;
            color: white;
            border: 2px solid #ffd700;
        }

        .btn-hero.secondary:hover {
            background-color: #ffd700;
            color: #2d5016;
            transform: translateY(-3px);
        }

        .features {
            background: rgba(255, 255, 255, 0.1);
            padding: 60px 20px;
            backdrop-filter: blur(10px);
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.95);
            color: #2d5016;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 20px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-10px);
        }

        .feature-icon {
            font-size: 48px;
            color: #ffd700;
            margin-bottom: 15px;
        }

        .feature-card h5 {
            font-weight: bold;
            margin-bottom: 10px;
        }

        .navbar {
            background: rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            border-bottom: 2px solid #ffd700;
        }

        .navbar-brand {
            font-size: 24px;
            font-weight: bold;
            color: #ffd700 !important;
        }

        .nav-link {
            color: white !important;
            margin: 0 10px;
            transition: color 0.3s ease;
        }

        .nav-link:hover {
            color: #ffd700 !important;
        }

        body > footer {
            background-color: rgba(0, 0, 0, 0.4);
            border-top: 2px solid #ffd700;
            margin-left: 0;
            width: 100%;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-building"></i> CCSICT
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="navbar-nav ms-auto">
                    <?php if (isLoggedIn()): ?>
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                        <a class="nav-link" href="logout.php">Logout</a>
                    <?php else: ?>
                        <a class="nav-link" href="login.php">Login</a>
                        <a class="nav-link" href="register.php">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="hero-content">
            <h1><i class="bi bi-buildings"></i> CCSICT Faculty Monitoring</h1>
            <p>Real-time Faculty Presence & Attendance Tracking System</p>
            <p class="text-muted-light">Monitor faculty availability instantly with QR code scanning and live status updates</p>
            
            <div class="hero-buttons">
                <?php if (isLoggedIn()): ?>
                    <a href="<?php echo SITE_URL; ?>/monitor/live_display.php" class="btn-hero primary">
                        <i class="bi bi-tv"></i> Live Monitor
                    </a>
                    <a href="<?php echo SITE_URL; ?>/dashboard.php" class="btn-hero secondary">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn-hero primary">
                        <i class="bi bi-box-arrow-in-right"></i> Login
                    </a>
                    <a href="register.php" class="btn-hero secondary">
                        <i class="bi bi-person-plus"></i> Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="features">
        <div class="container">
            <h2 class="text-center mb-5">Key Features</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="bi bi-qr-code"></i></div>
                        <h5>QR Code Scanning</h5>
                        <p>Automatic attendance tracking with unique QR codes for each faculty member</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="bi bi-clock-history"></i></div>
                        <h5>Real-Time Status</h5>
                        <p>Live monitoring of faculty presence and current activities with instant updates</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="bi bi-tv"></i></div>
                        <h5>Live Display</h5>
                        <p>Display faculty status on monitors outside offices for students and visitors</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="mt-auto text-center py-4">
        <div class="container">
            <p class="mb-0">&copy; 2026 CCSICT Faculty Monitoring System. All Rights Reserved.</p>
            <small>Developed for academic excellence and transparency</small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
