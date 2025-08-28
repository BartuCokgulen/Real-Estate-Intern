<?php
require_once 'config.php';
require_once 'includes/functions.php';

$page_title = 'Home';
$extra_css = '
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
<link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
<style>
    .hero-section {
        position: relative;
        height: 85vh;
        min-height: 600px;
        overflow: hidden;
        background-color: #2c3e50;
    }
    
    .hero-bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(44, 62, 80, 0.8), rgba(52, 152, 219, 0.7)), url("https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1773&q=80");
        background-size: cover;
        background-position: center;
        z-index: 1;
    }
    
    .hero-content {
        position: relative;
        z-index: 2;
        height: 100%;
        display: flex;
        align-items: center;
    }
    
    .hero-text h1 {
        font-size: 3.5rem;
        font-weight: 800;
        margin-bottom: 1.5rem;
        color: #ffffff;
        text-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    
    .hero-text p {
        font-size: 1.25rem;
        margin-bottom: 2rem;
        color: rgba(255,255,255,0.9);
        max-width: 600px;
    }
    
    .hero-search {
        background: rgba(255, 255, 255, 0.95);
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }
    
    .hero-btn {
        padding: 12px 30px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: all 0.3s ease;
        border-radius: 50px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.15);
    }
    
    .hero-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    }

    .section-title {
        position: relative;
        margin-bottom: 3rem;
        font-weight: 700;
    }
    
    .section-title:after {
        content: "";
        position: absolute;
        left: 50%;
        bottom: -15px;
        width: 80px;
        height: 3px;
        background: #2c3e50;
        transform: translateX(-50%);
    }
    
    .property-card {
        border: none;
        border-radius: 15px;
        overflow: hidden;
        transition: all 0.3s ease;
        margin-bottom: 30px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        height: 100%;
    }

    .property-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.12);
    }

    .property-img-container {
        position: relative;
        overflow: hidden;
        height: 220px;
    }

    .property-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    
    .property-card:hover .property-img {
        transform: scale(1.1);
    }

    .property-price {
        position: absolute;
        bottom: 15px;
        right: 15px;
        background: rgba(44, 62, 80, 0.9);
        color: white;
        padding: 8px 20px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 1.1rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    }

    .property-type {
        position: absolute;
        top: 15px;
        left: 15px;
        background: rgba(255, 193, 7, 0.9);
        color: #2c3e50;
        padding: 5px 15px;
        border-radius: 50px;
        font-weight: 600;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .property-info {
        padding: 20px;
    }
    
    .property-title {
        font-weight: 700;
        margin-bottom: 10px;
        color: #2c3e50;
    }
    
    .property-location {
        color: #6c757d;
        margin-bottom: 15px;
    }
    
    .property-features {
        display: flex;
        justify-content: space-between;
        padding-top: 15px;
        border-top: 1px solid #eee;
        color: #6c757d;
    }
    
    .property-feature {
        display: flex;
        align-items: center;
    }
    
    .property-feature i {
        margin-right: 5px;
        color: #2c3e50;
    }

    .features-section {
        background-color: #f8f9fa;
        position: relative;
        overflow: hidden;
    }
    
    .features-section::before {
        content: "";
        position: absolute;
        top: -100px;
        right: -100px;
        width: 300px;
        height: 300px;
        border-radius: 50%;
        background-color: rgba(44, 62, 80, 0.05);
        z-index: 1;
    }
    
    .features-section::after {
        content: "";
        position: absolute;
        bottom: -100px;
        left: -100px;
        width: 300px;
        height: 300px;
        border-radius: 50%;
        background-color: rgba(44, 62, 80, 0.05);
        z-index: 1;
    }
    
    .feature-box {
        text-align: center;
        padding: 40px 30px;
        background: #ffffff;
        border-radius: 15px;
        transition: all 0.3s ease;
        position: relative;
        z-index: 2;
        height: 100%;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }

    .feature-box:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.1);
    }

    .feature-icon {
        font-size: 3rem;
        width: 80px;
        height: 80px;
        line-height: 80px;
        margin: 0 auto 25px;
        color: #ffffff;
        background: #2c3e50;
        border-radius: 50%;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }
    
    .feature-box:hover .feature-icon {
        background: #ffc107;
        color: #2c3e50;
        transform: rotateY(180deg);
    }
    
    .feature-title {
        font-weight: 700;
        margin-bottom: 15px;
        color: #2c3e50;
    }
    
    .feature-text {
        color: #6c757d;
    }

    .cta-section {
        background: linear-gradient(135deg, #2c3e50, #3498db);
        position: relative;
        overflow: hidden;
    }
    
    .cta-section::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: url("https://images.unsplash.com/photo-1560520031-3a4dc4e9de0c?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1773&q=80") center/cover no-repeat;
        opacity: 0.1;
    }
    
    .cta-content {
        position: relative;
        z-index: 2;
    }
    
    .cta-title {
        font-weight: 800;
        margin-bottom: 20px;
        color: #ffffff;
    }
    
    .cta-text {
        color: rgba(255,255,255,0.9);
        font-size: 1.1rem;
        margin-bottom: 0;
    }
    
    .cta-btn {
        padding: 12px 30px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        background-color: #ffc107;
        color: #2c3e50;
        border: none;
        border-radius: 50px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        transition: all 0.3s ease;
    }
    
    .cta-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.3);
        background-color: #ffca2c;
    }

    .stats-section {
        background: linear-gradient(135deg, rgba(44, 62, 80, 0.95), rgba(52, 152, 219, 0.9)), url("https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1773&q=80");
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        color: white;
        padding: 60px 0;
        position: relative;
    }
    
.stats-section::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: url("data:image/svg+xml,%3Csvg%20width%3D%2260%22%20height%3D%2260%22%20viewBox%3D%220%200%2060%2060%22%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%3E%3Cg%20fill%3D%22none%22%20fill-rule%3D%22evenodd%22%3E%3Cg%20fill%3D%22%23ffffff%22%20fill-opacity%3D%220.05%22%3E%3Cpath%20d%3D%22M36%2034v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6%2034v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6%204V0H4v4H0v2h4v4h2V6h4V4H6z%22/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}


    .stat-box {
        text-align: center;
        padding: 30px 20px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 15px;
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
        transition: all 0.3s ease;
        height: 100%;
    }
    
    .stat-box:hover {
        transform: translateY(-10px);
        background: rgba(255, 255, 255, 0.15);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }
    
    .stat-icon {
        font-size: 2.5rem;
        margin-bottom: 20px;
        color: #ffc107;
    }

    .stat-number {
        font-size: 3rem;
        font-weight: 800;
        margin-bottom: 10px;
        background: linear-gradient(to right, #ffffff, #ffc107);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        display: inline-block;
    }
    
    .stat-title {
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 10px;
        color: #ffffff;
    }
    
    .stat-text {
        color: rgba(255, 255, 255, 0.8);
        font-size: 0.9rem;
    }
</style>';

$featured_query = "SELECT p.*, 
                         (SELECT image_url FROM property_images WHERE property_id = p.id AND is_primary = 1 LIMIT 1) as primary_image 
                  FROM properties p 
                  WHERE p.status = 'active' 
                  ORDER BY p.created_at DESC 
                  LIMIT 6";
$featured_stmt = $conn->query($featured_query);
$featured_properties = $featured_stmt->fetchAll(PDO::FETCH_ASSOC);

require_once 'includes/header.php';
?>

<section class="hero-section">
    <div class="hero-bg"></div>
    <div class="hero-content">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right" data-aos-duration="1000">
                    <div class="hero-text">
                        <h1>Find Your Dream Property</h1>
                        <p>Discover the perfect property that matches your lifestyle and preferences. Our extensive collection of premium properties ensures you'll find exactly what you're looking for.</p>
                        <a href="properties.php" class="btn btn-warning hero-btn">Browse Properties <i class="bi bi-arrow-right ms-2"></i></a>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left" data-aos-duration="1000" data-aos-delay="200">
                    <div class="hero-search p-4 mt-4 mt-lg-0">
                        <h3 class="mb-4 text-center">Find Your Perfect Home</h3>
                        <form action="properties.php" method="get">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="propertyType" class="form-label">Property Type</label>
                                    <select class="form-select" id="propertyType" name="type">
                                        <option value="">All Types</option>
                                        <option value="Apartment">Apartment</option>
                                        <option value="House">House</option>
                                        <option value="Villa">Villa</option>
                                        <option value="Commercial">Commercial</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="location" class="form-label">Location</label>
                                    <input type="text" class="form-control" id="location" name="location" placeholder="Any Location">
                                </div>
                                <div class="col-md-6">
                                    <label for="minPrice" class="form-label">Min Price</label>
                                    <select class="form-select" id="minPrice" name="min_price">
                                        <option value="">No Min</option>
                                        <option value="50000">$50,000</option>
                                        <option value="100000">$100,000</option>
                                        <option value="200000">$200,000</option>
                                        <option value="500000">$500,000</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="maxPrice" class="form-label">Max Price</label>
                                    <select class="form-select" id="maxPrice" name="max_price">
                                        <option value="">No Max</option>
                                        <option value="100000">$100,000</option>
                                        <option value="300000">$300,000</option>
                                        <option value="500000">$500,000</option>
                                        <option value="1000000">$1,000,000</option>
                                    </select>
                                </div>
                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-dark w-100">Search Properties <i class="bi bi-search ms-2"></i></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container" data-aos="fade-up" data-aos-duration="1000">
        <h2 class="text-center section-title">Featured Properties</h2>
        <p class="text-center text-muted mb-5">Explore our handpicked selection of premium properties</p>
        
        <div class="row">
            <?php foreach ($featured_properties as $property): ?>
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="<?php echo 100 * ($loop_index ?? 0); ?>">
                    <div class="property-card">
                        <div class="property-img-container">
                            <img src="<?php echo htmlspecialchars($property['primary_image'] ?? 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1773&q=80'); ?>" 
                                 class="property-img" alt="<?php echo htmlspecialchars($property['title']); ?>">
                            <div class="property-price">$<?php echo number_format($property['price']); ?></div>
                            <div class="property-type"><?php echo htmlspecialchars($property['type']); ?></div>
                        </div>
                        <div class="property-info">
                            <h5 class="property-title"><?php echo htmlspecialchars($property['title']); ?></h5>
                            <p class="property-location">
                                <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($property['location']); ?>
                            </p>
                            <div class="property-features">
                                <div class="property-feature">
                                    <i class="bi bi-house-door"></i> <?php echo htmlspecialchars($property['bedrooms'] ?? '3'); ?> Beds
                                </div>
                                <div class="property-feature">
                                    <i class="bi bi-water"></i> <?php echo htmlspecialchars($property['bathrooms'] ?? '2'); ?> Baths
                                </div>
                                <div class="property-feature">
                                    <i class="bi bi-rulers"></i> <?php echo htmlspecialchars($property['area'] ?? '1500'); ?> sqft
                                </div>
                            </div>
                            <div class="mt-4 text-center">
                                <a href="property-details.php?id=<?php echo $property['id']; ?>" class="btn btn-outline-dark w-100">View Details <i class="bi bi-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php $loop_index = isset($loop_index) ? $loop_index + 1 : 1; ?>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-5" data-aos="fade-up">
            <a href="properties.php" class="btn btn-warning hero-btn">View All Properties <i class="bi bi-arrow-right ms-2"></i></a>
        </div>
    </div>
</section>

<section class="stats-section py-5">
    <div class="container" data-aos="fade-up" data-aos-duration="1000">
        <div class="row g-4">
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="stat-box">
                    <div class="stat-icon">
                        <i class="bi bi-house-door"></i>
                    </div>
                    <div class="stat-number">1500+</div>
                    <h5 class="stat-title">Properties Listed</h5>
                    <p class="stat-text">Extensive selection of properties</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="stat-box">
                    <div class="stat-icon">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="stat-number">500+</div>
                    <h5 class="stat-title">Happy Clients</h5>
                    <p class="stat-text">Satisfied homeowners and investors</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="stat-box">
                    <div class="stat-icon">
                        <i class="bi bi-building"></i>
                    </div>
                    <div class="stat-number">50+</div>
                    <h5 class="stat-title">Cities Covered</h5>
                    <p class="stat-text">Wide geographical presence</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="400">
                <div class="stat-box">
                    <div class="stat-icon">
                        <i class="bi bi-award"></i>
                    </div>
                    <div class="stat-number">15+</div>
                    <h5 class="stat-title">Years Experience</h5>
                    <p class="stat-text">Industry expertise and knowledge</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="features-section py-5">
    <div class="container" data-aos="fade-up" data-aos-duration="1000">
        <h2 class="text-center section-title">Why Choose Us</h2>
        <p class="text-center text-muted mb-5">We provide exceptional service with a focus on quality and client satisfaction</p>
        
        <div class="row g-4">
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="feature-box">
                    <div class="feature-icon">
                        <i class="bi bi-house-check"></i>
                    </div>
                    <h4 class="feature-title">Verified Properties</h4>
                    <p class="feature-text">All our properties undergo a thorough verification process to ensure authenticity and quality, giving you complete peace of mind during your property search.</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="feature-box">
                    <div class="feature-icon">
                        <i class="bi bi-people"></i>
                    </div>
                    <h4 class="feature-title">Expert Agents</h4>
                    <p class="feature-text">Our team of professional agents brings years of industry experience and local market knowledge to help you find the perfect property that meets all your requirements.</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="feature-box">
                    <div class="feature-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h4 class="feature-title">Secure Transactions</h4>
                    <p class="feature-text">Your financial security is our priority. We implement the highest security standards to ensure all transactions are protected and processed safely.</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="400">
                <div class="feature-box">
                    <div class="feature-icon">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <h4 class="feature-title">Market Analysis</h4>
                    <p class="feature-text">Stay ahead with our comprehensive market analysis and insights, helping you make informed decisions about property investments and timing.</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="500">
                <div class="feature-box">
                    <div class="feature-icon">
                        <i class="bi bi-headset"></i>
                    </div>
                    <h4 class="feature-title">24/7 Support</h4>
                    <p class="feature-text">Our dedicated customer support team is available around the clock to address your queries and provide assistance whenever you need it.</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="600">
                <div class="feature-box">
                    <div class="feature-icon">
                        <i class="bi bi-hand-thumbs-up"></i>
                    </div>
                    <h4 class="feature-title">Client Satisfaction</h4>
                    <p class="feature-text">We pride ourselves on our high client satisfaction rates, with a commitment to excellence that ensures a positive experience for every client.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="cta-section py-5">
    <div class="container" data-aos="fade-up" data-aos-duration="1000">
        <div class="cta-content">
            <div class="row align-items-center">
                <div class="col-lg-8 col-md-7">
                    <h2 class="cta-title" data-aos="fade-up" data-aos-delay="100">Ready to Find Your Dream Home?</h2>
                    <p class="cta-text" data-aos="fade-up" data-aos-delay="200">Take the first step towards homeownership today. Our extensive property listings and expert agents are ready to help you find the perfect match.</p>
                </div>
                <div class="col-lg-4 col-md-5 text-md-end" data-aos="fade-up" data-aos-delay="300">
                    <a href="properties.php" class="cta-btn">
                        <span>Browse Properties</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
require_once 'includes/footer.php';
?>