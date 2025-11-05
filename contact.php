<?php
require_once 'config/config.php';

$success = '';
$error = '';

// Handle form submission
if ($_POST) {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $company = sanitize($_POST['company']);
    $subject = sanitize($_POST['subject']);
    $message = sanitize($_POST['message']);
    $productInterest = sanitize($_POST['product_interest'] ?? '');
    
    // Validation
    if (empty($name) || empty($email) || empty($message)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Save enquiry to database
        $insertQuery = "INSERT INTO enquiries (name, email, phone, company, subject, message, product_interest, ip_address) 
                       VALUES ('$name', '$email', '$phone', '$company', '$subject', '$message', '$productInterest', '{$_SERVER['REMOTE_ADDR']}')";
        
        if ($db->query($insertQuery)) {
            // Send email notification
            $to = ADMIN_EMAIL;
            $emailSubject = "New Enquiry from Motodrives Website: $subject";
            $emailBody = "
            <html>
            <head>
                <title>New Enquiry - Motodrives</title>
            </head>
            <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                    <div style='background: #007bff; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0;'>
                        <h2 style='margin: 0;'>New Customer Enquiry</h2>
                    </div>
                    
                    <div style='background: #f9f9f9; padding: 20px; border: 1px solid #ddd;'>
                        <h3 style='color: #007bff; margin-top: 0;'>Enquiry Details:</h3>
                        
                        <p><strong>Name:</strong> $name</p>
                        <p><strong>Email:</strong> $email</p>
                        <p><strong>Phone:</strong> $phone</p>
                        <p><strong>Company:</strong> $company</p>
                        <p><strong>Subject:</strong> $subject</p>";
                        
            if ($productInterest) {
                $emailBody .= "<p><strong>Product Interest:</strong> $productInterest</p>";
            }
            
            $emailBody .= "
                        <p><strong>Message:</strong></p>
                        <div style='background: white; padding: 15px; border-left: 4px solid #007bff; margin: 15px 0;'>
                            " . nl2br($message) . "
                        </div>
                        
                        <p><strong>IP Address:</strong> {$_SERVER['REMOTE_ADDR']}</p>
                        <p><strong>Submitted:</strong> " . date('Y-m-d H:i:s') . "</p>
                    </div>
                    
                    <div style='background: #f8f9fa; padding: 15px; text-align: center; border: 1px solid #ddd; border-top: none; border-radius: 0 0 5px 5px;'>
                        <p style='margin: 0; font-size: 14px; color: #666;'>
                            This email was sent from the Motodrives website contact form.
                        </p>
                    </div>
                </div>
            </body>
            </html>";
            
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: $name <$email>\r\n";
            $headers .= "Reply-To: $email\r\n";
            
            // Send email
            if (mail($to, $emailSubject, $emailBody, $headers)) {
                $success = 'Thank you for your enquiry! We will get back to you within 24 hours.';
            } else {
                $success = 'Your enquiry has been received. We will contact you soon.'; // Even if email fails, we still show success
            }
        } else {
            $error = 'Failed to submit enquiry. Please try again.';
        }
    }
}

// Get site settings for contact info
$settingsQuery = "SELECT * FROM settings";
$settingsResult = $db->query($settingsQuery);
$settings = [];
while ($row = $settingsResult->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Get categories for product interest dropdown
$categoriesQuery = "SELECT * FROM categories WHERE status = 'active' ORDER BY name ASC";
$categories = $db->query($categoriesQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Motodrives Industrial Solutions</title>
    <meta name="description" content="Get in touch with Motodrives for industrial drives, motors, and automation solutions. Contact our expert team for quotes and technical support.">
    <meta name="keywords" content="contact motodrives, industrial drives support, automation solutions quote, motor control contact">
    
    <!-- Open Graph -->
    <meta property="og:title" content="Contact Us - Motodrives">
    <meta property="og:description" content="Get in touch with Motodrives for industrial drives, motors, and automation solutions">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= SITE_URL ?>/contact.php">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        .contact-info-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .contact-info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }
        
        .contact-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin: 0 auto 1.5rem;
        }
        
        .map-container {
            height: 400px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .contact-form {
            background: white;
            border-radius: 15px;
            padding: 2.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
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
        
        .floating-label {
            position: relative;
        }
        
        .floating-label .form-control {
            padding-top: 1.5rem;
        }
        
        .floating-label label {
            position: absolute;
            top: 0.75rem;
            left: 1rem;
            transition: all 0.3s ease;
            color: #6c757d;
            pointer-events: none;
        }
        
        .floating-label .form-control:focus + label,
        .floating-label .form-control:not(:placeholder-shown) + label {
            top: 0.25rem;
            font-size: 0.75rem;
            color: #007bff;
            background: white;
            padding: 0 0.25rem;
        }
    </style>
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
                        <a class="nav-link active" href="contact.php">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="page-header" style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: white; padding: 120px 0 80px;">
        <div class="container text-center">
            <h1 class="display-4 fw-bold mb-4">Contact Us</h1>
            <p class="lead">Get in touch with our expert team for industrial automation solutions</p>
            <div class="row justify-content-center mt-5">
                <div class="col-md-4">
                    <div class="contact-info-card text-white" style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px);">
                        <div class="contact-icon" style="background: rgba(255,255,255,0.2);">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <h5>Call Us</h5>
                        <p><?= $settings['contact_phone'] ?? '+1 (555) 123-4567' ?></p>
                        <p class="small">Mon-Fri: 9AM-6PM EST</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="contact-info-card text-white" style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px);">
                        <div class="contact-icon" style="background: rgba(255,255,255,0.2);">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h5>Email Us</h5>
                        <p><?= $settings['contact_email'] ?? 'info@motodrives.com' ?></p>
                        <p class="small">24/7 Support Available</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="contact-info-card text-white" style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px);">
                        <div class="contact-icon" style="background: rgba(255,255,255,0.2);">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h5>Visit Us</h5>
                        <p><?= $settings['contact_address'] ?? '123 Industrial Drive, Tech City, TC 12345' ?></p>
                        <p class="small">By Appointment Only</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mb-4">
                    <!-- Contact Form -->
                    <div class="contact-form">
                        <h3 class="mb-4">Send Us a Message</h3>
                        
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
                        
                        <form method="POST" id="contactForm" novalidate>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" required 
                                           value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                                    <div class="invalid-feedback">Please enter your full name</div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" id="email" name="email" required 
                                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                                    <div class="invalid-feedback">Please enter a valid email address</div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                                           placeholder="+1 (555) 123-4567">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="company" class="form-label">Company Name</label>
                                    <input type="text" class="form-control" id="company" name="company" 
                                           value="<?= htmlspecialchars($_POST['company'] ?? '') ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="subject" class="form-label">Subject *</label>
                                <input type="text" class="form-control" id="subject" name="subject" required 
                                       value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>"
                                       placeholder="How can we help you?">
                            </div>
                            
                            <div class="mb-3">
                                <label for="product_interest" class="form-label">Product Interest</label>
                                <select class="form-select" id="product_interest" name="product_interest">
                                    <option value="">Select a product category (optional)</option>
                                    <?php while ($category = $categories->fetch_assoc()): ?>
                                    <option value="<?= htmlspecialchars($category['name']) ?>" 
                                            <?= (isset($_POST['product_interest']) && $_POST['product_interest'] == $category['name']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="mb-4">
                                <label for="message" class="form-label">Message *</label>
                                <textarea class="form-control" id="message" name="message" rows="5" required 
                                          placeholder="Please describe your requirements in detail..."><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                                <div class="invalid-feedback">Please enter your message</div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="newsletter" name="newsletter">
                                    <label class="form-check-label" for="newsletter">
                                        Subscribe to our newsletter for product updates and industry news
                                    </label>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>Send Message
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <!-- Contact Information -->
                    <div class="contact-info-card mb-4">
                        <div class="contact-icon">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <h4 class="text-center mb-3">Quick Information</h4>
                        
                        <div class="mb-3">
                            <h6 class="text-primary"><i class="fas fa-clock me-2"></i>Business Hours</h6>
                            <p class="mb-1"><strong>Monday - Friday:</strong> 9:00 AM - 6:00 PM EST</p>
                            <p class="mb-0"><strong>Saturday:</strong> 10:00 AM - 2:00 PM EST</p>
                            <p class="text-muted"><strong>Sunday:</strong> Closed</p>
                        </div>
                        
                        <div class="mb-3">
                            <h6 class="text-primary"><i class="fas fa-bolt me-2"></i>Emergency Support</h6>
                            <p>For urgent technical support, call our emergency hotline:</p>
                            <p class="fw-bold text-danger">+1 (555) 911-HELP</p>
                        </div>
                        
                        <div class="mb-3">
                            <h6 class="text-primary"><i class="fas fa-globe me-2"></i>Global Presence</h6>
                            <p class="small mb-0">Serving customers in 50+ countries with regional support centers in North America, Europe, and Asia-Pacific.</p>
                        </div>
                    </div>
                    
                    <!-- Quick Links -->
                    <div class="contact-info-card">
                        <h5 class="mb-3">Quick Links</h5>
                        <div class="list-group list-group-flush">
                            <a href="products.php" class="list-group-item list-group-item-action border-0">
                                <i class="fas fa-box me-2"></i>Product Catalog
                            </a>
                            <a href="gallery.php" class="list-group-item list-group-item-action border-0">
                                <i class="fas fa-images me-2"></i>Project Gallery
                            </a>
                            <a href="blog.php" class="list-group-item list-group-item-action border-0">
                                <i class="fas fa-blog me-2"></i>Technical Blog
                            </a>
                            <a href="industries.php" class="list-group-item list-group-item-action border-0">
                                <i class="fas fa-industry me-2"></i>Industries We Serve
                            </a>
                            <a href="assets/documents/catalog.pdf" class="list-group-item list-group-item-action border-0" target="_blank">
                                <i class="fas fa-download me-2"></i>Download Catalog
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <h3 class="text-center mb-4">Find Us on Map</h3>
            <div class="map-container">
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3024.678621401921!2d-74.0060!3d40.7128!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNDDCsDQyJzQ2LjgiTiA3NMKwMDAnMjYuMCJX!5e0!3m2!1sen!2sus!4v1234567890"
                    width="100%" 
                    height="100%" 
                    style="border:0;" 
                    allowfullscreen="" 
                    loading="lazy">
                </iframe>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5 bg-primary text-white">
        <div class="container text-center">
            <h2 class="display-5 fw-bold mb-4">Ready to Get Started?</h2>
            <p class="lead mb-4">Our technical team is ready to help you find the perfect solution for your automation needs</p>
            <div class="hero-buttons">
                <a href="tel:+15551234567" class="btn btn-light btn-lg me-3">
                    <i class="fas fa-phone me-2"></i>Call Now
                </a>
                <a href="products.php" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-shopping-cart me-2"></i>Browse Products
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

        // Form validation and submission
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Reset validation states
            this.classList.remove('was-validated');
            const inputs = this.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.classList.remove('is-invalid');
            });
            
            // Validate form
            let isValid = true;
            const requiredFields = this.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
                
                // Email validation
                if (field.type === 'email' && field.value) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(field.value)) {
                        field.classList.add('is-invalid');
                        isValid = false;
                    }
                }
            });
            
            if (isValid) {
                // Show loading state
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
                submitBtn.disabled = true;
                
                // Submit form
                this.submit();
            } else {
                // Scroll to first error
                const firstError = this.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });

        // Phone number formatting
        document.getElementById('phone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 0) {
                if (value.length <= 3) {
                    value = `(${value}`;
                } else if (value.length <= 6) {
                    value = `(${value.slice(0, 3)}) ${value.slice(3)}`;
                } else {
                    value = `(${value.slice(0, 3)}) ${value.slice(3, 6)}-${value.slice(6, 10)}`;
                }
            }
            e.target.value = value;
        });

        // Auto-populate subject if coming from product page
        const urlParams = new URLSearchParams(window.location.search);
        const productInterest = urlParams.get('product');
        if (productInterest && !document.getElementById('subject').value) {
            document.getElementById('subject').value = `Enquiry about ${productInterest}`;
            document.getElementById('product_interest').value = productInterest;
        }

        // Character counter for message
        const messageField = document.getElementById('message');
        if (messageField) {
            const maxLength = 1000;
            messageField.setAttribute('maxlength', maxLength);
            
            // Add character counter
            const counter = document.createElement('small');
            counter.className = 'text-muted';
            counter.textContent = `0 / ${maxLength} characters`;
            messageField.parentNode.appendChild(counter);
            
            messageField.addEventListener('input', function() {
                counter.textContent = `${this.value.length} / ${maxLength} characters`;
                if (this.value.length > maxLength * 0.9) {
                    counter.classList.add('text-warning');
                    counter.classList.remove('text-muted');
                } else {
                    counter.classList.add('text-muted');
                    counter.classList.remove('text-warning');
                }
            });
        }
    </script>
</body>
</html>