<?php
/**
 * Motodrives Website Installation Script
 * This script will guide you through the installation process
 */

// Prevent direct access after installation
if (file_exists('config/installed.lock')) {
    die('Installation already completed. Please delete the install.php file for security.');
}

// Installation steps
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$step = max(1, min(5, $step)); // Limit steps between 1-5

$errors = [];
$success = [];

// Check PHP version
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    $errors[] = 'PHP 7.4.0 or higher is required. Current version: ' . PHP_VERSION;
}

// Check required extensions
$required_extensions = ['mysqli', 'gd', 'curl', 'json', 'mbstring'];
foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $errors[] = "Required PHP extension '$ext' is not installed or enabled.";
    }
}

// Check writable directories
$writable_dirs = ['uploads', 'config'];
foreach ($writable_dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    if (!is_writable($dir)) {
        $errors[] = "Directory '$dir' is not writable. Please check permissions.";
    }
}

// Handle form submissions
if ($_POST) {
    switch ($step) {
        case 2:
            // Database connection test
            $db_host = $_POST['db_host'];
            $db_name = $_POST['db_name'];
            $db_user = $_POST['db_user'];
            $db_pass = $_POST['db_pass'];
            
            try {
                $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
                if ($conn->connect_error) {
                    throw new Exception($conn->connect_error);
                }
                $success[] = 'Database connection successful!';
                
                // Save database info to session
                session_start();
                $_SESSION['db_config'] = [
                    'host' => $db_host,
                    'name' => $db_name,
                    'user' => $db_user,
                    'pass' => $db_pass
                ];
                
                header('Location: install.php?step=3');
                exit();
            } catch (Exception $e) {
                $errors[] = 'Database connection failed: ' . $e->getMessage();
            }
            break;
            
        case 3:
            // Import database
            session_start();
            if (!isset($_SESSION['db_config'])) {
                header('Location: install.php?step=2');
                exit();
            }
            
            $db_config = $_SESSION['db_config'];
            
            try {
                $conn = new mysqli($db_config['host'], $db_config['user'], $db_config['pass'], $db_config['name']);
                
                // Read and execute SQL file
                $sql_file = 'sql/motodrives.sql';
                if (!file_exists($sql_file)) {
                    throw new Exception('Database schema file not found: ' . $sql_file);
                }
                
                $sql = file_get_contents($sql_file);
                
                // Split SQL into individual statements
                $statements = array_filter(array_map('trim', explode(';', $sql)));
                
                foreach ($statements as $statement) {
                    if (!empty($statement)) {
                        if (!$conn->query($statement)) {
                            throw new Exception('SQL Error: ' . $conn->error);
                        }
                    }
                }
                
                $success[] = 'Database tables created successfully!';
                
                // Save admin info to session
                $_SESSION['admin_config'] = [
                    'name' => $_POST['admin_name'],
                    'email' => $_POST['admin_email'],
                    'password' => $_POST['admin_password']
                ];
                
                header('Location: install.php?step=4');
                exit();
            } catch (Exception $e) {
                $errors[] = 'Database import failed: ' . $e->getMessage();
            }
            break;
            
        case 4:
            // Create config file and finalize
            session_start();
            if (!isset($_SESSION['db_config']) || !isset($_SESSION['admin_config'])) {
                header('Location: install.php?step=2');
                exit();
            }
            
            $db_config = $_SESSION['db_config'];
            $admin_config = $_SESSION['admin_config'];
            $site_config = $_POST;
            
            try {
                // Create config.php
                $config_content = "<?php\n";
                $config_content .= "// Database Configuration\n";
                $config_content .= "define('DB_HOST', '{$db_config['host']}');\n";
                $config_content .= "define('DB_USER', '{$db_config['user']}');\n";
                $config_content .= "define('DB_PASS', '{$db_config['pass']}');\n";
                $config_content .= "define('DB_NAME', '{$db_config['name']}');\n\n";
                $config_content .= "// Site Configuration\n";
                $config_content .= "define('SITE_URL', '{$site_config['site_url']}');\n";
                $config_content .= "define('SITE_NAME', '{$site_config['site_name']}');\n";
                $config_content .= "define('ADMIN_EMAIL', '{$admin_config['email']}');\n";
                $config_content .= "?>\n";
                
                if (file_put_contents('config/config.php', $config_content) === false) {
                    throw new Exception('Failed to create config.php file');
                }
                
                // Update admin user
                $conn = new mysqli($db_config['host'], $db_config['user'], $db_config['pass'], $db_config['name']);
                $hashed_password = password_hash($admin_config['password'], PASSWORD_DEFAULT);
                $update_admin = "UPDATE users SET name = '{$admin_config['name']}', email = '{$admin_config['email']}', password = '$hashed_password' WHERE id = 1";
                $conn->query($update_admin);
                
                // Update site settings
                $update_settings = [
                    'site_name' => $site_config['site_name'],
                    'site_description' => $site_config['site_description'],
                    'site_keywords' => $site_config['site_keywords'],
                    'contact_email' => $site_config['contact_email'],
                    'contact_phone' => $site_config['contact_phone'],
                    'contact_address' => $site_config['contact_address']
                ];
                
                foreach ($update_settings as $key => $value) {
                    $conn->query("UPDATE settings SET setting_value = '$value' WHERE setting_key = '$key'");
                }
                
                // Create installation lock file
                file_put_contents('config/installed.lock', date('Y-m-d H:i:s'));
                
                $success[] = 'Installation completed successfully!';
                
                // Clear session
                session_destroy();
                
                header('Location: install.php?step=5');
                exit();
            } catch (Exception $e) {
                $errors[] = 'Configuration failed: ' . $e->getMessage();
            }
            break;
    }
}

// Helper function to get current URL
function getCurrentUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $path = dirname($_SERVER['PHP_SELF']);
    return $protocol . '://' . $host . $path;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Motodrives Installation - Step <?= $step ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
        }
        
        .installation-container {
            max-width: 800px;
            margin: 50px auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .install-header {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .install-header h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .install-header p {
            margin: 0;
            opacity: 0.9;
        }
        
        .progress-steps {
            display: flex;
            justify-content: space-between;
            padding: 2rem;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        
        .step {
            flex: 1;
            text-align: center;
            position: relative;
        }
        
        .step::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 50%;
            right: -50%;
            height: 2px;
            background: #dee2e6;
            z-index: 1;
        }
        
        .step:last-child::before {
            display: none;
        }
        
        .step.active::before {
            background: #007bff;
        }
        
        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #dee2e6;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.5rem;
            font-weight: 600;
            position: relative;
            z-index: 2;
        }
        
        .step.active .step-number,
        .step.completed .step-number {
            background: #007bff;
            color: white;
        }
        
        .step-label {
            font-size: 0.875rem;
            color: #6c757d;
        }
        
        .step.active .step-label {
            color: #007bff;
            font-weight: 600;
        }
        
        .step.completed .step-label {
            color: #28a745;
        }
        
        .install-content {
            padding: 2rem;
        }
        
        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .success-animation {
            text-align: center;
            padding: 3rem;
        }
        
        .success-icon {
            width: 100px;
            height: 100px;
            background: #28a745;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            font-size: 3rem;
            color: white;
            animation: scaleIn 0.5s ease;
        }
        
        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }
        
        .requirements-list {
            list-style: none;
            padding: 0;
        }
        
        .requirements-list li {
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
        }
        
        .requirements-list li.success {
            background: #d4edda;
            color: #155724;
        }
        
        .requirements-list li.error {
            background: #f8d7da;
            color: #721c24;
        }
        
        .requirements-list i {
            margin-right: 0.75rem;
            width: 20px;
        }
    </style>
</head>
<body>
    <div class="installation-container">
        <!-- Header -->
        <div class="install-header">
            <h1><i class="fas fa-cogs me-2"></i>Motodrives Installation</h1>
            <p>Industrial Drives & Automation Website</p>
        </div>
        
        <!-- Progress Steps -->
        <div class="progress-steps">
            <div class="step <?= $step >= 1 ? 'completed' : '' ?> <?= $step == 1 ? 'active' : '' ?>">
                <div class="step-number">
                    <?= $step > 1 ? '<i class="fas fa-check"></i>' : '1' ?>
                </div>
                <div class="step-label">System Check</div>
            </div>
            <div class="step <?= $step >= 2 ? 'completed' : '' ?> <?= $step == 2 ? 'active' : '' ?>">
                <div class="step-number">
                    <?= $step > 2 ? '<i class="fas fa-check"></i>' : '2' ?>
                </div>
                <div class="step-label">Database</div>
            </div>
            <div class="step <?= $step >= 3 ? 'completed' : '' ?> <?= $step == 3 ? 'active' : '' ?>">
                <div class="step-number">
                    <?= $step > 3 ? '<i class="fas fa-check"></i>' : '3' ?>
                </div>
                <div class="step-label">Admin Setup</div>
            </div>
            <div class="step <?= $step >= 4 ? 'completed' : '' ?> <?= $step == 4 ? 'active' : '' ?>">
                <div class="step-number">
                    <?= $step > 4 ? '<i class="fas fa-check"></i>' : '4' ?>
                </div>
                <div class="step-label">Site Config</div>
            </div>
            <div class="step <?= $step >= 5 ? 'completed' : '' ?> <?= $step == 5 ? 'active' : '' ?>">
                <div class="step-number">
                    <?= $step > 5 ? '<i class="fas fa-check"></i>' : '5' ?>
                </div>
                <div class="step-label">Complete</div>
            </div>
        </div>
        
        <!-- Content -->
        <div class="install-content">
            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <h5><i class="fas fa-exclamation-triangle me-2"></i>Errors Found</h5>
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <h5><i class="fas fa-check-circle me-2"></i>Success</h5>
                <ul class="mb-0">
                    <?php foreach ($success as $msg): ?>
                    <li><?= htmlspecialchars($msg) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <?php switch ($step): case 1: ?>
                <!-- System Requirements Check -->
                <h3 class="mb-4">System Requirements Check</h3>
                
                <ul class="requirements-list">
                    <li class="<?= version_compare(PHP_VERSION, '7.4.0', '>=') ? 'success' : 'error' ?>">
                        <i class="fas <?= version_compare(PHP_VERSION, '7.4.0', '>=') ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
                        PHP Version: <?= PHP_VERSION ?> (Required: 7.4.0+)
                    </li>
                    <?php foreach ($required_extensions as $ext): ?>
                    <li class="<?= extension_loaded($ext) ? 'success' : 'error' ?>">
                        <i class="fas <?= extension_loaded($ext) ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
                        <?= $ext ?> Extension: <?= extension_loaded($ext) ? 'Installed' : 'Missing' ?>
                    </li>
                    <?php endforeach; ?>
                    <li class="<?= is_writable('uploads') ? 'success' : 'error' ?>">
                        <i class="fas <?= is_writable('uploads') ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
                        Uploads Directory: <?= is_writable('uploads') ? 'Writable' : 'Not Writable' ?>
                    </li>
                    <li class="<?= is_writable('config') ? 'success' : 'error' ?>">
                        <i class="fas <?= is_writable('config') ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
                        Config Directory: <?= is_writable('config') ? 'Writable' : 'Not Writable' ?>
                    </li>
                </ul>
                
                <?php if (empty($errors)): ?>
                <div class="text-center mt-4">
                    <a href="install.php?step=2" class="btn btn-primary btn-lg">
                        Continue to Database Setup <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
                <?php endif; ?>
                <?php break; ?>
                
                <?php case 2: ?>
                <!-- Database Configuration -->
                <h3 class="mb-4">Database Configuration</h3>
                <p class="text-muted mb-4">Please provide your database connection details.</p>
                
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="db_host" class="form-label">Database Host</label>
                            <input type="text" class="form-control" id="db_host" name="db_host" 
                                   value="localhost" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="db_name" class="form-label">Database Name</label>
                            <input type="text" class="form-control" id="db_name" name="db_name" 
                                   placeholder="motodrives" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="db_user" class="form-label">Database Username</label>
                            <input type="text" class="form-control" id="db_user" name="db_user" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="db_pass" class="form-label">Database Password</label>
                            <input type="password" class="form-control" id="db_pass" name="db_pass">
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="install.php?step=1" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Test Connection <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </form>
                <?php break; ?>
                
                <?php case 3: ?>
                <!-- Database Import & Admin Setup -->
                <h3 class="mb-4">Create Administrator Account</h3>
                <p class="text-muted mb-4">Set up your admin account for website management.</p>
                
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="admin_name" class="form-label">Admin Name</label>
                            <input type="text" class="form-control" id="admin_name" name="admin_name" 
                                   value="Admin" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="admin_email" class="form-label">Admin Email</label>
                            <input type="email" class="form-control" id="admin_email" name="admin_email" 
                                   placeholder="admin@example.com" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="admin_password" class="form-label">Admin Password</label>
                            <input type="password" class="form-control" id="admin_password" name="admin_password" 
                                   required minlength="8">
                            <small class="text-muted">Minimum 8 characters</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="admin_password_confirm" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="admin_password_confirm" name="admin_password_confirm" 
                                   required>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="install.php?step=2" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Create Admin Account <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </form>
                
                <script>
                    document.querySelector('form').addEventListener('submit', function(e) {
                        const password = document.getElementById('admin_password').value;
                        const confirm = document.getElementById('admin_password_confirm').value;
                        
                        if (password !== confirm) {
                            e.preventDefault();
                            alert('Passwords do not match!');
                        }
                    });
                </script>
                <?php break; ?>
                
                <?php case 4: ?>
                <!-- Site Configuration -->
                <h3 class="mb-4">Site Configuration</h3>
                <p class="text-muted mb-4">Configure your website settings.</p>
                
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="site_name" class="form-label">Site Name</label>
                            <input type="text" class="form-control" id="site_name" name="site_name" 
                                   value="Motodrives" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="site_url" class="form-label">Site URL</label>
                            <input type="url" class="form-control" id="site_url" name="site_url" 
                                   value="<?= getCurrentUrl() ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="site_description" class="form-label">Site Description</label>
                        <textarea class="form-control" id="site_description" name="site_description" rows="3">Leading manufacturer of industrial drives, motors, and automation equipment</textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="site_keywords" class="form-label">Site Keywords</label>
                        <input type="text" class="form-control" id="site_keywords" name="site_keywords" 
                               value="industrial drives, motors, automation, AC drives, DC drives, servo drives">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="contact_email" class="form-label">Contact Email</label>
                            <input type="email" class="form-control" id="contact_email" name="contact_email" 
                                   value="info@motodrives.com" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="contact_phone" class="form-label">Contact Phone</label>
                            <input type="tel" class="form-control" id="contact_phone" name="contact_phone" 
                                   value="+1 (555) 123-4567" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="contact_address" class="form-label">Contact Address</label>
                        <input type="text" class="form-control" id="contact_address" name="contact_address" 
                               value="123 Industrial Drive, Tech City, TC 12345" required>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="install.php?step=3" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back
                        </a>
                        <button type="submit" class="btn btn-success">
                            Complete Installation <i class="fas fa-check ms-2"></i>
                        </button>
                    </div>
                </form>
                <?php break; ?>
                
                <?php case 5: ?>
                <!-- Installation Complete -->
                <div class="success-animation">
                    <div class="success-icon">
                        <i class="fas fa-check"></i>
                    </div>
                    <h3 class="mb-3">Installation Complete!</h3>
                    <p class="text-muted mb-4">Your Motodrives website has been successfully installed.</p>
                    
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle me-2"></i>Next Steps:</h5>
                        <ol class="mb-0">
                            <li>Delete the <code>install.php</code> file for security</li>
                            <li>Login to the admin panel at <code>admin/login.php</code></li>
                            <li>Configure your products and content</li>
                            <li>Customize the website design and settings</li>
                        </ol>
                    </div>
                    
                    <div class="row text-start">
                        <div class="col-md-6">
                            <h6>Admin Login Details:</h6>
                            <p><strong>URL:</strong> <a href="admin/login.php">admin/login.php</a></p>
                            <p><strong>Email:</strong> <?= $_SESSION['admin_config']['email'] ?? 'admin@motodrives.com' ?></p>
                            <p><strong>Password:</strong> [Your chosen password]</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Quick Links:</h6>
                            <p><a href="index.php" class="btn btn-outline-primary btn-sm me-2">View Website</a></p>
                            <p><a href="admin/login.php" class="btn btn-primary btn-sm">Admin Panel</a></p>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning mt-4">
                        <h5><i class="fas fa-exclamation-triangle me-2"></i>Security Reminder</h5>
                        <p class="mb-0">Please delete the <code>install.php</code> file immediately to prevent unauthorized access to your installation.</p>
                    </div>
                    
                    <div class="d-flex justify-content-center gap-3 mt-4">
                        <a href="index.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-home me-2"></i>Visit Website
                        </a>
                        <a href="admin/login.php" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-cog me-2"></i>Admin Panel
                        </a>
                    </div>
                </div>
                <?php break; ?>
            <?php endswitch; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>