<?php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';
require_admin();

// Handle message status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message_id'], $_POST['action'])) {
    $message_id = (int)$_POST['message_id'];
    $action = $_POST['action'];
    
    try {
        if ($action === 'mark_read') {
            $stmt = $conn->prepare("UPDATE contact_messages SET status = 'read' WHERE id = ?");
            $stmt->execute([$message_id]);
            $_SESSION['success'] = "Message marked as read.";
        } elseif ($action === 'delete') {
            $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ?");
            $stmt->execute([$message_id]);
            $_SESSION['success'] = "Message has been deleted.";
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error updating message: " . $e->getMessage();
    }
    
    header('Location: messages.php');
    exit;
}

// Get messages
$query = "SELECT * FROM contact_messages ORDER BY created_at DESC";

try {
    $stmt = $conn->query($query);
    $messages = $stmt->fetchAll();
} catch(PDOException $e) {
    $messages = [];
    $_SESSION['error'] = "Error fetching messages: " . $e->getMessage();
}

$page_title = 'Contact Messages';
include 'includes/admin-header.php';
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Contact Messages</h1>
        <ol class="breadcrumb mb-0 bg-transparent">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item active">Messages</li>
        </ol>
    </div>
    
    <div class="card shadow border-0 mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold" style="color: var(--primary-color);"><i class="bi bi-envelope me-2"></i> Message Management</h6>
            <div>
                <span class="badge bg-primary rounded-pill"><?php echo count($messages); ?> Total Messages</span>
                <?php 
                $unread_count = 0;
                foreach ($messages as $m) {
                    if ($m['status'] === 'unread') $unread_count++;
                }
                if ($unread_count > 0): 
                ?>
                <span class="badge bg-warning rounded-pill ms-2"><?php echo $unread_count; ?> Unread</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($messages)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-envelope" style="font-size: 3rem; color: var(--secondary-color);"></i>
                    <p class="mt-3 text-muted">No messages found in the system.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="fw-bold">Sender</th>
                                <th class="fw-bold">Subject</th>
                                <th class="fw-bold">Message</th>
                                <th class="fw-bold">Status</th>
                                <th class="fw-bold">Date</th>
                                <th class="fw-bold text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($messages as $message): ?>
                                <tr class="<?php echo $message['status'] === 'unread' ? 'table-warning bg-opacity-25' : ''; ?>">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3" 
                                                 style="width: 40px; height: 40px; background-color: var(--light-color);">
                                                <span class="fw-bold" style="color: var(--primary-color);">
                                                    <?php echo substr(htmlspecialchars($message['name']), 0, 1); ?>
                                                </span>
                                            </div>
                                            <div>
                                                <div class="fw-bold"><?php echo htmlspecialchars($message['name']); ?></div>
                                                <div class="small text-muted">
                                                    <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>" class="text-decoration-none">
                                                        <?php echo htmlspecialchars($message['email']); ?>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-truncate d-inline-block" style="max-width: 200px;">
                                            <?php echo htmlspecialchars($message['subject']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" 
                                                data-bs-target="#messageModal<?php echo $message['id']; ?>">
                                            <i class="bi bi-eye"></i> View Message
                                        </button>
                                        
                                        <!-- Message Modal -->
                                        <div class="modal fade" id="messageModal<?php echo $message['id']; ?>" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header" style="background-color: var(--light-color);">
                                                        <h5 class="modal-title fw-bold">Message from <?php echo htmlspecialchars($message['name']); ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <div class="d-flex align-items-center mb-3">
                                                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3" 
                                                                     style="width: 50px; height: 50px; background-color: var(--light-color);">
                                                                    <span class="fw-bold" style="color: var(--primary-color); font-size: 1.2rem;">
                                                                        <?php echo substr(htmlspecialchars($message['name']), 0, 1); ?>
                                                                    </span>
                                                                </div>
                                                                <div>
                                                                    <div class="fw-bold fs-5"><?php echo htmlspecialchars($message['name']); ?></div>
                                                                    <div class="text-muted">
                                                                        <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>" class="text-decoration-none">
                                                                            <?php echo htmlspecialchars($message['email']); ?>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="card mb-3">
                                                                <div class="card-header bg-light">
                                                                    <strong>Subject:</strong> <?php echo htmlspecialchars($message['subject']); ?>
                                                                </div>
                                                                <div class="card-body">
                                                                    <div class="message-content">
                                                                        <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                                                                    </div>
                                                                </div>
                                                                <div class="card-footer text-muted">
                                                                    <small>Received: <?php echo format_date($message['created_at']); ?></small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        <?php if ($message['status'] === 'unread'): ?>
                                                            <form method="post" class="d-inline">
                                                                <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                                                <input type="hidden" name="action" value="mark_read">
                                                                <button type="submit" class="btn btn-primary">Mark as Read</button>
                                                            </form>
                                                        <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge <?php echo $message['status'] === 'unread' ? 'bg-warning' : 'bg-success'; ?> rounded-pill">
                                        <?php echo ucfirst($message['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted small">
                                        <?php echo format_date($message['created_at']); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" 
                                                data-bs-target="#messageModal<?php echo $message['id']; ?>">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        
                                        <?php if ($message['status'] === 'unread'): ?>
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                                <input type="hidden" name="action" value="mark_read">
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Mark as Read">
                                                    <i class="bi bi-check-circle"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this message?');">
                                            <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Message">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
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
</div>

<?php include 'includes/admin-footer.php'; ?>