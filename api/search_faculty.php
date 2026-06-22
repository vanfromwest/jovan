<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

try {
    $query = trim($_GET['q'] ?? '');
    $type = trim($_GET['type'] ?? '');
    $departmentId = isset($_GET['department_id']) ? intval($_GET['department_id']) : null;

    if (strlen($query) < 1) {
        jsonResponse(false, 'Search query is required');
    }

    $validTypes = ['name', 'department', 'position', 'status', 'activity', 'location', ''];
    if (!in_array($type, $validTypes)) {
        $type = '';
    }

    $results = searchFaculty($query, $type ?: null, $departmentId);

    jsonResponse(true, 'Search completed', [
        'results' => $results,
        'count' => count($results),
        'query' => $query,
        'type' => $type
    ]);

} catch (Exception $e) {
    jsonResponse(false, 'Search error: ' . $e->getMessage());
}

$conn->close();
?>
