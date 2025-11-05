<?php
require_once 'config/config.php';

// Get categories for filter
$categoriesQuery = "SELECT * FROM categories WHERE status = 'active' ORDER BY name ASC";
$categories = $db->query($categoriesQuery);

// Get products with filtering
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;

$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$categoryFilter = isset($_GET['category']) ? sanitize($_GET['category']) : '';
$sortBy = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'created_at';

$whereClause = "WHERE p.status = 'active'";
$params = [];

if ($search) {
    $whereClause .= " AND (p.name LIKE ? OR p.description LIKE ? OR p.features LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
}

if ($categoryFilter) {
    $whereClause .= " AND c.slug = ?";
    $params[] = $categoryFilter;
}

// Valid sort options
$validSorts = ['created_at', 'name', 'price'];
if (!in_array($sortBy, $validSorts)) {
    $sortBy = 'created_at';
}

$orderClause = "ORDER BY p.$sortBy DESC";
if ($sortBy === 'name') {
    $orderClause = "ORDER BY p.name ASC";
}

// Get total count
$countQuery = "SELECT COUNT(*) as total FROM products p 
               JOIN categories c ON p.category_id = c.id 
               $whereClause";

if (!empty($params)) {
    $stmt = $db->prepare($countQuery);
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $db->query($countQuery);
}

$total = $result->fetch_assoc()['total'];
$totalPages = ceil($total / $perPage);

// Get products
$productsQuery = "SELECT p.*, c.name as category_name, c.slug as category_slug 
                  FROM products p 
                  JOIN categories c ON p.category_id = c.id 
                  $whereClause 
                  $orderClause 
                  LIMIT ? OFFSET ?";

if (!empty($params)) {
    $allParams = array_merge($params, [$perPage, $offset]);
    $stmt = $db->prepare($productsQuery);
    $types = str_repeat('s', count($params)) . 'ii';
    $stmt->bind_param($types, ...$allParams);
    $stmt->execute();
    $products = $stmt->get_result();
} else {
    $productsQuery = $db->query($productsQuery);
}

// Get featured products for sidebar
$featuredQuery = "SELECT p.*, c.name as category_name, c.slug as category_slug 
                  FROM products p 
                  JOIN categories c ON p.category_id = c.id 
                  WHERE p.status = 'active' AND p.featured = 1 
                  ORDER BY p.created_at DESC 
                  LIMIT 4";
$featuredProducts = $db->query($featuredQuery);

// Get page title and meta info
$pageTitle = 'Products';
$pageDescription = 'Browse our comprehensive range of industrial drives, motors, and automation equipment';
if ($categoryFilter) {
    $categoryInfo = $db->query("SELECT name, description FROM categories WHERE slug = '$categoryFilter' LIMIT 1")->fetch_assoc();
    if ($categoryInfo) {
        $pageTitle = $categoryInfo['name'];
        $pageDescription = $categoryInfo['description'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - Motodrives Industrial Solutions</title>
    <meta name="description" content="<?= $pageDescription ?>">
    <meta name="keywords" content="industrial drives, motors, automation, <?= $categoryFilter ?>, <?= $search ?>">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?= $pageTitle ?> - Motodrives">
    <meta property="og:description" content="<?= $pageDescription ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= SITE_URL ?>/products.php<?= $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '' ?>">
    
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
                        <a class="nav-link" href="about.php">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="products.php">Products</a>
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
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-3"><?= $pageTitle ?></h1>
                    <p class="lead mb-4"><?= $pageDescription ?></p>
                    
                    <!-- Quick Search -->
                    <form method="GET" class="d-flex gap-2 max-width-500">
                        <input type="text" class="form-control form-control-lg" name="search" 
                               placeholder="Search for products..." value="<?= htmlspecialchars($search) ?>">
                        <button type="submit" class="btn btn-light btn-lg">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
                <div class="col-lg-4">
                    <div class="text-center">
                        <i class="fas fa-cogs fa-5x mb-3 opacity-75"></i>
                        <p class="mb-0"><?= number_format($total) ?> Products Available</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <!-- Sidebar -->
                <div class="col-lg-3 mb-4">
                    <div class="sticky-top" style="top: 100px;">
                        <!-- Categories Filter -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body">
                                <h5 class="card-title mb-4">
                                    <i class="fas fa-filter me-2"></i>Categories
                                </h5>
                                <div class="list-group list-group-flush">
                                    <a href="products.php" 
                                       class="list-group-item list-group-item-action border-0 <?= !$categoryFilter ? 'active' : '' ?>">
                                        <i class="fas fa-th me-2"></i>All Products
                                        <span class="badge bg-secondary float-end"><?= number_format($db->query("SELECT COUNT(*) as count FROM products WHERE status = 'active'")->fetch_assoc()['count']) ?></span>
                                    </a>
                                    <?php while ($category = $categories->fetch_assoc()): ?>
                                    <?php
                                    $countQuery = "SELECT COUNT(*) as count FROM products WHERE category_id = {$category['id']} AND status = 'active'";
                                    $count = $db->query($countQuery)->fetch_assoc()['count'];
                                    ?>
                                    <a href="products.php?category=<?= $category['slug'] ?>" 
                                       class="list-group-item list-group-item-action border-0 <?= $categoryFilter == $category['slug'] ? 'active' : '' ?>">
                                        <i class="fas fa-tag me-2"></i><?= htmlspecialchars($category['name']) ?>
                                        <span class="badge bg-secondary float-end"><?= number_format($count) ?></span>
                                    </a>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Featured Products -->
                        <?php if ($featuredProducts->num_rows > 0): ?>
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title mb-4">
                                    <i class="fas fa-star me-2"></i>Featured Products
                                </h5>
                                <?php while ($product = $featuredProducts->fetch_assoc()): ?>
                                <div class="d-flex mb-3">
                                    <img src="uploads/<?= $product['image'] ?: 'placeholder.jpg' ?>" 
                                         alt="<?= htmlspecialchars($product['name']) ?>" 
                                         class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            <a href="product-detail.php?id=<?= $product['id'] ?>" class="text-decoration-none">
                                                <?= htmlspecialchars($product['name']) ?>
                                            </a>
                                        </h6>
                                        <small class="text-muted"><?= htmlspecialchars($product['category_name']) ?></small>
                                        <?php if ($product['price']): ?>
                                        <div class="text-primary fw-bold">$<?= number_format($product['price'], 2) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="col-lg-9">
                    <!-- Sorting and Results Count -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <p class="mb-0 text-muted">
                                Showing <?= ($products->num_rows > 0) ? (($page - 1) * $perPage + 1) : 0 ?> 
                                - <?= min($page * $perPage, $total) ?> 
                                of <?= number_format($total) ?> products
                            </p>
                        </div>
                        <div>
                            <select class="form-select" onchange="window.location.href='?<?= http_build_query(array_merge($_GET, ['sort' => this.value])) ?>'">
                                <option value="created_at" <?= $sortBy == 'created_at' ? 'selected' : '' ?>>Latest First</option>
                                <option value="name" <?= $sortBy == 'name' ? 'selected' : '' ?>>Name (A-Z)</option>
                                <option value="price" <?= $sortBy == 'price' ? 'selected' : '' ?>>Price (High to Low)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Active Filters -->
                    <?php if ($search || $categoryFilter): ?>
                    <div class="alert alert-info d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <i class="fas fa-info-circle me-2"></i>
                            Active filters:
                            <?php if ($search): ?>
                            <span class="badge bg-secondary me-2">Search: <?= htmlspecialchars($search) ?></span>
                            <?php endif; ?>
                            <?php if ($categoryFilter): ?>
                            <span class="badge bg-secondary me-2">Category: <?= htmlspecialchars($categoryFilter) ?></span>
                            <?php endif; ?>
                        </div>
                        <a href="products.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Clear Filters
                        </a>
                    </div>
                    <?php endif; ?>

                    <!-- Products Grid -->
                    <div class="row g-4">
                        <?php if ($products->num_rows > 0): ?>
                            <?php while ($product = $products->fetch_assoc()): ?>
                            <div class="col-lg-4 col-md-6">
                                <div class="product-card animate-on-scroll" data-category="<?= $product['category_slug'] ?>">
                                    <div class="position-relative">
                                        <?php if ($product['image']): ?>
                                        <img src="uploads/<?= $product['image'] ?>" 
                                             alt="<?= htmlspecialchars($product['name']) ?>" 
                                             class="card-img-top" loading="lazy">
                                        <?php else: ?>
                                        <img src="https://picsum.photos/seed/<?= $product['id'] ?>/400x300.jpg" 
                                             alt="<?= htmlspecialchars($product['name']) ?>" 
                                             class="card-img-top" loading="lazy">
                                        <?php endif; ?>
                                        
                                        <?php if ($product['featured']): ?>
                                        <div class="position-absolute top-0 end-0 m-2">
                                            <span class="badge bg-warning">
                                                <i class="fas fa-star me-1"></i>Featured
                                            </span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="product-card-body">
                                        <div class="mb-2">
                                            <span class="badge bg-primary me-2"><?= htmlspecialchars($product['category_name']) ?></span>
                                        </div>
                                        <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                                        <p class="card-text text-muted">
                                            <?= substr(htmlspecialchars($product['description']), 0, 100) ?>...
                                        </p>
                                        
                                        <?php if ($product['price']): ?>
                                        <div class="mb-3">
                                            <span class="h5 text-primary fw-bold">$<?= number_format($product['price'], 2) ?></span>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <a href="product-detail.php?id=<?= $product['id'] ?>" 
                                               class="btn btn-primary btn-sm">
                                                <i class="fas fa-eye me-1"></i>View Details
                                            </a>
                                            <a href="contact.php?product=<?= urlencode($product['name']) ?>" 
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-envelope me-1"></i>Enquire
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                        <div class="col-12">
                            <div class="text-center py-5">
                                <i class="fas fa-search fa-4x text-muted mb-3"></i>
                                <h4>No Products Found</h4>
                                <p class="text-muted">
                                    <?php if ($search || $categoryFilter): ?>
                                    Try adjusting your search criteria or browse all products.
                                    <?php else: ?>
                                    No products are available at the moment.
                                    <?php endif; ?>
                                </p>
                                <?php if ($search || $categoryFilter): ?>
                                <a href="products.php" class="btn btn-primary">
                                    <i class="fas fa-th me-2"></i>View All Products
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <nav class="mt-5">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page - 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $categoryFilter ? '&category=' . urlencode($categoryFilter) : '' ?><?= $sortBy != 'created_at' ? '&sort=' . urlencode($sortBy) : '' ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <?php 
                            $startPage = max(1, $page - 2);
                            $endPage = min($totalPages, $page + 2);
                            
                            for ($i = $startPage; $i <= $endPage; $i++): 
                            ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $categoryFilter ? '&category=' . urlencode($categoryFilter) : '' ?><?= $sortBy != 'created_at' ? '&sort=' . urlencode($sortBy) : '' ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page + 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $categoryFilter ? '&category=' . urlencode($categoryFilter) : '' ?><?= $sortBy != 'created_at' ? '&sort=' . urlencode($sortBy) : '' ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5 bg-primary text-white">
        <div class="container text-center">
            <h2 class="display-5 fw-bold mb-4">Can't Find What You're Looking For?</h2>
            <p class="lead mb-4">Our engineering team can design custom solutions for your specific requirements</p>
            <div class="hero-buttons">
                <a href="contact.php" class="btn btn-light btn-lg me-3">
                    <i class="fas fa-phone me-2"></i>Contact Us
                </a>
                <a href="tel:+15551234567" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-phone-alt me-2"></i>Call Now
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
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
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
                        <li><i class="fas fa-phone me-2"></i>+1 (555) 123-4567</li>
                        <li><i class="fas fa-envelope me-2"></i>info@motodrives.com</li>
                        <li><i class="fas fa-map-marker-alt me-2"></i>123 Industrial Drive, Tech City, TC 12345</li>
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

        // Product search with debounce
        let searchTimeout;
        const searchInput = document.querySelector('input[name="search"]');
        
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const query = this.value.trim();
                
                if (query.length >= 3 || query.length === 0) {
                    searchTimeout = setTimeout(() => {
                        // Auto-submit search when user stops typing
                        const form = this.closest('form');
                        if (form) {
                            form.submit();
                        }
                    }, 500);
                }
            });
        }

        // Lazy loading for images
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                });
            });

            document.querySelectorAll('img[loading="lazy"]').forEach(img => {
                imageObserver.observe(img);
            });
        }
    </script>
</body>
</html>