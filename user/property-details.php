<?php
require_once '../config.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    $_SESSION['error'] = "Please login to view property details.";
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
    $stmt = $conn->prepare("
        SELECT p.*, u.username as owner_name
        FROM properties p
        LEFT JOIN users u ON p.user_id = u.id
        WHERE p.id = ? AND p.user_id = ?
    ");
    $stmt->execute([$property_id, $_SESSION['user_id']]);
    $property = $stmt->fetch();

    if (!$property) {
        $_SESSION['error'] = "Property not found or you don't have permission to view it.";
        header('Location: properties.php');
        exit;
    }

    // Get property images
    $stmt = $conn->prepare("SELECT * FROM property_images WHERE property_id = ?");
    $stmt->execute([$property_id]);
    $images = $stmt->fetchAll();

    // Get property features
    // $stmt = $conn->prepare("SELECT * FROM property_features WHERE property_id = ?");
    // $stmt->execute([$property_id]);
    // $features = $stmt->fetchAll();
} catch(PDOException $e) {
    $_SESSION['error'] = "Error fetching property details: " . $e->getMessage();
    header('Location: properties.php');
    exit;
}

$page_title = $property['title'];
include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <!-- Property Images -->
        <div class="col-md-8">
            <?php if (!empty($images)): ?>
                <div id="propertyCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        <?php foreach ($images as $key => $image): ?>
                            <div class="carousel-item <?php echo $key === 0 ? 'active' : ''; ?>">
                                <img src="<?php echo htmlspecialchars($image['image_url']); ?>" 
                                     class="d-block w-100" alt="Property Image"
                                     style="height: 500px; object-fit: cover;">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($images) > 1): ?>
                        <button class="carousel-control-prev" type="button" data-bs-target="#propertyCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#propertyCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="bg-light d-flex align-items-center justify-content-center" style="height: 500px;">
                    <i class="bi bi-house-door" style="font-size: 5rem;"></i>
                </div>
            <?php endif; ?>
        </div>

        <!-- Property Details -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title"><?php echo htmlspecialchars($property['title']); ?></h2>
                    <p class="text-primary h3 mb-3"><?php echo format_price($property['price']); ?></p>
                    
                    <div class="mb-3">
                        <span class="badge bg-<?php echo get_status_color($property['status']); ?>">
                            <?php echo ucfirst($property['status']); ?>
                        </span>
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <p class="mb-0"><i class="bi bi-house-door"></i> <?php echo ucfirst($property['type']); ?></p>
                        </div>
                        <div class="col-6">
                            <p class="mb-0"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($property['location']); ?></p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-4">
                            <p class="mb-0"><i class="bi bi-door-open"></i> <?php echo $property['bedrooms']; ?> Beds</p>
                        </div>
                        <div class="col-4">
                            <p class="mb-0"><i class="bi bi-droplet"></i> <?php echo $property['bathrooms']; ?> Baths</p>
                        </div>
                        <div class="col-4">
                            <p class="mb-0"><i class="bi bi-arrows-angle-expand"></i> <?php echo $property['area']; ?> sq ft</p>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="edit-property.php?id=<?php echo $property['id']; ?>" class="btn btn-primary">
                            <i class="bi bi-pencil"></i> Edit Property
                        </a>
                        <a href="properties.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Properties
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Property Description -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Description</h5>
                </div>
                <div class="card-body">
                    <?php echo nl2br(htmlspecialchars($property['description'])); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 