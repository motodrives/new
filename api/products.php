<?php
/**
 * Products API Endpoint
 * Returns JSON data for products
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/config.php';

// Get query parameters
$category = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';
$featured = isset($_GET['featured']) ? (int)$_GET['featured'] : null;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Build query
$whereClause = "WHERE p.status = 'active'";
$params = [];

if ($search) {
    $whereClause .= " AND (p.name LIKE ? OR p.description LIKE ? OR p.features LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
}

if ($category) {
    $whereClause .= " AND c.slug = ?";
    $params[] = $category;
}

if ($featured !== null) {
    $whereClause .= " AND p.featured = ?";
    $params[] = $featured;
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

// Get products
$productsQuery = "SELECT p.id, p.name, p.slug, p.description, p.image, p.price, 
                         p.specifications, p.features, p.meta_title, p.meta_description,
                         p.created_at, c.name as category_name, c.slug as category_slug
                  FROM products p 
                  JOIN categories c ON p.category_id = c.id 
                  $whereClause 
                  ORDER BY p.featured DESC, p.created_at DESC 
                  LIMIT ? OFFSET ?";

if (!empty($params)) {
    $allParams = array_merge($params, [$limit, $offset]);
    $stmt = $db->prepare($productsQuery);
    $types = str_repeat('s', count($params)) . 'ii';
    $stmt->bind_param($types, ...$allParams);
    $stmt->execute();
    $products = $stmt->get_result();
} else {
    $products = $db->query($productsQuery);
}

// Format response
$productsArray = [];
while ($product = $products->fetch_assoc()) {
    // Add image URL
    $product['image_url'] = $product['image'] ? SITE_URL . '/uploads/' . $product['image'] : null;
    $product['category_url'] = SITE_URL . '/products/' . $product['category_slug'];
    $product['product_url'] = SITE_URL . '/product/' . $product['slug'];
    $product['price_formatted'] = $product['price'] ? '$' . number_format($product['price'], 2) : null;
    
    // Parse specifications if they exist
    if ($product['specifications']) {
        $specLines = explode("\n", $product['specifications']);
        $product['specifications_array'] = array_filter($specLines);
    } else {
        $product['specifications_array'] = [];
    }
    
    // Parse features if they exist
    if ($product['features']) {
        $featureLines = explode("\n", $product['features']);
        $product['features_array'] = array_filter($featureLines);
    } else {
        $product['features_array'] = [];
    }
    
    $productsArray[] = $product;
}

$response = [
    'success' => true,
    'data' => $productsArray,
    'pagination' => [
        'page' => $page,
        'limit' => $limit,
        'total' => $total,
        'total_pages' => ceil($total / $limit),
        'has_next' => $page * $limit < $total,
        'has_prev' => $page > 1
    ],
    'filters' => [
        'category' => $category,
        'search' => $search,
        'featured' => $featured
    ]
];

echo json_encode($response, JSON_PRETTY_PRINT);
?>