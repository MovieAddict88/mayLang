# 🎬 CineCraze - Complete Setup Instructions

## 🎯 Overview

This guide will walk you through setting up CineCraze from scratch to a fully functioning movie streaming platform. Estimated time: **10 minutes**.

---

## 📦 What You'll Need

Before starting, ensure you have:

- [ ] Web hosting account (free or paid)
- [ ] MySQL database access
- [ ] FTP client (FileZilla) OR File Manager access
- [ ] Basic understanding of web hosting
- [ ] Your hosting credentials ready

---

## 🚀 Installation Methods

Choose the method that works best for you:

### Method 1: One-Click Installation (Recommended) ⚡

**Perfect for beginners**

1. **Upload Files**
   ```
   - Download this entire project
   - Extract the ZIP file
   - Upload ALL files to your web root (public_html or htdocs)
   - Keep the folder structure intact
   ```

2. **Access Installation Wizard**
   ```
   Open browser: http://yourdomain.com/install/install.php
   ```

3. **Step 1: Database Configuration**
   ```
   Database Host: localhost (usually)
   Database Name: cinecraze_db (or create new)
   Database User: your_mysql_username
   Database Password: your_mysql_password
   ```
   Click "Continue"

4. **Step 2: Admin Account**
   ```
   Username: admin (or your choice)
   Email: your@email.com
   Password: [strong password]
   Confirm Password: [same password]
   ```
   Click "Create Account"

5. **Step 3: Done!**
   ```
   ✅ Installation complete
   🗑️ Delete the /install directory
   🎬 Visit your site
   ```

---

### Method 2: Manual Installation (Advanced) 🔧

**For advanced users**

#### Part 1: File Upload

```bash
# Via FTP (FileZilla)
1. Connect to your hosting via FTP
2. Navigate to public_html or web root
3. Upload all project files
4. Set permissions:
   - uploads/: 755 (rwxr-xr-x)
   - config/: 755
   - All .php files: 644
```

#### Part 2: Database Setup

```sql
# Create database
CREATE DATABASE cinecraze_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Create user (optional)
CREATE USER 'cinecraze_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON cinecraze_db.* TO 'cinecraze_user'@'localhost';
FLUSH PRIVILEGES;
```

#### Part 3: Configuration

```php
# Edit config/database.php
define('DB_HOST', 'localhost');
define('DB_USER', 'cinecraze_user');
define('DB_PASS', 'your_password');
define('DB_NAME', 'cinecraze_db');
```

#### Part 4: Database Tables

Run the SQL from `install/install.php` or use the installation wizard.

#### Part 5: Create Admin

Either:
- Use the registration page and manually update role to 'admin' in database
- OR run SQL:
```sql
INSERT INTO users (username, email, password, role, status, created_at) 
VALUES ('admin', 'admin@example.com', '$2y$10$hashed_password', 'admin', 'active', NOW());
```

---

## 🌐 Hosting-Specific Instructions

### For InfinityFree

1. **Sign Up**: https://infinityfree.net/
2. **Create Website**: Choose subdomain or connect domain
3. **Access cPanel**: From dashboard
4. **MySQL Database**:
   - Go to "MySQL Databases"
   - Create database (note the prefix: epiz_12345_)
   - Database name will be: epiz_12345_cinecraze
5. **File Upload**:
   - Use File Manager
   - Navigate to htdocs
   - Upload all files
6. **Run Installer**: yourdomain.infinityfreeapp.com/install/install.php

**Special Notes**:
- Database prefix is automatic (epiz_xxxxx_)
- Use the full database name in installer
- May have slight delays in database operations

---

### For 000webhost

1. **Sign Up**: https://www.000webhost.com/
2. **Create Website**: Enter details
3. **File Manager**:
   - Delete default files
   - Upload CineCraze files to public_html
4. **Database**:
   - Click "Set up database"
   - Create database
   - Note credentials
5. **Run Installer**: yoursite.000webhostapp.com/install/install.php

**Special Notes**:
- Database credentials shown once only
- Save them immediately
- No prefix needed

---

### For cPanel Hosting (Shared/Paid)

1. **Login to cPanel**
2. **Create Database**:
   - MySQL Databases
   - Create New Database: cinecraze_db
   - Create MySQL User
   - Add User to Database
   - Grant ALL PRIVILEGES
3. **Upload Files**:
   - File Manager → public_html
   - Upload all files
   - OR use FTP
4. **Run Installer**: yourdomain.com/install/install.php

**Special Notes**:
- May have database prefix (username_)
- Full name: username_cinecraze_db
- Use full name in installer

---

## ✅ Post-Installation Checklist

After successful installation:

### Security
- [ ] Delete `/install` directory (IMPORTANT!)
- [ ] Change default admin password
- [ ] Verify file permissions
- [ ] Enable HTTPS if available
- [ ] Check .htaccess is working

### Testing
- [ ] Can you access homepage?
- [ ] Can you register a new user?
- [ ] Can you login?
- [ ] Can you access admin panel?
- [ ] Can you add a movie?
- [ ] Does search work?
- [ ] Do filters work?
- [ ] Can you play a video?

### Configuration
- [ ] Update site name in config/config.php
- [ ] Add categories if needed
- [ ] Upload some test content
- [ ] Set featured movies
- [ ] Test responsive design

---

## 🎬 Adding Your First Movie

1. **Login to Admin**
   ```
   URL: http://yourdomain.com/admin/
   Use admin credentials
   ```

2. **Click "Add Content"**

3. **Fill in Details**:
   ```
   Title: Inception
   Type: Movie
   Category: Sci-Fi
   Year: 2010
   Rating: 8.8
   Duration: 148
   Language: English
   Country: USA
   
   Thumbnail URL:
   https://image.tmdb.org/t/p/w500/9gk7adHYeDvHkCSEqAvQNLV5Uge.jpg
   
   Video URL (YouTube):
   https://www.youtube.com/watch?v=YoHD9XEInc0
   
   Description:
   A thief who steals corporate secrets through dream-sharing 
   technology is given the inverse task of planting an idea.
   
   Genre: Action, Sci-Fi, Thriller
   Cast: Leonardo DiCaprio, Joseph Gordon-Levitt
   ```

4. **Click "Save Content"**

5. **Visit Homepage** to see your movie!

---

## 🖼️ Getting Movie Thumbnails

### Free Sources:

1. **TMDB (The Movie Database)**
   - https://www.themoviedb.org/
   - Search movie
   - Right-click poster → Copy image address
   - Use URL in thumbnail field

2. **IMDB**
   - https://www.imdb.com/
   - Search movie
   - Right-click poster → Copy image address

3. **Unsplash** (for generic images)
   - https://unsplash.com/
   - Search for movie stills
   - Use image URL

4. **YouTube Thumbnails** (for YouTube videos)
   ```
   Format: https://img.youtube.com/vi/VIDEO_ID/maxresdefault.jpg
   Replace VIDEO_ID with actual YouTube video ID
   ```

---

## 🎥 Video URL Formats

### YouTube Videos
```
https://www.youtube.com/watch?v=VIDEO_ID
https://youtu.be/VIDEO_ID
https://www.youtube.com/embed/VIDEO_ID
```

### Direct Video Files
```
https://example.com/movies/video.mp4
https://example.com/videos/movie.m3u8 (HLS)
```

### Vimeo
```
https://vimeo.com/VIDEO_ID
https://player.vimeo.com/video/VIDEO_ID
```

---

## 🎨 Customization Guide

### Change Site Name
```php
// Edit: config/config.php
define('SITE_NAME', 'Your Site Name');
```

### Change Colors
```css
/* Edit: index.php and admin/index.php */
:root {
    --primary: #e50914;        /* Main color */
    --primary-dark: #b20710;   /* Hover color */
    --background: #0a0a0a;     /* Background */
}
```

### Add Categories
```sql
-- Run in MySQL
INSERT INTO categories (name, slug) VALUES ('Horror', 'horror');
INSERT INTO categories (name, slug) VALUES ('Comedy', 'comedy');
```

### Change Items Per Page
```php
// Edit: config/config.php
define('ITEMS_PER_PAGE', 20); // Change to your preference
```

---

## 🔧 Troubleshooting

### "Database connection failed"
```
✓ Check database credentials in config/database.php
✓ Verify database exists
✓ Check MySQL service is running
✓ Verify user has privileges
```

### "500 Internal Server Error"
```
✓ Check .htaccess syntax
✓ Verify PHP version (7.0+)
✓ Check error logs
✓ Verify file permissions (755/644)
```

### "Install directory not found"
```
✓ You already completed installation
✓ Directory was deleted (correct)
✓ Access site directly: yourdomain.com
```

### "Videos not playing"
```
✓ Check video URL is correct
✓ Verify video is publicly accessible
✓ Test URL in browser directly
✓ Check if HTTPS/HTTP matches
```

### "Can't login to admin"
```
✓ Verify admin account was created
✓ Check role is set to 'admin' in database
✓ Clear browser cookies
✓ Try different browser
```

### "Site looks broken on mobile"
```
✓ Clear browser cache
✓ Check viewport meta tag exists
✓ Verify CSS loaded correctly
✓ Try different mobile browser
```

---

## 📊 Sample Data

Want to test with sample content? Run this SQL:

```sql
-- Import sample_data.sql
-- This adds 15+ movies and series
-- Via phpMyAdmin or MySQL command line
```

Or manually via phpMyAdmin:
1. Login to phpMyAdmin
2. Select your database
3. Go to "Import" tab
4. Choose `sample_data.sql`
5. Click "Go"

---

## 🔐 Security Best Practices

After installation:

1. **Delete Install Directory**
   ```bash
   rm -rf /path/to/install/
   # OR via File Manager: Delete /install folder
   ```

2. **Strong Admin Password**
   - Minimum 12 characters
   - Include numbers, symbols
   - Change regularly

3. **File Permissions**
   ```bash
   chmod 755 uploads/
   chmod 755 admin/ api/ config/ includes/
   chmod 644 *.php
   chmod 644 .htaccess
   ```

4. **Enable HTTPS**
   - Get SSL certificate (Let's Encrypt is free)
   - Update .htaccess to force HTTPS
   - Update SITE_URL in config

5. **Regular Backups**
   - Backup database weekly
   - Backup files monthly
   - Test restore process

---

## 📱 Testing Responsive Design

### Desktop Browser
1. Open site in Chrome/Firefox
2. Press F12 (Developer Tools)
3. Click device icon (toggle device toolbar)
4. Test different sizes:
   - Galaxy S5 (360px)
   - iPad (768px)
   - iPad Pro (1024px)
   - Custom (200px for smartwatch)

### Real Devices
- Test on actual phone
- Test on tablet
- Test on TV browser if available

---

## 🚀 Going Live Checklist

Before promoting your site:

- [ ] SSL certificate installed
- [ ] Custom domain connected
- [ ] At least 20+ movies added
- [ ] All categories populated
- [ ] Featured movies set
- [ ] About page created (optional)
- [ ] Contact information added
- [ ] Terms of service (if needed)
- [ ] Privacy policy (if needed)
- [ ] Social media links
- [ ] SEO meta tags
- [ ] Google Analytics (optional)
- [ ] Favicon added
- [ ] 404 page customized

---

## 📞 Getting Help

### Documentation
- README.md - Main documentation
- QUICKSTART.md - Quick setup
- DEPLOYMENT.md - Deployment guides
- FEATURES.md - All features
- TROUBLESHOOTING section in README

### Resources
- PHP Manual: https://www.php.net/manual/
- MySQL Docs: https://dev.mysql.com/doc/
- Hosting Support: Contact your host

### Common Issues
Most issues are solved by:
1. Checking credentials
2. Verifying file permissions
3. Reading error logs
4. Clearing cache

---

## 🎉 Success!

If you've followed all steps, you should now have:

✅ Fully functional movie streaming platform
✅ Admin dashboard for management
✅ User registration and login
✅ Content management system
✅ Responsive design on all devices
✅ Secure and optimized installation

**Now go add some movies and enjoy! 🎬🍿**

---

## 💡 Pro Tips

1. **Start Small**: Add 10-20 movies first
2. **Use CDN**: For thumbnails and videos
3. **Test Often**: Check each feature after changes
4. **Backup Regularly**: Before major updates
5. **Monitor Performance**: Check load times
6. **Update Content**: Add new movies regularly
7. **Engage Users**: Encourage registration
8. **Social Share**: Add share buttons
9. **Mobile First**: Most users are mobile
10. **Keep Learning**: PHP/MySQL has great docs

---

**Questions?** Review the documentation files included with this project.

**Good luck with your streaming platform!** 🚀
