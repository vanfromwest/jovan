<?php
require 'config/config.php';
require 'config/database.php';

$email = 'facultyjovan@ccsict.com';
$stmt = $conn->prepare('SELECT u.id, u.fullname, f.id as faculty_id, f.qr_token, qc.qr_path FROM users u LEFT JOIN faculty f ON u.id = f.user_id LEFT JOIN qr_codes qc ON f.id = qc.faculty_id WHERE u.email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo 'Email: ' . $email . "\n";
    echo 'Name: ' . $row['fullname'] . "\n";
    echo 'Faculty ID: ' . $row['faculty_id'] . "\n";
    echo 'QR Token: ' . ($row['qr_token'] ?: 'Not generated yet') . "\n";
    echo 'QR File: ' . ($row['qr_path'] ?: 'No file') . "\n";
} else {
    echo 'User not found' . "\n";
}
$stmt->close();
?>
