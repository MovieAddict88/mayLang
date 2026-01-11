<?php

declare(strict_types=1);

function tmdb_api_key(): string
{
    $config = app_config();
    return (string)($config['app']['tmdb_api_key'] ?? '');
}

function tmdb_get(string $path, array $params = []): array
{
    $key = tmdb_api_key();
    if ($key === '') {
        throw new RuntimeException('TMDB API key is not configured. Set it during install or in config/config.php.');
    }

    $params = array_merge($params, [
        'api_key' => $key,
        'language' => 'en-US',
    ]);

    $url = 'https://api.themoviedb.org/3' . $path . '?' . http_build_query($params);

    $body = null;
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $body = curl_exec($ch);
        $err = curl_error($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($body === false || $status >= 400) {
            throw new RuntimeException('TMDB request failed: ' . ($err ?: ('HTTP ' . $status)));
        }
    } else {
        $body = @file_get_contents($url);
        if ($body === false) {
            throw new RuntimeException('TMDB request failed. (Enable curl or allow_url_fopen)');
        }
    }

    $decoded = json_decode((string)$body, true);
    if (!is_array($decoded)) {
        throw new RuntimeException('TMDB response could not be parsed.');
    }

    return $decoded;
}
