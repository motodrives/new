# 🏭 Motodrives - Industrial Drives & Automation Website

[![License](https://img.shields.io/badge/license-Proprietary-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D7.4-8892BF.svg)](https://php.net/)
[![MySQL](https://img.shields.io/badge/mysql-%3E%3D5.7-4479A1.svg)](https://www.mysql.com/)
[![Docker](https://img.shields.io/badge/docker-ready-2496ED.svg)](https://www.docker.com/)

A comprehensive, dynamic, and SEO-optimized business website for industrial drives, motors, and automation equipment manufacturing.

## 🌟 Features

### Frontend
- ✨ Modern responsive design with Bootstrap 5
- 🎨 Dynamic product catalog with filtering and search
- 📱 Mobile-first responsive layout
- 🌙 Dark mode toggle
- ⚡ Lazy loading and performance optimization
- 🔍 SEO-optimized with meta tags and structured data
- 📊 Interactive counters and animations
- 📧 Contact form with validation

### Backend
- 🔐 Secure admin panel with authentication
- 📦 Complete CRUD operations for all content
- 🖼️ Image upload and management system
- 📨 Email notification system
- 📊 Dashboard with real-time analytics
- 🔒 Role-based access control
- 💾 Database backup functionality

### Technical
- 🐳 Docker containerization ready
- 🚀 One-click deployment to Render.com
- 🔒 Security hardened (XSS, SQL injection protection)
- 📈 Performance optimized (caching, compression)
- 🌐 API endpoints for extensibility
- 📱 Progressive Web App ready

## 🚀 Quick Start

### Using Docker (Recommended)

```bash
# Clone the repository
git clone https://github.com/yourusername/motodrives-website.git
cd motodrives-website

# Start with Docker Compose
docker-compose up -d

# Access the application
# Website: http://localhost:8080
# Admin: http://localhost:8080/admin/login.php
# phpMyAdmin: http://localhost:8081
```

### Manual Installation

```bash
# Clone the repository
git clone https://github.com/yourusername/motodrives-website.git
cd motodrives-website

# Install dependencies
composer install

# Configure environment
cp .env.example .env
# Edit .env with your settings

# Run installation wizard
# Navigate to: http://localhost/install.php
```

## 📋 Requirements

- **PHP** 7.4 or higher (8.0+ recommended)
- **MySQL** 5.7+ or MariaDB 10.2+
- **Apache** 2.4+ with mod_rewrite
- **Composer** (for dependency management)
- **Docker** (optional, for containerized deployment)

### Required PHP Extensions
- mysqli
- gd
- curl
- json
- mbstring
- session

## 🐳 Docker Deployment

### Development

```bash
# Start all services
docker-compose up -d

# View logs
docker-compose logs -f

# Stop services
docker-compose down
```

### Production

```bash
# Use production configuration
docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d

# Scale web service
docker-compose up -d --scale web=3
```

## 🌐 Deploy to Render.com

[![Deploy to Render](https://render.com/images/deploy-to-render-button.svg)](https://render.com/deploy)

### Automatic Deployment

1. Fork this repository
2. Sign up at [Render.com](https://render.com)
3. Click "New +" → "Blueprint"
4. Connect your GitHub repository
5. Render will automatically deploy using `render.yaml`

### Manual Deployment

See [GITHUB_SETUP.md](GITHUB_SETUP.md) for detailed instructions.

## 📖 Documentation

- [Installation Guide](README.md) - Detailed installation instructions
- [Deployment Guide](DEPLOYMENT.md) - Docker and hosting deployment
- [GitHub Setup](GITHUB_SETUP.md) - GitHub and Render.com setup
- [Project Summary](PROJECT_SUMMARY.md) - Complete project overview

## 🗂️ Project Structure

```
motodrives/
├── admin/              # Admin panel
│   ├── dashboard.php
│   ├── login.php
│   └── manage_*.php
├── api/                # API endpoints
│   ├── contact.php
│   └── products.php
├── assets/             # Frontend assets
│   ├── css/
│   ├── js/
│   └── images/
├── config/             # Configuration files
│   └── config.php
├── sql/                # Database schema
│   └── motodrives.sql
├── uploads/            # User uploads
├── index.php           # Homepage
├── products.php        # Products page
├── contact.php         # Contact page
├── about.php           # About page
├── Dockerfile          # Docker configuration
├── docker-compose.yml  # Docker Compose config
├── render.yaml         # Render.com blueprint
└── composer.json       # PHP dependencies
```

## 🔧 Configuration

### Environment Variables

Create a `.env` file from `.env.example`:

```env
# Database
DB_HOST=localhost
DB_NAME=motodrives
DB_USER=your_user
DB_PASS=your_password

# Site
SITE_URL=http://localhost:8080
SITE_NAME=Motodrives
ADMIN_EMAIL=admin@motodrives.com

# Environment
APP_ENV=development
APP_DEBUG=true
```

### Database Configuration

For Docker:
```env
DB_HOST=db
DB_NAME=motodrives
DB_USER=motodrives_user
DB_PASS=motodrives_pass
```

For Render.com:
```env
DB_HOST=${DATABASE_URL}  # Auto-configured
```

## 🔐 Security

### Default Credentials

**⚠️ Change immediately after installation!**

- Email: `admin@motodrives.com`
- Password: `admin123`

### Security Features

- ✅ Password hashing with bcrypt
- ✅ SQL injection prevention
- ✅ XSS protection
- ✅ CSRF token validation
- ✅ Secure session management
- ✅ File upload validation
- ✅ Input sanitization

### Security Checklist

- [ ] Change default admin password
- [ ] Delete `install.php` after setup
- [ ] Set `APP_DEBUG=false` in production
- [ ] Use HTTPS/SSL certificate
- [ ] Configure firewall rules
- [ ] Set proper file permissions
- [ ] Enable security headers
- [ ] Regular security updates

## 📊 Admin Panel

Access the admin panel at `/admin/login.php`

### Features

- 📈 Dashboard with analytics
- 📦 Product management (CRUD)
- 📝 Blog/news management
- 🖼️ Gallery management
- 📧 Enquiry management
- ⚙️ Settings configuration
- 👥 User management

## 🎨 Customization

### Branding

Edit `assets/css/style.css`:

```css
:root {
    --electric-blue: #007bff;
    --steel-grey: #6c757d;
    /* Add your brand colors */
}
```

### Content

Use the admin panel to:
- Add/edit products
- Manage blog posts
- Update company information
- Configure site settings

## 🧪 Testing

```bash
# Run PHP tests
composer test

# Check code style
composer check-style

# Fix code style
composer fix-style
```

## 📈 Performance

### Optimization Features

- ⚡ Lazy loading for images
- 🗜️ Gzip compression
- 💾 Browser caching
- 🔄 Database query optimization
- 📦 Minified assets
- 🚀 CDN ready

### Performance Monitoring

```bash
# Check page load time
curl -w "@curl-format.txt" -o /dev/null -s http://localhost:8080

# Monitor database performance
docker-compose exec db mysqladmin -u root -p processlist
```

## 🔄 Updates

### Pull Latest Changes

```bash
git pull origin main
docker-compose down
docker-compose up -d --build
```

### Database Migrations

```bash
# Backup current database
docker-compose exec db mysqldump -u root -p motodrives > backup.sql

# Run migrations
docker-compose exec web php migrate.php
```

## 🐛 Troubleshooting

### Common Issues

**Database connection failed**
```bash
# Check database is running
docker-compose ps

# Verify credentials in .env
cat .env | grep DB_
```

**File upload fails**
```bash
# Check permissions
chmod 777 uploads/

# Check PHP limits
php -i | grep upload_max_filesize
```

**404 errors**
```bash
# Ensure .htaccess exists
ls -la .htaccess

# Check Apache mod_rewrite
apache2ctl -M | grep rewrite
```

## 📞 Support

- 📧 Email: support@motodrives.com
- 🐛 Issues: [GitHub Issues](https://github.com/yourusername/motodrives-website/issues)
- 📖 Docs: [Documentation](README.md)

## 🤝 Contributing

This is a proprietary project. Please contact the team for contribution guidelines.

## 📄 License

Proprietary - All rights reserved. Contact Motodrives for licensing information.

## 🙏 Acknowledgments

- Bootstrap 5 for the UI framework
- Font Awesome for icons
- PHP community for excellent tools
- Docker for containerization

---

**Built with ❤️ by NinjaTech AI**

For more information, visit [Motodrives.com](https://motodrives.com)