<?php

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/session_check.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    autoExpireAnnouncements();
    
    $type = $_GET['type'] ?? 'all';

    if ($type === 'pinned') {
        $announcements = getPinnedAnnouncements(10, true);
    } else {
        $announcements = getAnnouncements(10, true);
    }

    echo json_encode([
        'success' => true,
        'count' => count($announcements),
        'announcements' => $announcements
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>