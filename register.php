<?php
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Please fill in all fields.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        try {
            $check_query = "SELECT id FROM users WHERE username = ? OR email = ?";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->execute([$username, $email]);
            
            if ($check_stmt->fetch()) {
                $error = "Username or email already exists.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $query = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->execute([$username, $email, $hashed_password]);
                
                $success = "Registration successful! You can now login.";
            }
        } catch(PDOException $e) {
            $error = "An error occurred. Please try again later.";
        }
    }
}

$page_title = 'Register';
require_once 'includes/header.php';
?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-lg rounded-3 overflow-hidden">
                <div class="card-header bg-primary text-white text-center py-4" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%) !important;">
                    <h4 class="mb-0"><i class="bi bi-person-plus-fill me-2"></i>Create Account</h4>
                </div>
                <div class="card-body p-4">
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i><?php echo $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            <div class="mt-2">
                                <a href="login.php" class="btn btn-sm btn-success">
                                    <i class="bi bi-box-arrow-in-right me-1"></i>Go to Login
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!$success): ?>
                    <form method="POST" action="" class="mt-3">
                        <div class="mb-4">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-person"></i>
                                </span>
                                <input type="text" class="form-control border-start-0 ps-0" id="username" name="username" placeholder="Choose a username" required>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input type="email" class="form-control border-start-0 ps-0" id="email" name="email" placeholder="Your email address" required>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input type="password" class="form-control border-start-0 ps-0" id="password" name="password" placeholder="Create a password" required>
                            </div>
                            <div class="form-text"><i class="bi bi-info-circle me-1"></i>Password must be at least 6 characters long.</div>
                        </div>
                        
                        <div class="mb-4">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-shield-lock"></i>
                                </span>
                                <input type="password" class="form-control border-start-0 ps-0" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                            </div>
                        </div>
                        
                        <div class="mb-4 form-check">
                            <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                            <label class="form-check-label" for="terms">I agree to the <a href="#" class="text-decoration-none">Terms of Service</a> and <a href="#" class="text-decoration-none">Privacy Policy</a></label>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary py-2" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%) !important; border: none;">
                                <i class="bi bi-person-plus me-2"></i>Create Account
                            </button>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-light text-center py-3">
                    <p class="mb-0">Already have an account? <a href="login.php" class="text-primary fw-bold">Login here</a></p>
                </div>
            </div>
            <div class="text-center mt-4 text-muted">
                <small>&copy; <?php echo date('Y'); ?> RealEstate. All rights reserved.</small>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>