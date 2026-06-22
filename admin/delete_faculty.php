<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/session_check.php';
require_once '../includes/functions.php';

requireRole(['Admin']);

$facultyId = intval($_GET['id'] ?? 0);

if ($facultyId <= 0) {
    header('Location: faculty_management.php?error=Invalid faculty ID');
    exit();
}

// Get faculty details
$faculty = getFacultyWithUser($facultyId);

if (!$faculty) {
    header('Location: faculty_management.php?error=Faculty not found');
    exit();
}

// Delete the faculty member
if (deleteFacultyMember($facultyId)) {
    logActivity('FACULTY_DELETE', 'Deleted faculty: ' . $faculty['fullname'], getCurrentUserId());
    header('Location: faculty_management.php?deleted=1');
    exit();
} else {
    header('Location: faculty_management.php?error=Failed to delete faculty');
    exit();
}
?>
