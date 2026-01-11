# CineCraze Conversion Guide

## What Has Been Completed

### ✅ Backend Infrastructure (100%)
1. **Database Schema** (`database.sql`)
   - Complete MySQL schema with all tables
   - Users, content, seasons, episodes, streaming sources
   - Watch later, watch history, user reactions
   - Settings and embed servers management
   - Admin activity logging

2. **Core PHP Files** (`includes/`)
   - `config.php` - Configuration constants
   - `Database.php` - Database singleton class with helpers
   - `functions.php` - Authentication, sanitization, user/content helpers

3. **Admin Panel** (`admin/`)
   - `index.php` - Beautiful responsive login page
   - `dashboard.php` - Full-featured dashboard with user monitoring
   - `settings.php` - API keys and embed servers CRUD
   - `logout.php` - Session termination
   - Stub files for TMDB generator, manual entry, bulk operations, data management

4. **API Endpoints** (`api/`)
   - `auth.php` - Complete authentication API (register, login, verify, logout)
   - `content.php` - Full content API with filtering, pagination, search
   - `user_actions.php` - Watch later, likes/dislikes, progress tracking

5. **Design System**
   - Fully responsive admin panel (Poppins font, modern cards)
   - Support for devices from 220px (smart watches) to 4K
   - Uses `clamp()` for fluid typography
   - Mobile-first approach with comprehensive media queries

### ⏳ What Needs to Be Completed

#### 1. Public Website Conversion (`index.php`)
The original `index.html` (8499 lines) needs to be converted to `index.php` with:
- Mandatory authentication modal (cannot be closed until login/register)
- Integration with `/api/auth.php` for authentication
- Fetch content from `/api/content.php` instead of JSON files
- Maintain IndexedDB caching for offline support
- Persistent login using tokens (stored in cookies)
- All existing features: carousel, filters, search, player, etc.

**Key Changes Required:**
```javascript
// OLD: Fetch from JSON
fetch('playlists/playlist.json')

// NEW: Fetch from API with authentication
fetch('/api/content.php?action=list&limit=20&page=1', {
    headers: {
        'Authorization': 'Bearer ' + getStoredToken()
    }
})
```

#### 2. Admin Content Generators

**TMDB Generator (`admin/tmdb_generator.php`)**
- Fetch metadata from TMDB API using stored API key
- Forms for ID-based and search-based lookup
- Auto-populate content fields from TMDB
- Season/episode management for TV series
- Preview and save to database

**Manual Generator (`admin/manual_generator.php`)**
- Rich form for manual content entry
- All fields: title, description, poster, backdrop, trailer
- Genres, countries, languages selection
- Cast and directors input
- Streaming sources management
- Season/episode creation for series

**Bulk Generator (`admin/bulk_generator.php`)**
- Import multiple TMDB IDs at once
- CSV import functionality
- Progress tracking
- Error handling and reporting

**Data Management (`admin/data_management.php`)**
- Export database to JSON (for backups)
- Import JSON playlists (convert old format)
- Duplicate detection and cleanup
- Bulk edit operations
- Database statistics

#### 3. Enhanced Features

**User Management (`admin/users.php`)**
- User list with sorting and filtering
- CRUD operations (edit, delete, activate/deactivate)
- Viewing user activity and watch history
- Role management (user vs admin)

**Content Management (`admin/content.php`)**
- List all content with filters
- Edit existing content
- Delete content
- Featured/trending toggles
- Bulk operations

**Analytics Dashboard**
- Views over time graphs
- Popular content
- User engagement metrics
- Growth statistics

## Quick Start for Developers

### 1. Database Setup
```bash
mysql -u root -p < database.sql
```

### 2. Configuration
Edit `includes/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'cinecraze');
define('DB_USER', 'your_user');
define('DB_PASS', 'your_password');
define('SITE_URL', 'https://yourdomain.com');
```

### 3. Test Admin Panel
1. Navigate to `/admin/`
2. Login with: `admin@cinecraze.com` / `admin123`
3. Go to Settings and add TMDB API key

### 4. Test API Endpoints

**Register User:**
```bash
curl -X POST http://localhost/api/auth.php?action=register \
  -H "Content-Type: application/json" \
  -d '{"username":"testuser","email":"test@example.com","password":"test123","full_name":"Test User"}'
```

**Login:**
```bash
curl -X POST http://localhost/api/auth.php?action=login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"test123"}'
```

**Get Content:**
```bash
curl http://localhost/api/content.php?action=list&limit=10
```

## File Structure
```
project/
├── admin/
│   ├── index.php (login)
│   ├── dashboard.php (main dashboard)
│   ├── settings.php (API keys, servers)
│   ├── tmdb_generator.php (TODO)
│   ├── manual_generator.php (TODO)
│   ├── bulk_generator.php (TODO)
│   ├── data_management.php (TODO)
│   └── logout.php
├── api/
│   ├── auth.php (complete)
│   ├── content.php (complete)
│   └── user_actions.php (complete)
├── includes/
│   ├── config.php
│   ├── Database.php
│   └── functions.php
├── database.sql
├── index.php (TODO - needs full conversion from index.html)
├── cinecraze.html (original, for reference)
└── index.html (original, for reference)
```

## Converting index.html to index.php - Step by Step

### Step 1: Add PHP Header with Authentication Check
```php
<?php
require_once 'includes/config.php';
require_once 'includes/Database.php';
require_once 'includes/functions.php';

$isAuthenticated = false;
$userData = null;

// Check for token in cookie or session
if (isset($_COOKIE['login_token'])) {
    $user = getUserByToken($_COOKIE['login_token']);
    if ($user) {
        $isAuthenticated = true;
        $userData = $user;
        $_SESSION['user_id'] = $user['id'];
    }
} elseif (isLoggedIn()) {
    $isAuthenticated = true;
    $userData = getUserById($_SESSION['user_id']);
}
?>
```

### Step 2: Add Authentication Modal (Before Closing </body>)
```html
<!-- Auth Modal -->
<div id="authModal" class="auth-modal" style="display: <?php echo $isAuthenticated ? 'none' : 'flex'; ?>">
    <div class="auth-modal-content">
        <!-- Login/Register forms here -->
    </div>
</div>
```

### Step 3: Replace JSON Fetch with API Calls
```javascript
// Old code
async function loadPlaylist() {
    const response = await fetch('playlist.json');
    const data = await response.json();
    // ...
}

// New code
async function loadPlaylist() {
    const token = localStorage.getItem('auth_token');
    const response = await fetch('/api/content.php?action=list&limit=20&page=1', {
        headers: {
            'Authorization': `Bearer ${token}`
        }
    });
    const data = await response.json();
    // ...
}
```

### Step 4: Implement Authentication Modal Logic
```javascript
class AuthModal {
    constructor() {
        this.modal = document.getElementById('authModal');
        this.init();
    }
    
    async login(email, password) {
        const response = await fetch('/api/auth.php?action=login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, password })
        });
        const data = await response.json();
        if (data.success) {
            localStorage.setItem('auth_token', data.data.token);
            document.cookie = `login_token=${data.data.token}; max-age=${30*24*60*60}; path=/`;
            this.close();
            location.reload();
        }
    }
    
    async register(username, email, password, fullName) {
        const response = await fetch('/api/auth.php?action=register', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, email, password, full_name: fullName })
        });
        const data = await response.json();
        if (data.success) {
            await this.login(email, password);
        }
    }
}
```

## Security Considerations

1. **HTTPS Required**: Use HTTPS in production for secure token transmission
2. **Token Expiry**: Tokens expire after 30 days
3. **Password Policy**: Minimum 6 characters (increase in production)
4. **SQL Injection**: All queries use prepared statements
5. **XSS Protection**: All outputs are sanitized
6. **CSRF**: Session-based protection for forms

## Performance Optimizations

1. **Pagination**: API returns max 100 items per request
2. **IndexedDB**: Cache content locally for offline access
3. **Lazy Loading**: Load content as user scrolls
4. **CDN**: Use CDN for static assets (images, CSS, JS)
5. **Database Indexing**: All foreign keys and search fields indexed

## Next Steps

1. **Priority 1**: Complete index.php conversion with authentication modal
2. **Priority 2**: Implement TMDB generator for easy content addition
3. **Priority 3**: Build manual content entry form
4. **Priority 4**: Create data import/export tools
5. **Priority 5**: Add user management interface
6. **Priority 6**: Implement analytics and reporting

## Testing Checklist

- [ ] Database schema installs without errors
- [ ] Admin panel login works
- [ ] Settings page saves API keys and embed servers
- [ ] User registration via API works
- [ ] User login via API works
- [ ] Token verification works
- [ ] Content API returns data correctly
- [ ] Filters and search work in content API
- [ ] Watch later functionality works
- [ ] Like/dislike functionality works
- [ ] Progress tracking works
- [ ] Responsive design works on all devices
- [ ] Authentication modal cannot be bypassed
- [ ] Tokens persist across browser sessions
- [ ] IndexedDB caching works

## Support

For questions or issues with the conversion:
1. Check database.sql is properly imported
2. Verify config.php settings
3. Check PHP error logs
4. Test API endpoints with curl/Postman
5. Verify JavaScript console for errors

## Credits

Original CineCraze HTML apps by original developers
PHP/MySQL conversion and responsive admin panel design implemented
