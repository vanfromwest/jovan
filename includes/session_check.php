<?php
/**
 * Session Checker / Authentication Middleware
 * CCSICT Faculty Monitoring System
 */

require_once __DIR__ . '/../config/config.php';

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Check if user has required role
function checkRole($allowedRoles = []) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $userRole = $_SESSION['user_role'] ?? null;
    
    if (empty($allowedRoles)) {
        return true; // Any logged-in user
    }
    
    return in_array($userRole, $allowedRoles);
}

// Redirect to login if not authenticated
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/login.php');
        exit();
    }
}

// Redirect if specific role is required
function requireRole($roles = []) {
    requireLogin();
    
    if (!checkRole($roles)) {
        header('Location: ' . SITE_URL . '/index.php?error=Unauthorized Access');
        exit();
    }
}

// Get current user ID
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Get current user role
function getCurrentUserRole() {
    return $_SESSION['user_role'] ?? null;
}

// Get current user info
function getCurrentUserInfo() {
    global $conn;
    
    $userId = getCurrentUserId();
    if (!$userId) {
        return null;
    }
    
    if (!$conn) {
        require_once __DIR__ . '/../config/database.php';
    }
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Log activity
function logActivity($action, $details = '', $userId = null) {
    global $conn;
    
    if (!$userId && isLoggedIn()) {
        $userId = getCurrentUserId();
    }
    
    if (!$userId) {
        return false;
    }
    
    try {
        if (!$conn) {
            require_once __DIR__ . '/../config/database.php';
        }
        
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $stmt = $conn->prepare("
            INSERT INTO activity_logs (user_id, action, details, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        if ($stmt) {
            $stmt->bind_param("issss", $userId, $action, $details, $ip_address, $user_agent);
            return $stmt->execute();
        }
        return false;
    } catch (Throwable $e) {
        // Silently fail if activity_logs table doesn't exist or connection issues
        return false;
    }
}

// Log out user
function logout() {
    $userId = getCurrentUserId();
    
    if ($userId) {
        logActivity('LOGOUT', 'User logged out');
    }
    
    $_SESSION = [];
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    
    session_destroy();
}

?>
