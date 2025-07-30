<?php
require_once 'includes/functions.php';
require_once 'config.php';
require_login();

try {
    $query = "SELECT p.*, 
             (SELECT image_url FROM property_images WHERE property_id = p.id AND is_primary = 1 LIMIT 1) as primary_image 
             FROM properties p 
             WHERE p.user_id = ? 
             ORDER BY p.created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([$_SESSION['user_id']]);
    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $properties = [];
}

$page_title = 'My Properties';
$extra_css = '
<style>
    .property-card {
        transition: transform 0.2s;
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
        border: none;
    }
    .property-card:hover {
        transform: translateY(-5px);
    }
    .property-card img {
        height: 200px;
        object-fit: cover;
    }
    .status-badge {
        position: absolute;
        top: 10px;
        left: 10px;
        z-index: 10;
        padding: 5px 10px;
        border-radius: 15px;
    }
    .status-pending {
        background-color: #ffc107;
        color: #000;
    }
    .status-approved {
        background-color: #28a745;
        color: #fff;
    }
    .status-rejected {
        background-color: #dc3545;
        color: #fff;
    }
</style>';

require_once 'includes/header.php';

if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
    unset($_SESSION['error_message']);
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>My Properties</h1>
        <a href="add-property.php" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Add New Property
        </a>
    </div>
    
    <div class="row">
        <?php if (!empty($properties)): ?>
            <?php foreach ($properties as $property): ?>
                <div class="col-md-4 mb-4">
                    <div class="card property-card h-100">
                        <div class="position-relative">
                            <img src="<?php echo !empty($property['primary_image']) ? 
                                            htmlspecialchars($property['primary_image']) : 
                                            'assets/images/placeholder.jpg'; ?>" 
                                 class="card-img-top" alt="Property Image">
                            <div class="status-badge status-<?php echo strtolower($property['status']); ?>">
                                <?php echo ucfirst($property['status']); ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($property['title']); ?></h5>
                            <p class="card-text">
                                <strong>Price:</strong> <?php echo format_price($property['price']); ?><br>
                                <strong>Location:</strong> <?php echo htmlspecialchars($property['location']); ?><br>
                                <strong>Type:</strong> <?php echo ucfirst($property['type']); ?><br>
                                <?php if (!empty($property['bedrooms'])) : ?>
                                    <strong>Bedrooms:</strong> <?php echo htmlspecialchars($property['bedrooms']); ?><br>
                                <?php endif; ?>
                                <?php if (!empty($property['size'])) : ?>
                                    <strong>Size:</strong> <?php echo number_format($property['size']); ?> sq ft
                                <?php endif; ?>
                            </p>
                            <div class="d-flex justify-content-between">
                                <a href="property-details.php?id=<?php echo $property['id']; ?>" class="btn btn-primary">View Details</a>
                                <?php if ($property['status'] !== 'approved'): ?>
                                    <a href="edit-property.php?id=<?php echo $property['id']; ?>" class="btn btn-secondary">Edit</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info">
                    You haven't added any properties yet. 
                    <a href="add-property.php">Add your first property listing!</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 