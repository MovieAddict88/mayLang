<?php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

define('APP_ROOT', dirname(__DIR__));

define('CONFIG_FILE', APP_ROOT . '/config/config.php');
define('INSTALLED_FILE', APP_ROOT . '/config/.installed');

function app_is_installed(): bool
{
    return is_file(CONFIG_FILE) && is_file(INSTALLED_FILE);
}

function app_config(): array
{
    static $config;
    if ($config !== null) {
        return $config;
    }

    if (!is_file(CONFIG_FILE)) {
        $config = [];
        return $config;
    }

    $loaded = require CONFIG_FILE;
    $config = is_array($loaded) ? $loaded : [];
    return $config;
}

require_once APP_ROOT . '/src/db.php';
require_once APP_ROOT . '/src/security.php';
require_once APP_ROOT . '/src/auth.php';
require_once APP_ROOT . '/src/http.php';
