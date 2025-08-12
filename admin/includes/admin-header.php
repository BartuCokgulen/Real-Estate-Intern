<?php
require_once __DIR__ . '/../../includes/functions.php';
init_session();

// Check if user is admin
if (!is_admin()) {
    $_SESSION['error'] = "You don't have permission to access this page.";
    header('Location: /realestate/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - Admin Panel' : 'Admin Panel'; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #4e73df;
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
            background-color: #f8f9fc;
        }
        
        .sidebar {
            min-height: calc(100vh - 56px);
            background: linear-gradient(180deg, var(--primary-color) 0%, #224abe 100%);
            padding: 20px 0;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            margin-bottom: 5px;
            border-radius: 0;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .sidebar .nav-link:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
            padding-left: 25px;
        }
        
        .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.2);
            border-left: 4px solid #fff;
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
            font-size: 1.1rem;
        }
        
        .content {
            padding: 20px;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.2rem;
            letter-spacing: 0.5px;
        }
        
        .card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
            padding: 1rem 1.25rem;
            font-weight: 600;
        }
        
        .table {
            color: #5a5c69;
        }
        
        .btn {
            border-radius: 0.35rem;
            padding: 0.375rem 0.75rem;
            font-weight: 500;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }
        
        .btn-info {
            background-color: var(--info-color);
            border-color: var(--info-color);
        }
        
        .btn-warning {
            background-color: var(--warning-color);
            border-color: var(--warning-color);
        }
        
        .btn-danger {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
        }
    </style>
    
    <?php if (isset($extra_css)) echo $extra_css; ?>
</head>
<body>
    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(90deg, var(--primary-color) 0%, #224abe 100%); box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);">
        <div class="container-fluid px-4">
            <a class="navbar-brand d-flex align-items-center" href="/realestate/admin/index.php">
                <i class="bi bi-building me-2" style="font-size: 1.4rem;"></i>
                <span>RealEstate Admin</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link px-3 <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active fw-bold' : ''; ?>" href="/realestate/admin/index.php">
                            <i class="bi bi-speedometer2 me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 <?php echo basename($_SERVER['PHP_SELF']) === 'properties.php' ? 'active fw-bold' : ''; ?>" href="/realestate/admin/properties.php">
                            <i class="bi bi-houses me-1"></i> Properties
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 <?php echo basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active fw-bold' : ''; ?>" href="/realestate/admin/users.php">
                            <i class="bi bi-people me-1"></i> Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 <?php echo basename($_SERVER['PHP_SELF']) === 'messages.php' ? 'active fw-bold' : ''; ?>" href="/realestate/admin/messages.php">
                            <i class="bi bi-envelope me-1"></i> Messages
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link px-3" href="/realestate/index.php" target="_blank">
                            <i class="bi bi-house"></i> View Site
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="rounded-circle bg-light text-primary d-flex align-items-center justify-content-center me-2" 
                                 style="width: 32px; height: 32px; font-weight: bold;">
                                <?php echo substr(htmlspecialchars($_SESSION['username']), 0, 1); ?>
                            </div>
                            <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            <li><a class="dropdown-item" href="/realestate/logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i> Logout
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">

                    
                    <div class="px-3 mb-4 d-none d-md-block">
                        <div class="bg-white bg-opacity-10 rounded p-3 text-white">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-white text-primary d-flex align-items-center justify-content-center me-3" 
                                     style="width: 40px; height: 40px; font-weight: bold;">
                                    <?php echo substr(htmlspecialchars($_SESSION['username']), 0, 1); ?>
                                </div>
                                <div>
                                    <div class="fw-bold"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                                    <div class="small opacity-75">Administrator</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <h6 class="sidebar-heading px-3 mt-4 mb-2 text-white opacity-75 text-uppercase fw-bold small">
                        <i class="bi bi-gear-fill me-2"></i> Management
                    </h6>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>" 
                               href="/realestate/admin/index.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'properties.php' ? 'active' : ''; ?>" 
                               href="/realestate/admin/properties.php">
                                <i class="bi bi-houses"></i> Properties
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : ''; ?>" 
                               href="/realestate/admin/users.php">
                                <i class="bi bi-people"></i> Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'messages.php' ? 'active' : ''; ?>" 
                               href="/realestate/admin/messages.php">
                                <i class="bi bi-envelope"></i> Messages
                            </a>
                        </li>
                    </ul>
                    
                    <h6 class="sidebar-heading px-3 mt-4 mb-2 text-white opacity-75 text-uppercase fw-bold small">
                        <i class="bi bi-link-45deg me-2"></i> Quick Links
                    </h6>
                    
                    <ul class="nav flex-column mb-4">
                        <li class="nav-item">
                            <a class="nav-link" href="/realestate/index.php" target="_blank">
                                <i class="bi bi-house"></i> View Website
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/realestate/logout.php">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                        <?php 
                        echo $_SESSION['success'];
                        unset($_SESSION['success']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                        <?php 
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>