<?php
/**
 * Motodrives Configuration
 * Uses environment variables for production deployment
 */

// Database Configuration
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'motodrives');

// Site Configuration
define('SITE_URL', getenv('SITE_URL') ?: 'http://localhost/motodrives');
define('SITE_NAME', getenv('SITE_NAME') ?: 'Motodrives');
define('ADMIN_EMAIL', getenv('ADMIN_EMAIL') ?: 'admin@motodrives.com');

// Environment Detection
$isProduction = (getenv('APP_ENV') === 'production');

// File Upload Configuration
define('UPLOAD_PATH', 'uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf']);

// Session Configuration
define('SESSION_NAME', 'motodrives_session');
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);

// Error Reporting
if ($isProduction) {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Timezone
date_default_timezone_set('UTC');

// Create Database Connection
class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
    
    public $conn;
    
    public function __construct() {
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname);
        
        if ($this->conn->connect_error) {
            if ($isProduction) {
                error_log("Database connection failed: " . $this->conn->connect_error);
                die("Service temporarily unavailable. Please try again later.");
            } else {
                die("Connection failed: " . $this->conn->connect_error);
            }
        }
        
        // Set charset
        $this->conn->set_charset("utf8mb4");
    }
    
    public function query($sql) {
        return $this->conn->query($sql);
    }
    
    public function prepare($sql) {
        return $this->conn->prepare($sql);
    }
    
    public function escape($string) {
        return $this->conn->real_escape_string($string);
    }
    
    public function insert_id() {
        return $this->conn->insert_id;
    }
    
    public function close() {
        $this->conn->close();
    }
}

// Initialize database connection
$db = new Database();

// Utility Functions
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = trim($text, '-');
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = strtolower($text);
    $text = preg_replace('~[^\w]+~', '', $text);
    return empty($text) ? 'n-a' : $text;
}

function uploadFile($file, $destination) {
    $allowed = ALLOWED_EXTENSIONS;
    $filename = $file['name'];
    $filetype = pathinfo($filename, PATHINFO_EXTENSION);
    $filesize = $file['size'];
    
    // Validate file
    if (!in_array(strtolower($filetype), $allowed)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }
    
    if ($filesize > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File too large'];
    }
    
    // Generate unique filename
    $newname = time() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '', $filename);
    $target_path = $destination . $newname;
    
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return ['success' => true, 'filename' => $newname, 'path' => $target_path];
    } else {
        return ['success' => false, 'message' => 'Upload failed'];
    }
}

// Start session
session_name(SESSION_NAME);
session_start();
?>