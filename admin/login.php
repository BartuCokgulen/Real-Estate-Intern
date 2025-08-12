<?php
session_start();
require_once '../config.php';

if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        $query = "SELECT id, username, password FROM users WHERE username = ? AND role = 'admin'";
        
        try {
            $stmt = $conn->prepare($query);
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                header('Location: index.php');
                exit();
            } else {
                $error = 'Invalid username or password';
            }
        } catch(PDOException $e) {
            $error = 'Login failed. Please try again.';
        }
    } else {
        $error = 'Please enter both username and password';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Real Estate</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4e73df;
            --primary-rgb: 78, 115, 223;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --light-color: #f8f9fc;
            --dark-color: #5a5c69;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .admin-login-wrapper {
            width: 100%;
            max-width: 400px;
        }
        
        .admin-logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-color) 0%, #224abe 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #224abe 100%);
            color: white;
            text-align: center;
            padding: 1.5rem;
            border-bottom: none;
        }
        
        .input-group-text {
            background-color: transparent;
            border-right: none;
        }
        
        .form-control {
            border-left: none;
            font-size: 0.9rem;
        }
        
        .form-control:focus {
            box-shadow: none;
            border-color: #ced4da;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, #224abe 100%);
            border: none;
            padding: 0.6rem;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #4668c5 0%, #1c3ea1 100%);
        }
        
        .back-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .back-link:hover {
            color: #224abe;
        }
    </style>
</head>
<body>
    <div class="admin-login-wrapper">
        <div class="admin-logo">
            <i class="bi bi-shield-lock text-white" style="font-size: 2rem;"></i>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Admin Login</h4>
                <p class="mb-0 small">Enter your credentials to access the admin panel</p>
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
                            <span class="input-group-text">
                                <i class="bi bi-person text-muted"></i>
                            </span>
                            <input type="text" class="form-control" id="username" name="username" placeholder="Admin username" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-lock text-muted"></i>
                            </span>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Admin password" required>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Login to Dashboard
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="card-footer bg-light text-center py-3">
                <a href="../index.php" class="back-link">
                    <i class="bi bi-arrow-left me-1"></i>Back to Website
                </a>
            </div>
        </div>
        
        <div class="text-center mt-4 text-muted">
            <small>&copy; <?php echo date('Y'); ?> RealEstate Admin. All rights reserved.</small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>