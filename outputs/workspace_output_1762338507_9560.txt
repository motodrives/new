# Motodrives Deployment Guide

This guide covers deployment options for the Motodrives website using Docker, Render.com, and traditional hosting.

## 🐳 Docker Deployment

### Prerequisites
- Docker installed on your system
- Docker Compose installed
- Git (for cloning the repository)

### Quick Start with Docker

1. **Clone the Repository**
   ```bash
   git clone https://github.com/yourusername/motodrives.git
   cd motodrives
   ```

2. **Configure Environment**
   ```bash
   cp .env.example .env
   # Edit .env with your configuration
   nano .env
   ```

3. **Build and Start Containers**
   ```bash
   docker-compose up -d
   ```

4. **Access the Application**
   - Website: http://localhost:8080
   - phpMyAdmin: http://localhost:8081
   - Admin Panel: http://localhost:8080/admin/login.php

5. **Run Installation**
   - Navigate to http://localhost:8080/install.php
   - Follow the installation wizard
   - Use these database credentials:
     - Host: `db`
     - Database: `motodrives`
     - User: `motodrives_user`
     - Password: `motodrives_pass`

6. **Post-Installation**
   ```bash
   # Remove installation file
   docker-compose exec web rm install.php
   ```

### Docker Commands

```bash
# Start containers
docker-compose up -d

# Stop containers
docker-compose down

# View logs
docker-compose logs -f

# Restart containers
docker-compose restart

# Access web container shell
docker-compose exec web bash

# Access database
docker-compose exec db mysql -u motodrives_user -p motodrives

# Backup database
docker-compose exec db mysqldump -u motodrives_user -p motodrives > backup.sql

# Restore database
docker-compose exec -T db mysql -u motodrives_user -p motodrives < backup.sql
```

### Production Docker Deployment

For production, create a `docker-compose.prod.yml`:

```yaml
version: '3.8'

services:
  web:
    build:
      context: .
      dockerfile: Dockerfile
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
    restart: always

  db:
    environment:
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}
      MYSQL_DATABASE: ${DB_NAME}
      MYSQL_USER: ${DB_USER}
      MYSQL_PASSWORD: ${DB_PASS}
    restart: always
```

Deploy with:
```bash
docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d
```

## 🚀 Render.com Deployment

### Prerequisites
- GitHub account
- Render.com account
- Repository pushed to GitHub

### Deployment Steps

1. **Push to GitHub**
   ```bash
   git init
   git add .
   git commit -m "Initial commit"
   git branch -M main
   git remote add origin https://github.com/yourusername/motodrives.git
   git push -u origin main
   ```

2. **Connect to Render.com**
   - Log in to [Render.com](https://render.com)
   - Click "New +" → "Blueprint"
   - Connect your GitHub repository
   - Render will automatically detect `render.yaml`

3. **Configure Environment Variables**
   Render will create services based on `render.yaml`. Update these variables:
   - `SITE_URL`: Your Render.com URL (e.g., https://motodrives.onrender.com)
   - `ADMIN_EMAIL`: Your admin email
   - Database credentials are auto-configured

4. **Deploy**
   - Render will automatically build and deploy
   - Wait for deployment to complete (5-10 minutes)
   - Access your site at the provided URL

5. **Run Installation**
   - Navigate to your-app.onrender.com/install.php
   - Complete the installation wizard
   - Database credentials are pre-filled from environment variables

6. **Post-Deployment**
   - Remove install.php via Render shell or redeploy without it
   - Configure custom domain (optional)
   - Set up SSL (automatic with Render)

### Render.com Configuration

The `render.yaml` file includes:
- **Web Service**: PHP application with Apache
- **Database**: MySQL database
- **Persistent Disk**: For uploads (1GB)
- **Environment Variables**: Auto-configured

### Custom Domain Setup

1. Go to your web service settings
2. Click "Custom Domains"
3. Add your domain
4. Update DNS records as instructed
5. SSL certificate is automatically provisioned

### Render.com Commands

```bash
# View logs
render logs -s motodrives-web

# SSH into container
render shell -s motodrives-web

# Restart service
render restart -s motodrives-web

# Database backup
render db backup motodrives-db
```

## 📦 Traditional Hosting (cPanel/Plesk)

### cPanel Deployment

1. **Upload Files**
   - Use File Manager or FTP
   - Upload to `public_html/motodrives/`
   - Extract if uploaded as ZIP

2. **Create Database**
   - cPanel → MySQL Databases
   - Create database: `username_motodrives`
   - Create user with strong password
   - Grant all privileges

3. **Import Database**
   - cPanel → phpMyAdmin
   - Select database
   - Import `sql/motodrives.sql`

4. **Configure Application**
   - Navigate to yourdomain.com/motodrives/install.php
   - Follow installation wizard
   - Or manually edit `config/config.php`

5. **Set Permissions**
   ```bash
   chmod 755 /home/username/public_html/motodrives
   chmod 777 /home/username/public_html/motodrives/uploads
   chmod 777 /home/username/public_html/motodrives/config
   ```

6. **Configure .htaccess**
   - Update RewriteBase if needed
   - Ensure mod_rewrite is enabled

### Plesk Deployment

1. **Create Subscription**
   - Add domain or subdomain
   - Set document root to motodrives folder

2. **Upload Files**
   - Use File Manager or FTP
   - Upload to httpdocs/

3. **Database Setup**
   - Plesk → Databases → Add Database
   - Create user with permissions
   - Import SQL via phpMyAdmin

4. **Run Installation**
   - Access install.php
   - Complete setup wizard

5. **SSL Configuration**
   - Plesk → SSL/TLS Certificates
   - Install Let's Encrypt certificate

## 🔧 Environment Configuration

### Development Environment

```bash
# .env for development
APP_ENV=development
APP_DEBUG=true
DB_HOST=localhost
DB_NAME=motodrives
DB_USER=root
DB_PASS=
SITE_URL=http://localhost:8080
```

### Production Environment

```bash
# .env for production
APP_ENV=production
APP_DEBUG=false
DB_HOST=your-db-host
DB_NAME=motodrives
DB_USER=motodrives_user
DB_PASS=secure_password
SITE_URL=https://yourdomain.com
```

## 🔐 Security Checklist

Before going live:

- [ ] Change default admin password
- [ ] Delete install.php
- [ ] Set APP_DEBUG=false
- [ ] Use strong database passwords
- [ ] Enable HTTPS/SSL
- [ ] Configure firewall rules
- [ ] Set proper file permissions
- [ ] Enable security headers
- [ ] Configure backup schedule
- [ ] Set up monitoring

## 📊 Monitoring & Maintenance

### Health Checks

```bash
# Check application status
curl -f http://localhost:8080/ || echo "Application down"

# Check database connection
docker-compose exec db mysqladmin ping -h localhost

# Check disk space
df -h
```

### Backup Strategy

```bash
# Database backup
mysqldump -u user -p motodrives > backup_$(date +%Y%m%d).sql

# Files backup
tar -czf uploads_backup_$(date +%Y%m%d).tar.gz uploads/

# Automated daily backup (cron)
0 2 * * * /path/to/backup-script.sh
```

### Performance Optimization

1. **Enable Caching**
   - Browser caching via .htaccess
   - OpCache for PHP
   - Database query caching

2. **CDN Integration**
   - Use CDN for static assets
   - Configure in admin settings

3. **Image Optimization**
   - Compress images before upload
   - Use lazy loading (already implemented)

4. **Database Optimization**
   ```sql
   OPTIMIZE TABLE products, blogs, enquiries;
   ```

## 🆘 Troubleshooting

### Common Issues

**Issue: Database connection failed**
```bash
# Check database credentials in config/config.php
# Verify database service is running
docker-compose ps
```

**Issue: 404 errors on pages**
```bash
# Ensure .htaccess is present
# Check Apache mod_rewrite is enabled
a2enmod rewrite
```

**Issue: File upload fails**
```bash
# Check directory permissions
chmod 777 uploads/
# Check PHP upload limits
php -i | grep upload_max_filesize
```

**Issue: Slow performance**
```bash
# Enable OpCache
# Optimize database
# Check server resources
```

## 📞 Support

For deployment issues:
- Check logs: `docker-compose logs -f`
- Review error logs in Apache
- Consult README.md for detailed documentation
- Contact support: support@motodrives.com

---

**Deployment Complete!** Your Motodrives website is now live and ready to serve customers worldwide.