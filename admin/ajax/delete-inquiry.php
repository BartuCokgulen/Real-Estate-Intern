<?php
require_once '../../config.php';
require_once '../../includes/functions.php';

// Require admin access
require_admin();

header('Content-Type: application/json');

if (!isset($_POST['inquiry_id']) || !is_numeric($_POST['inquiry_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid inquiry ID.'
    ]);
    exit;
}

try {
    $query = "DELETE FROM property_inquiries WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$_POST['inquiry_id']]);
    
    echo json_encode(['success' => true]);
} catch(PDOException $e) {
    error_log("Error in delete-inquiry.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while deleting the inquiry.'
    ]);
} 