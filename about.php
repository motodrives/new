<?php
require_once 'config/config.php';

// Fetch company information from settings
$settingsQuery = "SELECT * FROM settings";
$settingsResult = $db->query($settingsQuery);
$settings = [];
while ($row = $settingsResult->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Get featured products for showcase
$featuredQuery = "SELECT p.*, c.name as category_name FROM products p 
                  JOIN categories c ON p.category_id = c.id 
                  WHERE p.status = 'active' AND p.featured = 1 
                  ORDER BY p.created_at DESC LIMIT 6";
$featuredProducts = $db->query($featuredQuery);

// Get team members (could be from a team table, using static data for now)
$teamMembers = [
    [
        'name' => 'John Anderson',
        'position' => 'CEO & Founder',
        'bio' => 'With over 25 years of experience in industrial automation, John founded Motodrives with a vision to provide cutting-edge drive solutions.',
        'image' => 'team1.jpg'
    ],
    [
        'name' => 'Sarah Chen',
        'position' => 'CTO',
        'bio' => 'Sarah leads our technical innovation with expertise in motor control systems and industrial IoT integration.',
        'image' => 'team2.jpg'
    ],
    [
        'name' => 'Michael Roberts',
        'position' => 'Head of Engineering',
        'bio' => 'Michael oversees product development and ensures our drives meet the highest industry standards.',
        'image' => 'team3.jpg'
    ],
    [
        'name' => 'Emily Martinez',
        'position' => 'Operations Director',
        'bio' => 'Emily manages our global operations and ensures seamless delivery of solutions to customers worldwide.',
        'image' => 'team4.jpg'
    ]
];

// Company milestones
$milestones = [
    ['year' => '1998', 'title' => 'Company Founded', 'description' => 'Started with a small workshop focusing on motor repairs.'],
    ['year' => '2005', 'title' => 'First Product Launch', 'description' => 'Introduced our first AC drive series to the market.'],
    ['year' => '2012', 'title' => 'International Expansion', 'description' => 'Expanded operations to serve customers in 15 countries.'],
    ['year' => '2018', 'title' => 'ISO 9001 Certification', 'description' => 'Achieved ISO 9001:2015 quality management certification.'],
    ['year' => '2023', 'title' => '25 Years in Business', 'description' => 'Celebrating 25 years of excellence in industrial automation.']
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Motodrives Industrial Solutions</title>
    <meta name="description" content="Learn about Motodrives - 25 years of excellence in industrial drives, motors, and automation equipment manufacturing.">
    <meta name="keywords" content="about motodrives, industrial automation history, drive manufacturer, company profile">
    
    <!-- Open Graph -->
    <meta property="og:title" content="About Us - Motodrives">
    <meta property="og:description" content="25 years of excellence in industrial drives and automation solutions">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= SITE_URL ?>/about.php">
    
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
                Motodrives
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="about.php">About Us</a>
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

    <!-- Page Header -->
    <section class="page-header" style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: white; padding: 120px 0 80px;">
        <div class="container text-center">
            <h1 class="display-4 fw-bold mb-4">About Motodrives</h1>
            <p class="lead">25 Years of Excellence in Industrial Automation</p>
            <div class="row justify-content-center mt-5">
                <div class="col-md-3">
                    <div class="text-center">
                        <h2 class="display-4 fw-bold">25+</h2>
                        <p>Years Experience</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h2 class="display-4 fw-bold">500+</h2>
                        <p>Products</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h2 class="display-4 fw-bold">50+</h2>
                        <p>Countries</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h2 class="display-4 fw-bold">1000+</h2>
                        <p>Happy Clients</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Story -->
    <section class="py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4">
                    <h2 class="display-5 fw-bold mb-4">Our Story</h2>
                    <p class="lead">Founded in 1998, Motodrives has grown from a small motor repair workshop into a leading manufacturer of industrial drives and automation equipment.</p>
                    <p>Our journey began with a simple mission: to provide reliable, high-quality motor control solutions that help industries operate more efficiently. Over the past 25 years, we've expanded our product range, invested heavily in R&D, and built a reputation for innovation and excellence.</p>
                    <p>Today, Motodrives serves customers in over 50 countries, offering a comprehensive range of AC drives, DC drives, servo systems, and automation solutions that power industries worldwide.</p>
                    <div class="mt-4">
                        <a href="contact.php" class="btn btn-primary btn-lg me-3">
                            <i class="fas fa-phone me-2"></i>Contact Us
                        </a>
                        <a href="products.php" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-shopping-cart me-2"></i>Our Products
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <img src="https://picsum.photos/seed/factory/600x400.jpg" alt="Our Factory" class="img-fluid rounded shadow">
                </div>
            </div>
        </div>
    </section>

    <!-- Mission & Vision -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="text-center mb-4">
                                <div class="feature-icon">
                                    <i class="fas fa-bullseye fa-3x text-primary"></i>
                                </div>
                            </div>
                            <h3 class="text-center mb-3">Our Mission</h3>
                            <p class="text-center">To empower industries worldwide with innovative, reliable, and energy-efficient motor control solutions that enhance productivity and sustainability.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="text-center mb-4">
                                <div class="feature-icon">
                                    <i class="fas fa-eye fa-3x text-primary"></i>
                                </div>
                            </div>
                            <h3 class="text-center mb-3">Our Vision</h3>
                            <p class="text-center">To be the global leader in industrial drive technology, pioneering solutions that drive the future of automation and smart manufacturing.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-lg-4 mb-4">
                    <div class="text-center">
                        <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                        <h4>Quality First</h4>
                        <p>ISO 9001:2015 certified manufacturing processes ensuring the highest quality standards.</p>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="text-center">
                        <i class="fas fa-lightbulb fa-3x text-primary mb-3"></i>
                        <h4>Innovation</h4>
                        <p>Continuous R&D investment to develop cutting-edge drive technologies.</p>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="text-center">
                        <i class="fas fa-handshake fa-3x text-primary mb-3"></i>
                        <h4>Customer Focus</h4>
                        <p>Building long-term partnerships through exceptional service and support.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Timeline -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Our Journey</h2>
                <p class="lead">Key milestones in our 25-year history</p>
            </div>
            
            <div class="row">
                <?php foreach ($milestones as $milestone): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-primary mb-3">
                                <h3 class="fw-bold"><?= $milestone['year'] ?></h3>
                            </div>
                            <h5 class="card-title"><?= $milestone['title'] ?></h5>
                            <p class="card-text"><?= $milestone['description'] ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Team -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Leadership Team</h2>
                <p class="lead">Meet the people driving our success</p>
            </div>
            
            <div class="row">
                <?php foreach ($teamMembers as $member): ?>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card border-0 shadow-sm">
                        <img src="https://picsum.photos/seed/<?= $member['image'] ?>/400x300.jpg" 
                             alt="<?= htmlspecialchars($member['name']) ?>" 
                             class="card-img-top">
                        <div class="card-body text-center">
                            <h5 class="card-title"><?= htmlspecialchars($member['name']) ?></h5>
                            <p class="text-primary mb-2"><?= htmlspecialchars($member['position']) ?></p>
                            <p class="card-text small"><?= htmlspecialchars($member['bio']) ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Certifications -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Certifications & Awards</h2>
                <p class="lead">Recognized for excellence and quality</p>
            </div>
            
            <div class="row text-center">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="p-4">
                        <i class="fas fa-certificate fa-4x text-primary mb-3"></i>
                        <h5>ISO 9001:2015</h5>
                        <p>Quality Management System</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="p-4">
                        <i class="fas fa-award fa-4x text-primary mb-3"></i>
                        <h5>CE Marked</h5>
                        <p>European Conformity Certification</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="p-4">
                        <i class="fas fa-globe fa-4x text-primary mb-3"></i>
                        <h5>UL Listed</h5>
                        <p>Underwriters Laboratories Certification</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="p-4">
                        <i class="fas fa-leaf fa-4x text-primary mb-3"></i>
                        <h5>RoHS Compliant</h5>
                        <p>Restriction of Hazardous Substances</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <?php if ($featuredProducts->num_rows > 0): ?>
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Featured Products</h2>
                <p class="lead">Some of our most popular solutions</p>
            </div>
            
            <div class="row g-4">
                <?php while ($product = $featuredProducts->fetch_assoc()): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="product-card">
                        <?php if ($product['image']): ?>
                        <img src="uploads/<?= $product['image'] ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="card-img-top">
                        <?php else: ?>
                        <img src="https://picsum.photos/seed/product<?= $product['id'] ?>/400x300.jpg" alt="<?= htmlspecialchars($product['name']) ?>" class="card-img-top">
                        <?php endif; ?>
                        <div class="product-card-body">
                            <span class="badge bg-primary mb-2"><?= htmlspecialchars($product['category_name']) ?></span>
                            <h5><?= htmlspecialchars($product['name']) ?></h5>
                            <p><?= substr(htmlspecialchars($product['description']), 0, 100) ?>...</p>
                            <a href="product-detail.php?id=<?= $product['id'] ?>" class="btn btn-primary btn-sm">
                                Learn More
                            </a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- CTA Section -->
    <section class="py-5 bg-primary text-white">
        <div class="container text-center">
            <h2 class="display-5 fw-bold mb-4">Partner With Us</h2>
            <p class="lead mb-4">Join thousands of companies that trust Motodrives for their automation needs</p>
            <div class="hero-buttons">
                <a href="contact.php" class="btn btn-light btn-lg me-3">
                    <i class="fas fa-phone me-2"></i>Get Started
                </a>
                <a href="assets/documents/catalog.pdf" class="btn btn-outline-light btn-lg">
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
                    <h5>Motodrives</h5>
                    <p>Leading manufacturer of industrial drives, motors, and automation equipment</p>
                    <div class="social-links">
                        <a href="<?= $settings['social_facebook'] ?? '#' ?>"><i class="fab fa-facebook-f"></i></a>
                        <a href="<?= $settings['social_linkedin'] ?? '#' ?>"><i class="fab fa-linkedin-in"></i></a>
                        <a href="<?= $settings['social_twitter'] ?? '#' ?>"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 mb-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php">Home</a></li>
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
                    <p>&copy; <?= date('Y') ?> Motodrives. All rights reserved.</p>
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
            }, 500);
        });
    </script>
</body>
</html>