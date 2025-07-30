<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to add favorites']);
    exit();
}

if (!isset($_POST['property_id']) || !is_numeric($_POST['property_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid property ID']);
    exit();
}

$user_id = $_SESSION['user_id'];
$property_id = $_POST['property_id'];

try {
    // Check if property exists
    $check_property = $conn->prepare("SELECT id FROM properties WHERE id = ?");
    $check_property->execute([$property_id]);
    if (!$check_property->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Property not found']);
        exit();
    }

    // Check if already in favorites
    $check_favorite = $conn->prepare("SELECT id FROM favorites WHERE user_id = ? AND property_id = ?");
    $check_favorite->execute([$user_id, $property_id]);
    $existing_favorite = $check_favorite->fetch();

    if ($existing_favorite) {
        // Remove from favorites
        $delete = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND property_id = ?");
        $delete->execute([$user_id, $property_id]);
        echo json_encode(['success' => true, 'action' => 'removed', 'message' => 'Removed from favorites']);
    } else {
        // Add to favorites
        $insert = $conn->prepare("INSERT INTO favorites (user_id, property_id) VALUES (?, ?)");
        $insert->execute([$user_id, $property_id]);
        echo json_encode(['success' => true, 'action' => 'added', 'message' => 'Added to favorites']);
    }
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
} 