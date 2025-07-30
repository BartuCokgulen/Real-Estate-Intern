<?php
require_once 'config.php';
require_once 'includes/functions.php';

$type = isset($_GET['type']) ? clean_input($_GET['type']) : '';
$min_price = isset($_GET['min_price']) ? (int)$_GET['min_price'] : '';
$max_price = isset($_GET['max_price']) ? (int)$_GET['max_price'] : '';
$location = isset($_GET['location']) ? clean_input($_GET['location']) : '';
$bedrooms = isset($_GET['bedrooms']) ? (int)$_GET['bedrooms'] : '';
$sort = isset($_GET['sort']) ? clean_input($_GET['sort']) : 'newest';

$query = "SELECT p.*, u.username as owner_name, 
                 (SELECT image_url FROM property_images WHERE property_id = p.id LIMIT 1) as main_image
          FROM properties p
          LEFT JOIN users u ON p.user_id = u.id
          WHERE p.status = 'active'";
$params = [];

if ($type) {
    $query .= " AND p.type = ?";
    $params[] = $type;
}
if ($min_price) {
    $query .= " AND p.price >= ?";
    $params[] = $min_price;
}
if ($max_price) {
    $query .= " AND p.price <= ?";
    $params[] = $max_price;
}
if ($location) {
    $query .= " AND p.location LIKE ?";
    $params[] = "%$location%";
}
if ($bedrooms) {
    $query .= " AND p.bedrooms = ?";
    $params[] = $bedrooms;
}

switch ($sort) {
    case 'price_low':
        $query .= " ORDER BY p.price ASC";
        break;
    case 'price_high':
        $query .= " ORDER BY p.price DESC";
        break;
    case 'oldest':
        $query .= " ORDER BY p.created_at ASC";
        break;
    default:
        $query .= " ORDER BY p.created_at DESC";
}

try {
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $properties = $stmt->fetchAll();
} catch(PDOException $e) {
    $properties = [];
    $_SESSION['error'] = "Error fetching properties: " . $e->getMessage();
}

try {
    $stmt = $conn->query("SELECT DISTINCT location FROM properties WHERE status = 'active' ORDER BY location");
    $locations = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch(PDOException $e) {
    $locations = [];
}

$page_title = 'Properties';
$extra_css = '
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/nouislider@14.6.3/distribute/nouislider.min.css">
<style>
    body {
        background-color: #f8f9fa;
    }
    .section-title {
        color: #333;
        font-weight: 700;
        position: relative;
        padding-bottom: 10px;
        margin-bottom: 30px;
    }
    .section-title:after {
        content: "";
        position: absolute;
        bottom: 0;
        left: 0;
        width: 60px;
        height: 3px;
        background: linear-gradient(to right, #0d6efd, #6610f2);
    }
    .property-card {
        transition: all 0.3s ease;
        border: none;
        overflow: hidden;
    }
    .property-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important;
    }
    .property-card img {
        height: 220px;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    .property-card:hover img {
        transform: scale(1.05);
    }
    .property-badge {
        font-size: 0.8rem;
        letter-spacing: 0.5px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }
    .form-select:focus, .form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    .bg-gradient-primary {
        background: linear-gradient(to right, #0d6efd, #6610f2);
    }
    .noUi-connect {
        background: linear-gradient(to right, #0d6efd, #6610f2);
    }
    .price-inputs, .size-inputs {
        display: flex;
        justify-content: space-between;
        margin-top: 10px;
    }
    .price-inputs input, .size-inputs input {
        width: 45%;
    }
    .empty-state {
        border-radius: 10px;
        background-color: #f8f9fa;
    }
    .btn-primary {
        background: linear-gradient(to right, #0d6efd, #6610f2);
        border: none;
        transition: all 0.3s ease;
    }
    .btn-primary:hover {
        background: linear-gradient(to right, #0b5ed7, #5a0cd6);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
    }
    .btn-outline-primary {
        border-color: #0d6efd;
        color: #0d6efd;
        transition: all 0.3s ease;
    }
    .btn-outline-primary:hover {
        background-color: #0d6efd;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
    }
    .property-details i {
        font-size: 1.1rem;
    }
</style>';

$extra_js = '
<script src="https://cdn.jsdelivr.net/npm/nouislider@14.6.3/distribute/nouislider.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const priceRange = document.getElementById("price-range");
        const minPrice = document.getElementById("min_price");
        const maxPrice = document.getElementById("max_price");
        if (priceRange && minPrice && maxPrice) {
            noUiSlider.create(priceRange, {
                start: [
                    minPrice.value || 0, 
                    maxPrice.value || 1000000
                ],
                connect: true,
                range: {
                    "min": 0,
                    "max": 1000000
                },
                format: {
                    to: value => Math.round(value),
                    from: value => value
                }
            });
            priceRange.noUiSlider.on("update", (values, handle) => {
                const value = values[handle];
                if (handle === 0) {
                    minPrice.value = value;
                } else {
                    maxPrice.value = value;
                }
            });
        }
        const sizeRange = document.getElementById("size-range");
        const minSize = document.getElementById("min_size");
        const maxSize = document.getElementById("max_size");
        if (sizeRange && minSize && maxSize) {
            noUiSlider.create(sizeRange, {
                start: [
                    minSize.value || 0, 
                    maxSize.value || 5000
                ],
                connect: true,
                range: {
                    "min": 0,
                    "max": 5000
                },
                format: {
                    to: value => Math.round(value),
                    from: value => value
                }
            });
            sizeRange.noUiSlider.on("update", (values, handle) => {
                const value = values[handle];
                if (handle === 0) {
                    minSize.value = value;
                } else {
                    maxSize.value = value;
                }
            });
        }
    });
</script>';

include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="card mb-5 shadow-sm border-0 rounded-lg overflow-hidden">
        <div class="card-header bg-gradient-primary text-white py-3">
            <h5 class="mb-0"><i class="bi bi-funnel-fill me-2"></i>Find Your Dream Property</h5>
        </div>
        <div class="card-body p-4">
            <form method="GET" class="row g-4">
                <div class="col-md-3">
                    <label class="form-label fw-bold"><i class="bi bi-house me-1"></i>Property Type</label>
                    <select name="type" class="form-select shadow-sm border-0 bg-light">
                        <option value="">All Types</option>
                        <option value="house" <?php echo $type === 'house' ? 'selected' : ''; ?>>House</option>
                        <option value="apartment" <?php echo $type === 'apartment' ? 'selected' : ''; ?>>Apartment</option>
                        <option value="villa" <?php echo $type === 'villa' ? 'selected' : ''; ?>>Villa</option>
                        <option value="land" <?php echo $type === 'land' ? 'selected' : ''; ?>>Land</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold"><i class="bi bi-geo-alt me-1"></i>Location</label>
                    <select name="location" class="form-select shadow-sm border-0 bg-light">
                        <option value="">All Locations</option>
                        <?php foreach ($locations as $loc): ?>
                            <option value="<?php echo htmlspecialchars($loc); ?>" 
                                    <?php echo $location === $loc ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($loc); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold"><i class="bi bi-currency-dollar me-1"></i>Price Range</label>
                    <div id="price-range" class="mt-2 mb-3"></div>
                    <div class="d-flex justify-content-between">
                        <div class="input-group shadow-sm" style="width: 45%;">
                            <span class="input-group-text bg-light border-0"> ₺</span>
                            <input type="number" id="min_price" name="min_price" class="form-control border-0 bg-light" value="<?php echo $min_price; ?>" placeholder="Min">
                        </div>
                        <div class="input-group shadow-sm" style="width: 45%;">
                            <span class="input-group-text bg-light border-0"> ₺</span>
                            <input type="number" id="max_price" name="max_price" class="form-control border-0 bg-light" value="<?php echo $max_price; ?>" placeholder="Max">
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold"><i class="bi bi-door-open me-1"></i>Bedrooms</label>
                    <select name="bedrooms" class="form-select shadow-sm border-0 bg-light">
                        <option value="">Any</option>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo $bedrooms === $i ? 'selected' : ''; ?>>
                                <?php echo $i; ?>+
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold"><i class="bi bi-sort-down me-1"></i>Sort By</label>
                    <select name="sort" class="form-select shadow-sm border-0 bg-light">
                        <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                        <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price (Low to High)</option>
                        <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price (High to Low)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100 shadow-sm py-2 fw-bold">
                        <i class="bi bi-search me-2"></i> Find Properties
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="properties-section">
        <h2 class="section-title mb-4"><i class="bi bi-buildings me-2"></i>Available Properties</h2>
        <?php if (empty($properties)): ?>
            <div class="empty-state p-5 text-center bg-light rounded-lg shadow-sm">
                <i class="bi bi-search-heart fs-1 text-muted mb-3 d-block"></i>
                <h4 class="text-muted">No properties found</h4>
                <p class="text-muted">Try adjusting your search criteria to find more properties.</p>
                <a href="properties.php" class="btn btn-outline-primary mt-3">
                    <i class="bi bi-arrow-repeat me-2"></i>Reset Filters
                </a>
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($properties as $property): ?>
                    <div class="col">
                        <div class="card property-card h-100 border-0 shadow-sm rounded-lg overflow-hidden">
                            <div class="position-relative">
                                <img src="<?php echo !empty($property['main_image']) ? htmlspecialchars($property['main_image']) : 'assets/images/placeholder.jpg'; ?>" 
                                     class="card-img-top" alt="<?php echo htmlspecialchars($property['title']); ?>">
                                <div class="property-badge position-absolute top-0 end-0 m-3 px-3 py-1 rounded-pill bg-primary text-white fw-bold">
                                    <?php echo ucfirst(htmlspecialchars($property['type'])); ?>
                                </div>
                                <?php if (is_logged_in()): ?>
                                    <button class="favorite-btn position-absolute top-0 start-0 m-3 btn btn-light rounded-circle shadow-sm p-2" 
                                            data-property-id="<?php echo $property['id']; ?>"
                                            data-favorited="<?php echo is_property_favorited($conn, $_SESSION['user_id'], $property['id']) ? 'true' : 'false'; ?>">
                                        <i class="bi bi-heart<?php echo is_property_favorited($conn, $_SESSION['user_id'], $property['id']) ? '-fill text-danger' : ''; ?> fs-5"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-success fs-6 px-3 py-2"><?php echo format_price($property['price']); ?></span>
                                    <span class="text-muted small"><i class="bi bi-calendar-date me-1"></i><?php echo date('M d, Y', strtotime($property['created_at'])); ?></span>
                                </div>
                                <h5 class="card-title fw-bold mb-3"><?php echo htmlspecialchars($property['title']); ?></h5>
                                <div class="property-details mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="bi bi-geo-alt text-primary me-2"></i>
                                        <span><?php echo htmlspecialchars($property['location']); ?></span>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="bi bi-house-door text-primary me-2"></i>
                                        <span><?php echo isset($property['bedrooms']) ? $property['bedrooms'] . ' Bedrooms' : 'N/A'; ?></span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-person text-primary me-2"></i>
                                        <span>Listed by <?php echo htmlspecialchars($property['owner_name']); ?></span>
                                    </div>
                                </div>
                                <a href="property-details.php?id=<?php echo $property['id']; ?>" class="btn btn-primary w-100 py-2 fw-bold">
                                    <i class="bi bi-eye me-2"></i>View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php if (is_logged_in()): ?>
<script>
document.querySelectorAll('.favorite-btn').forEach(button => {
    button.addEventListener('click', async function() {
        const propertyId = this.dataset.propertyId;
        const response = await fetch('ajax/toggle-favorite.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `property_id=${propertyId}`
        });
        if (response.ok) {
            const result = await response.json();
            if (result.success) {
                const icon = this.querySelector('i');
                if (result.favorited) {
                    icon.classList.remove('bi-heart');
                    icon.classList.add('bi-heart-fill');
                    this.dataset.favorited = 'true';
                } else {
                    icon.classList.remove('bi-heart-fill');
                    icon.classList.add('bi-heart');
                    this.dataset.favorited = 'false';
                }
            }
        }
    });
});
</script>
<?php endif; ?>
<?php include 'includes/footer.php'; ?>