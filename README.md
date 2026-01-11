# 🎬 CineCraze - Fullstack PHP/MySQL Movie Platform

A complete, production-ready movie streaming platform built with pure PHP and MySQL. Features user authentication, admin dashboard, and fully responsive design from smartwatch to 4K displays.

## ✨ Features

### Public Site (index.php)
- 🎥 Dynamic movie/series/live TV catalog
- 🔍 Real-time search functionality
- 🎯 Category and type filtering
- 🎨 Featured content carousel
- 📱 Fully responsive (200px smartwatch → 4K+ smart TV)
- 🔐 User authentication system
- 🎬 Integrated video player (Plyr.js)
- 💾 Watch later functionality
- ⭐ Like/dislike system

### Admin Dashboard (cinecraze.html → admin/index.php)
- 📊 Modern card-based dashboard
- 📈 Real-time statistics
- 🎬 CRUD operations for movies/series
- 👥 User management
- 🏷️ Category management
- 📝 Manual & bulk content addition
- 🎥 YouTube integration
- 🎯 Header navigation (replacing bottom tabs)
- 📱 Responsive across all devices

### Technical Features
- ✅ Pure PHP/MySQL (no frameworks)
- 🔒 Secure session-based authentication
- 🛡️ SQL injection protection (prepared statements)
- 🎨 Modern, clean UI with CSS variables
- 📱 Mobile-first responsive design
- 🚀 Optimized for both free and paid hosting
- 📦 One-click installation wizard
- 🔧 No external dependencies (except CDN assets)

## 📋 Requirements

### Minimum Requirements (Free Hosting)
- PHP 7.0 or higher
- MySQL 5.6 or higher
- Apache with mod_rewrite
- 50MB disk space
- 128MB RAM

### Recommended Requirements (Paid Hosting)
- PHP 8.0 or higher
- MySQL 8.0 or higher
- 256MB RAM
- SSL Certificate

## 🚀 Installation

### Method 1: Automatic Installation (Recommended)

1. **Upload Files**
   - Download/clone this repository
   - Upload all files to your web server root directory
   - Ensure the `/uploads` folder is writable (chmod 755)

2. **Run Installation Wizard**
   - Navigate to: `http://yourdomain.com/install/install.php`
   - Follow the step-by-step wizard:
     - **Step 1**: Configure database connection
     - **Step 2**: Create admin account
     - **Step 3**: Complete setup

3. **Post-Installation**
   - Delete the `/install` directory for security
   - Login to admin panel: `http://yourdomain.com/admin/`
   - Start adding your content!

### Method 2: Manual Installation

1. **Create Database**
   ```sql
   CREATE DATABASE cinecraze_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. **Import Database Schema**
   - The installation wizard creates tables automatically
   - Or manually run the SQL from `/install/install.php`

3. **Configure Database**
   - Edit `/config/database.php`
   - Update with your database credentials:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'your_username');
     define('DB_PASS', 'your_password');
     define('DB_NAME', 'cinecraze_db');
     ```

4. **Create Admin Account**
   - Use the registration page or insert directly:
   ```sql
   INSERT INTO users (username, email, password, role, status, created_at) 
   VALUES ('admin', 'admin@example.com', PASSWORD_HASH, 'admin', 'active', NOW());
   ```

## 🎯 Responsive Breakpoints

The application is fully responsive across:

| Device | Width | Optimizations |
|--------|-------|---------------|
| Smartwatch | 200-250px | Simplified layout, essential content only |
| Small Phone | 320-480px | Single column, touch-optimized |
| Phone | 481-768px | Optimized grid, collapsible navigation |
| Tablet | 769-1024px | Multi-column layout, enhanced navigation |
| Laptop | 1025-1440px | Full features, side-by-side content |
| Desktop | 1441-2560px | Maximum content density |
| 4K/Smart TV | 2561px+ | Large fonts, grid optimization |

## 🎨 Customization

### Changing Colors
Edit CSS variables in the `<style>` section:
```css
:root {
    --primary: #e50914;        /* Main brand color */
    --primary-dark: #b20710;   /* Hover states */
    --background: #0a0a0a;     /* Dark background */
    --surface: #1a1a1a;        /* Card backgrounds */
}
```

### Adding Categories
1. Login to admin panel
2. Navigate to Database Management
3. Insert new category with name and slug
4. Or add via SQL:
```sql
INSERT INTO categories (name, slug) VALUES ('Horror', 'horror');
```

## 📁 File Structure

```
cinecraze/
├── admin/
│   ├── index.php          # Admin dashboard
│   ├── add-movie.php      # Add new content
│   └── edit-movie.php     # Edit existing content
├── api/
│   ├── movie.php          # Movie details API
│   └── search.php         # Search API
├── config/
│   ├── config.php         # Application config
│   └── database.php       # Database connection
├── includes/
│   ├── auth.php           # Authentication functions
│   └── functions.php      # Helper functions
├── install/
│   └── install.php        # Installation wizard
├── uploads/               # User uploads directory
├── .htaccess              # Apache configuration
├── .gitignore             # Git ignore rules
├── index.php              # Public homepage
├── login.php              # Login page
├── register.php           # Registration page
├── logout.php             # Logout handler
└── README.md              # This file
```

## 🔒 Security Features

- ✅ Password hashing with bcrypt
- ✅ Prepared statements (SQL injection protection)
- ✅ XSS protection via htmlspecialchars
- ✅ Session-based authentication
- ✅ CSRF token support (add as needed)
- ✅ File upload validation
- ✅ .htaccess protection for sensitive files

## 🌐 Deployment

### Free Hosting (InfinityFree, 000webhost)

1. Create account on hosting platform
2. Upload files via File Manager or FTP
3. Create MySQL database via cPanel
4. Run installation wizard
5. Update `.htaccess` if needed for specific host

**Notes for Free Hosting:**
- Some hosts may have PHP memory limits
- CDN assets (FontAwesome, Plyr) are loaded externally
- Ensure `mod_rewrite` is available

### Paid Hosting (cPanel)

1. Upload via FTP or cPanel File Manager
2. Create database and user via MySQL Databases
3. Point domain to installation directory
4. Run installation wizard
5. Enable SSL certificate (Let's Encrypt)
6. Configure .htaccess for HTTPS redirect

### Shared Hosting Tips

```php
// Add to config.php for strict hosting
ini_set('display_errors', 0);
error_reporting(0);

// Increase limits if allowed
ini_set('memory_limit', '256M');
ini_set('max_execution_time', '300');
```

## 🔧 Configuration

### Site Settings
Edit `/config/config.php`:
```php
define('SITE_NAME', 'CineCraze');
define('SITE_URL', 'https://yourdomain.com');
define('ADMIN_EMAIL', 'admin@yourdomain.com');
```

### Database Optimization
For better performance:
```sql
-- Add indexes
CREATE INDEX idx_movie_type ON movies(type);
CREATE INDEX idx_movie_category ON movies(category);
CREATE FULLTEXT INDEX idx_search ON movies(title, description);
```

## 🎬 Adding Content

### Via Admin Panel
1. Login to `/admin/`
2. Click "Add Content"
3. Fill in movie details:
   - Title, Type (Movie/Series/Live)
   - Thumbnail URL (external image)
   - Video URL (YouTube or direct)
   - Description, Genre, Cast, etc.
4. Save

### YouTube Integration
- Supports YouTube embed URLs
- Automatic video ID extraction
- Uses Plyr.js for consistent player UI

### Bulk Import (Custom)
Add bulk import functionality in admin panel:
```php
// Parse CSV/JSON and insert multiple movies
foreach ($movies as $movie) {
    createMovie($movie);
}
```

## 🐛 Troubleshooting

### Database Connection Error
- Verify credentials in `/config/database.php`
- Ensure MySQL service is running
- Check if database exists

### 500 Internal Server Error
- Check `.htaccess` compatibility
- Review PHP error logs
- Verify file permissions (755 for directories, 644 for files)

### Video Player Not Working
- Ensure Plyr.js CDN is accessible
- Check video URL format
- Verify CORS settings for external videos

### Responsive Issues
- Clear browser cache
- Check viewport meta tag
- Verify CSS media queries

## 📊 Database Schema

### Users Table
```sql
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- username (VARCHAR, UNIQUE)
- email (VARCHAR, UNIQUE)
- password (VARCHAR, hashed)
- role (ENUM: admin, user)
- status (ENUM: active, inactive)
- created_at (TIMESTAMP)
- last_login (TIMESTAMP)
```

### Movies Table
```sql
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- title (VARCHAR)
- description (TEXT)
- thumbnail (VARCHAR, URL)
- video_url (VARCHAR, URL)
- trailer_url (VARCHAR, URL)
- type (ENUM: movie, series, live)
- category (VARCHAR)
- year (INT)
- rating (DECIMAL)
- duration (INT, minutes)
- language (VARCHAR)
- country (VARCHAR)
- genre (VARCHAR)
- cast_crew (TEXT)
- featured (BOOLEAN)
- status (ENUM: active, inactive, deleted)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

## 🚀 Performance Tips

1. **Enable Caching**
   - Use browser caching via .htaccess
   - Implement page caching for public pages

2. **Optimize Images**
   - Use CDN for thumbnails
   - Compress images before upload
   - Use WebP format when possible

3. **Database Optimization**
   - Regular optimization: `OPTIMIZE TABLE movies;`
   - Add indexes for frequently queried columns
   - Use LIMIT in queries

4. **CDN Integration**
   - Use CDN for static assets
   - External hosting for video files

## 📝 License

This project is open-source and free to use for personal and commercial projects.

## 🤝 Support

For issues or questions:
1. Check the troubleshooting section
2. Review error logs (`/error_log`)
3. Verify hosting requirements
4. Check database connection

## 🔄 Updates

To update the application:
1. Backup database and files
2. Download latest version
3. Replace files (keep `/config/database.php`)
4. Run any migration scripts
5. Clear cache

## 🎉 Credits

- **Plyr.js** - Video player
- **Font Awesome** - Icons
- **Google Fonts** - Typography

---

**Version:** 1.0.0  
**Last Updated:** 2024  
**Author:** CineCraze Team

Enjoy your streaming platform! 🎬🍿
