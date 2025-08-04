<?php
require_once 'config.php';

$page_title = 'Contact Us';
$extra_css = '
<style>
    .contact-icon {
        font-size: 2rem;
        color: #0d6efd;
        margin-bottom: 1rem;
    }
    .contact-info {
        padding: 2rem;
        background-color: #f8f9fa;
        border-radius: 10px;
        height: 100%;
    }
    .map-container {
        height: 300px;
        margin-top: 2rem;
    }
    .map-container iframe {
        width: 100%;
        height: 100%;
        border: none;
        border-radius: 10px;
    }
</style>';

require_once 'includes/header.php';
?>

<div class="container mt-4">
    <h1 class="text-center mb-4">Contact Us</h1>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="contact-info">
                <h3 class="mb-4">Get in Touch</h3>
                <div class="mb-4">
                    <i class="bi bi-geo-alt contact-icon"></i>
                    <h4>Address</h4>
                    <p>123 Real Estate Street<br>Istanbul, Turkey 34000</p>
                </div>
                <div class="mb-4">
                    <i class="bi bi-telephone contact-icon"></i>
                    <h4>Phone</h4>
                    <p>+90 (212) 555-0123</p>
                </div>
                <div class="mb-4">
                    <i class="bi bi-envelope contact-icon"></i>
                    <h4>Email</h4>
                    <p>info@realestate.com</p>
                </div>
                <div>
                    <i class="bi bi-clock contact-icon"></i>
                    <h4>Working Hours</h4>
                    <p>Monday - Friday: 9:00 AM - 6:00 PM<br>
                       Saturday: 10:00 AM - 4:00 PM<br>
                       Sunday: Closed</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title mb-4">Send us a Message</h3>
                    <form action="process-contact.php" method="POST">
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone">
                        </div>
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="map-container">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d192698.6201996862!2d28.871754966088876!3d41.005495809966735!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x14caa7040068086b%3A0xe1ccfe98bc01b0d0!2zxLBzdGFuYnVs!5e0!3m2!1str!2str!4v1648670567勇" allowfullscreen="" loading="lazy"></iframe>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

<?php require_once 'includes/footer.php'; ?> 