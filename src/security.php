<?php

declare(strict_types=1);

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return (string)$_SESSION['csrf_token'];
}

function csrf_validate(?string $token): void
{
    $expected = $_SESSION['csrf_token'] ?? '';
    if (!$token || !is_string($expected) || !hash_equals($expected, $token)) {
        http_response_code(419);
        exit('Invalid CSRF token.');
    }
}

function password_hash_str(string $password): string
{
    $hash = password_hash($password, PASSWORD_DEFAULT);
    if ($hash === false) {
        throw new RuntimeException('Failed to hash password.');
    }
    return $hash;
}
