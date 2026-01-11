<?php

declare(strict_types=1);

function auth_user(): ?array
{
    static $user;
    if ($user !== null) {
        return $user;
    }

    if (empty($_SESSION['user_id'])) {
        $user = null;
        return null;
    }

    $stmt = db()->prepare('SELECT id, email, role, created_at, last_login_at FROM users WHERE id = :id');
    $stmt->execute([':id' => (int)$_SESSION['user_id']]);
    $row = $stmt->fetch();
    $user = $row ?: null;
    return $user;
}

function auth_require_login(): void
{
    if (!app_is_installed()) {
        redirect('/install/');
    }

    if (!auth_user()) {
        redirect('/auth/login.php');
    }
}

function auth_require_admin(): void
{
    if (!app_is_installed()) {
        redirect('/install/');
    }

    $user = auth_user();
    if (!$user) {
        redirect('/admin/login.php');
    }

    if (($user['role'] ?? '') !== 'admin') {
        http_response_code(403);
        exit('Forbidden');
    }
}

function auth_login(string $email, string $password): bool
{
    $email = trim(strtolower($email));

    $stmt = db()->prepare('SELECT id, email, password_hash, role FROM users WHERE email = :email');
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if (!$user || empty($user['password_hash']) || !password_verify($password, $user['password_hash'])) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$user['id'];

    db()->prepare('UPDATE users SET last_login_at = NOW(), last_login_ip = :ip WHERE id = :id')
        ->execute([
            ':ip' => (string)($_SERVER['REMOTE_ADDR'] ?? ''),
            ':id' => (int)$user['id'],
        ]);

    return true;
}

function auth_logout(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool)$params['secure'], (bool)$params['httponly']);
    }

    session_destroy();
}

function auth_register(string $email, string $password): array
{
    $email = trim(strtolower($email));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Invalid email address.');
    }
    if (strlen($password) < 8) {
        throw new InvalidArgumentException('Password must be at least 8 characters.');
    }

    $stmt = db()->prepare('SELECT id FROM users WHERE email = :email');
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch()) {
        throw new InvalidArgumentException('Email is already registered.');
    }

    $hash = password_hash_str($password);

    $stmt = db()->prepare('INSERT INTO users (email, password_hash, role, created_at) VALUES (:email, :hash, :role, NOW())');
    $stmt->execute([
        ':email' => $email,
        ':hash' => $hash,
        ':role' => 'user',
    ]);

    return [
        'id' => (int)db()->lastInsertId(),
        'email' => $email,
        'role' => 'user',
    ];
}
