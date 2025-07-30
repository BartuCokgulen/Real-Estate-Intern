<?php
session_start();

require_once 'config.php';
require_once 'includes/functions.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean_input($_POST['username']);
    $password = $_POST['password'];
    
    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['is_admin'] = ($user['role'] === 'admin');
            
            $_SESSION['success'] = "Welcome back, " . htmlspecialchars($user['username']) . "!";
            
            if ($user['role'] === 'admin') {
                header('Location: admin/index.php');
            } else {
                header('Location: index.php');
            }
            exit;
        } else {
            $_SESSION['error'] = "Invalid username or password.";
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error during login.";
    }
}

$page_title = 'Login';
require_once 'includes/header.php';
?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-lg rounded-3 overflow-hidden">
                <div class="card-header bg-primary text-white text-center py-4" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%) !important;">
                    <h4 class="mb-0"><i class="bi bi-person-circle me-2"></i>Welcome Back</h4>
                </div>
                <div class="card-body p-4">
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" class="mt-3">
                        <div class="mb-4">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-person"></i>
                                </span>
                                <input type="text" class="form-control border-start-0 ps-0" id="username" name="username" placeholder="Enter your username" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input type="password" class="form-control border-start-0 ps-0" id="password" name="password" placeholder="Enter your password" required>
                            </div>
                        </div>
                        <div class="mb-4 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                            <a href="#" class="float-end text-decoration-none">Forgot password?</a>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary py-2" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%) !important; border: none;">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Login
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-footer bg-light text-center py-3">
                    <p class="mb-0">Don't have an account? <a href="register.php" class="text-primary fw-bold">Register here</a></p>
                </div>
            </div>
            <div class="text-center mt-4 text-muted">
                <small>&copy; <?php echo date('Y'); ?> RealEstate. All rights reserved.</small>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>