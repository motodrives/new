<?php
session_start();
require_once '../config/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$success = '';
$error = '';

// Handle product deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $productId = $_GET['delete'];
    
    // Get product image to delete
    $productQuery = "SELECT image FROM products WHERE id = $productId";
    $productResult = $db->query($productQuery);
    $product = $productResult->fetch_assoc();
    
    // Delete product from database
    $deleteQuery = "DELETE FROM products WHERE id = $productId";
    if ($db->query($deleteQuery)) {
        // Delete image file if exists
        if ($product['image'] && file_exists('../uploads/' . $product['image'])) {
            unlink('../uploads/' . $product['image']);
        }
        $success = 'Product deleted successfully!';
    } else {
        $error = 'Failed to delete product.';
    }
}

// Handle form submission
if ($_POST) {
    $name = sanitize($_POST['name']);
    $slug = slugify($name);
    $categoryId = $_POST['category_id'];
    $description = sanitize($_POST['description']);
    $specifications = sanitize($_POST['specifications']);
    $features = sanitize($_POST['features']);
    $price = !empty($_POST['price']) ? $_POST['price'] : NULL;
    $status = $_POST['status'];
    $featured = isset($_POST['featured']) ? 1 : 0;
    $metaTitle = sanitize($_POST['meta_title']);
    $metaDescription = sanitize($_POST['meta_description']);
    
    // Handle image upload
    $image = '';
    if (!empty($_FILES['image']['name'])) {
        $uploadResult = uploadFile($_FILES['image'], '../uploads/');
        if ($uploadResult['success']) {
            $image = $uploadResult['filename'];
        } else {
            $error = $uploadResult['message'];
        }
    }
    
    // Handle gallery images
    $galleryImages = '';
    if (!empty($_FILES['gallery']['name'][0])) {
        $galleryArray = [];
        foreach ($_FILES['gallery']['name'] as $key => $name) {
            if (!empty($name)) {
                $file = [
                    'name' => $name,
                    'type' => $_FILES['gallery']['type'][$key],
                    'tmp_name' => $_FILES['gallery']['tmp_name'][$key],
                    'error' => $_FILES['gallery']['error'][$key],
                    'size' => $_FILES['gallery']['size'][$key]
                ];
                $uploadResult = uploadFile($file, '../uploads/');
                if ($uploadResult['success']) {
                    $galleryArray[] = $uploadResult['filename'];
                }
            }
        }
        $galleryImages = implode(',', $galleryArray);
    }
    
    if (!$error) {
        if (isset($_POST['product_id']) && is_numeric($_POST['product_id'])) {
            // Update existing product
            $productId = $_POST['product_id'];
            
            $updateQuery = "UPDATE products SET 
                name = '$name', 
                slug = '$slug', 
                category_id = $categoryId, 
                description = '$description',
                specifications = '$specifications',
                features = '$features',
                price = " . ($price ? "'$price'" : 'NULL') . ",
                status = '$status',
                featured = $featured,
                meta_title = '$metaTitle',
                meta_description = '$metaDescription'";
            
            if ($image) {
                $updateQuery .= ", image = '$image'";
            }
            
            if ($galleryImages) {
                $updateQuery .= ", gallery_images = '$galleryImages'";
            }
            
            $updateQuery .= " WHERE id = $productId";
            
            if ($db->query($updateQuery)) {
                $success = 'Product updated successfully!';
            } else {
                $error = 'Failed to update product.';
            }
        } else {
            // Insert new product
            $insertQuery = "INSERT INTO products (name, slug, category_id, description, specifications, features, image, gallery_images, price, status, featured, meta_title, meta_description) 
                           VALUES ('$name', '$slug', $categoryId, '$description', '$specifications', '$features', '$image', '$galleryImages', " . ($price ? "'$price'" : 'NULL') . ", '$status', $featured, '$metaTitle', '$metaDescription')";
            
            if ($db->query($insertQuery)) {
                $success = 'Product added successfully!';
            } else {
                $error = 'Failed to add product.';
            }
        }
    }
}

// Fetch categories
$categoriesQuery = "SELECT * FROM categories WHERE status = 'active' ORDER BY name ASC";
$categories = $db->query($categoriesQuery);

// Fetch products with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$categoryFilter = isset($_GET['category']) ? (int)$_GET['category'] : 0;

$whereClause = "WHERE 1=1";
if ($search) {
    $whereClause .= " AND (p.name LIKE '%$search%' OR p.description LIKE '%$search%')";
}
if ($categoryFilter) {
    $whereClause .= " AND p.category_id = $categoryFilter";
}

$totalQuery = "SELECT COUNT(*) as total FROM products p $whereClause";
$totalResult = $db->query($totalQuery);
$total = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($total / $perPage);

$productsQuery = "SELECT p.*, c.name as category_name FROM products p 
                  JOIN categories c ON p.category_id = c.id 
                  $whereClause 
                  ORDER BY p.created_at DESC 
                  LIMIT $perPage OFFSET $offset";
$products = $db->query($productsQuery);

// Get product for editing
$editProduct = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $productId = $_GET['edit'];
    $productQuery = "SELECT * FROM products WHERE id = $productId";
    $productResult = $db->query($productQuery);
    $editProduct = $productResult->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Motodrives Admin</title>
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
        
        .main-content {
            margin-left: 250px;
            padding: 2rem;
        }
        
        .top-nav {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 1rem 2rem;
            margin: -2rem -2rem 2rem -2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .page-header {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }
        
        .product-table {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        
        .product-table table {
            margin: 0;
        }
        
        .product-table th {
            background: #f8f9fa;
            border: none;
            font-weight: 600;
            color: #495057;
        }
        
        .product-table td {
            vertical-align: middle;
            border-color: #f0f0f0;
        }
        
        .product-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 10px;
        }
        
        .status-badge {
            padding: 0.35rem 0.65rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .action-buttons .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            margin: 0 0.125rem;
        }
        
        .form-section {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }
        
        .form-control, .form-select {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }
        
        .image-preview {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid #e0e0e0;
            margin-top: 0.5rem;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -250px;
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <a href="dashboard.php" class="logo">
            <i class="fas fa-cogs"></i>
            Motodrives
        </a>
        
        <div class="sidebar-menu">
            <a href="dashboard.php">
                <i class="fas fa-tachometer-alt"></i>
                Dashboard
            </a>
            <a href="manage_products.php" class="active">
                <i class="fas fa-box"></i>
                Products
            </a>
            <a href="manage_categories.php">
                <i class="fas fa-tags"></i>
                Categories
            </a>
            <a href="manage_blogs.php">
                <i class="fas fa-blog"></i>
                Blog Posts
            </a>
            <a href="manage_gallery.php">
                <i class="fas fa-images"></i>
                Gallery
            </a>
            <a href="manage_industries.php">
                <i class="fas fa-industry"></i>
                Industries
            </a>
            <a href="enquiries.php">
                <i class="fas fa-envelope"></i>
                Enquiries
            </a>
            <a href="settings.php">
                <i class="fas fa-cog"></i>
                Settings
            </a>
            <a href="logout.php">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navigation -->
        <div class="top-nav">
            <div>
                <h4 class="mb-0">Manage Products</h4>
                <small class="text-muted">Add, edit, and manage your product catalog</small>
            </div>
            <div>
                <a href="dashboard.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?= $success ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Product Form -->
        <div class="form-section">
            <h5 class="mb-4"><?= $editProduct ? 'Edit Product' : 'Add New Product' ?></h5>
            
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="product_id" value="<?= $editProduct['id'] ?? '' ?>">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Product Name *</label>
                        <input type="text" class="form-control" id="name" name="name" required 
                               value="<?= htmlspecialchars($editProduct['name'] ?? '') ?>">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="category_id" class="form-label">Category *</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php while ($category = $categories->fetch_assoc()): ?>
                            <option value="<?= $category['id'] ?>" 
                                    <?= (isset($editProduct['category_id']) && $editProduct['category_id'] == $category['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4"><?= htmlspecialchars($editProduct['description'] ?? '') ?></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="specifications" class="form-label">Specifications</label>
                        <textarea class="form-control" id="specifications" name="specifications" rows="3"><?= htmlspecialchars($editProduct['specifications'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="features" class="form-label">Features</label>
                        <textarea class="form-control" id="features" name="features" rows="3"><?= htmlspecialchars($editProduct['features'] ?? '') ?></textarea>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="price" class="form-label">Price</label>
                        <input type="number" class="form-control" id="price" name="price" step="0.01" 
                               value="<?= htmlspecialchars($editProduct['price'] ?? '') ?>">
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="active" <?= (isset($editProduct['status']) && $editProduct['status'] == 'active') ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= (isset($editProduct['status']) && $editProduct['status'] == 'inactive') ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Featured Product</label>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="featured" name="featured" 
                                   <?= (isset($editProduct['featured']) && $editProduct['featured']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="featured">
                                Mark as featured
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="image" class="form-label">Product Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <?php if ($editProduct && $editProduct['image']): ?>
                        <img src="../uploads/<?= $editProduct['image'] ?>" alt="Product Image" class="image-preview">
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="gallery" class="form-label">Gallery Images</label>
                        <input type="file" class="form-control" id="gallery" name="gallery[]" accept="image/*" multiple>
                        <small class="text-muted">You can select multiple images</small>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="meta_title" class="form-label">Meta Title</label>
                        <input type="text" class="form-control" id="meta_title" name="meta_title" 
                               value="<?= htmlspecialchars($editProduct['meta_title'] ?? '') ?>">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="meta_description" class="form-label">Meta Description</label>
                        <textarea class="form-control" id="meta_description" name="meta_description" rows="2"><?= htmlspecialchars($editProduct['meta_description'] ?? '') ?></textarea>
                    </div>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i><?= $editProduct ? 'Update Product' : 'Add Product' ?>
                    </button>
                    <?php if ($editProduct): ?>
                    <a href="manage_products.php" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Products List -->
        <div class="product-table">
            <div class="p-3 border-bottom">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <h5 class="mb-0">Products List</h5>
                    </div>
                    <div class="col-md-8">
                        <form method="GET" class="d-flex gap-2">
                            <input type="text" class="form-control" name="search" placeholder="Search products..." 
                                   value="<?= htmlspecialchars($search) ?>">
                            <select class="form-select" name="category" style="max-width: 200px;">
                                <option value="">All Categories</option>
                                <?php 
                                $categories->data_seek(0);
                                while ($category = $categories->fetch_assoc()): 
                                ?>
                                <option value="<?= $category['id'] ?>" 
                                        <?= $categoryFilter == $category['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                            <button type="submit" class="btn btn-outline-secondary">
                                <i class="fas fa-search"></i>
                            </button>
                            <?php if ($search || $categoryFilter): ?>
                            <a href="manage_products.php" class="btn btn-outline-danger">
                                <i class="fas fa-times"></i>
                            </a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Featured</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($products->num_rows > 0): ?>
                            <?php while ($product = $products->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php if ($product['image']): ?>
                                    <img src="../uploads/<?= $product['image'] ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-img">
                                    <?php else: ?>
                                    <img src="https://via.placeholder.com/60x60/007bff/ffffff?text=No" alt="No Image" class="product-img">
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($product['name']) ?></strong>
                                    <br>
                                    <small class="text-muted"><?= date('M d, Y', strtotime($product['created_at'])) ?></small>
                                </td>
                                <td><?= htmlspecialchars($product['category_name']) ?></td>
                                <td>
                                    <?php if ($product['price']): ?>
                                    $<?= number_format($product['price'], 2) ?>
                                    <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge bg-<?= $product['status'] == 'active' ? 'success' : 'secondary' ?>">
                                        <?= ucfirst($product['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($product['featured']): ?>
                                    <i class="fas fa-star text-warning"></i>
                                    <?php else: ?>
                                    <i class="far fa-star text-muted"></i>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="manage_products.php?edit=<?= $product['id'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="../product-detail.php?id=<?= $product['id'] ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="manage_products.php?delete=<?= $product['id'] ?>" 
                                           class="btn btn-sm btn-outline-danger" 
                                           onclick="return confirm('Are you sure you want to delete this product?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-box fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No products found</p>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="p-3 border-top">
                <nav>
                    <ul class="pagination pagination-sm mb-0 justify-content-center">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= $categoryFilter ?>">
                                <?= $i ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // Image preview
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    let preview = document.querySelector('.image-preview');
                    if (!preview) {
                        preview = document.createElement('img');
                        preview.className = 'image-preview';
                        e.target.parentNode.appendChild(preview);
                    }
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
        
        // Auto-generate slug from product name
        document.getElementById('name').addEventListener('input', function() {
            // This would typically be handled server-side, but we can provide visual feedback
        });
    </script>
</body>
</html>