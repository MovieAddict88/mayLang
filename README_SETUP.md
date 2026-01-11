# CineCraze - PHP/MySQL Setup Guide

## Overview
CineCraze has been converted from a static HTML/JSON-based application to a modern PHP/MySQL powered streaming platform with user authentication and a responsive admin panel.

## Features

### Admin Panel
- Modern dashboard with user monitoring and statistics
- TMDB content generator
- Manual content entry
- Bulk operations
- Data management (import/export, cleanup)
- Settings management (API keys, embed servers)
- Fully responsive design (supports smartphones, tablets, laptops, PCs, smart TVs, and smart watches)

### Public Website
- User registration and authentication (mandatory login)
- Persistent login with tokens (one-time login)
- Content fetching from database API
- IndexedDB caching for offline support
- Lazy loading and pagination
- Watch later functionality
- Like/dislike system
- Watch progress tracking
- Fully responsive design

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or MariaDB 10.3 or higher
- Web server (Apache/Nginx)
- Composer (optional, for future dependencies)

### Step 1: Database Setup

1. Create the database and import the schema:
```bash
mysql -u root -p < database.sql
```

Or manually:
```sql
CREATE DATABASE cinecraze CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cinecraze;
SOURCE database.sql;
```

### Step 2: Configuration

1. Edit `includes/config.php` and update database credentials:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'cinecraze');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
```

2. Update `SITE_URL` to match your domain:
```php
define('SITE_URL', 'https://yourdomain.com');
```

### Step 3: Web Server Configuration

#### Apache (.htaccess)
Create a `.htaccess` file in the root directory:
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^api/(.*)$ api/$1 [L]
```

#### Nginx
Add this to your nginx configuration:
```nginx
location /api/ {
    try_files $uri $uri/ /api/$uri;
}

location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### Step 4: Permissions

Set proper permissions for upload directories:
```bash
mkdir -p uploads cache temp
chmod 755 uploads cache temp
chown www-data:www-data uploads cache temp
```

### Step 5: Default Admin Account

Default admin credentials:
- Email: admin@cinecraze.com
- Password: admin123

**IMPORTANT:** Change this password immediately after first login!

## API Endpoints

### Authentication API (`/api/auth.php`)
- `POST /api/auth.php?action=register` - Register new user
- `POST /api/auth.php?action=login` - User login
- `GET /api/auth.php?action=verify&token=...` - Verify token
- `POST /api/auth.php?action=logout` - Logout user

### Content API (`/api/content.php`)
- `GET /api/content.php?action=list` - Get content list (with filters)
- `GET /api/content.php?action=detail&id=...` - Get content details
- `GET /api/content.php?action=featured` - Get featured content
- `GET /api/content.php?action=trending` - Get trending content
- `GET /api/content.php?action=genres` - Get all genres
- `GET /api/content.php?action=countries` - Get all countries
- `GET /api/content.php?action=years` - Get all years
- `GET /api/content.php?action=search&q=...` - Search content

### User Actions API (`/api/user_actions.php`)
- `POST /api/user_actions.php?action=watch_later_add` - Add to watch later
- `POST /api/user_actions.php?action=watch_later_remove` - Remove from watch later
- `GET /api/user_actions.php?action=watch_later_list` - Get watch later list
- `POST /api/user_actions.php?action=react` - Like/dislike content
- `GET /api/user_actions.php?action=check_reaction` - Check user reaction
- `POST /api/user_actions.php?action=update_progress` - Update watch progress
- `GET /api/user_actions.php?action=get_progress` - Get watch progress
- `GET /api/user_actions.php?action=watch_history` - Get watch history

## Database Schema

### Main Tables
- `users` - User accounts and authentication
- `content` - Movies, TV series, and live TV
- `seasons` - TV series seasons
- `episodes` - TV series episodes
- `streaming_sources` - Content streaming URLs
- `embed_servers` - Configurable embed servers
- `settings` - Application settings
- `watch_later` - User watch later lists
- `watch_history` - User watch progress
- `user_reactions` - User likes/dislikes
- `admin_logs` - Admin activity logging

## Responsive Design

The application uses modern CSS techniques for full responsiveness:
- `clamp()` for fluid typography and spacing
- Media queries for all device sizes
- Mobile-first approach
- Support for devices from 220px (smart watches) to 4K displays

### Breakpoints
- Smart watches: < 320px
- Mobile phones: 320px - 767px
- Tablets: 768px - 1023px
- Laptops/Desktops: 1024px - 1439px
- Large screens: 1440px+

## Security Features

- Password hashing with bcrypt
- Prepared statements (SQL injection protection)
- Input sanitization
- XSS protection
- CSRF protection (session-based)
- Secure token-based authentication
- HTTP-only cookies for tokens

## Development

### Adding TMDB Integration
To enable TMDB content import:
1. Get API key from https://www.themoviedb.org/settings/api
2. Add key in Admin Panel → Settings
3. Use TMDB Generator to import content

### Adding Content Manually
1. Go to Admin Panel → Manual Entry
2. Fill in content details
3. Add streaming sources
4. Publish content

## Troubleshooting

### Database Connection Issues
- Check credentials in `includes/config.php`
- Ensure MySQL service is running
- Verify user has proper permissions

### Login Issues
- Clear browser cookies and cache
- Check session configuration in `php.ini`
- Verify token expiry settings

### API Not Working
- Check web server URL rewrite rules
- Verify file permissions
- Check PHP error logs

## Future Enhancements

Planned features:
- Complete TMDB generator implementation
- Manual content entry form
- Bulk operations tools
- Data import/export functionality
- User management in admin panel
- Content moderation features
- Analytics and reporting
- Multi-language support
- CDN integration

## License
Copyright © 2024 CineCraze. All rights reserved.

## Support
For issues and questions, please create an issue in the repository.
