<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/session_check.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$loginError = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $rememberMe = isset($_POST['remember_me']);

    if (empty($email) || empty($password)) {
        $loginError = 'Email and password are required';
    } else {
        // Prepare statement to prevent SQL injection
        $stmt = $conn->prepare("
            SELECT id, fullname, email, password, role, status
            FROM users
            WHERE email = ? AND is_active = 1
        ");
        
        if (!$stmt) {
            $loginError = 'Database error: ' . $conn->error;
        } else {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user && verifyPassword($password, $user['password'])) {
                // Check if account is approved
                if ($user['status'] !== 'APPROVED') {
                    $loginError = 'Your account is not approved yet. Please wait for admin approval.';
                } else {
                    // Successful login
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['fullname'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];
                    
                    // Update last login time
                    $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                    $updateStmt->bind_param("i", $user['id']);
                    $updateStmt->execute();
                    
                    // Log activity
                    logActivity('LOGIN', 'User logged in', $user['id']);
                    
                    // Redirect based on role
                    if ($user['role'] === 'Admin') {
                        header('Location: admin/dashboard.php');
                    } elseif ($user['role'] === 'Faculty') {
                        header('Location: faculty/dashboard.php');
                    } else {
                        header('Location: student/dashboard.php');
                    }
                    exit();
                }
            } else {
                $loginError = 'Invalid email or password';
            }
            $stmt->close();
        }
    }
}

$pageTitle = 'Login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
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

        <?php if (!empty($loginError)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle-fill"></i> <?php echo htmlspecialchars($loginError); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
                <label class="form-check-label" for="remember_me">
                    Remember me
                </label>
            </div>

            <button type="submit" class="btn btn-primary w-100 mb-3">
                <i class="bi bi-box-arrow-in-right"></i> Login
            </button>
        </form>

        <hr>

        <div class="text-center">
            <p class="mb-2">Don't have an account?</p>
            <a href="register.php" class="btn btn-outline-primary w-100">
                <i class="bi bi-person-plus"></i> Register Here
            </a>
        </div>

        <div class="text-center mt-3">
            <a href="forgot_password.php" class="small text-muted">Forgot Password?</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
