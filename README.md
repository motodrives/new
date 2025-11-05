# Motodrives - Industrial Drives & Automation Website

A comprehensive, dynamic, and SEO-optimized business website for Motodrives, an industrial drives, motors, and automation equipment manufacturer.

## 🚀 Features

### Frontend Features
- **Modern Responsive Design** - Built with Bootstrap 5 and custom CSS
- **Hero Section** - Animated background with compelling call-to-action
- **Dynamic Product Catalog** - With filtering, search, and pagination
- **Industry Showcase** - Display sectors served with interactive cards
- **Blog/News System** - Dynamic content management
- **Gallery** - Product and installation images with lightbox
- **Contact Form** - With validation and email notifications
- **Interactive Elements** - Counters, animations, smooth scrolling
- **Dark Mode Toggle** - User preference theming
- **SEO Optimized** - Meta tags, sitemap, structured data

### Backend Features
- **Secure Admin Panel** - Role-based authentication system
- **CRUD Operations** - Complete management for all content types
- **Dashboard Analytics** - Real-time statistics and metrics
- **File Upload System** - Secure image handling with validation
- **Email Integration** - PHPMailer for contact form submissions
- **Data Export** - CSV export for enquiries and reports
- **API Endpoints** - JSON APIs for frontend integration

### Technical Features
- **PHP 8.0+ Compatible** - Modern PHP practices
- **MySQL Database** - Optimized schema with proper relationships
- **SEO-Friendly URLs** - .htaccess routing and URL rewriting
- **Security Hardened** - Input validation, XSS/SQL injection protection
- **Performance Optimized** - Lazy loading, caching, compression
- **Mobile Responsive** - Fully responsive across all devices

## 📋 Requirements

### Server Requirements
- **PHP** 7.4+ (8.0+ recommended)
- **MySQL** 5.7+ or MariaDB 10.2+
- **Apache** 2.4+ with mod_rewrite enabled
- **SSL Certificate** (recommended for production)

### Required PHP Extensions
- `mysqli` - Database connectivity
- `gd` - Image processing
- `curl` - HTTP requests
- `json` - JSON handling
- `mbstring` - Multi-byte string handling
- `session` - Session management

### Optional but Recommended
- `opcache` - PHP bytecode cache
- `imagick` - Advanced image processing
- `redis` - Caching (for high-traffic sites)

## 🛠️ Installation

### Quick Installation (Recommended)

1. **Upload Files**
   ```bash
   # Upload all files to your web server
   # Ensure the directory structure is maintained
   ```

2. **Set Permissions**
   ```bash
   chmod 755 /path/to/motodrives
   chmod 777 /path/to/motodrives/uploads
   chmod 777 /path/to/motodrives/config
   ```

3. **Run Installation Wizard**
   - Open your browser and navigate to: `https://yourdomain.com/motodrives/install.php`
   - Follow the 5-step installation wizard
   - The wizard will guide you through:
     - System requirements check
     - Database configuration
     - Admin account setup
     - Site configuration
     - Final installation

4. **Secure Installation**
   ```bash
   # Delete installation file
   rm /path/to/motodrives/install.php
   ```

### Manual Installation

1. **Database Setup**
   ```sql
   -- Create database
   CREATE DATABASE motodrives CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   
   -- Create user (optional but recommended)
   CREATE USER 'motodrives'@'localhost' IDENTIFIED BY 'secure_password';
   GRANT ALL PRIVILEGES ON motodrives.* TO 'motodrives'@'localhost';
   FLUSH PRIVILEGES;
   
   -- Import database schema
   mysql -u username -p motodrives < sql/motodrives.sql
   ```

2. **Configuration**
   ```bash
   # Copy configuration template
   cp config/config.example.php config/config.php
   
   # Edit configuration
   nano config/config.php
   ```
   
   Update the following settings:
   ```php
   // Database Configuration
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_database_user');
   define('DB_PASS', 'your_database_password');
   define('DB_NAME', 'motodrives');
   
   // Site Configuration
   define('SITE_URL', 'https://yourdomain.com/motodrives');
   define('SITE_NAME', 'Motodrives');
   define('ADMIN_EMAIL', 'admin@yourdomain.com');
   ```

3. **Default Admin Account**
   - **Email:** admin@motodrives.com
   - **Password:** admin123
   - **Important:** Change this immediately after first login!

## 🌐 Deployment

### cPanel Deployment

1. **Upload Files**
   - Use File Manager or FTP to upload files
   - Extract ZIP if uploaded as archive
   - Ensure public_html/motodrives/ structure

2. **Create Database**
   - Go to cPanel > MySQL Databases
   - Create new database and user
   - Grant all privileges

3. **Configure Site**
   - Run installation wizard: `yourdomain.com/motodrives/install.php`
   - Or manually edit `config/config.php`

4. **Set Cron Jobs** (optional)
   ```bash
   # Backup database daily
   0 2 * * * /usr/bin/mysqldump -u user -ppassword motodrives > /path/to/backup.sql
   ```

### DirectAdmin Deployment

1. **Upload via File Manager**
   - Navigate to public_html
   - Upload and extract files
   - Set permissions as needed

2. **Database Setup**
   - DirectAdmin > MySQL Management
   - Create database and user
   - Import SQL file via phpMyAdmin

3. **Domain Configuration**
   - Point domain or subdomain to motodrives folder
   - Enable SSL certificate

### Plesk Deployment

1. **Create Subscription**
   - Add new domain or subdomain
   - Set document root to motodrives folder

2. **Database Creation**
   - Plesk > Databases > Add Database
   - Create database user with permissions

3. **Upload Files**
   - Use File Manager or FTP
   - Run installation wizard

## ⚙️ Configuration

### .htaccess Configuration
The included `.htaccess` file handles:
- SEO-friendly URLs
- Security headers
- HTTPS redirection
- Gzip compression
- Browser caching
- File access restrictions

### Customization Options

#### Branding
Edit `config/config.php` or use admin panel:
```php
define('SITE_NAME', 'Your Company Name');
define('SITE_DESCRIPTION', 'Your company description');
```

#### Email Settings
Configure in admin panel > Settings:
- SMTP settings for reliable email delivery
- Email templates customization
- Autoresponder setup

#### File Upload
Adjust limits in `config/config.php`:
```php
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);
```

## 🔧 Maintenance

### Regular Tasks

1. **Database Backups**
   ```bash
   # Daily backup
   mysqldump -u user -p motodrives > backup_$(date +%Y%m%d).sql
   
   # Compress backup
   gzip backup_$(date +%Y%m%d).sql
   ```

2. **Log Cleanup**
   ```bash
   # Clear old logs (monthly)
   find /var/log -name "*.log" -mtime +30 -delete
   ```

3. **Cache Clear**
   ```bash
   # Clear browser cache (if implemented)
   rm -rf cache/*
   ```

### Updates and Security

1. **Keep PHP Updated**
   - Regularly update to latest stable version
   - Monitor security advisories

2. **Database Optimization**
   ```sql
   -- Optimize tables monthly
   OPTIMIZE TABLE products, blogs, enquiries;
   
   -- Check for issues
   CHECK TABLE products, blogs, enquiries;
   ```

3. **Security Hardening**
   - Review admin access logs
   - Update passwords regularly
   - Monitor file integrity
   - Keep backups secure

## 🎨 Customization

### Theme Customization

1. **Colors and Styles**
   - Edit `assets/css/style.css`
   - Modify CSS variables for quick changes:
   ```css
   :root {
       --electric-blue: #007bff;
       --steel-grey: #6c757d;
       --primary-color: #your-color;
   }
   ```

2. **Layout Changes**
   - Modify Bootstrap grid in PHP templates
   - Update responsive breakpoints in CSS

3. **Adding Pages**
   ```php
   // 1. Create new PHP file (e.g., services.php)
   // 2. Include header and footer
   require_once 'config/config.php';
   include 'includes/header.php';
   // Your content here
   include 'includes/footer.php';
   ```

### Advanced Customization

1. **Custom APIs**
   - Add new endpoints in `api/` directory
   - Follow existing API patterns

2. **Integration with Third Parties**
   - CRM integration
   - Payment gateways
   - Shipping calculators

3. **Performance Optimization**
   - Implement Redis caching
   - Add CDN for static assets
   - Optimize database queries

## 📊 SEO Optimization

### Built-in SEO Features
- **Meta Tags** - Automatic and custom meta tags
- **Sitemap.xml** - Auto-generated XML sitemap
- **Robots.txt** - Search engine directives
- **Structured Data** - JSON-LD markup
- **URL Structure** - SEO-friendly URLs
- **Image Optimization** - Alt tags and lazy loading

### SEO Best Practices

1. **Content Optimization**
   - Use target keywords naturally
   - Optimize headings (H1, H2, H3)
   - Write compelling meta descriptions

2. **Technical SEO**
   - Monitor page load speed
   - Ensure mobile-friendliness
   - Fix broken links regularly

3. **Local SEO**
   - Configure Google My Business
   - Add local business schema
   - Optimize for local keywords

## 🚀 Performance

### Optimization Techniques
- **Lazy Loading** - Images and content loaded on demand
- **Gzip Compression** - Reduced file sizes
- **Browser Caching** - Faster repeat visits
- **Database Optimization** - Efficient queries and indexing
- **CDN Ready** - Easy CDN integration

### Monitoring
- Use tools like Google PageSpeed Insights
- Monitor Core Web Vitals
- Track bounce rates and user engagement

## 🔍 Troubleshooting

### Common Issues

1. **Installation Fails**
   - Check file permissions (755 for directories, 644 for files)
   - Verify PHP version and extensions
   - Ensure database credentials are correct

2. **Images Not Uploading**
   - Check uploads directory permissions (777)
   - Verify file size limits
   - Ensure PHP upload limits are sufficient

3. **Email Not Sending**
   - Check SMTP configuration
   - Verify server email settings
   - Check spam folders

4. **404 Errors**
   - Ensure .htaccess is present and readable
   - Verify Apache mod_rewrite is enabled
   - Check file permissions

### Debug Mode
Enable debugging by editing `config/config.php`:
```php
// Enable error reporting (development only)
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## 📞 Support

### Documentation
- Check this README first
- Review inline code comments
- Examine database schema in `sql/motodrives.sql`

### Common Questions
- **Password Reset:** Use admin panel or directly in database
- **Backup:** Use admin export or manual database dump
- **Migration:** Export database, update config, import on new server

## 📄 License

This project is proprietary software. Contact Motodrives for licensing information.

## 🤝 Contributing

This is a proprietary project. Please do not make unauthorized modifications or distributions.

---

**Motodrives** - Empowering Industries with Automation Solutions

For technical support, contact: tech-support@motodrives.com