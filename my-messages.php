<?php
require_once 'config.php';
require_once 'includes/functions.php';

require_login();

try {
    $query = "SELECT pi.*, p.title as property_title, p.id as property_id 
              FROM property_inquiries pi 
              INNER JOIN properties p ON pi.property_id = p.id 
              WHERE p.user_id = ? 
              ORDER BY pi.created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([$_SESSION['user_id']]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Mark messages as read
    if (!empty($messages)) {
        $update_query = "UPDATE property_inquiries SET status = 'read' 
                        WHERE id IN (SELECT pi.id 
                                   FROM property_inquiries pi 
                                   INNER JOIN properties p ON pi.property_id = p.id 
                                   WHERE p.user_id = ? AND pi.status = 'new')";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->execute([$_SESSION['user_id']]);
    }
} catch(PDOException $e) {
    error_log("Error in my-messages.php: " . $e->getMessage());
    $messages = [];
    $_SESSION['error'] = "An error occurred while fetching your messages.";
}

$page_title = 'My Messages';
$extra_css = '
<style>
    .message-card {
        transition: transform 0.2s;
        border-left: 4px solid #0d6efd;
    }
    .message-card.unread {
        background-color: #f8f9fa;
        border-left-color: #198754;
    }
    .message-card:hover {
        transform: translateY(-2px);
    }
    .message-meta {
        font-size: 0.9rem;
        color: #6c757d;
    }
    .property-link {
        color: #0d6efd;
        text-decoration: none;
    }
    .property-link:hover {
        text-decoration: underline;
    }
</style>';

require_once 'includes/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>My Messages</h1>
        <a href="my-properties.php" class="btn btn-outline-primary">
            <i class="bi bi-building"></i> My Properties
        </a>
    </div>

    <?php if (empty($messages)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> You don't have any messages yet.
            When someone inquires about your properties, their messages will appear here.
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($messages as $message): ?>
                <div class="col-12 mb-3">
                    <div class="card message-card <?php echo $message['status'] === 'new' ? 'unread' : ''; ?>">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0">
                                    Inquiry for 
                                    <a href="property-details.php?id=<?php echo $message['property_id']; ?>" 
                                       class="property-link">
                                        <?php echo htmlspecialchars($message['property_title']); ?>
                                    </a>
                                </h5>
                                <span class="badge bg-<?php echo $message['status'] === 'new' ? 'success' : 'secondary'; ?>">
                                    <?php echo ucfirst($message['status']); ?>
                                </span>
                            </div>
                            
                            <div class="message-meta mb-2">
                                <strong>From:</strong> <?php echo htmlspecialchars($message['name']); ?> 
                                (<?php echo htmlspecialchars($message['email']); ?>)
                                <?php if (!empty($message['phone'])): ?>
                                    | <strong>Phone:</strong> <?php echo htmlspecialchars($message['phone']); ?>
                                <?php endif; ?>
                                <br>
                                <strong>Received:</strong> <?php echo format_date($message['created_at']); ?>
                            </div>
                            
                            <p class="card-text"><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                            
                            <div class="d-flex justify-content-end">
                                <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>" 
                                   class="btn btn-primary">
                                    <i class="bi bi-reply"></i> Reply via Email
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?> 