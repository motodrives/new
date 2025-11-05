<?php
session_start();
require_once '../config/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get dashboard statistics
$totalProducts = $db->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$totalEnquiries = $db->query("SELECT COUNT(*) as count FROM enquiries")->fetch_assoc()['count'];
$totalBlogs = $db->query("SELECT COUNT(*) as count FROM blogs")->fetch_assoc()['count'];
$activeProducts = $db->query("SELECT COUNT(*) as count FROM products WHERE status = 'active'")->fetch_assoc()['count'];
$newEnquiries = $db->query("SELECT COUNT(*) as count FROM enquiries WHERE status = 'new'")->fetch_assoc()['count'];
$publishedBlogs = $db->query("SELECT COUNT(*) as count FROM blogs WHERE status = 'published'")->fetch_assoc()['count'];

// Get recent enquiries
$recentEnquiriesQuery = "SELECT * FROM enquiries ORDER BY created_at DESC LIMIT 5";
$recentEnquiries = $db->query($recentEnquiriesQuery);

// Get recent blog posts
$recentBlogsQuery = "SELECT * FROM blogs ORDER BY created_at DESC LIMIT 5";
$recentBlogs = $db->query($recentBlogsQuery);

// Get top products (most viewed or featured)
$topProductsQuery = "SELECT p.*, c.name as category_name FROM products p 
                     JOIN categories c ON p.category_id = c.id 
                     WHERE p.status = 'active' 
                     ORDER BY p.featured DESC, p.created_at DESC LIMIT 5";
$topProducts = $db->query($topProductsQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Motodrives Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Inter', sans-serif;
        }
        
        .sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: white;
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .sidebar.collapsed {
            width: 80px;
        }
        
        .sidebar .logo {
            padding: 1.5rem;
            font-size: 1.5rem;
            font-weight: 700;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-decoration: none;
            color: white;
            display: flex;
            align-items: center;
        }
        
        .sidebar .logo i {
            margin-right: 0.5rem;
        }
        
        .sidebar.collapsed .logo span {
            display: none;
        }
        
        .sidebar-menu {
            padding: 1rem 0;
        }
        
        .sidebar-menu a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left: 3px solid white;
        }
        
        .sidebar-menu a i {
            margin-right: 0.75rem;
            width: 20px;
        }
        
        .sidebar.collapsed .sidebar-menu span {
            display: none;
        }
        
        .main-content {
            margin-left: 250px;
            transition: all 0.3s ease;
        }
        
        .main-content.expanded {
            margin-left: 80px;
        }
        
        .top-nav {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 999;
        }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.12);
        }
        
        .stats-card.primary { border-left-color: #007bff; }
        .stats-card.success { border-left-color: #28a745; }
        .stats-card.warning { border-left-color: #ffc107; }
        .stats-card.danger { border-left-color: #dc3545; }
        
        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .stats-icon.primary { background: rgba(0,123,255,0.1); color: #007bff; }
        .stats-icon.success { background: rgba(40,167,69,0.1); color: #28a745; }
        .stats-icon.warning { background: rgba(255,193,7,0.1); color: #ffc107; }
        .stats-icon.danger { background: rgba(220,53,69,0.1); color: #dc3545; }
        
        .recent-activity {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        
        .activity-item {
            padding: 1rem 0;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1rem;
        }
        
        .toggle-sidebar {
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            color: #6c757d;
        }
        
        .user-dropdown {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }
        
        .content-wrapper {
            padding: 2rem;
        }
        
        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        
        .page-subtitle {
            color: #6c757d;
            margin-bottom: 2rem;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <a href="dashboard.php" class="logo">
            <i class="fas fa-cogs"></i>
            <span>Motodrives</span>
        </a>
        
        <div class="sidebar-menu">
            <a href="dashboard.php" class="active">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="manage_products.php">
                <i class="fas fa-box"></i>
                <span>Products</span>
            </a>
            <a href="manage_categories.php">
                <i class="fas fa-tags"></i>
                <span>Categories</span>
            </a>
            <a href="manage_blogs.php">
                <i class="fas fa-blog"></i>
                <span>Blog Posts</span>
            </a>
            <a href="manage_gallery.php">
                <i class="fas fa-images"></i>
                <span>Gallery</span>
            </a>
            <a href="manage_industries.php">
                <i class="fas fa-industry"></i>
                <span>Industries</span>
            </a>
            <a href="enquiries.php">
                <i class="fas fa-envelope"></i>
                <span>Enquiries</span>
            </a>
            <a href="settings.php">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
            <a href="logout.php">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Top Navigation -->
        <div class="top-nav">
            <div class="d-flex align-items-center">
                <button class="toggle-sidebar" id="toggleSidebar">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            
            <div class="user-dropdown">
                <div class="user-avatar">
                    <?= substr($_SESSION['user_name'], 0, 1) ?>
                </div>
                <div class="d-none d-md-block">
                    <div class="fw-semibold"><?= htmlspecialchars($_SESSION['user_name']) ?></div>
                    <small class="text-muted"><?= ucfirst($_SESSION['user_role']) ?></small>
                </div>
                <div class="dropdown">
                    <button class="btn btn-sm" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Page Content -->
        <div class="content-wrapper">
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle">Welcome back! Here's an overview of your business.</p>
            
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stats-card primary">
                        <div class="stats-icon primary">
                            <i class="fas fa-box"></i>
                        </div>
                        <h3><?= number_format($totalProducts) ?></h3>
                        <p class="text-muted mb-0">Total Products</p>
                        <small class="text-success"><i class="fas fa-arrow-up me-1"></i><?= number_format($activeProducts) ?> Active</small>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stats-card success">
                        <div class="stats-icon success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h3><?= number_format($publishedBlogs) ?></h3>
                        <p class="text-muted mb-0">Published Blogs</p>
                        <small class="text-success"><i class="fas fa-arrow-up me-1"></i><?= number_format($totalBlogs - $publishedBlogs) ?> Drafts</small>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stats-card warning">
                        <div class="stats-icon warning">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h3><?= number_format($totalEnquiries) ?></h3>
                        <p class="text-muted mb-0">Total Enquiries</p>
                        <small class="text-warning"><i class="fas fa-exclamation me-1"></i><?= number_format($newEnquiries) ?> New</small>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stats-card danger">
                        <div class="stats-icon danger">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3>1,247</h3>
                        <p class="text-muted mb-0">Website Visitors</p>
                        <small class="text-danger"><i class="fas fa-arrow-up me-1"></i>+12% This month</small>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="recent-activity">
                        <h5 class="mb-4">Recent Enquiries</h5>
                        <?php if ($recentEnquiries->num_rows > 0): ?>
                            <?php while ($enquiry = $recentEnquiries->fetch_assoc()): ?>
                            <div class="activity-item">
                                <div class="activity-icon bg-primary text-white">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?= htmlspecialchars($enquiry['name']) ?></h6>
                                            <p class="text-muted mb-0 small"><?= htmlspecialchars(substr($enquiry['message'], 0, 50)) ?>...</p>
                                        </div>
                                        <small class="text-muted"><?= date('M d, H:i', strtotime($enquiry['created_at'])) ?></small>
                                    </div>
                                    <span class="badge bg-<?= $enquiry['status'] == 'new' ? 'danger' : ($enquiry['status'] == 'read' ? 'warning' : 'success') ?> mt-2">
                                        <?= ucfirst($enquiry['status']) ?>
                                    </span>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-muted">No recent enquiries</p>
                        <?php endif; ?>
                        
                        <div class="mt-3">
                            <a href="enquiries.php" class="btn btn-sm btn-outline-primary">View All Enquiries</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6 mb-4">
                    <div class="recent-activity">
                        <h5 class="mb-4">Top Products</h5>
                        <?php if ($topProducts->num_rows > 0): ?>
                            <?php while ($product = $topProducts->fetch_assoc()): ?>
                            <div class="activity-item">
                                <div class="activity-icon bg-success text-white">
                                    <i class="fas fa-box"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?= htmlspecialchars($product['name']) ?></h6>
                                            <p class="text-muted mb-0 small"><?= htmlspecialchars($product['category_name']) ?></p>
                                        </div>
                                        <?php if ($product['featured']): ?>
                                        <span class="badge bg-warning">Featured</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-muted">No products available</p>
                        <?php endif; ?>
                        
                        <div class="mt-3">
                            <a href="manage_products.php" class="btn btn-sm btn-outline-primary">Manage Products</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Toggle Sidebar
        document.getElementById('toggleSidebar').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
            
            // Mobile sidebar toggle
            if (window.innerWidth <= 768) {
                sidebar.classList.toggle('show');
            }
        });
        
        // Mobile sidebar handling
        function handleMobileSidebar() {
            const sidebar = document.getElementById('sidebar');
            if (window.innerWidth > 768) {
                sidebar.classList.remove('show');
            }
        }
        
        window.addEventListener('resize', handleMobileSidebar);
        handleMobileSidebar();
        
        // Auto-refresh dashboard data every 30 seconds
        setInterval(function() {
            // You can implement AJAX calls here to refresh data
            console.log('Refreshing dashboard data...');
        }, 30000);
        
        // Animate numbers on load
        function animateNumbers() {
            const numbers = document.querySelectorAll('.stats-card h3');
            numbers.forEach(number => {
                const finalValue = parseInt(number.textContent.replace(/,/g, ''));
                let currentValue = 0;
                const increment = finalValue / 50;
                const timer = setInterval(() => {
                    currentValue += increment;
                    if (currentValue >= finalValue) {
                        currentValue = finalValue;
                        clearInterval(timer);
                    }
                    number.textContent = Math.floor(currentValue).toLocaleString();
                }, 20);
            });
        }
        
        // Run animation when page loads
        window.addEventListener('load', animateNumbers);
    </script>
</body>
</html>