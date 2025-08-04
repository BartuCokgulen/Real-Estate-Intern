<?php
require_once '../config.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    $_SESSION['error'] = "Please login to edit properties.";
    header('Location: ../login.php');
    exit;
}

// Check if property ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Property ID is required.";
    header('Location: properties.php');
    exit;
}

$property_id = (int)$_GET['id'];

// Get property details
try {
    $stmt = $conn->prepare("SELECT * FROM properties WHERE id = ? AND user_id = ?");
    $stmt->execute([$property_id, $_SESSION['user_id']]);
    $property = $stmt->fetch();

    if (!$property) {
        $_SESSION['error'] = "Property not found or you don't have permission to edit it.";
        header('Location: properties.php');
        exit;
    }

    // Get property images
    $stmt = $conn->prepare("SELECT * FROM property_images WHERE property_id = ?");
    $stmt->execute([$property_id]);
    $images = $stmt->fetchAll();
} catch(PDOException $e) {
    $_SESSION['error'] = "Error fetching property details: " . $e->getMessage();
    header('Location: properties.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $type = $_POST['type'];
    $location = trim($_POST['location']);
    $bedrooms = (int)$_POST['bedrooms'];
    $bathrooms = (int)$_POST['bathrooms'];
    $area = (float)$_POST['area'];
    $status = $_POST['status'];

    if (empty($title) || empty($description) || empty($location)) {
        $_SESSION['error'] = "Please fill in all required fields.";
    } else {
        try {
            // Update property details
            $stmt = $conn->prepare("
                UPDATE properties 
                SET title = ?, description = ?, price = ?, type = ?, 
                    location = ?, bedrooms = ?, bathrooms = ?, area = ?, 
                    status = ?, updated_at = NOW()
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([
                $title, $description, $price, $type, $location,
                $bedrooms, $bathrooms, $area, $status,
                $property_id, $_SESSION['user_id']
            ]);

            // Handle image uploads
            if (!empty($_FILES['images']['name'][0])) {
                $upload_dir = '../uploads/properties/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                        $file_name = $_FILES['images']['name'][$key];
                        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                        $new_name = uniqid() . '.' . $file_ext;
                        $target_path = $upload_dir . $new_name;

                        if (move_uploaded_file($tmp_name, $target_path)) {
                            $stmt = $conn->prepare("
                                INSERT INTO property_images (property_id, image_url, created_at)
                                VALUES (?, ?, NOW())
                            ");
                            $stmt->execute([$property_id, 'uploads/properties/' . $new_name]);
                        }
                    }
                }
            }

            $_SESSION['success'] = "Property updated successfully.";
            header('Location: properties.php');
            exit;
        } catch(PDOException $e) {
            $_SESSION['error'] = "Error updating property: " . $e->getMessage();
        }
    }
}

$page_title = 'Edit Property';
include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Edit Property</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?php 
                            echo $_SESSION['error'];
                            unset($_SESSION['error']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title *</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?php echo htmlspecialchars($property['title']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description *</label>
                            <textarea class="form-control" id="description" name="description" 
                                      rows="5" required><?php echo htmlspecialchars($property['description']); ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label">Price *</label>
                                <input type="number" class="form-control" id="price" name="price" 
                                       value="<?php echo $property['price']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">Type *</label>
                                <select class="form-select" id="type" name="type" required>
                                    <option value="house" <?php echo $property['type'] === 'house' ? 'selected' : ''; ?>>House</option>
                                    <option value="apartment" <?php echo $property['type'] === 'apartment' ? 'selected' : ''; ?>>Apartment</option>
                                    <option value="villa" <?php echo $property['type'] === 'villa' ? 'selected' : ''; ?>>Villa</option>
                                    <option value="land" <?php echo $property['type'] === 'land' ? 'selected' : ''; ?>>Land</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="location" class="form-label">Location *</label>
                            <input type="text" class="form-control" id="location" name="location" 
                                   value="<?php echo htmlspecialchars($property['location']); ?>" required>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="bedrooms" class="form-label">Bedrooms</label>
                                <input type="number" class="form-control" id="bedrooms" name="bedrooms" 
                                       value="<?php echo $property['bedrooms']; ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="bathrooms" class="form-label">Bathrooms</label>
                                <input type="number" class="form-control" id="bathrooms" name="bathrooms" 
                                       value="<?php echo $property['bathrooms']; ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="area" class="form-label">Area (sq ft)</label>
                                <input type="number" class="form-control" id="area" name="area" 
                                       value="<?php echo $property['area']; ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status *</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="draft" <?php echo $property['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                <option value="pending" <?php echo $property['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="active" <?php echo $property['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="sold" <?php echo $property['status'] === 'sold' ? 'selected' : ''; ?>>Sold</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="images" class="form-label">Additional Images</label>
                            <input type="file" class="form-control" id="images" name="images[]" 
                                   multiple accept="image/*">
                            <small class="text-muted">You can select multiple images.</small>
                        </div>

                        <?php if (!empty($images)): ?>
                            <div class="mb-3">
                                <label class="form-label">Current Images</label>
                                <div class="row">
                                    <?php foreach ($images as $image): ?>
                                        <div class="col-md-4 mb-2">
                                            <div class="position-relative">
                                                <img src="<?php echo htmlspecialchars($image['image_url']); ?>" 
                                                     class="img-thumbnail" alt="Property Image">
                                                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1"
                                                        onclick="deleteImage(<?php echo $image['id']; ?>)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Update Property</button>
                            <a href="properties.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function deleteImage(imageId) {
    if (confirm('Are you sure you want to delete this image?')) {
        window.location.href = 'delete-image.php?id=' + imageId + '&property_id=<?php echo $property_id; ?>';
    }
}
</script>

<?php include '../includes/footer.php'; ?> 