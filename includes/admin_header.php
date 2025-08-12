<?php
require_once __DIR__ . '/functions.php';
require_admin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Admin Dashboard'; ?> - Real Estate</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        .stat-card {
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .content-card {
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            border: none;
            transition: transform 0.2s;
        }
        .content-card:hover {
            transform: translateY(-5px);
        }
    </style>
    <?php if (isset($extra_css)) echo $extra_css; ?>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/realestate/admin">Admin Dashboard</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="adminNavbar">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>" 
                           href="/realestate/admin/index.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'properties.php' ? 'active' : ''; ?>" 
                           href="/realestate/admin/properties.php">Properties</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : ''; ?>" 
                           href="/realestate/admin/users.php">Users</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'inquiries.php' ? 'active' : ''; ?>" 
                           href="/realestate/admin/inquiries.php">
                            Inquiries
                            <?php 
                            $new_inquiries = count_new_inquiries($conn);
                            if ($new_inquiries > 0): 
                            ?>
                                <span class="badge bg-danger"><?php echo $new_inquiries; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'contact-messages.php' ? 'active' : ''; ?>" 
                           href="/realestate/admin/contact-messages.php">
                            Messages
                            <?php 
                            $new_messages = count_new_messages($conn);
                            if ($new_messages > 0): 
                            ?>
                                <span class="badge bg-danger"><?php echo $new_messages; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="/realestate" target="_blank">View Site</a>
                    </li>
                    <li class="nav-item">
                        <span class="nav-link">Welcome, <?php echo get_user_name(); ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/realestate/logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="container mt-3">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="container mt-3">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>
</body>
</html> 