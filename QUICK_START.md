# 🚀 CineCraze Quick Start Guide

## For Free Hosting Users (InfinityFree, 000webhost, etc.)

### Step-by-Step Installation

#### 1. Get Free Hosting
- Sign up at [InfinityFree](https://infinityfree.net/) (recommended)
- Or [000webhost](https://www.000webhost.com/)
- Or [Freehostia](https://www.freehostia.com/)

#### 2. Create Database
1. Login to your hosting control panel (cPanel/Vista Panel)
2. Go to **MySQL Databases**
3. Create a new database (note the name)
4. Create database user (note username & password)
5. Add user to database with **ALL PRIVILEGES**

Example credentials you'll get:
```
Database Name: abc123_cinecraze
Database User: abc123_user
Database Pass: your_password_here
Database Host: localhost
```

#### 3. Upload Files
**Option A: File Manager (Easiest)**
1. Go to File Manager in control panel
2. Navigate to `htdocs` or `public_html`
3. Upload all CineCraze files
4. If uploaded as ZIP, extract it

**Option B: FTP**
1. Get FTP credentials from control panel
2. Use FileZilla or similar FTP client
3. Upload all files to `htdocs` or `public_html`

#### 4. Run Web Installer
1. Open your browser
2. Go to: `http://yoursite.com/install.php`
3. Follow the 6-step wizard:
   - **Step 1**: Check requirements (should all pass ✓)
   - **Step 2**: Enter database credentials from Step 2
   - **Step 3**: Import database (automatic)
   - **Step 4**: Create admin account (your login)
   - **Step 5**: Write config file (automatic)
   - **Step 6**: Done! 🎉

#### 5. Delete Installer
**IMPORTANT for security!**
1. Go back to File Manager
2. Delete `install.php` file

#### 6. Start Using
- **Public Site**: `http://yoursite.com/`
- **Admin Panel**: `http://yoursite.com/admin/`
- Use the admin credentials you created in Step 4

---

## For Paid Hosting Users (cPanel)

### Fast Track Installation

#### Using cPanel:

1. **Create Database**
   ```
   cPanel > MySQL Databases > Create New Database
   Create user and assign to database
   ```

2. **Upload via cPanel File Manager**
   ```
   cPanel > File Manager > public_html
   Upload files or upload ZIP and extract
   ```

3. **Run Installer**
   ```
   Visit: http://yourdomain.com/install.php
   Enter database credentials
   Follow wizard
   ```

4. **Delete Installer**
   ```
   cPanel > File Manager > Delete install.php
   ```

5. **Enable SSL (Recommended)**
   ```
   cPanel > SSL/TLS > Install free SSL certificate
   ```

---

## For VPS Users (Command Line)

### Quick Commands:

```bash
# Install requirements
sudo apt update
sudo apt install apache2 mysql-server php8.1 php8.1-mysql php8.1-mbstring

# Create database
sudo mysql -u root -p
CREATE DATABASE cinecraze;
CREATE USER 'cinecraze'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL ON cinecraze.* TO 'cinecraze'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Upload files
cd /var/www/html
sudo git clone your-repo cinecraze
cd cinecraze

# Set permissions
sudo chown -R www-data:www-data .
sudo chmod -R 755 .

# Run installer via browser
# Or import database manually:
mysql -u cinecraze -p cinecraze < database.sql

# Enable Apache modules
sudo a2enmod rewrite
sudo systemctl restart apache2

# Visit http://your-vps-ip/cinecraze/install.php
```

---

## Post-Installation Setup

### 1. Admin Panel Configuration

Login to Admin Panel: `http://yoursite.com/admin/`

#### Add TMDB API Key (For Content Import)
1. Go to **Settings**
2. Get free API key from [TMDB](https://www.themoviedb.org/settings/api)
3. Paste in "TMDB API Key" field
4. Click **Save Settings**

#### Configure Embed Servers
Default servers are already added:
- VidSrc
- 2Embed
- SuperEmbed

To add more:
1. Go to **Settings** tab
2. Scroll to "Embed Servers"
3. Enter server name and URL
4. Set priority (lower = higher priority)
5. Click **Add Server**

### 2. Add Content

#### Option A: TMDB Generator (Coming Soon)
1. Go to **TMDB Generator**
2. Enter TMDB ID or search
3. Import automatically

#### Option B: Manual Entry (Coming Soon)
1. Go to **Manual Entry**
2. Fill in movie/series details
3. Add streaming links
4. Publish

#### Option C: Direct Database Entry
For now, insert directly into database:

```sql
INSERT INTO content (title, content_type, description, poster_url, year, rating) 
VALUES ('Test Movie', 'movie', 'Description here', 'https://image.url/poster.jpg', 2024, 8.5);
```

### 3. Test User Registration

1. Open your site: `http://yoursite.com/`
2. Should see login/register modal
3. Create test account
4. Verify you can login

---

## Common Issues & Solutions

### ❌ "Database connection failed"
**Solution**: 
- Check credentials in `includes/config.php`
- On free hosting, use provided database name format (e.g., `abc123_dbname`)
- Verify database user has privileges

### ❌ "500 Internal Server Error"
**Solution**:
- Check file permissions (755 for folders, 644 for files)
- Verify `.htaccess` exists
- Check PHP version (must be 7.4+)
- View error logs in cPanel

### ❌ "Cannot write config.php"
**Solution**:
- Set `includes/` folder to writable (755 or 777 temporarily)
- On shared hosting, use File Manager to manually edit
- After installation, set back to 755

### ❌ "API not working"
**Solution**:
- Ensure `.htaccess` file exists in root
- Check if mod_rewrite is enabled
- Try accessing directly: `/api/auth.php?action=verify`

### ❌ "Site shows code instead of page"
**Solution**:
- PHP is not enabled in that folder
- Contact hosting support to enable PHP
- Rename files from `.php` to `.php5` if needed (some hosts)

### ❌ "Too many redirects"
**Solution**:
- Clear browser cache and cookies
- Check if HTTPS forcing is conflicting
- Disable HTTPS forcing in `.htaccess` temporarily

---

## Free Hosting Tips

### Optimize for Limited Resources:

1. **Use External Image Hosting**
   - Upload images to [Imgur](https://imgur.com/)
   - Use Imgur URLs in database

2. **Enable Cloudflare**
   - Free CDN and SSL
   - Reduces server load
   - Speeds up your site

3. **Regular Cleanup**
   - Delete old admin logs
   - Remove unused content
   - Optimize database tables

4. **Monitor Limits**
   - Check storage usage
   - Monitor bandwidth
   - Stay within limits to avoid suspension

---

## Upgrade Path

### Free → Paid Hosting Migration:

1. **Backup**
   ```
   cPanel > phpMyAdmin > Export database
   Download all files via FTP
   ```

2. **Upload to New Host**
   ```
   Upload files to new hosting
   Create database on new host
   Import database backup
   ```

3. **Update Config**
   ```
   Edit includes/config.php
   Update DB credentials
   Update SITE_URL
   ```

4. **Update DNS**
   ```
   Point domain to new hosting
   Wait for propagation (24-48 hours)
   ```

5. **Enable SSL**
   ```
   Install free Let's Encrypt SSL
   Update SITE_URL to https://
   ```

---

## Next Steps

✅ Site is live
✅ Admin panel accessible
✅ User registration works

**Now:**
1. ⚙️ Add TMDB API key
2. 🎬 Add your first content
3. 🎨 Customize site name in Settings
4. 📱 Test on mobile devices
5. 🔒 Enable HTTPS/SSL
6. 📊 Monitor in Dashboard

---

## Support Resources

- 📖 [Full Setup Guide](README_SETUP.md)
- 🚀 [Deployment Guide](DEPLOYMENT_GUIDE.md)
- 🔧 [Technical Documentation](CONVERSION_GUIDE.md)
- 💬 Create GitHub Issue for help

---

## Security Checklist

Before going live:
- [ ] Delete `install.php`
- [ ] Change default admin password
- [ ] Enable HTTPS/SSL
- [ ] Set strong passwords
- [ ] Restrict file permissions
- [ ] Regular backups
- [ ] Update SITE_URL to https://

---

**🎉 Congratulations! Your streaming platform is ready!**

Start adding content and share with your users! 🍿
