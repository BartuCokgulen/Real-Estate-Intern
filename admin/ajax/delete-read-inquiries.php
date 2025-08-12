<?php
require_once '../../config.php';
require_once '../../includes/functions.php';

// Require admin access
require_admin();

header('Content-Type: application/json');

try {
    $query = "DELETE FROM property_inquiries WHERE status = 'read'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    echo json_encode(['success' => true]);
} catch(PDOException $e) {
    error_log("Error in delete-read-inquiries.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while deleting read messages.'
    ]);
} 