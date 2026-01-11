# 🌐 Deployment Guide - CineCraze

Complete deployment instructions for both free and paid hosting platforms.

## 📦 Free Hosting Deployment

### InfinityFree (Recommended Free Host)

**Pros**: Unlimited bandwidth, PHP/MySQL support, no ads  
**Cons**: Some resource limitations

#### Steps:

1. **Create Account**
   - Visit: https://infinityfree.net/
   - Sign up for free account
   - Create a new website

2. **Access Control Panel**
   - Login to your cPanel
   - Note your MySQL credentials

3. **Create Database**
   - Go to MySQL Databases
   - Create new database (e.g., `epiz_12345_cinecraze`)
   - Note the database name, username, and password

4. **Upload Files**
   - Use File Manager or FTP
   - Upload all files to `htdocs` folder
   - Ensure all folders are uploaded (admin, api, config, etc.)

5. **Set Permissions**
   ```
   uploads/ → 755
   config/ → 755
   All .php files → 644
   ```

6. **Run Installer**
   - Visit: `http://yoursite.infinityfreeapp.com/install/install.php`
   - Enter database details (use the ones from step 3)
   - Create admin account
   - Complete installation

7. **Post-Installation**
   - Delete `/install` directory via File Manager
   - Test your site: `http://yoursite.infinityfreeapp.com`

#### InfinityFree-Specific Settings

Add to `.htaccess`:
```apache
# InfinityFree optimizations
php_value max_execution_time 300
php_value memory_limit 128M
```

---

### 000webhost

**Pros**: Easy to use, decent performance  
**Cons**: Some limitations on free plan

#### Steps:

1. **Sign Up**
   - Visit: https://www.000webhost.com/
   - Create free account
   - Create website

2. **Access File Manager**
   - Login to control panel
   - Go to File Manager
   - Navigate to `public_html`

3. **Upload Files**
   - Delete existing files in `public_html`
   - Upload all CineCraze files
   - Wait for upload to complete

4. **Setup Database**
   - Go to "Set up database"
   - Create new database
   - Note credentials

5. **Install**
   - Visit: `http://yoursite.000webhostapp.com/install/install.php`
   - Complete installation wizard

6. **Cleanup**
   - Delete install folder via File Manager

---

### Byethost

**Pros**: No ads, good uptime  
**Cons**: Limited resources

#### Steps:

1. **Register**
   - Visit: https://byet.host/
   - Free sign-up
   - Create hosting account

2. **cPanel Access**
   - Login to cPanel
   - Access File Manager

3. **Upload via FTP** (Recommended for Byethost)
   ```
   Host: ftp.yoursite.byethost.com
   Username: Your username
   Password: Your password
   Port: 21
   ```

4. **Database Setup**
   - cPanel → MySQL Databases
   - Create database and user
   - Grant all privileges

5. **Install**
   - Navigate to installation URL
   - Complete setup

---

## 💳 Paid Hosting Deployment

### Shared Hosting (cPanel)

Suitable for: Hostinger, Bluehost, SiteGround, Namecheap, etc.

#### Steps:

1. **Purchase Hosting**
   - Choose shared hosting plan ($2-10/month)
   - Register/connect domain

2. **Access cPanel**
   - Login via hosting provider dashboard
   - Open cPanel

3. **Create Database**
   - MySQL Databases → Create New Database
   - Create MySQL User
   - Add User to Database
   - Grant ALL PRIVILEGES

4. **Upload Files**
   
   **Method A: File Manager**
   - cPanel → File Manager
   - Navigate to `public_html`
   - Upload ZIP file
   - Extract all files
   
   **Method B: FTP (Recommended)**
   - Use FileZilla or similar
   - Connect with FTP credentials
   - Upload all files to `public_html`

5. **Set Permissions**
   ```bash
   chmod 755 uploads
   chmod 755 admin api config includes install
   chmod 644 *.php
   chmod 644 .htaccess
   ```

6. **Domain Configuration**
   - Point domain to `public_html`
   - Wait for DNS propagation (up to 24 hours)

7. **SSL Certificate** (Recommended)
   - cPanel → SSL/TLS
   - Install Let's Encrypt (free)
   - Enable HTTPS

8. **Run Installer**
   - Visit: `https://yourdomain.com/install/install.php`
   - Complete installation

9. **Enable HTTPS Redirect**
   
   Edit `.htaccess`:
   ```apache
   # Force HTTPS
   RewriteEngine On
   RewriteCond %{HTTPS} off
   RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   ```

10. **Production Settings**
    
    Edit `config/config.php`:
    ```php
    // Disable errors in production
    error_reporting(0);
    ini_set('display_errors', 0);
    ```

---

## 🔧 FTP Deployment Guide

### Using FileZilla

1. **Download FileZilla**
   - https://filezilla-project.org/

2. **Connect to Server**
   ```
   Host: ftp.yourdomain.com (or IP address)
   Username: Your FTP username
   Password: Your FTP password
   Port: 21
   ```

3. **Upload Files**
   - Local site (left panel): Navigate to CineCraze folder
   - Remote site (right panel): Navigate to web root
   - Select all files/folders
   - Right-click → Upload
   - Wait for completion

4. **Verify Upload**
   - Check all folders are present
   - Verify file permissions

---

## 📱 Subdomain Deployment

If you want: `movies.yourdomain.com`

### Steps:

1. **Create Subdomain**
   - cPanel → Subdomains
   - Subdomain: `movies`
   - Document Root: `/public_html/movies` (or custom)

2. **Upload Files**
   - Upload to subdomain directory
   - Follow normal installation steps

3. **DNS Configuration**
   - Usually automatic
   - Wait 1-24 hours for propagation

---

## 🚀 VPS/Cloud Deployment

For advanced users with VPS (DigitalOcean, Linode, AWS, etc.)

### Requirements:
- Ubuntu 20.04+ / CentOS 7+
- LAMP stack (Linux, Apache, MySQL, PHP)
- SSH access

### Quick Setup:

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install LAMP
sudo apt install apache2 mysql-server php libapache2-mod-php php-mysql -y

# Enable Apache modules
sudo a2enmod rewrite
sudo systemctl restart apache2

# Create database
sudo mysql
CREATE DATABASE cinecraze_db;
CREATE USER 'cinecraze_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT ALL PRIVILEGES ON cinecraze_db.* TO 'cinecraze_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Upload files
cd /var/www/html
sudo rm index.html
# Upload via SFTP or git clone

# Set permissions
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html
sudo chmod -R 775 /var/www/html/uploads

# Configure Apache
sudo nano /etc/apache2/sites-available/000-default.conf
# Add: AllowOverride All in <Directory>

# Restart Apache
sudo systemctl restart apache2

# Install SSL
sudo apt install certbot python3-certbot-apache -y
sudo certbot --apache -d yourdomain.com
```

---

## 🔒 Security Checklist

After deployment:

- [ ] Delete `/install` directory
- [ ] Set proper file permissions
- [ ] Enable HTTPS/SSL
- [ ] Change default database prefix (optional)
- [ ] Disable PHP error display
- [ ] Enable firewall (if VPS)
- [ ] Regular backups setup
- [ ] Strong admin password
- [ ] Keep PHP/MySQL updated

---

## 🔍 Testing Deployment

### Check List:

1. **Homepage**
   - Visit main URL
   - Check if design loads correctly
   - Test responsive design (resize browser)

2. **Registration**
   - Create test user account
   - Verify email validation

3. **Login**
   - Login with test account
   - Check session persistence

4. **Admin Panel**
   - Login to `/admin/`
   - Verify dashboard loads
   - Test statistics display

5. **Add Content**
   - Add test movie
   - Check thumbnail display
   - Test video playback

6. **Search**
   - Search for added content
   - Verify results display

7. **Filters**
   - Test category filter
   - Test type filter (Movie/Series/Live)

8. **Mobile Testing**
   - Open on mobile device
   - Check touch interactions
   - Verify responsive layout

---

## 📊 Performance Optimization

### After Deployment:

1. **Enable Caching**
   ```apache
   # Add to .htaccess
   <IfModule mod_expires.c>
       ExpiresActive On
       ExpiresByType image/jpg "access plus 1 year"
       ExpiresByType image/jpeg "access plus 1 year"
       ExpiresByType image/png "access plus 1 year"
       ExpiresByType text/css "access plus 1 month"
       ExpiresByType application/javascript "access plus 1 month"
   </IfModule>
   ```

2. **Compress Output**
   ```apache
   <IfModule mod_deflate.c>
       AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
   </IfModule>
   ```

3. **Database Optimization**
   ```sql
   -- Run monthly
   OPTIMIZE TABLE movies;
   OPTIMIZE TABLE users;
   OPTIMIZE TABLE categories;
   ```

4. **Use CDN**
   - CloudFlare (free plan available)
   - Protects site and improves speed

---

## 🔄 Updating Application

When updates are available:

1. **Backup Current Installation**
   ```bash
   # Backup files
   tar -czf cinecraze_backup_$(date +%Y%m%d).tar.gz /path/to/cinecraze
   
   # Backup database
   mysqldump -u username -p database_name > backup_$(date +%Y%m%d).sql
   ```

2. **Download Update**
   - Download new version
   - Extract files

3. **Preserve Config**
   - Keep your `config/database.php`
   - Keep your `.htaccess` modifications

4. **Upload New Files**
   - Replace old files with new ones
   - Don't replace config files

5. **Test**
   - Check homepage
   - Test admin panel
   - Verify all features work

---

## 🆘 Common Deployment Issues

### "Database connection failed"
**Solution**: Double-check credentials in `config/database.php`

### "500 Internal Server Error"
**Solution**: 
- Check `.htaccess` compatibility
- Review PHP error logs
- Verify file permissions

### "Page not found" or broken links
**Solution**: Ensure `mod_rewrite` is enabled

### "Upload failed" or "Permission denied"
**Solution**: Set uploads folder to 755 or 775

### CSS/Images not loading
**Solution**: Clear browser cache, check file paths

### Videos not playing
**Solution**: Verify video URLs are accessible and HTTPS if site uses HTTPS

---

## 📞 Support Resources

- **Hosting Support**: Contact your hosting provider
- **PHP Documentation**: https://www.php.net/docs.php
- **MySQL Documentation**: https://dev.mysql.com/doc/
- **Apache Docs**: https://httpd.apache.org/docs/

---

**Good luck with your deployment! 🚀**

*Remember: Always backup before making changes!*
