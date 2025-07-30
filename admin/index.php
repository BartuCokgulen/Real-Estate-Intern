<?php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';
require_admin();

// Get statistics
try {
    // Total properties count
    $stmt = $conn->query("SELECT COUNT(*) FROM properties");
    $total_properties = $stmt->fetchColumn();
    
    // Pending properties count
    $stmt = $conn->query("SELECT COUNT(*) FROM properties WHERE status = 'pending'");
    $pending_properties = $stmt->fetchColumn();
    
    // Total users count
    $stmt = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'user'");
    $total_users = $stmt->fetchColumn();
    
    // Recent properties
    $stmt = $conn->query("
        SELECT 
            p.id,
            p.title,
            p.status,
            p.created_at,
            u.username as owner
        FROM properties p
        JOIN users u ON p.user_id = u.id
        ORDER BY p.created_at DESC
        LIMIT 5
    ");
    $stmt->execute();
    $recent_properties = $stmt->fetchAll();

} catch(PDOException $e) {
    error_log("Error in admin dashboard: " . $e->getMessage());
}

$page_title = 'Dashboard';
include 'includes/admin-header.php';
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
        <div class="d-none d-sm-inline-block">
            <span class="mr-2 d-none d-lg-inline text-gray-600">
                <i class="bi bi-calendar-event me-1"></i> <?php echo date('F d, Y'); ?>
            </span>
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-uppercase mb-1" style="color: var(--primary-color)">
                                Total Properties
                            </div>
                            <div class="h3 mb-0 fw-bold text-gray-800"><?php echo $total_properties; ?></div>
                            <div class="mt-2">
                                <a href="properties.php" class="text-decoration-none" style="color: var(--primary-color)">
                                    View Details <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="rounded-circle p-3" style="background-color: rgba(var(--primary-rgb), 0.1)">
                                <i class="bi bi-house-door" style="font-size: 2rem; color: var(--primary-color)"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-uppercase mb-1" style="color: var(--warning-color)">
                                Pending Properties
                            </div>
                            <div class="h3 mb-0 fw-bold text-gray-800"><?php echo $pending_properties; ?></div>
                            <div class="mt-2">
                                <a href="properties.php?status=pending" class="text-decoration-none" style="color: var(--warning-color)">
                                    View Details <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="rounded-circle p-3" style="background-color: rgba(var(--warning-rgb), 0.1)">
                                <i class="bi bi-hourglass-split" style="font-size: 2rem; color: var(--warning-color)"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-uppercase mb-1" style="color: var(--success-color)">
                                Total Users
                            </div>
                            <div class="h3 mb-0 fw-bold text-gray-800"><?php echo $total_users; ?></div>
                            <div class="mt-2">
                                <a href="users.php" class="text-decoration-none" style="color: var(--success-color)">
                                    View Details <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="rounded-circle p-3" style="background-color: rgba(var(--success-rgb), 0.1)">
                                <i class="bi bi-people" style="font-size: 2rem; color: var(--success-color)"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-uppercase mb-1" style="color: var(--danger-color)">
                                New Messages
                            </div>
                            <div class="h3 mb-0 fw-bold text-gray-800">
                                <?php 
                                try {
                                    $stmt = $conn->query("SELECT COUNT(*) FROM messages WHERE is_read = 0");
                                    echo $stmt->fetchColumn();
                                } catch(PDOException $e) {
                                    echo "0";
                                }
                                ?>
                            </div>
                            <div class="mt-2">
                                <a href="messages.php" class="text-decoration-none" style="color: var(--danger-color)">
                                    View Details <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="rounded-circle p-3" style="background-color: rgba(var(--danger-rgb), 0.1)">
                                <i class="bi bi-envelope" style="font-size: 2rem; color: var(--danger-color)"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Properties -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card shadow border-0 mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold" style="color: var(--primary-color);"><i class="bi bi-house-door me-2"></i> Recent Properties</h6>
                    <a href="properties.php" class="btn btn-sm" style="background-color: var(--primary-color); color: white;">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_properties)): ?>
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
                                        <th class="fw-bold">Status</th>
                                        <th class="fw-bold">Date</th>
                                        <th class="fw-bold text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_properties as $property): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if (!empty($property['image_url'])): ?>
                                                        <img src="../<?php echo htmlspecialchars($property['image_url']); ?>" 
                                                             class="rounded me-3" style="width: 40px; height: 40px; object-fit: cover;">
                                                    <?php else: ?>
                                                        <div class="rounded me-3 d-flex align-items-center justify-content-center" 
                                                             style="width: 40px; height: 40px; background-color: var(--light-color);">
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
                                            <td><?php echo htmlspecialchars($property['owner']); ?></td>
                                            <td>
                                                <span class="badge rounded-pill bg-<?php echo get_status_color($property['status']); ?>">
                                                    <?php echo ucfirst($property['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo format_date($property['created_at']); ?></td>
                                            <td class="text-center">
                                                <a href="../property-details.php?id=<?php echo $property['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i> View
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
        </div>
        
        <!-- Quick Actions and Stats -->
        <div class="col-md-4">
            <!-- Quick Actions -->
            <div class="card shadow border-0 mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold" style="color: var(--primary-color);"><i class="bi bi-lightning-charge me-2"></i> Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        <a href="properties.php" class="btn btn-outline-primary d-flex align-items-center justify-content-between">
                            <span><i class="bi bi-house-door me-2"></i> Manage Properties</span>
                            <i class="bi bi-arrow-right"></i>
                        </a>
                        <a href="users.php" class="btn btn-outline-success d-flex align-items-center justify-content-between">
                            <span><i class="bi bi-people me-2"></i> Manage Users</span>
                            <i class="bi bi-arrow-right"></i>
                        </a>
                        <a href="messages.php" class="btn btn-outline-danger d-flex align-items-center justify-content-between">
                            <span><i class="bi bi-envelope me-2"></i> View Messages</span>
                            <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- System Info -->
            <div class="card shadow border-0">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold" style="color: var(--primary-color);"><i class="bi bi-info-circle me-2"></i> System Info</h6>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><i class="bi bi-hdd me-2"></i> PHP Version</span>
                            <span class="badge rounded-pill" style="background-color: var(--primary-color);"><?php echo phpversion(); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><i class="bi bi-database me-2"></i> Database</span>
                            <span class="badge rounded-pill" style="background-color: var(--success-color);">Connected</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><i class="bi bi-clock-history me-2"></i> Server Time</span>
                            <span class="text-muted"><?php echo date('H:i:s'); ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>