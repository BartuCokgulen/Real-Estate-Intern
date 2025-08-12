<?php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';
require_admin();

// Handle user status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['action'])) {
    $user_id = (int)$_POST['user_id'];
    $action = $_POST['action'];
    
    try {
        if ($action === 'activate') {
            $stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE id = ?");
            $stmt->execute([$user_id]);
            $_SESSION['success'] = "User has been activated.";
        } elseif ($action === 'deactivate') {
            $stmt = $conn->prepare("UPDATE users SET status = 'inactive' WHERE id = ?");
            $stmt->execute([$user_id]);
            $_SESSION['success'] = "User has been deactivated.";
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error updating user status: " . $e->getMessage();
    }
    
    header('Location: users.php');
    exit;
}

// Get users
$query = "SELECT u.*, 
                 (SELECT COUNT(*) FROM properties WHERE user_id = u.id) as property_count
          FROM users u 
          WHERE u.role = 'user'
          ORDER BY u.created_at DESC";

try {
    $stmt = $conn->query($query);
    $users = $stmt->fetchAll();
} catch(PDOException $e) {
    $users = [];
    $_SESSION['error'] = "Error fetching users: " . $e->getMessage();
}

$page_title = 'Manage Users';
include 'includes/admin-header.php';
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manage Users</h1>
        <ol class="breadcrumb mb-0 bg-transparent">
            <li class="breadcrumb-item"><a href="/realestate/admin/index.php" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item active">Users</li>
        </ol>
    </div>
    
    <div class="card shadow border-0 mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold" style="color: var(--primary-color);"><i class="bi bi-people me-2"></i> User Management</h6>
            <div>
                <span class="badge bg-primary rounded-pill"><?php echo count($users); ?> Total Users</span>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($users)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-people" style="font-size: 3rem; color: var(--secondary-color);"></i>
                    <p class="mt-3 text-muted">No users found in the system.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="fw-bold">User</th>
                                <th class="fw-bold">Email</th>
                                <th class="fw-bold">Properties</th>
                                <th class="fw-bold">Status</th>
                                <th class="fw-bold">Joined</th>
                                <th class="fw-bold text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3" 
                                                 style="width: 40px; height: 40px; background-color: var(--light-color);">
                                                <span class="fw-bold" style="color: var(--primary-color);">
                                                    <?php echo substr(htmlspecialchars($user['username']), 0, 1); ?>
                                                </span>
                                            </div>
                                            <div>
                                                <div class="fw-bold"><?php echo htmlspecialchars($user['username']); ?></div>
                                                <div class="small text-muted">User ID: <?php echo $user['id']; ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <span class="badge rounded-pill" style="background-color: var(--primary-color);">
                                            <?php echo $user['property_count']; ?> properties
                                        </span>
                                    </td>
                                    <td>
                                        <?php $status = isset($user['status']) ? $user['status'] : 'inactive'; ?>
                                        <span class="badge rounded-pill bg-<?php echo $status === 'active' ? 'success' : 'danger'; ?>">
                                            <?php echo ucfirst($status); ?>
                                        </span>
                                    </td>
                                    <td><?php echo format_date($user['created_at']); ?></td>
                                    <td class="text-center">
                                        <?php if ($status === 'active'): ?>
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="action" value="deactivate">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                        onclick="return confirm('Are you sure you want to deactivate this user?')">
                                                    <i class="bi bi-person-x"></i> Deactivate
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="action" value="activate">
                                                <button type="submit" class="btn btn-sm btn-outline-success">
                                                    <i class="bi bi-person-check"></i> Activate
                                                </button>
                                            </form>
                                        <?php endif; ?>
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

<?php include 'includes/admin-footer.php'; ?>