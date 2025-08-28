<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: ../login.php');
    exit();
}

// Check if property_id is provided
if (!isset($_POST['property_id'])) {
    $_SESSION['error'] = "Invalid request.";
    header('Location: favorites.php');
    exit();
}

$property_id = (int)$_POST['property_id'];

try {
    // Check if the favorite exists
    $stmt = $conn->prepare("SELECT * FROM favorites WHERE user_id = ? AND property_id = ?");
    $stmt->execute([$_SESSION['user_id'], $property_id]);
    
    if ($stmt->rowCount() > 0) {
        // Remove from favorites
        $stmt = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND property_id = ?");
        $stmt->execute([$_SESSION['user_id'], $property_id]);
        
        $_SESSION['success'] = "Property removed from favorites.";
    } else {
        $_SESSION['error'] = "Property not found in favorites.";
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Error removing property from favorites.";
}

header('Location: favorites.php');
exit();