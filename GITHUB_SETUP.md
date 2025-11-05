# GitHub and Render.com Setup Guide

Complete guide for deploying Motodrives website from GitHub to Render.com

## 📋 Prerequisites

- GitHub account
- Render.com account (free tier available)
- Git installed on your local machine
- Basic knowledge of Git commands

## 🚀 Step-by-Step Deployment

### Step 1: Prepare Your Local Repository

1. **Initialize Git Repository**
   ```bash
   cd /path/to/motodrives
   git init
   ```

2. **Configure Git (if not already done)**
   ```bash
   git config --global user.name "Your Name"
   git config --global user.email "your.email@example.com"
   ```

3. **Add All Files**
   ```bash
   git add .
   ```

4. **Create Initial Commit**
   ```bash
   git commit -m "Initial commit: Motodrives website"
   ```

### Step 2: Create GitHub Repository

1. **Go to GitHub**
   - Navigate to https://github.com
   - Click the "+" icon → "New repository"

2. **Repository Settings**
   - Repository name: `motodrives-website`
   - Description: "Industrial Drives & Automation Equipment Website"
   - Visibility: Private (recommended) or Public
   - **DO NOT** initialize with README, .gitignore, or license
   - Click "Create repository"

3. **Connect Local to GitHub**
   ```bash
   git remote add origin https://github.com/YOUR_USERNAME/motodrives-website.git
   git branch -M main
   git push -u origin main
   ```

### Step 3: Deploy to Render.com

#### Option A: Using Blueprint (Recommended)

1. **Login to Render.com**
   - Go to https://render.com
   - Sign up or log in
   - Connect your GitHub account

2. **Create New Blueprint**
   - Click "New +" → "Blueprint"
   - Select your GitHub repository
   - Render will detect `render.yaml` automatically
   - Click "Apply"

3. **Configure Services**
   Render will create:
   - Web Service (motodrives-web)
   - MySQL Database (motodrives-db)
   - Persistent Disk for uploads

4. **Wait for Deployment**
   - Initial deployment takes 5-10 minutes
   - Monitor progress in the dashboard
   - Check logs for any errors

5. **Access Your Site**
   - URL will be: `https://motodrives-web.onrender.com`
   - Or your custom domain if configured

#### Option B: Manual Setup

1. **Create Database**
   - Dashboard → "New +" → "PostgreSQL" or "MySQL"
   - Name: `motodrives-db`
   - Plan: Free or Starter
   - Click "Create Database"
   - Save connection details

2. **Create Web Service**
   - Dashboard → "New +" → "Web Service"
   - Connect repository
   - Settings:
     - Name: `motodrives-web`
     - Environment: Docker
     - Branch: main
     - Region: Choose closest to your users
     - Plan: Free or Starter

3. **Configure Environment Variables**
   Add these in the Environment tab:
   ```
   DB_HOST=<from database connection>
   DB_NAME=motodrives
   DB_USER=<from database connection>
   DB_PASS=<from database connection>
   SITE_URL=https://motodrives-web.onrender.com
   SITE_NAME=Motodrives
   ADMIN_EMAIL=admin@motodrives.com
   APP_ENV=production
   APP_DEBUG=false
   ```

4. **Add Persistent Disk**
   - Go to web service settings
   - Click "Disks"
   - Add disk:
     - Name: uploads
     - Mount Path: /var/www/html/uploads
     - Size: 1GB

5. **Deploy**
   - Click "Manual Deploy" → "Deploy latest commit"
   - Wait for build and deployment

### Step 4: Initial Setup

1. **Run Installation Wizard**
   ```
   https://your-app.onrender.com/install.php
   ```

2. **Database Configuration**
   - Host: Use the internal database URL from Render
   - Database: motodrives
   - User: From Render database credentials
   - Password: From Render database credentials

3. **Complete Setup**
   - Follow the 5-step wizard
   - Create admin account
   - Configure site settings

4. **Remove Installation File**
   - Option 1: Via Render Shell
     ```bash
     render shell -s motodrives-web
     rm /var/www/html/install.php
     ```
   - Option 2: Remove from repository and redeploy
     ```bash
     git rm install.php
     git commit -m "Remove installation file"
     git push
     ```

### Step 5: Custom Domain (Optional)

1. **Add Custom Domain in Render**
   - Go to web service settings
   - Click "Custom Domains"
   - Add your domain (e.g., www.motodrives.com)

2. **Update DNS Records**
   Add these records at your domain registrar:
   ```
   Type: CNAME
   Name: www
   Value: motodrives-web.onrender.com
   
   Type: A (for root domain)
   Name: @
   Value: [Render's IP - provided in dashboard]
   ```

3. **SSL Certificate**
   - Automatically provisioned by Render
   - Usually takes 5-15 minutes

4. **Update Environment Variables**
   ```
   SITE_URL=https://www.yourdomain.com
   ```

## 🔄 Continuous Deployment

### Automatic Deployments

Render automatically deploys when you push to GitHub:

```bash
# Make changes to your code
git add .
git commit -m "Update feature X"
git push origin main

# Render will automatically:
# 1. Detect the push
# 2. Build new Docker image
# 3. Deploy to production
# 4. Run health checks
```

### Manual Deployments

```bash
# From Render Dashboard
# Go to your web service
# Click "Manual Deploy"
# Select "Deploy latest commit"
```

### Deployment Branches

To use different branches:

1. **Create Development Branch**
   ```bash
   git checkout -b development
   git push -u origin development
   ```

2. **Create Separate Render Service**
   - Create new web service
   - Point to `development` branch
   - Use different environment variables

## 🔐 Security Best Practices

### Environment Variables

Never commit sensitive data:
```bash
# ❌ DON'T DO THIS
git add config/config.php

# ✅ DO THIS
# Use environment variables in Render
# Keep config.php in .gitignore
```

### Secrets Management

1. **Database Credentials**
   - Use Render's internal database URLs
   - Never hardcode in files

2. **API Keys**
   - Store in Render environment variables
   - Access via `getenv()` in PHP

3. **Admin Passwords**
   - Change default password immediately
   - Use strong, unique passwords

### Access Control

1. **GitHub Repository**
   - Set to Private if possible
   - Use branch protection rules
   - Require pull request reviews

2. **Render Dashboard**
   - Enable 2FA
   - Limit team access
   - Use role-based permissions

## 📊 Monitoring & Logs

### View Logs in Render

```bash
# Via Dashboard
# Go to your service → Logs tab

# Via CLI
render logs -s motodrives-web --tail 100
```

### Health Checks

Render automatically monitors:
- HTTP response codes
- Response times
- Container health

Configure in `render.yaml`:
```yaml
healthCheckPath: /
```

### Alerts

Set up in Render Dashboard:
- Email notifications
- Slack integration
- Webhook alerts

## 🔧 Troubleshooting

### Build Failures

**Issue: Docker build fails**
```bash
# Check Dockerfile syntax
# Review build logs in Render
# Ensure all dependencies are listed
```

**Issue: Database connection fails**
```bash
# Verify environment variables
# Check database is running
# Confirm internal URL is used
```

### Runtime Errors

**Issue: 500 Internal Server Error**
```bash
# Check application logs
# Verify file permissions
# Review PHP error logs
```

**Issue: File upload fails**
```bash
# Ensure disk is mounted
# Check disk space
# Verify permissions
```

### Performance Issues

**Issue: Slow response times**
```bash
# Upgrade Render plan
# Enable caching
# Optimize database queries
# Use CDN for static assets
```

## 📦 Backup Strategy

### Database Backups

```bash
# Render provides automatic backups
# Manual backup via CLI:
render db backup motodrives-db

# Download backup:
render db download-backup motodrives-db <backup-id>
```

### File Backups

```bash
# Backup uploads directory
render shell -s motodrives-web
tar -czf uploads_backup.tar.gz /var/www/html/uploads
# Download via SFTP or Render dashboard
```

### Automated Backups

Set up cron job in Render:
```yaml
# In render.yaml
services:
  - type: cron
    name: backup-job
    schedule: "0 2 * * *"  # Daily at 2 AM
    dockerfilePath: ./Dockerfile
    dockerCommand: /backup-script.sh
```

## 🚀 Scaling

### Vertical Scaling

Upgrade Render plan:
- Free → Starter → Standard → Pro
- More CPU, RAM, and bandwidth

### Horizontal Scaling

For high traffic:
1. Enable auto-scaling in Render
2. Use load balancer
3. Implement caching (Redis)
4. Use CDN for static assets

## 📞 Support Resources

- **Render Documentation**: https://render.com/docs
- **Render Community**: https://community.render.com
- **GitHub Issues**: Create issues in your repository
- **Email Support**: support@render.com

## ✅ Deployment Checklist

Before going live:

- [ ] Repository pushed to GitHub
- [ ] Render services created and deployed
- [ ] Database configured and connected
- [ ] Environment variables set
- [ ] Installation wizard completed
- [ ] install.php removed
- [ ] Admin password changed
- [ ] Custom domain configured (if applicable)
- [ ] SSL certificate active
- [ ] Backups configured
- [ ] Monitoring enabled
- [ ] Performance tested
- [ ] Security audit completed

---

**Congratulations!** Your Motodrives website is now live on Render.com with continuous deployment from GitHub.

For updates, simply push to GitHub and Render will automatically deploy your changes.