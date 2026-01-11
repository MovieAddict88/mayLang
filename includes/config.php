<?php
/**
 * CineCraze Configuration File
 * 
 * IMPORTANT: After installation, keep this file secure and outside web root if possible
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'cinecraze');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Site Configuration
define('SITE_NAME', 'CineCraze');
define('SITE_URL', 'http://localhost');
define('ADMIN_URL', SITE_URL . '/admin');

// Security Configuration
define('SESSION_LIFETIME', 86400 * 30); // 30 days in seconds
define('TOKEN_EXPIRY', 86400 * 30); // 30 days in seconds
define('PASSWORD_SALT', 'cinecraze_salt_' . md5(__DIR__)); // Auto-generated salt

// Path Configuration
define('BASE_PATH', dirname(__DIR__));
define('INCLUDES_PATH', BASE_PATH . '/includes');
define('ADMIN_PATH', BASE_PATH . '/admin');
define('UPLOAD_PATH', BASE_PATH . '/uploads');

// Timezone
date_default_timezone_set('UTC');

// Error Reporting
// Set to 0 in production, E_ALL for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
