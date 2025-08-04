<?php
require_once '../config.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    $_SESSION['error'] = "Please login to access your properties.";
    header('Location: ../login.php');
    exit;
}

// Get user's properties with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

try {
    // Get total count
    $stmt = $conn->prepare("SELECT COUNT(*) FROM properties WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $total_properties = $stmt->fetchColumn();
    $total_pages = ceil($total_properties / $per_page);

    // Get properties for current page
    $stmt = $conn->prepare("
        SELECT p.*, 
               (SELECT image_url FROM property_images WHERE property_id = p.id LIMIT 1) as main_image
        FROM properties p
        WHERE p.user_id = ?
        ORDER BY p.created_at DESC
        LIMIT " . (int)$offset . ", " . (int)$per_page
    );
    $stmt->execute([$_SESSION['user_id']]);
    $properties = $stmt->fetchAll();
} catch(PDOException $e) {
    $properties = [];
    $_SESSION['error'] = "Error fetching properties: " . $e->getMessage();
}

$page_title = 'My Properties';
include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <!-- User Info Card -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">My Properties</h5>
                    <p class="card-text">
                        <strong>Total Properties:</strong> <?php echo $total_properties; ?><br>
                        <strong>Active Properties:</strong> <?php echo count(array_filter($properties, function($p) { return $p['status'] === 'active'; })); ?>
                    </p>
                    <a href="add-property.php" class="btn btn-primary">
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
                            <h6 class="card-title">Pending Approval</h6>
                            <h2><?php echo count(array_filter($properties, function($p) { return $p['status'] === 'pending'; })); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h6 class="card-title">Draft Properties</h6>
                            <h2><?php echo count(array_filter($properties, function($p) { return $p['status'] === 'draft'; })); ?></h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Properties List -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">All Properties</h5>
        </div>
        <div class="card-body">
            <?php if (empty($properties)): ?>
                <div class="alert alert-info">
                    You haven't listed any properties yet. 
                    <a href="../add-property.php" class="alert-link">Add your first property</a>.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($properties as $property): ?>
                                <tr>
                                    <td>
                                        <?php if ($property['main_image']): ?>
                                            <img src="<?php echo htmlspecialchars($property['main_image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($property['title']); ?>"
                                                 style="width: 50px; height: 50px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="bg-light d-flex align-items-center justify-content-center" 
                                                 style="width: 50px; height: 50px;">
                                                <i class="bi bi-house-door"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($property['title']); ?></td>
                                    <td><?php echo ucfirst($property['type']); ?></td>
                                    <td><?php echo format_price($property['price']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo get_status_color($property['status']); ?>">
                                            <?php echo ucfirst($property['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo format_date($property['created_at']); ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="property-details.php?id=<?php echo $property['id']; ?>" 
                                               class="btn btn-sm btn-info" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="edit-property.php?id=<?php echo $property['id']; ?>" 
                                               class="btn btn-sm btn-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-danger" 
                                                    title="Delete"
                                                    onclick="confirmDelete(<?php echo $property['id']; ?>)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
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
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this property? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" action="../delete-property.php" style="display: inline;">
                    <input type="hidden" name="property_id" id="deletePropertyId">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(propertyId) {
    document.getElementById('deletePropertyId').value = propertyId;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>

<?php include '../includes/footer.php'; ?> 