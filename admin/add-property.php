<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? '';
    $type = $_POST['type'] ?? '';
    $location = $_POST['location'] ?? '';
    $bedrooms = $_POST['bedrooms'] ?? '';
    $size_sqft = $_POST['size_sqft'] ?? '';
    $status = $_POST['status'] ?? 'active';

    if (!empty($title) && !empty($price) && !empty($location)) {
        try {
            $conn->beginTransaction();

            // Insert main property data
            $query = "INSERT INTO properties (title, description, price, type, location, bedrooms, size_sqft, status) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->execute([$title, $description, $price, $type, $location, $bedrooms, $size_sqft, $status]);
            
            $property_id = $conn->lastInsertId();

            // Handle multiple image uploads
            if (!empty($_FILES['images']['name'][0])) {
                $upload_dir = '../uploads/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                $images_query = "INSERT INTO property_images (property_id, image_url, is_primary) VALUES (?, ?, ?)";
                $images_stmt = $conn->prepare($images_query);

                foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                        $file_extension = strtolower(pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION));
                        
                        if (in_array($file_extension, $allowed_extensions)) {
                            $new_filename = uniqid() . '.' . $file_extension;
                            $destination = $upload_dir . $new_filename;

                            if (move_uploaded_file($tmp_name, $destination)) {
                                // First image will be primary
                                $is_primary = ($key === 0) ? 1 : 0;
                                $images_stmt->execute([$property_id, 'uploads/' . $new_filename, $is_primary]);
                            }
                        }
                    }
                }
            }

            $conn->commit();
            header('Location: properties.php');
            exit();
        } catch(PDOException $e) {
            $conn->rollBack();
            $error = 'Error adding property. Please try again.';
        }
    } else {
        $error = 'Please fill in all required fields.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Property - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .image-preview {
            max-width: 150px;
            max-height: 150px;
            margin: 10px;
        }
        #imagePreviewContainer {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Admin Dashboard</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="properties.php">Properties</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">Users</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h2 class="mb-0">Add New Property</h2>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-success"><?php echo $message; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="title" class="form-label">Title *</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="price" class="form-label">Price *</label>
                                    <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="type" class="form-label">Type *</label>
                                    <select class="form-control" id="type" name="type" required>
                                        <option value="sale">For Sale</option>
                                        <option value="rent">For Rent</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="location" class="form-label">Location *</label>
                                <input type="text" class="form-control" id="location" name="location" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="bedrooms" class="form-label">Bedrooms</label>
                                    <input type="text" class="form-control" id="bedrooms" name="bedrooms">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="size_sqft" class="form-label">Size (sq ft)</label>
                                    <input type="number" class="form-control" id="size_sqft" name="size_sqft">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="images" class="form-label">Property Images (Multiple) *</label>
                                <input type="file" class="form-control" id="images" name="images[]" accept="image/*" multiple required onchange="previewImages(this)">
                                <small class="text-muted">First uploaded image will be the primary image</small>
                                <div id="imagePreviewContainer"></div>
                            </div>

                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="active">Active</option>
                                    <option value="sold">Sold</option>
                                    <option value="rented">Rented</option>
                                </select>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Add Property</button>
                                <a href="properties.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function previewImages(input) {
        const container = document.getElementById('imagePreviewContainer');
        container.innerHTML = '';

        if (input.files) {
            Array.from(input.files).forEach(file => {
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.classList.add('image-preview');
                        container.appendChild(img);
                    }
                    reader.readAsDataURL(file);
                }
            });
        }
    }
    </script>
</body>
</html> 