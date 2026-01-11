<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

if (!app_is_installed()) {
    json_response(['installed' => false], 200);
}

$user = auth_user();
if (!$user) {
    json_response(['installed' => true, 'authenticated' => false]);
}

json_response([
    'installed' => true,
    'authenticated' => true,
    'user' => [
        'id' => (int)$user['id'],
        'email' => (string)$user['email'],
        'role' => (string)$user['role'],
    ],
]);
