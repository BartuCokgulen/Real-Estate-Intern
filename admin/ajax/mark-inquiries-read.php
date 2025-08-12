<?php
require_once '../../config.php';
require_once '../../includes/functions.php';

// Require admin access
require_admin();

header('Content-Type: application/json');

try {
    $query = "UPDATE property_inquiries SET status = 'read' WHERE status = 'new'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    echo json_encode(['success' => true]);
} catch(PDOException $e) {
    error_log("Error in mark-inquiries-read.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while marking messages as read.'
    ]);
} 