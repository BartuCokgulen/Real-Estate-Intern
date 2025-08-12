<?php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';
require_admin();

// Handle property status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['property_id'], $_POST['action'])) {
    $property_id = (int)$_POST['property_id'];
    $action = $_POST['action'];
    
    try {
        if ($action === 'approve') {
            $stmt = $conn->prepare("UPDATE properties SET status = 'active' WHERE id = ?");
            $stmt->execute([$property_id]);
            $_SESSION['success'] = "Property has been approved.";
        } elseif ($action === 'reject') {
            $stmt = $conn->prepare("UPDATE properties SET status = 'rejected' WHERE id = ?");
            $stmt->execute([$property_id]);
            $_SESSION['success'] = "Property has been rejected.";
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error updating property status: " . $e->getMessage();
    }
    
    header('Location: properties.php');
    exit;
}

// Get properties with user information
$query = "SELECT p.*, u.username as owner_name,
                 (SELECT image_url FROM property_images WHERE property_id = p.id LIMIT 1) as main_image
          FROM properties p
          LEFT JOIN users u ON p.user_id = u.id
          ORDER BY 
            CASE p.status
                WHEN 'pending' THEN 1
                WHEN 'active' THEN 2
                WHEN 'rejected' THEN 3
                ELSE 4
            END,
            p.created_at DESC";

try {
    $stmt = $conn->query($query);
    $properties = $stmt->fetchAll();
} catch(PDOException $e) {
    $properties = [];
    $_SESSION['error'] = "Error fetching properties: " . $e->getMessage();
}

$page_title = 'Manage Properties';
include 'includes/admin-header.php';
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manage Properties</h1>
        <ol class="breadcrumb mb-0 bg-transparent">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item active">Properties</li>
        </ol>
    </div>
    
    <div class="card shadow border-0 mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold" style="color: var(--primary-color);"><i class="bi bi-house-door me-2"></i> Property Management</h6>
            <div>
                <span class="badge bg-primary rounded-pill"><?php echo count($properties); ?> Total Properties</span>
                <?php 
                $pending_count = 0;
                foreach ($properties as $p) {
                    if ($p['status'] === 'pending') $pending_count++;
                }
                if ($pending_count > 0): 
                ?>
                <span class="badge bg-warning rounded-pill ms-2"><?php echo $pending_count; ?> Pending</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($properties)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-house" style="font-size: 3rem; color: var(--secondary-color);"></i>
                    <p class="mt-3 text-muted">No properties found in the system.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="fw-bold">Property</th>
                                <th class="fw-bold">Owner</th>
                                <th class="fw-bold">Type</th>
                                <th class="fw-bold">Price</th>
                                <th class="fw-bold">Location</th>
                                <th class="fw-bold">Status</th>
                                <th class="fw-bold">Created</th>
                                <th class="fw-bold text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($properties as $property): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if ($property['main_image']): ?>
                                                <img src="../<?php echo htmlspecialchars($property['main_image']); ?>" 
                                                     alt="Property Image" class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="rounded me-3 d-flex align-items-center justify-content-center" 
                                                     style="width: 50px; height: 50px; background-color: var(--light-color);">
                                                    <i class="bi bi-house" style="color: var(--primary-color);"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="fw-bold text-truncate" style="max-width: 200px;">
                                                    <?php echo htmlspecialchars($property['title']); ?>
                                                </div>
                                                <div class="small text-muted">
                                                    ID: <?php echo $property['id']; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($property['owner_name']); ?></td>
                                    <td><?php echo get_property_type_badge($property['type']); ?></td>
                                    <td><span class="fw-bold" style="color: var(--primary-color);"><?php echo format_price($property['price']); ?></span></td>
                                    <td><span class="text-truncate d-inline-block" style="max-width: 150px;"><?php echo htmlspecialchars($property['location']); ?></span></td>
                                    <td>
                                        <span class="badge rounded-pill bg-<?php echo get_status_color($property['status']); ?>">
                                            <?php echo ucfirst($property['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo format_date($property['created_at']); ?></td>
                                    <td class="text-center">
                                        <div class="btn-group">
                                <a href="../property-details.php?id=<?php echo $property['id']; ?>" 
                                   class="btn btn-sm btn-outline-primary" target="_blank">
                                    <i class="bi bi-eye"></i> View
                                </a>
                                
                                <?php if ($property['status'] === 'pending'): ?>
                                    <form action="" method="POST" class="d-inline ms-1">
                                        <input type="hidden" name="property_id" value="<?php echo $property['id']; ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn btn-sm btn-outline-success" 
                                                onclick="return confirm('Are you sure you want to approve this property?')">
                                            <i class="bi bi-check-lg"></i> Approve
                                        </button>
                                    </form>
                                    <form action="" method="POST" class="d-inline ms-1">
                                        <input type="hidden" name="property_id" value="<?php echo $property['id']; ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Are you sure you want to reject this property?')">
                                            <i class="bi bi-x-lg"></i> Reject
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
</div>

<?php include 'includes/admin-footer.php'; ?>