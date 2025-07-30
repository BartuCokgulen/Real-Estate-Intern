<?php
require_once 'config.php';
require_once 'includes/functions.php';

// Require login for this page
require_login();

try {
    $query = "SELECT p.*, 
             (SELECT image_url FROM property_images WHERE property_id = p.id AND is_primary = 1 LIMIT 1) as primary_image 
             FROM properties p 
             INNER JOIN favorites f ON p.id = f.property_id 
             WHERE f.user_id = ? 
             ORDER BY f.created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([$_SESSION['user_id']]);
    $favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $favorites = [];
}

$page_title = 'My Favorites';
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
    .favorite-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 10;
        background: rgba(255, 255, 255, 0.9);
        border: none;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
    }
    .favorite-btn:hover {
        background: rgba(255, 255, 255, 1);
        transform: scale(1.1);
    }
    .favorite-btn i {
        font-size: 1.2rem;
        color: red;
    }
</style>';

$extra_js = '
<script>
    function toggleFavorite(propertyId, button) {
        fetch("api/toggle_favorite.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: `property_id=${propertyId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.action === "removed") {
                // Remove the property card from the page
                button.closest(".col-md-4").remove();
                
                // Check if there are any favorites left
                if (document.querySelectorAll(".property-card").length === 0) {
                    location.reload(); // Reload to show the "no favorites" message
                }
            } else if (!data.success) {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("An error occurred. Please try again.");
        });
    }
</script>';

require_once 'includes/header.php';
?>

<div class="container mt-4">
    <h1 class="mb-4">My Favorite Properties</h1>
    
    <div class="row">
        <?php if (!empty($favorites)): ?>
            <?php foreach ($favorites as $property): ?>
                <div class="col-md-4 mb-4">
                    <div class="card property-card h-100">
                        <div class="position-relative">
                            <img src="<?php echo !empty($property['primary_image']) ? 
                                            htmlspecialchars($property['primary_image']) : 
                                            'assets/images/placeholder.jpg'; ?>" 
                                 class="card-img-top" alt="Property Image">
                            <button class="favorite-btn active" 
                                    onclick="toggleFavorite(<?php echo $property['id']; ?>, this)">
                                <i class="bi bi-heart-fill"></i>
                            </button>
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
                            <a href="property-details.php?id=<?php echo $property['id']; ?>" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info">
                    You haven't added any properties to your favorites yet. 
                    <a href="properties.php">Browse properties</a> to add some!
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 