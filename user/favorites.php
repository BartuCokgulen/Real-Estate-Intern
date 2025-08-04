<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: ../login.php');
    exit();
}

$page_title = 'My Favorites';
include 'includes/header.php';

// Get current page number
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Get total number of favorites
$stmt = $conn->prepare("
    SELECT COUNT(*) 
    FROM favorites f 
    JOIN properties p ON f.property_id = p.id 
    WHERE f.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$total_favorites = $stmt->fetchColumn();
$total_pages = ceil($total_favorites / $per_page);

// Get favorites for current page
$stmt = $conn->prepare("
    SELECT p.*, f.created_at as favorited_at 
    FROM favorites f 
    JOIN properties p ON f.property_id = p.id 
    WHERE f.user_id = ? 
    ORDER BY f.created_at DESC 
    LIMIT " . (int)$offset . ", " . (int)$per_page
);
$stmt->execute([$_SESSION['user_id']]);
$favorites = $stmt->fetchAll();
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="properties.php">
                            <i class="bi bi-house-door"></i> Properties
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="favorites.php">
                            <i class="bi bi-heart"></i> Favorites
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">
                            <i class="bi bi-person"></i> Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="messages.php">
                            <i class="bi bi-envelope"></i> Messages
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="settings.php">
                            <i class="bi bi-gear"></i> Settings
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">My Favorites</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary">Share</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle">
                        <i class="bi bi-calendar3"></i> This week
                    </button>
                </div>
            </div>

            <?php if (empty($favorites)): ?>
                <div class="alert alert-info">
                    You haven't added any properties to your favorites yet. 
                    <a href="../properties.php" class="alert-link">Browse properties</a> to find your favorites.
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($favorites as $property): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 shadow-sm">
                                <?php if (!empty($property['image'])): ?>
                                    <img src="../uploads/properties/<?php echo htmlspecialchars($property['image']); ?>" 
                                         class="card-img-top" alt="<?php echo htmlspecialchars($property['title']); ?>"
                                         style="height: 200px; object-fit: cover;">
                                <?php endif; ?>
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($property['title']); ?></h5>
                                    <p class="card-text">
                                        <strong>Price:</strong> $<?php echo number_format($property['price']); ?><br>
                                        <strong>Location:</strong> <?php echo htmlspecialchars($property['location']); ?><br>
                                        <strong>Status:</strong> 
                                        <span class="badge bg-<?php echo $property['status'] === 'active' ? 'success' : 'warning'; ?>">
                                            <?php echo ucfirst($property['status']); ?>
                                        </span>
                                    </p>
                                    <div class="btn-group w-100">
                                        <a href="property-details.php?id=<?php echo $property['id']; ?>" 
                                           class="btn btn-outline-primary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                        <form action="remove-favorite.php" method="POST" class="d-inline">
                                            <input type="hidden" name="property_id" value="<?php echo $property['id']; ?>">
                                            <button type="submit" class="btn btn-outline-danger">
                                                <i class="bi bi-heart-fill"></i> Remove
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 