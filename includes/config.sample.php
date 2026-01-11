<?php
/**
 * CineCraze Configuration File (SAMPLE)
 * 
 * Copy this file to config.php and update with your settings
 */

// Database Configuration
// For shared hosting, get these from your hosting control panel
define('DB_HOST', 'localhost'); // Usually 'localhost' on shared hosting
define('DB_NAME', 'your_database_name'); // Your database name
define('DB_USER', 'your_database_user'); // Your database username
define('DB_PASS', 'your_database_password'); // Your database password
define('DB_CHARSET', 'utf8mb4');

// Site Configuration
define('SITE_NAME', 'CineCraze'); // Your site name
define('SITE_URL', 'https://yourdomain.com'); // Your site URL (no trailing slash)
define('ADMIN_URL', SITE_URL . '/admin');

// Security Configuration
define('SESSION_LIFETIME', 86400 * 30); // 30 days in seconds
define('TOKEN_EXPIRY', 86400 * 30); // 30 days in seconds
define('PASSWORD_SALT', 'change_this_to_random_string_' . md5(__DIR__));

// Path Configuration
define('BASE_PATH', dirname(__DIR__));
define('INCLUDES_PATH', BASE_PATH . '/includes');
define('ADMIN_PATH', BASE_PATH . '/admin');
define('UPLOAD_PATH', BASE_PATH . '/uploads');

// Timezone
date_default_timezone_set('UTC');

// Error Reporting
// IMPORTANT: Set to 0 in production for security
error_reporting(0); // Change to E_ALL for debugging
ini_set('display_errors', 0); // Change to 1 for debugging

// Session Configuration
ini_set('session.cookie_lifetime', SESSION_LIFETIME);
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Installation check
define('INSTALLED', true);
