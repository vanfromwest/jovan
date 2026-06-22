<?php
/**
 * API: Get Faculty Status
 * Returns real-time status of all faculty members
 */

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

try {
    // Get all approved faculty with their status
    $stmt = $conn->prepare("
        SELECT 
            f.id,
            u.id as user_id,
            u.fullname,
            u.profile_image,
            fs.status,
            fs.activity,
            fs.location,
            fs.travel_from,
            fs.travel_to,
            fs.travel_days,
            fs.updated_at
        FROM faculty f
        JOIN users u ON f.user_id = u.id
        LEFT JOIN faculty_status fs ON f.id = fs.faculty_id
        WHERE u.status = 'APPROVED'
        ORDER BY u.fullname ASC
    ");
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $facultyData = [];
    while ($row = $result->fetch_assoc()) {
        $facultyData[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $facultyData
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching faculty status: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
