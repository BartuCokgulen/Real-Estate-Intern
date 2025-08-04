<?php
require_once '../config.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    $_SESSION['error'] = "Please login to access the contact page.";
    header('Location: ../login.php');
    exit;
}

// Handle contact form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    
    if (empty($subject) || empty($message)) {
        $_SESSION['error'] = "Please fill in all fields.";
    } else {
        try {
            $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([
                $_SESSION['username'],
                isset($_SESSION['email']) ? $_SESSION['email'] : $_SESSION['username'] . '@example.com',
                $subject,
                $message
            ]);
            $_SESSION['success'] = "Your message has been sent successfully.";
        } catch(PDOException $e) {
            $_SESSION['error'] = "Error sending message: " . $e->getMessage();
        }
    }
    
    header('Location: contact.php');
    exit;
}

// Get user's message history
try {
    $stmt = $conn->prepare("SELECT * FROM contact_messages WHERE email = ? ORDER BY created_at DESC");
    $stmt->execute([isset($_SESSION['email']) ? $_SESSION['email'] : $_SESSION['username'] . '@example.com']);
    $messages = $stmt->fetchAll();
} catch(PDOException $e) {
    $messages = [];
    $_SESSION['error'] = "Error fetching messages: " . $e->getMessage();
}

$page_title = 'Contact Support';
include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <!-- User Info Card -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Contact Support</h5>
                    <p class="card-text">
                        <strong>Total Messages:</strong> <?php echo count($messages); ?><br>
                        <strong>Last Message:</strong> <?php echo !empty($messages) ? format_date($messages[0]['created_at']) : 'No messages yet'; ?>
                    </p>
                    <p class="card-text">
                        Need help? Send us a message and we'll get back to you as soon as possible.
                    </p>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="col-md-8 mb-4">
            <div class="row">
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h6 class="card-title">Total Messages</h6>
                            <h2><?php echo count($messages); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h6 class="card-title">Response Time</h6>
                            <h2>24h</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h6 class="card-title">Support Hours</h6>
                            <h2>24/7</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Contact Form -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Send a Message</h5>
                </div>
                <div class="card-body">
                    <form action="" method="POST">
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Message History -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Message History</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($messages)): ?>
                        <div class="alert alert-info">
                            You haven't sent any messages yet.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Subject</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Message</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($messages as $message): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($message['subject']); ?></td>
                                            <td><?php echo format_date($message['created_at']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo get_message_status_color($message['status']); ?>">
                                                    <?php echo ucfirst($message['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo substr(htmlspecialchars($message['message']), 0, 50) . '...'; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 