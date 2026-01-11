<?php
// Application Configuration
session_start();

// Site Settings
define('SITE_NAME', 'CineCraze');
define('SITE_URL', 'http://localhost');
define('ADMIN_EMAIL', 'admin@cinecraze.com');

// Security
define('HASH_ALGO', PASSWORD_BCRYPT);
define('SESSION_LIFETIME', 86400); // 24 hours

// Pagination
define('ITEMS_PER_PAGE', 20);

// File Upload
define('MAX_FILE_SIZE', 5242880); // 5MB
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Timezone
date_default_timezone_set('UTC');

// Error Reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once __DIR__ . '/database.php';
