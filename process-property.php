<?php
require_once 'includes/functions.php';
require_once 'config.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: add-property.php');
    exit;
}

$upload_dir = 'uploads/properties/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

try {
    $conn->beginTransaction();

    // Validate required fields
    $required_fields = ['title', 'description', 'price', 'location', 'type'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Please fill in all required fields.");
        }
    }

    // Insert property data
    $query = "INSERT INTO properties (
        user_id, title, description, price, location, type,
        bedrooms, bathrooms, area, status, created_at
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW()
    )";

    // Debug user ID
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("User session not found. Please log in again.");
    }

    $stmt = $conn->prepare($query);
    $stmt->execute([
        $_SESSION['user_id'],
        $_POST['title'],
        $_POST['description'],
        $_POST['price'],
        $_POST['location'],
        $_POST['type'],
        $_POST['bedrooms'] ?: null,
        $_POST['bathrooms'] ?: null,
        $_POST['area'] ?: null
    ]);

    $property_id = $conn->lastInsertId();

    // Validate image upload
    if (empty($_FILES['property_images']) || empty($_FILES['property_images']['name'][0])) {
        throw new Exception("Please upload at least one image.");
    }

    // Handle image uploads
    $images = $_FILES['property_images'];
    $total_images = count($images['name']);
    $uploaded_images = 0;
    
    for ($i = 0; $i < $total_images; $i++) {
        if ($images['error'][$i] === UPLOAD_ERR_OK) {
            $tmp_name = $images['tmp_name'][$i];
            $name = $images['name'][$i];
            
            // Validate image type
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_type = mime_content_type($tmp_name);
            if (!in_array($file_type, $allowed_types)) {
                throw new Exception("Invalid file type. Only JPG, PNG and GIF are allowed.");
            }
            
            // Generate unique filename
            $extension = pathinfo($name, PATHINFO_EXTENSION);
            $unique_filename = uniqid() . '_' . $property_id . '.' . $extension;
            $target_path = $upload_dir . $unique_filename;
            
            // Move uploaded file
            if (move_uploaded_file($tmp_name, $target_path)) {
                // Insert image record
                $image_query = "INSERT INTO property_images (
                    property_id, image_url, is_primary, created_at
                ) VALUES (?, ?, ?, NOW())";
                
                $stmt = $conn->prepare($image_query);
                $stmt->execute([
                    $property_id,
                    $target_path,
                    $i === 0 ? 1 : 0  // First image is primary
                ]);
                $uploaded_images++;
            }
        }
    }

    if ($uploaded_images === 0) {
        throw new Exception("Failed to upload any images. Please try again.");
    }

    $conn->commit();
    $_SESSION['success_message'] = "Property listing submitted successfully! It will be reviewed by an admin.";
    header('Location: my-properties.php');
    exit;

} catch (Exception $e) {
    $conn->rollBack();
    error_log("Property submission error: " . $e->getMessage());
    $_SESSION['error_message'] = $e->getMessage();
    header('Location: add-property.php');
    exit;
} 