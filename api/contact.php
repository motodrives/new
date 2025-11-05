<?php
/**
 * Contact Form API Endpoint
 * Handles AJAX contact form submissions
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

require_once '../config/config.php';

// Get POST data
$json = file_get_contents('php://input');
$data = json_decode($json, true) ?: $_POST;

// Validate required fields
$required = ['name', 'email', 'message'];
$errors = [];

foreach ($required as $field) {
    if (empty($data[$field])) {
        $errors[$field] = ucfirst($field) . ' is required';
    }
}

// Validate email
if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Valid email address is required';
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Validation failed', 'errors' => $errors]);
    exit;
}

// Sanitize input
$name = sanitize($data['name']);
$email = sanitize($data['email']);
$phone = sanitize($data['phone'] ?? '');
$company = sanitize($data['company'] ?? '');
$subject = sanitize($data['subject'] ?? 'Website Enquiry');
$message = sanitize($data['message']);
$productInterest = sanitize($data['product_interest'] ?? '');

// Save to database
$insertQuery = "INSERT INTO enquiries (name, email, phone, company, subject, message, product_interest, ip_address) 
               VALUES ('$name', '$email', '$phone', '$company', '$subject', '$message', '$productInterest', '{$_SERVER['REMOTE_ADDR']}')";

if ($db->query($insertQuery)) {
    // Send email notification
    $to = ADMIN_EMAIL;
    $emailSubject = "New Enquiry from Motodrives: $subject";
    $emailBody = "
    <html>
    <head><title>New Enquiry</title></head>
    <body>
        <h2>New Customer Enquiry</h2>
        <p><strong>Name:</strong> $name</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Phone:</strong> $phone</p>
        <p><strong>Company:</strong> $company</p>
        <p><strong>Product Interest:</strong> $productInterest</p>
        <p><strong>Message:</strong><br>" . nl2br($message) . "</p>
        <p><small>Submitted: " . date('Y-m-d H:i:s') . "</small>
    </body>
    </html>";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: $name <$email>\r\n";
    
    mail($to, $emailSubject, $emailBody, $headers);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Thank you for your enquiry! We will contact you soon.',
        'enquiry_id' => $db->insert_id
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to save enquiry']);
}
?>