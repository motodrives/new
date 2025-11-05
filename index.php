<?php
require_once 'config/config.php';

// Fetch featured products
$featuredProductsQuery = "SELECT p.*, c.name as category_name FROM products p 
                          JOIN categories c ON p.category_id = c.id 
                          WHERE p.status = 'active' AND p.featured = 1 
                          ORDER BY p.created_at DESC LIMIT 6";
$featuredProducts = $db->query($featuredProductsQuery);

// Fetch industries
$industriesQuery = "SELECT * FROM industries WHERE status = 'active' ORDER BY sort_order ASC LIMIT 8";
$industries = $db->query($industriesQuery);

// Fetch latest blog posts
$blogsQuery = "SELECT * FROM blogs WHERE status = 'published' ORDER BY created_at DESC LIMIT 3";
$blogs = $db->query($blogsQuery);

// Fetch site settings
$settingsQuery = "SELECT * FROM settings";
$settingsResult = $db->query($settingsQuery);
$settings = [];
while ($row = $settingsResult->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $settings['site_name'] ?? 'Motodrives' ?> - Industrial Drives & Automation Solutions</title>
    <meta name="description" content="<?= $settings['site_description'] ?? 'Leading manufacturer of industrial drives, motors, and automation equipment' ?>">
    <meta name="keywords" content="<?= $settings['site_keywords'] ?? 'industrial drives, motors, automation, AC drives, DC drives, servo drives' ?>">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?= $settings['site_name'] ?? 'Motodrives' ?>">
    <meta property="og:description" content="<?= $settings['site_description'] ?? 'Leading manufacturer of industrial drives, motors, and automation equipment' ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= SITE_URL ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Loading Screen -->
    <div class="loading" id="loading">
        <div class="spinner"></div>
    </div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-cogs"></i>
                <?= $settings['site_name'] ?? 'Motodrives' ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="industries.php">Industries</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="gallery.php">Gallery</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="blog.php">Blog</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-content">
            <h1 class="display-4 fw-bold mb-4">Industrial Drives & Automation Solutions</h1>
            <p class="lead mb-4">Empowering industries with cutting-edge motor control technology, precision engineering, and reliable automation solutions for over 25 years.</p>
            <div class="hero-buttons">
                <a href="products.php" class="btn btn-primary btn-lg me-3">
                    <i class="fas fa-shopping-cart me-2"></i>Explore Products
                </a>
                <a href="contact.php" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-phone me-2"></i>Get Quote
                </a>
            </div>
        </div>
        <div class="hero-video">
            <video autoplay muted loop playsinline style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: 1;">
                <source src="assets/images/hero-bg.mp4" type="video/mp4">
            </video>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-award fa-3x text-primary"></i>
                        </div>
                        <h4>Quality Certified</h4>
                        <p>ISO 9001:2015 certified manufacturing processes ensuring highest quality standards.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-clock fa-3x text-primary"></i>
                        </div>
                        <h4>25+ Years Experience</h4>
                        <p>Decades of expertise in industrial automation and motor control systems.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-globe fa-3x text-primary"></i>
                        </div>
                        <h4>Global Reach</h4>
                        <p>Serving customers in 50+ countries with comprehensive support network.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Featured Products</h2>
                <p class="lead text-muted">Our top-selling industrial drives and automation solutions</p>
            </div>
            
            <div class="row g-4">
                <?php while ($product = $featuredProducts->fetch_assoc()): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="product-card animate-on-scroll">
                        <?php if ($product['image']): ?>
                        <img src="uploads/<?= $product['image'] ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="card-img-top">
                        <?php else: ?>
                        <img src="https://via.placeholder.com/400x300/007bff/ffffff?text=Product" alt="<?= htmlspecialchars($product['name']) ?>" class="card-img-top">
                        <?php endif; ?>
                        <div class="product-card-body">
                            <span class="badge bg-primary mb-2"><?= htmlspecialchars($product['category_name']) ?></span>
                            <h3><?= htmlspecialchars($product['name']) ?></h3>
                            <p><?= substr(htmlspecialchars($product['description']), 0, 100) ?>...</p>
                            <div class="mt-auto">
                                <a href="product-detail.php?id=<?= $product['id'] ?>" class="btn btn-primary">
                                    <i class="fas fa-info-circle me-2"></i>View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            
            <div class="text-center mt-5">
                <a href="products.php" class="btn btn-outline-primary btn-lg">
                    View All Products <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Industries Served -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Industries We Serve</h2>
                <p class="lead text-muted">Comprehensive automation solutions across various sectors</p>
            </div>
            
            <div class="row g-4">
                <?php while ($industry = $industries->fetch_assoc()): ?>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="industry-card animate-on-scroll">
                        <div class="industry-icon">
                            <i class="<?= $industry['icon'] ?? 'fa-industry' ?>"></i>
                        </div>
                        <h5><?= htmlspecialchars($industry['name']) ?></h5>
                        <p><?= substr(htmlspecialchars($industry['description']), 0, 80) ?>...</p>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- Counter Section -->
    <section class="counter-section">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-3 col-6 mb-4">
                    <div class="counter">
                        <h3 class="counter-number" data-target="500">0</h3>
                        <p>Products Delivered</p>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-4">
                    <div class="counter">
                        <h3 class="counter-number" data-target="50">0</h3>
                        <p>Countries Served</p>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-4">
                    <div class="counter">
                        <h3 class="counter-number" data-target="1000">0</h3>
                        <p>Happy Clients</p>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-4">
                    <div class="counter">
                        <h3 class="counter-number" data-target="25">0</h3>
                        <p>Years Experience</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Latest Blog Posts -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Latest News & Insights</h2>
                <p class="lead text-muted">Stay updated with industry trends and technological innovations</p>
            </div>
            
            <div class="row g-4">
                <?php while ($blog = $blogs->fetch_assoc()): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="blog-card animate-on-scroll">
                        <?php if ($blog['image']): ?>
                        <img src="uploads/<?= $blog['image'] ?>" alt="<?= htmlspecialchars($blog['title']) ?>" class="card-img-top">
                        <?php else: ?>
                        <img src="https://via.placeholder.com/400x250/6c757d/ffffff?text=Blog" alt="<?= htmlspecialchars($blog['title']) ?>" class="card-img-top">
                        <?php endif; ?>
                        <div class="card-body">
                            <span class="badge bg-secondary mb-2"><?= htmlspecialchars($blog['category'] ?? 'Industry News') ?></span>
                            <h5 class="card-title"><?= htmlspecialchars($blog['title']) ?></h5>
                            <p class="card-text"><?= substr(htmlspecialchars($blog['excerpt'] ?? $blog['content']), 0, 120) ?>...</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-user me-1"></i><?= htmlspecialchars($blog['author']) ?>
                                </small>
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i><?= date('M d, Y', strtotime($blog['created_at'])) ?>
                                </small>
                            </div>
                            <a href="blog-detail.php?slug=<?= $blog['slug'] ?>" class="btn btn-outline-primary btn-sm mt-3">
                                Read More <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            
            <div class="text-center mt-5">
                <a href="blog.php" class="btn btn-outline-primary btn-lg">
                    View All Posts <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5 bg-primary text-white">
        <div class="container text-center">
            <h2 class="display-5 fw-bold mb-4">Ready to Transform Your Operations?</h2>
            <p class="lead mb-4">Get expert consultation and customized solutions for your industrial automation needs</p>
            <div class="hero-buttons">
                <a href="contact.php" class="btn btn-light btn-lg me-3">
                    <i class="fas fa-phone me-2"></i>Contact Us
                </a>
                <a href="products.php" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-download me-2"></i>Download Catalog
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5><?= $settings['site_name'] ?? 'Motodrives' ?></h5>
                    <p><?= $settings['site_description'] ?? 'Leading manufacturer of industrial drives, motors, and automation equipment' ?></p>
                    <div class="social-links">
                        <?php if (!empty($settings['social_facebook'])): ?>
                        <a href="<?= $settings['social_facebook'] ?>"><i class="fab fa-facebook-f"></i></a>
                        <?php endif; ?>
                        <?php if (!empty($settings['social_linkedin'])): ?>
                        <a href="<?= $settings['social_linkedin'] ?>"><i class="fab fa-linkedin-in"></i></a>
                        <?php endif; ?>
                        <?php if (!empty($settings['social_twitter'])): ?>
                        <a href="<?= $settings['social_twitter'] ?>"><i class="fab fa-twitter"></i></a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-2 mb-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="products.php">Products</a></li>
                        <li><a href="industries.php">Industries</a></li>
                        <li><a href="blog.php">Blog</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 mb-4">
                    <h5>Products</h5>
                    <ul class="list-unstyled">
                        <li><a href="products.php?category=ac-drives">AC Drives</a></li>
                        <li><a href="products.php?category=dc-drives">DC Drives</a></li>
                        <li><a href="products.php?category=servo-drives">Servo Drives</a></li>
                        <li><a href="products.php?category=motors">Motors</a></li>
                        <li><a href="products.php?category=controllers">Controllers</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 mb-4">
                    <h5>Contact Info</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-phone me-2"></i><?= $settings['contact_phone'] ?? '+1 (555) 123-4567' ?></li>
                        <li><i class="fas fa-envelope me-2"></i><?= $settings['contact_email'] ?? 'info@motodrives.com' ?></li>
                        <li><i class="fas fa-map-marker-alt me-2"></i><?= $settings['contact_address'] ?? '123 Industrial Drive, Tech City, TC 12345' ?></li>
                    </ul>
                </div>
            </div>
            <hr class="my-4 border-secondary">
            <div class="row">
                <div class="col-md-6">
                    <p>&copy; <?= date('Y') ?> <?= $settings['site_name'] ?? 'Motodrives' ?>. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="privacy.php" class="text-muted me-3">Privacy Policy</a>
                    <a href="terms.php" class="text-muted">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Dark Mode Toggle -->
    <div class="dark-mode-toggle">
        <div class="theme-switch" id="themeSwitch">
            <div class="theme-switch-slider"></div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    
    <?php if (!empty($settings['analytics_code'])): ?>
    <?= $settings['analytics_code'] ?>
    <?php endif; ?>

    <script>
        // Hide loading screen when page is fully loaded
        window.addEventListener('load', function() {
            setTimeout(function() {
                const loading = document.getElementById('loading');
                if (loading) {
                    loading.style.opacity = '0';
                    setTimeout(function() {
                        loading.style.display = 'none';
                    }, 500);
                }
            }, 1000);
        });
    </script>
</body>
</html>