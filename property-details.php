<?php
session_start();
require_once 'config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: properties.php');
    exit();
}

$property_id = $_GET['id'];

try {
    $query = "SELECT p.*, u.username as owner_name 
             FROM properties p 
             LEFT JOIN users u ON p.user_id = u.id 
             WHERE p.id = ? AND p.status = 'active'";
    $stmt = $conn->prepare($query);
    $stmt->execute([$property_id]);
    $property = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$property) {
        header('Location: properties.php');
        exit();
    }

    $images_query = "SELECT * FROM property_images WHERE property_id = ? ORDER BY is_primary DESC";
    $images_stmt = $conn->prepare($images_query);
    $images_stmt->execute([$property_id]);
    $property_images = $images_stmt->fetchAll(PDO::FETCH_ASSOC);

    $is_favorite = false;
    if (isset($_SESSION['user_id'])) {
        $fav_query = "SELECT id FROM favorites WHERE user_id = ? AND property_id = ?";
        $fav_stmt = $conn->prepare($fav_query);
        $fav_stmt->execute([$_SESSION['user_id'], $property_id]);
        $is_favorite = $fav_stmt->fetch() !== false;
    }
} catch(PDOException $e) {
    header('Location: properties.php');
    exit();
}

$page_title = htmlspecialchars($property['title']);
$extra_css = '
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css">
<style>
    .swiper {
        width: 100%;
        height: 400px;
    }
    .swiper-slide img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .favorite-btn {
        background: none;
        border: none;
        font-size: 1.5rem;
        color: #dc3545;
        cursor: pointer;
        transition: transform 0.2s;
        padding: 5px;
        line-height: 1;
    }
    .favorite-btn:hover {
        transform: scale(1.1);
    }
    .favorite-btn.active {
        color: #dc3545;
    }
    .favorite-btn:not(.active) {
        color: #6c757d;
    }
</style>';

$extra_js = '
<script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
<script>
    const swiper = new Swiper(".swiper", {
        loop: true,
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },
        pagination: {
            el: ".swiper-pagination",
        },
    });

    function toggleFavorite(propertyId) {
        fetch("api/toggle_favorite.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: `property_id=${propertyId}`,
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const btn = document.querySelector(".favorite-btn");
                btn.classList.toggle("active");
                btn.innerHTML = btn.classList.contains("active") 
                    ? \'<i class="bi bi-heart-fill"></i>\' 
                    : \'<i class="bi bi-heart"></i>\';
            } else {
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
    <!-- Property Images Slider -->
    <div class="card mb-4">
        <div class="card-body p-0">
            <div class="swiper">
                <div class="swiper-wrapper">
                    <?php foreach ($property_images as $image): ?>
                        <div class="swiper-slide">
                            <img src="<?php echo htmlspecialchars($image['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($property['title']); ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-pagination"></div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
            </div>
        </div>
    </div>

    <!-- Property Details -->
    <div class="row">
        <div class="col-md-8">
            <div class="card content-card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h1 class="card-title mb-0"><?php echo htmlspecialchars($property['title']); ?></h1>
                        <?php if (is_logged_in()): ?>
                            <button class="favorite-btn <?php echo $is_favorite ? 'active' : ''; ?>" 
                                    onclick="toggleFavorite(<?php echo $property['id']; ?>)">
                                <i class="bi bi-heart<?php echo $is_favorite ? '-fill' : ''; ?>"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                    <h4 class="text-primary mb-3"><?php echo format_price($property['price']); ?></h4>
                    <p class="lead"><?php echo htmlspecialchars($property['description']); ?></p>
                    
                    <hr>
                    
                    <h5>Property Details</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li><strong>Type:</strong> <?php echo ucfirst($property['type']); ?></li>
                                <li><strong>Status:</strong> <?php echo ucfirst($property['status']); ?></li>
                                <li><strong>Location:</strong> <?php echo htmlspecialchars($property['location']); ?></li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li><strong>Bedrooms:</strong> <?php echo $property['bedrooms']; ?></li>
                                <li><strong>Bathrooms:</strong> <?php echo $property['bathrooms']; ?></li>
                                <li><strong>Size:</strong> <?php echo $property['size']; ?> sqft</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Contact Form -->
            <div class="card content-card">
                <div class="card-body">
                    <h5 class="card-title">Interested in this property?</h5>
                    <form action="send-inquiry.php" method="POST">
                        <input type="hidden" name="property_id" value="<?php echo $property['id']; ?>">
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Your Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone">
                        </div>
                        
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Send Inquiry</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 