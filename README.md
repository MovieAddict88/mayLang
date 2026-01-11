# 🎬 CineCraze - Modern Streaming Platform

A powerful PHP/MySQL streaming platform with user authentication, admin panel, and responsive design.

## ✨ Features

- 🔐 User authentication & registration
- 👤 User management with CRUD operations
- 🎯 Admin dashboard with statistics
- 🎬 Content management (Movies, TV Series, Live TV)
- 🔍 Advanced search and filtering
- ❤️ Watch later & favorites
- 📊 Watch progress tracking
- 📱 Fully responsive (smart watches to 4K displays)
- 🎨 Modern UI with Poppins font
- 🔒 Secure with prepared statements & password hashing
- 🚀 Easy deployment on free or paid hosting

## 🚀 Quick Start

### Option 1: Web Installer (Easiest)
1. Upload all files to your hosting
2. Visit `http://yourdomain.com/install.php`
3. Follow the wizard
4. Delete `install.php` when done

### Option 2: Manual Setup
1. Create MySQL database
2. Import `database.sql`
3. Edit `includes/config.php` with your database credentials
4. Visit your site

## 📋 Requirements

- PHP 7.4 or higher
- MySQL 5.7 or MariaDB 10.3+
- PDO & PDO_MySQL extensions
- Apache/Nginx with mod_rewrite

## 📖 Documentation

- [Setup Guide](README_SETUP.md) - Detailed installation instructions
- [Deployment Guide](DEPLOYMENT_GUIDE.md) - Deploy on any hosting
- [Conversion Guide](CONVERSION_GUIDE.md) - Technical details & API docs

## 🔑 Default Login

After installation using the web installer, use your configured admin credentials.

## 🌐 Deployment

Works on:
- ✅ Free hosting (InfinityFree, 000webhost, etc.)
- ✅ Shared hosting (cPanel, Plesk)
- ✅ VPS (Ubuntu, CentOS, Debian)
- ✅ Docker
- ✅ Cloud (AWS, DigitalOcean, Linode)

## 📁 Project Structure

```
cinecraze/
├── admin/              # Admin panel
│   ├── index.php       # Login
│   ├── dashboard.php   # Main dashboard
│   ├── settings.php    # Settings & API keys
│   └── ...
├── api/                # REST API endpoints
│   ├── auth.php        # Authentication
│   ├── content.php     # Content management
│   └── user_actions.php # User interactions
├── includes/           # Core PHP files
│   ├── config.php      # Configuration
│   ├── Database.php    # Database class
│   └── functions.php   # Helper functions
├── database.sql        # Database schema
├── index.php           # Public website
├── install.php         # Web installer
└── .htaccess           # Apache config
```

## 🔧 Configuration

Edit `includes/config.php` after installation or use the web installer:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('SITE_URL', 'https://yourdomain.com');
```

## 🎯 API Endpoints

### Authentication
- `POST /api/auth.php?action=register` - Register user
- `POST /api/auth.php?action=login` - Login
- `GET /api/auth.php?action=verify` - Verify token

### Content
- `GET /api/content.php?action=list` - Get content
- `GET /api/content.php?action=detail&id=X` - Get details
- `GET /api/content.php?action=search&q=query` - Search

### User Actions
- `POST /api/user_actions.php?action=watch_later_add` - Add to watch later
- `POST /api/user_actions.php?action=react` - Like/dislike
- `POST /api/user_actions.php?action=update_progress` - Update progress

## 🎨 Design Features

- Modern gradient backgrounds
- Smooth animations
- Card-based layouts
- Responsive typography with `clamp()`
- Mobile-first approach
- Dark theme optimized

## 🔒 Security Features

- Password hashing (bcrypt)
- Prepared SQL statements
- Input sanitization
- XSS protection
- CSRF protection
- HTTP-only cookies
- Session security

## 📱 Responsive Breakpoints

- Smart watches: < 320px
- Mobile: 320px - 767px
- Tablet: 768px - 1023px
- Desktop: 1024px - 1439px
- Large: 1440px+

## 🤝 Contributing

Contributions are welcome! Please feel free to submit issues or pull requests.

## 📄 License

Copyright © 2024 CineCraze. All rights reserved.

## 🆘 Support

- Check [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md) for troubleshooting
- Review [README_SETUP.md](README_SETUP.md) for detailed setup
- Create issues for bugs or feature requests

## ⚡ Performance Tips

1. Enable OPcache in PHP
2. Use CDN for static assets
3. Enable gzip compression (included in .htaccess)
4. Optimize images before upload
5. Regular database cleanup

## 🔄 Upgrading

To upgrade to latest version:
1. Backup database
2. Replace files (keep config.php)
3. Run any migration scripts
4. Clear cache

---

Made with ❤️ for streaming enthusiasts
