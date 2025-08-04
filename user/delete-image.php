<?php
require_once '../config.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    $_SESSION['error'] = "Please login to delete images.";
    header('Location: ../login.php');
    exit;
}

// Check if image ID and property ID are provided
if (!isset($_GET['id']) || !isset($_GET['property_id'])) {
    $_SESSION['error'] = "Image ID and Property ID are required.";
    header('Location: properties.php');
    exit;
}

$image_id = (int)$_GET['id'];
$property_id = (int)$_GET['property_id'];

try {
    // First verify that the property belongs to the user
    $stmt = $conn->prepare("SELECT id FROM properties WHERE id = ? AND user_id = ?");
    $stmt->execute([$property_id, $_SESSION['user_id']]);
    if (!$stmt->fetch()) {
        $_SESSION['error'] = "You don't have permission to delete this image.";
        header('Location: properties.php');
        exit;
    }

    // Get image details
    $stmt = $conn->prepare("SELECT image_url FROM property_images WHERE id = ? AND property_id = ?");
    $stmt->execute([$image_id, $property_id]);
    $image = $stmt->fetch();

    if ($image) {
        // Delete the physical file
        $file_path = '../' . $image['image_url'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        // Delete from database
        $stmt = $conn->prepare("DELETE FROM property_images WHERE id = ?");
        $stmt->execute([$image_id]);

        $_SESSION['success'] = "Image deleted successfully.";
    } else {
        $_SESSION['error'] = "Image not found.";
    }
} catch(PDOException $e) {
    $_SESSION['error'] = "Error deleting image: " . $e->getMessage();
}

header('Location: edit-property.php?id=' . $property_id);
exit; 