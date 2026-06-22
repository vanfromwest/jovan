<?php
/**
 * Helper Functions
 * CCSICT Faculty Monitoring System
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';

// ============================================
// File Upload Functions
// ============================================

function uploadProfileImage($file, $userId) {
    if (!isset($file) || $file['error'] == UPLOAD_ERR_NO_FILE) {
        return null;
    }
    
    // Validate file
    if ($file['error'] != UPLOAD_ERR_OK) {
        return false;
    }
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS)) {
        return false;
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return false;
    }
    
    // Create upload directory if not exists
    $uploadDir = UPLOAD_DIR . 'profiles/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate unique filename
    $filename = 'profile_' . $userId . '_' . time() . '.' . $ext;
    $filepath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return $filename;
    }
    
    return false;
}

function deleteProfileImage($filename) {
    if (empty($filename)) {
        return true;
    }
    
    $filepath = UPLOAD_DIR . 'profiles/' . $filename;
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    
    return true;
}

// ============================================
// QR Code Functions
// ============================================

function generateQRCode($data, $size = 200, $filename = null) {
    global $conn;
    
    if (!file_exists(QRCODE_DIR)) {
        mkdir(QRCODE_DIR, 0755, true);
    }
    
    if (!$filename) {
        $filename = uniqid('qr_', true) . '.png';
    }
    
    $filepath = QRCODE_DIR . $filename;
    
    // Use built-in PHP QR code generation (simple implementation)
    $qrData = $data;
    $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=' . $size . 'x' . $size . '&data=' . urlencode($qrData);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $qrUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
    
    $qrContent = curl_exec($ch);
    curl_close($ch);
    
    if (!$qrContent) {
        return false;
    }
    
    if (file_put_contents($filepath, $qrContent)) {
        return $filename;
    }
    
    return false;
}

function generateQRToken() {
    return bin2hex(random_bytes(50));
}

// ============================================
// Faculty Functions
// ============================================

function getFacultyId($userId) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT id FROM faculty WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row ? $row['id'] : null;
}

function getFacultyByQRToken($qrToken) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT f.id, f.user_id, f.qr_token, u.fullname, u.profile_image
        FROM faculty f
        JOIN users u ON f.user_id = u.id
        WHERE f.qr_token = ?
    ");
    $stmt->bind_param("s", $qrToken);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_assoc();
}

function getFacultyQRCode($facultyId) {
    global $conn;

    $stmt = $conn->prepare("SELECT qr_token, qr_path FROM qr_codes WHERE faculty_id = ? LIMIT 1");
    $stmt->bind_param("i", $facultyId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getFacultyStatus($facultyId) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT * FROM faculty_status WHERE faculty_id = ?
    ");
    $stmt->bind_param("i", $facultyId);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_assoc();
}

function updateFacultyStatus($facultyId, $status, $activity = null, $location = null, $travelFrom = null, $travelTo = null, $travelDays = null) {
    global $conn;
    
    // Check if status record exists
    $checkStmt = $conn->prepare("SELECT id FROM faculty_status WHERE faculty_id = ?");
    $checkStmt->bind_param("i", $facultyId);
    $checkStmt->execute();
    $exists = $checkStmt->get_result()->fetch_assoc();
    
    if ($exists) {
        // Update existing status
        $stmt = $conn->prepare("
            UPDATE faculty_status 
            SET status = ?, activity = ?, location = ?, travel_from = ?, travel_to = ?, travel_days = ?, updated_at = NOW()
            WHERE faculty_id = ?
        ");
        $stmt->bind_param("sssssii", $status, $activity, $location, $travelFrom, $travelTo, $travelDays, $facultyId);
    } else {
        // Insert new status
        $stmt = $conn->prepare("
            INSERT INTO faculty_status (faculty_id, status, activity, location, travel_from, travel_to, travel_days)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("isssssi", $facultyId, $status, $activity, $location, $travelFrom, $travelTo, $travelDays);
    }
    
    return $stmt->execute();
}

function getAllFaculty($departmentId = null) {
    global $conn;
    
    if ($departmentId) {
        $stmt = $conn->prepare("
            SELECT u.*, f.id as faculty_id, f.qr_token, f.position
            FROM users u
            JOIN faculty f ON u.id = f.user_id
            WHERE u.role = 'Faculty' AND u.department_id = ? AND u.status = 'APPROVED'
            ORDER BY u.fullname ASC
        ");
        $stmt->bind_param("i", $departmentId);
    } else {
        $stmt = $conn->prepare("
            SELECT u.*, f.id as faculty_id, f.qr_token, f.position
            FROM users u
            JOIN faculty f ON u.id = f.user_id
            WHERE u.role = 'Faculty' AND u.status = 'APPROVED'
            ORDER BY u.fullname ASC
        ");
    }
    
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function searchFaculty($query, $type = null, $departmentId = null) {
    global $conn;
    
    $search = '%' . $query . '%';
    $exactSearch = $query . '%';
    
    $sql = "
        SELECT 
            f.id as faculty_id,
            u.id as user_id,
            u.fullname,
            u.email,
            u.profile_image,
            f.position,
            u.department_id,
            d.name as department,
            fs.status,
            fs.activity,
            fs.location,
            fs.travel_from,
            fs.travel_to,
            fs.travel_days,
            fs.updated_at
        FROM users u
        JOIN faculty f ON u.id = f.user_id
        LEFT JOIN departments d ON u.department_id = d.id
        LEFT JOIN faculty_status fs ON f.id = fs.faculty_id
        WHERE u.role = 'Faculty' AND u.status = 'APPROVED'
    ";
    
    $params = [];
    $types = '';
    
    if ($departmentId) {
        $sql .= " AND u.department_id = ?";
        $params[] = $departmentId;
        $types .= 'i';
    }
    
    if ($type === 'name') {
        $sql .= " AND u.fullname LIKE ?";
        $params[] = $search;
        $types .= 's';
    } elseif ($type === 'department') {
        $sql .= " AND d.name LIKE ?";
        $params[] = $search;
        $types .= 's';
    } elseif ($type === 'position') {
        $sql .= " AND f.position LIKE ?";
        $params[] = $search;
        $types .= 's';
    } elseif ($type === 'status') {
        $sql .= " AND fs.status LIKE ?";
        $params[] = $search;
        $types .= 's';
    } elseif ($type === 'activity') {
        $sql .= " AND fs.activity LIKE ?";
        $params[] = $search;
        $types .= 's';
    } elseif ($type === 'location') {
        $sql .= " AND fs.location LIKE ?";
        $params[] = $search;
        $types .= 's';
    } else {
        $sql .= " AND (
            u.fullname LIKE ? OR
            d.name LIKE ? OR
            f.position LIKE ? OR
            fs.status LIKE ? OR
            fs.activity LIKE ? OR
            fs.location LIKE ?
        )";
        array_push($params, $search, $search, $search, $search, $search, $search);
        $types .= 'ssssss';
    }
    
    $sql .= " ORDER BY 
        CASE WHEN u.fullname LIKE ? THEN 0 ELSE 1 END,
        u.fullname ASC
        LIMIT 50";
    
    $params[] = $exactSearch;
    $types .= 's';
    
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    foreach ($results as &$row) {
        $row['status'] = $row['status'] ?? 'OUT';
        $row['department'] = $row['department'] ?? 'N/A';
    }
    
    return $results;
}

function getFacultyWithUser($facultyId) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT u.*, f.id as faculty_id, f.position, f.qr_token
        FROM users u
        JOIN faculty f ON u.id = f.user_id
        WHERE f.id = ?
    ");
    $stmt->bind_param("i", $facultyId);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_assoc();
}

function updateFacultyInfo($facultyId, $fullname, $position, $departmentId, $email) {
    global $conn;
    
    // Get user_id from faculty
    $stmt = $conn->prepare("SELECT user_id FROM faculty WHERE id = ?");
    $stmt->bind_param("i", $facultyId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if (!$result) {
        return false;
    }
    
    $userId = $result['user_id'];
    
    // Update user info
    $userStmt = $conn->prepare("
        UPDATE users
        SET fullname = ?, email = ?, department_id = ?
        WHERE id = ?
    ");
    $userStmt->bind_param("ssii", $fullname, $email, $departmentId, $userId);
    $userResult = $userStmt->execute();
    
    // Update faculty position
    $facultyStmt = $conn->prepare("
        UPDATE faculty
        SET position = ?
        WHERE id = ?
    ");
    $facultyStmt->bind_param("si", $position, $facultyId);
    $facultyResult = $facultyStmt->execute();
    
    return $userResult && $facultyResult;
}

function deleteFacultyMember($facultyId) {
    global $conn;
    
    // Get user_id from faculty
    $stmt = $conn->prepare("SELECT user_id FROM faculty WHERE id = ?");
    $stmt->bind_param("i", $facultyId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if (!$result) {
        return false;
    }
    
    $userId = $result['user_id'];
    
    // Delete faculty record
    $facultyStmt = $conn->prepare("DELETE FROM faculty WHERE id = ?");
    $facultyStmt->bind_param("i", $facultyId);
    $facultyResult = $facultyStmt->execute();
    
    // Delete user record
    $userStmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $userStmt->bind_param("i", $userId);
    $userResult = $userStmt->execute();
    
    return $facultyResult && $userResult;
}

// ============================================
// Attendance Functions
// ============================================

function getOrCreateAttendanceRecord($facultyId, $date) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT * FROM attendance WHERE faculty_id = ? AND scan_date = ?
    ");
    $stmt->bind_param("is", $facultyId, $date);
    $stmt->execute();
    
    $result = $stmt->get_result()->fetch_assoc();
    
    if (!$result) {
        $insertStmt = $conn->prepare("
            INSERT INTO attendance (faculty_id, scan_date)
            VALUES (?, ?)
        ");
        $insertStmt->bind_param("is", $facultyId, $date);
        $insertStmt->execute();
        
        return getOrCreateAttendanceRecord($facultyId, $date);
    }
    
    return $result;
}

function recordTimeIn($facultyId, $date, $time) {
    global $conn;
    
    $record = getOrCreateAttendanceRecord($facultyId, $date);
    
    $stmt = $conn->prepare("
        UPDATE attendance 
        SET time_in = ?, updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->bind_param("si", $time, $record['id']);
    
    return $stmt->execute();
}

function recordTimeOut($facultyId, $date, $time, $activity, $location) {
    global $conn;
    
    $record = getOrCreateAttendanceRecord($facultyId, $date);
    
    $stmt = $conn->prepare("
        UPDATE attendance 
        SET time_out = ?, activity_out = ?, location_out = ?, updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->bind_param("sssi", $time, $activity, $location, $record['id']);
    
    return $stmt->execute();
}

function getFacultyAttendance($facultyId, $startDate = null, $endDate = null) {
    global $conn;
    
    if ($startDate && $endDate) {
        $stmt = $conn->prepare("
            SELECT * FROM attendance 
            WHERE faculty_id = ? AND scan_date BETWEEN ? AND ?
            ORDER BY scan_date DESC
        ");
        $stmt->bind_param("iss", $facultyId, $startDate, $endDate);
    } else {
        $stmt = $conn->prepare("
            SELECT * FROM attendance 
            WHERE faculty_id = ?
            ORDER BY scan_date DESC
            LIMIT 30
        ");
        $stmt->bind_param("i", $facultyId);
    }
    
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// ============================================
// User Management Functions
// ============================================

function getPendingUsers() {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT * FROM users 
        WHERE status = 'PENDING'
        ORDER BY created_at DESC
    ");
    $stmt->execute();
    
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function approveUser($userId) {
    global $conn;
    
    $status = 'APPROVED';
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $userId);
    
    $result = $stmt->execute();
    
    if ($result) {
        logActivity('USER_APPROVED', 'User ID: ' . $userId, getCurrentUserId());
    }
    
    return $result;
}

function rejectUser($userId) {
    global $conn;
    
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    
    $result = $stmt->execute();
    
    if ($result) {
        logActivity('USER_REJECTED', 'User ID: ' . $userId . ' deleted', getCurrentUserId());
    }
    
    return $result;
}

function getAllUsers($role = null, $status = null) {
    global $conn;
    
    $query = "SELECT * FROM users WHERE 1=1";
    $types = "";
    $params = [];
    
    if ($role) {
        $query .= " AND role = ?";
        $types .= "s";
        $params[] = $role;
    }
    
    if ($status) {
        $query .= " AND status = ?";
        $types .= "s";
        $params[] = $status;
    }
    
    $query .= " ORDER BY created_at DESC";
    
    $stmt = $conn->prepare($query);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// ============================================
// User CRUD Functions
// ============================================

function getUserById($userId) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_assoc();
}

function updateUser($userId, $fullname, $email, $contactNumber = null, $departmentId = null) {
    global $conn;
    
    $stmt = $conn->prepare("
        UPDATE users 
        SET fullname = ?, email = ?, contact_number = ?, department_id = ?, updated_at = CURRENT_TIMESTAMP
        WHERE id = ?
    ");
    
    $stmt->bind_param("sssii", $fullname, $email, $contactNumber, $departmentId, $userId);
    
    return $stmt->execute();
}

function deleteUser($userId) {
    global $conn;
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Get the user first to check their role
        $user = getUserById($userId);
        if (!$user) {
            $conn->rollback();
            return false;
        }
        
        // If faculty, delete related records
        if ($user['role'] === 'Faculty') {
            // Delete QR codes
            $stmt = $conn->prepare("DELETE FROM qr_codes WHERE faculty_id IN (SELECT id FROM faculty WHERE user_id = ?)");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            
            // Delete faculty status
            $stmt = $conn->prepare("DELETE FROM faculty_status WHERE faculty_id IN (SELECT id FROM faculty WHERE user_id = ?)");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            
            // Delete attendance records
            $stmt = $conn->prepare("DELETE FROM attendance WHERE faculty_id IN (SELECT id FROM faculty WHERE user_id = ?)");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            
            // Delete faculty record (cascades handled by database)
            $stmt = $conn->prepare("DELETE FROM faculty WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
        }
        
        // Delete profile image if exists
        if ($user['profile_image']) {
            deleteProfileImage($user['profile_image']);
        }
        
        // Delete user record
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $success = $stmt->execute();
        
        if ($success) {
            $conn->commit();
            return true;
        } else {
            $conn->rollback();
            return false;
        }
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

function getUsersByRole($role) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE role = ? ORDER BY fullname ASC");
    $stmt->bind_param("s", $role);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// ============================================
// Announcement Functions
// ============================================

function getAnnouncements($limit = 10, $todayOnly = false) {
    global $conn;
    
    autoExpireAnnouncements();
    
    $sql = "
        SELECT a.*, u.fullname, u.profile_image
        FROM announcements a
        JOIN users u ON a.created_by = u.id
        WHERE a.is_active = 1
        AND (a.expiration_date IS NULL OR a.expiration_date >= NOW())
    ";
    if ($todayOnly) {
        $sql .= " AND DATE(a.created_at) = CURDATE()";
    }
    $sql .= " ORDER BY a.is_pinned DESC, a.created_at DESC LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getPinnedAnnouncements($limit = 5, $todayOnly = false) {
    global $conn;
    
    autoExpireAnnouncements();
    
    $sql = "
        SELECT a.*, u.fullname, u.profile_image
        FROM announcements a
        JOIN users u ON a.created_by = u.id
        WHERE a.is_active = 1 AND a.is_pinned = 1
        AND (a.expiration_date IS NULL OR a.expiration_date >= NOW())
    ";
    if ($todayOnly) {
        $sql .= " AND DATE(a.created_at) = CURDATE()";
    }
    $sql .= " ORDER BY a.created_at DESC LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function createAnnouncement($title, $content, $userId, $isPinned = 0, $priority = 'MEDIUM', $expirationDate = null) {
    global $conn;
    
    $userStmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $userStmt->bind_param("i", $userId);
    $userStmt->execute();
    $user = $userStmt->get_result()->fetch_assoc();
    $isPinned = ($user && $user['role'] === 'Admin') ? 1 : $isPinned;
    
    $stmt = $conn->prepare("
        INSERT INTO announcements (title, content, created_by, is_pinned, priority, expiration_date)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("ssiiss", $title, $content, $userId, $isPinned, $priority, $expirationDate);
    
    return $stmt->execute();
}

function updateAnnouncement($id, $title, $content, $isPinned = null, $priority = null, $expirationDate = null) {
    global $conn;
    
    $updates = ["title = ?", "content = ?", "updated_at = NOW()"];
    $params = [$title, $content];
    $types = "ss";
    
    if ($isPinned !== null) {
        $updates[] = "is_pinned = ?";
        $params[] = $isPinned;
        $types .= "i";
    }
    if ($priority !== null) {
        $updates[] = "priority = ?";
        $params[] = $priority;
        $types .= "s";
    }
    if ($expirationDate !== null) {
        $updates[] = "expiration_date = ?";
        $params[] = $expirationDate;
        $types .= "s";
    }
    
    $params[] = $id;
    $types .= "i";
    
    $stmt = $conn->prepare("
        UPDATE announcements 
        SET " . implode(", ", $updates) . "
        WHERE id = ?
    ");
    $stmt->bind_param($types, ...$params);
    
    return $stmt->execute();
}

function deleteAnnouncement($id) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE announcements SET is_active = 0 WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    return $stmt->execute();
}

function autoExpireAnnouncements() {
    global $conn;
    
    $stmt = $conn->prepare("
        UPDATE announcements 
        SET is_active = 0 
        WHERE is_active = 1 
        AND expiration_date IS NOT NULL 
        AND expiration_date < NOW()
    ");
    
    return $stmt->execute();
}

// ============================================
// Input Validation
// ============================================

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePassword($password) {
    return strlen($password) >= 6;
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// ============================================
// JSON Response Helper
// ============================================

function jsonResponse($success, $message = '', $data = []) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

// ============================================
// Date/Time Formatting
// ============================================

function formatDateTime($datetime) {
    return date('M d, Y h:i A', strtotime($datetime));
}

function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

function formatTime($time) {
    return date('h:i A', strtotime($time));
}

// ============================================
// Department Functions
// ============================================

function getAllDepartments() {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM departments ORDER BY name ASC");
    $stmt->execute();
    
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getDepartmentName($departmentId) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT name FROM departments WHERE id = ?");
    $stmt->bind_param("i", $departmentId);
    $stmt->execute();
    
    $result = $stmt->get_result()->fetch_assoc();
    return $result ? $result['name'] : 'N/A';
}

// ============================================
// Activity Functions
// ============================================

function getAllActivities() {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM activities ORDER BY name ASC");
    $stmt->execute();
    
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// ============================================
// Text Utility Functions
// ============================================

function truncateText($text, $length = 100) {
    if (!$text || strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

?>
