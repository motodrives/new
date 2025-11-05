# 🚀 Motodrives - Quick Start Guide

Get your Motodrives website up and running in 5 minutes!

## 🎯 Choose Your Deployment Method

### Option 1: Docker (Easiest - Recommended)

```bash
# 1. Clone repository
git clone https://github.com/yourusername/motodrives-website.git
cd motodrives-website

# 2. Start containers
docker-compose up -d

# 3. Open browser
# Website: http://localhost:8080
# Admin: http://localhost:8080/admin/login.php
# phpMyAdmin: http://localhost:8081

# 4. Run installation wizard
# Navigate to: http://localhost:8080/install.php
# Database credentials:
#   Host: db
#   Database: motodrives
#   User: motodrives_user
#   Password: motodrives_pass

# 5. Login to admin
# Email: admin@motodrives.com
# Password: admin123 (CHANGE THIS!)

# 6. Delete installation file
docker-compose exec web rm install.php
```

**Done! Your website is live! 🎉**

---

### Option 2: Render.com (Cloud Deployment)

```bash
# 1. Push to GitHub
git init
git add .
git commit -m "Initial commit"
git remote add origin https://github.com/yourusername/motodrives-website.git
git push -u origin main

# 2. Deploy to Render
# - Go to https://render.com
# - Click "New +" → "Blueprint"
# - Connect your GitHub repo
# - Click "Apply"

# 3. Wait 5-10 minutes for deployment

# 4. Access your site
# URL: https://motodrives-web.onrender.com

# 5. Run installation wizard
# Navigate to your-app.onrender.com/install.php

# 6. Login and start managing content!
```

**Your website is live on the internet! 🌐**

---

### Option 3: Traditional Hosting (cPanel/Plesk)

```bash
# 1. Upload files via FTP or File Manager
# Upload to: public_html/motodrives/

# 2. Create MySQL database
# cPanel → MySQL Databases
# Database: username_motodrives
# User: username_motodrive
# Password: [strong password]

# 3. Import database
# phpMyAdmin → Import → sql/motodrives.sql

# 4. Set permissions
chmod 755 /path/to/motodrives
chmod 777 /path/to/motodrives/uploads
chmod 777 /path/to/motodrives/config

# 5. Run installation wizard
# Navigate to: yourdomain.com/motodrives/install.php

# 6. Complete setup and start using!
```

**Your website is ready! 🎊**

---

## 📋 Post-Installation Checklist

After installation, complete these steps:

### Security (Critical!)
- [ ] Change admin password from default
- [ ] Delete `install.php` file
- [ ] Set `APP_DEBUG=false` in production
- [ ] Enable HTTPS/SSL certificate

### Content Setup
- [ ] Update company information in admin settings
- [ ] Add your products with images
- [ ] Create blog posts
- [ ] Upload gallery images
- [ ] Customize contact information

### Customization
- [ ] Update logo and branding
- [ ] Adjust color scheme in CSS
- [ ] Configure email settings
- [ ] Set up social media links

### Testing
- [ ] Test contact form
- [ ] Check all pages load correctly
- [ ] Verify mobile responsiveness
- [ ] Test admin panel functionality

---

## 🎨 Quick Customization

### Change Colors

Edit `assets/css/style.css`:

```css
:root {
    --electric-blue: #007bff;  /* Your primary color */
    --steel-grey: #6c757d;     /* Your secondary color */
}
```

### Update Logo

Replace logo in navigation:
```html
<!-- In navigation section -->
<a class="navbar-brand" href="index.php">
    <img src="assets/images/logo.png" alt="Motodrives">
</a>
```

### Add Products

1. Login to admin panel
2. Go to "Products" → "Add New"
3. Fill in product details
4. Upload images
5. Click "Save"

---

## 🆘 Quick Troubleshooting

### Database Connection Error
```bash
# Check credentials in config/config.php
# Verify database is running
docker-compose ps  # For Docker
```

### Can't Upload Images
```bash
# Fix permissions
chmod 777 uploads/
```

### 404 Errors on Pages
```bash
# Ensure .htaccess exists
# Check Apache mod_rewrite is enabled
```

### Forgot Admin Password
```sql
-- Reset via database
UPDATE users SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
WHERE email = 'admin@motodrives.com';
-- New password: admin123
```

---

## 📚 Next Steps

### Learn More
- [Full Documentation](README.md)
- [Deployment Guide](DEPLOYMENT.md)
- [GitHub Setup](GITHUB_SETUP.md)

### Get Support
- 📧 Email: support@motodrives.com
- 🐛 Issues: GitHub Issues
- 📖 Docs: Complete documentation

---

## 🎉 You're All Set!

Your Motodrives website is now ready to:
- ✅ Showcase your products
- ✅ Generate leads through contact forms
- ✅ Manage content easily via admin panel
- ✅ Rank well in search engines (SEO optimized)
- ✅ Scale as your business grows

**Start adding your products and content to make it yours!**

---

**Need help?** Check the [full documentation](README.md) or contact support.