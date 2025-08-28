<?php
require_once __DIR__ . '/functions.php';
init_session();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Real Estate'; ?></title>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            font-family: 'Poppins', sans-serif;
        }
        main {
            flex: 1;
        }
        .custom-navbar {
            background-color: #2c3e50;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            padding: 15px 0;
            transition: all 0.3s ease;
        }
        .custom-navbar.scrolled {
            padding: 10px 0;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            background-color: rgba(44, 62, 80, 0.98);
        }
        .navbar-brand {
            font-weight: 700;
            font-size: 1.6rem;
            color: #ffffff;
            letter-spacing: -0.5px;
        }
        .navbar-brand span {
            color: #ffc107;
            font-weight: 800;
        }
        .navbar-nav .nav-link {
            position: relative;
            color: rgba(255, 255, 255, 0.85);
            font-weight: 500;
            padding: 10px 15px;
            margin: 0 5px;
            transition: all 0.3s ease;
        }
        .navbar-nav .nav-link:hover, .navbar-nav .nav-link.active {
            color: #ffffff;
        }
        .navbar-nav .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            background-color: #ffc107;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            transition: width 0.3s ease;
        }
        .navbar-nav .nav-link:hover::after, .navbar-nav .nav-link.active::after {
            width: 70%;
        }
        .navbar-nav .badge {
            position: absolute;
            top: 0;
            right: -5px;
            font-size: 0.7em;
            background-color: #e74c3c;
        }
        .navbar-toggler {
            border: none;
            padding: 0;
            width: 30px;
            height: 30px;
            position: relative;
            background-color: transparent;
        }
        .navbar-toggler:focus {
            box-shadow: none;
        }
        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 0.85%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }
        .dropdown-menu {
            min-width: 220px;
            border: none;
            border-radius: 8px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            padding: 10px;
            margin-top: 15px;
            background-color: #34495e;
        }
        .dropdown-menu .dropdown-item {
            padding: 10px 15px;
            border-radius: 5px;
            transition: all 0.2s ease;
            color: rgba(255, 255, 255, 0.85);
        }
        .dropdown-menu .dropdown-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }
        .dropdown-menu .dropdown-item i {
            width: 20px;
            text-align: center;
            margin-right: 10px;
            color: #ffc107;
        }
        .dropdown-menu .dropdown-divider {
            border-top-color: rgba(255, 255, 255, 0.1);
        }
        .user-welcome {
            color: rgba(255, 255, 255, 0.85);
            padding: .5rem 1rem;
            margin-bottom: 0;
            font-weight: 500;
        }
        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background-color: #ffc107;
            color: #2c3e50;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 5px;
            font-weight: 600;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }
        .dropdown-toggle:hover .user-avatar {
            transform: scale(1.05);
        }
        .property-card {
            transition: transform 0.2s;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            border: none;
        }
        .property-card:hover {
            transform: translateY(-5px);
        }
        .favorite-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 10;
            background: rgba(255,255,255,0.9);
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        .favorite-btn:hover {
            background: rgba(255,255,255,1);
            transform: scale(1.1);
        }
        .favorite-btn.active {
            color: red;
        }
        

    </style>
    
    <?php if (isset($extra_css)) echo $extra_css; ?>
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg custom-navbar">
            <div class="container">
                <a class="navbar-brand" href="/">Real<span>Estate</span></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav mx-auto">
                        <li class="nav-item">
                            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>" href="/"><i class="bi bi-house-door me-1"></i> Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'properties.php') ? 'active' : ''; ?>" href="/properties.php"><i class="bi bi-building me-1"></i> Properties</a>
                        </li>
                        <?php if (is_logged_in()): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>" href="/user/dashboard.php"><i class="bi bi-speedometer2 me-1"></i> Dashboard</a>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'contact.php') ? 'active' : ''; ?>" href="/contact.php"><i class="bi bi-chat-dots me-1"></i> Contact</a>
                        </li>
                        <?php if (is_admin()): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) ? 'active' : ''; ?>" href="/admin/"><i class="bi bi-gear me-1"></i> Admin Panel</a>
                        </li>
                        <?php endif; ?>
                    </ul>
                    <ul class="navbar-nav ms-auto">
                        <?php if (is_logged_in()): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <div class="user-avatar">
                                        <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                                    </div>
                                    <span class="d-none d-md-inline"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <li><a class="dropdown-item" href="/user/dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                                    <li><a class="dropdown-item" href="/favorites.php"><i class="bi bi-heart"></i> Favorites</a></li>
                                    <li><a class="dropdown-item" href="/my-messages.php"><i class="bi bi-envelope"></i> Messages</a></li>
                                    <li><a class="dropdown-item" href="/my-properties.php"><i class="bi bi-houses"></i> My Properties</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/login.php"><i class="bi bi-box-arrow-in-right me-1"></i> Login</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/register.php"><i class="bi bi-person-plus me-1"></i> Register</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    
    <main>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
    
    <?php if (isset($extra_js)) echo $extra_js; ?>

    <script>
        $(document).ready(function() {
            $('.owl-carousel').owlCarousel({
                loop: true,
                margin: 20,
                nav: true,
                autoplay: true,
                autoplayTimeout: 5000,
                autoplayHoverPause: true,
                responsive: {
                    0: {
                        items: 1
                    },
                    768: {
                        items: 2
                    },
                    992: {
                        items: 3
                    }
                }
            });
            
            $(window).scroll(function() {
                if ($(this).scrollTop() > 50) {
                    $('.custom-navbar').addClass('scrolled');
                } else {
                    $('.custom-navbar').removeClass('scrolled');
                }
            });
            
            var currentLocation = window.location.pathname;
            $('.navbar-nav .nav-link').each(function() {
                var linkHref = $(this).attr('href');
                if (currentLocation.indexOf(linkHref) !== -1 && linkHref !== '/') {
                    $(this).addClass('active');
                } else if (currentLocation === '/' && linkHref === '/') {
                    $(this).addClass('active');
                }
            });
            
            AOS.init({
                duration: 800,
                easing: 'ease-in-out',
                once: true,
                mirror: false
            });
        });
    </script>