<?php
require_once '../config.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    $_SESSION['error'] = "Please login to access your dashboard.";
    header('Location: ../login.php');
    exit;
}

// Get user's properties
try {
    $stmt = $conn->prepare("SELECT * FROM properties WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $properties = $stmt->fetchAll();
} catch(PDOException $e) {
    $properties = [];
    $_SESSION['error'] = "Error fetching properties: " . $e->getMessage();
}

// Get user's favorites
try {
    $stmt = $conn->prepare("
        SELECT p.*, 
               (SELECT image_url FROM property_images WHERE property_id = p.id LIMIT 1) as main_image
        FROM properties p
        INNER JOIN favorites f ON p.id = f.property_id
        WHERE f.user_id = ?
        ORDER BY f.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $favorites = $stmt->fetchAll();
} catch(PDOException $e) {
    $favorites = [];
    $_SESSION['error'] = "Error fetching favorites: " . $e->getMessage();
}

// Get user's messages
try {
    $stmt = $conn->prepare("
        SELECT m.*, p.title as property_title 
        FROM property_inquiries m
        LEFT JOIN properties p ON m.property_id = p.id
        WHERE p.user_id = ?
        ORDER BY m.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $messages = $stmt->fetchAll();
} catch(PDOException $e) {
    $messages = [];
    $_SESSION['error'] = "Error fetching messages: " . $e->getMessage();
}

$page_title = 'User Dashboard';
include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <!-- User Info Card -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h5>
                    <p class="card-text">
                        <strong>Properties Listed:</strong> <?php echo count($properties); ?><br>
                        <strong>Favorites:</strong> <?php echo count($favorites); ?><br>
                        <strong>Messages:</strong> <?php echo count($messages); ?>
                    </p>
                    <a href="../add-property.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Add New Property
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="col-md-8 mb-4">
            <div class="row">
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h6 class="card-title">Active Properties</h6>
                            <h2><?php echo count(array_filter($properties, function($p) { return $p['status'] === 'active'; })); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h6 class="card-title">Total Views</h6>
                            <h2>--</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h6 class="card-title">New Messages</h6>
                            <h2><?php echo count(array_filter($messages, function($m) { return $m['status'] === 'new'; })); ?></h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Properties -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Your Recent Properties</h5>
            <a href="../my-properties.php" class="btn btn-sm btn-primary">View All</a>
        </div>
        <div class="card-body">
            <?php if (empty($properties)): ?>
                <p class="text-muted">You haven't listed any properties yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($properties, 0, 5) as $property): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($property['title']); ?></td>
                                    <td><?php echo ucfirst($property['type']); ?></td>
                                    <td><?php echo format_price($property['price']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo get_status_color($property['status']); ?>">
                                            <?php echo ucfirst($property['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="../property-details.php?id=<?php echo $property['id']; ?>" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="../user/edit-property.php?id=<?php echo $property['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Messages -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Recent Messages</h5>
            <a href="../my-messages.php" class="btn btn-sm btn-primary">View All</a>
        </div>
        <div class="card-body">
            <?php if (empty($messages)): ?>
                <p class="text-muted">No messages yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>From</th>
                                <th>Property</th>
                                <th>Message</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($messages, 0, 5) as $message): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($message['name']); ?></td>
                                    <td><?php echo htmlspecialchars($message['property_title']); ?></td>
                                    <td><?php echo substr(htmlspecialchars($message['message']), 0, 50) . '...'; ?></td>
                                    <td><?php echo format_date($message['created_at']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo get_message_status_color($message['status']); ?>">
                                            <?php echo ucfirst($message['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Favorites -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Your Favorites</h5>
            <a href="../favorites.php" class="btn btn-sm btn-primary">View All</a>
        </div>
        <div class="card-body">
            <?php if (empty($favorites)): ?>
                <p class="text-muted">You haven't added any properties to your favorites yet.</p>
            <?php else: ?>
                <div class="row">
                    <?php foreach (array_slice($favorites, 0, 3) as $property): ?>
                        <div class="col-md-4">
                            <div class="card">
                                <?php if ($property['main_image']): ?>
                                    <img src="<?php echo htmlspecialchars($property['main_image']); ?>" 
                                         class="card-img-top" alt="<?php echo htmlspecialchars($property['title']); ?>"
                                         style="height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                         style="height: 200px;">
                                        <i class="bi bi-house-door" style="font-size: 3rem;"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="card-body">
                                    <h6 class="card-title"><?php echo htmlspecialchars($property['title']); ?></h6>
                                    <p class="card-text">
                                        <strong><?php echo format_price($property['price']); ?></strong><br>
                                        <small class="text-muted">
                                            <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($property['location']); ?>
                                        </small>
                                    </p>
                                    <a href="../property-details.php?id=<?php echo $property['id']; ?>" class="btn btn-sm btn-primary">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 