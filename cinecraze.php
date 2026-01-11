<?php

declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

if (!app_is_installed()) {
    redirect('/install/');
}

$user = auth_user();
if (!$user || ($user['role'] ?? '') !== 'admin') {
    redirect('/admin/login.php');
}

redirect('/admin/');
