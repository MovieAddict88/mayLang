<?php

declare(strict_types=1);

function str_starts_with_compat(string $haystack, string $needle): bool
{
    if ($needle === '') {
        return true;
    }
    return substr($haystack, 0, strlen($needle)) === $needle;
}

function base_url(): string
{
    $config = app_config();
    $base = (string)($config['app']['base_url'] ?? '');
    if ($base !== '') {
        return rtrim($base, '/');
    }

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $dir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

    return $scheme . '://' . $host . ($dir !== '' ? $dir : '');
}

function redirect(string $path): void
{
    if (str_starts_with_compat($path, 'http://') || str_starts_with_compat($path, 'https://')) {
        header('Location: ' . $path);
        exit;
    }

    $url = $path;
    if ($path === '' || $path[0] !== '/') {
        $url = '/' . $path;
    }

    header('Location: ' . $url);
    exit;
}

function json_response(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}
