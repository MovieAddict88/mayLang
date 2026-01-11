<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

if (!app_is_installed()) {
    json_response(['Categories' => []], 503);
}

$pdo = db();

function build_servers(PDO $pdo, int $mediaId): array
{
    $stmt = $pdo->prepare('SELECT label, url FROM media_streams WHERE media_id = :id ORDER BY sort_order ASC, id ASC');
    $stmt->execute([':id' => $mediaId]);
    $rows = $stmt->fetchAll();

    $servers = [];
    $i = 1;
    foreach ($rows as $r) {
        $servers[] = [
            'name' => ($r['label'] ?? '') !== '' ? (string)$r['label'] : ('Server ' . $i),
            'url' => (string)$r['url'],
        ];
        $i++;
    }

    return $servers;
}

function build_series_seasons(PDO $pdo, int $mediaId, ?string $posterFallback): array
{
    $stmt = $pdo->prepare('SELECT id, season_number, episode_number, title, description FROM media_episodes WHERE media_id = :id ORDER BY season_number ASC, episode_number ASC');
    $stmt->execute([':id' => $mediaId]);
    $rows = $stmt->fetchAll();

    $seasons = [];

    foreach ($rows as $ep) {
        $seasonNum = (int)$ep['season_number'];
        $seasonKey = (string)$seasonNum;
        if (!isset($seasons[$seasonKey])) {
            $seasons[$seasonKey] = [
                'Season' => $seasonNum,
                'SeasonPoster' => $posterFallback,
                'Episodes' => [],
            ];
        }

        $streamStmt = $pdo->prepare('SELECT label, url FROM media_episode_streams WHERE episode_id = :id ORDER BY sort_order ASC, id ASC');
        $streamStmt->execute([':id' => (int)$ep['id']]);
        $streams = $streamStmt->fetchAll();

        $servers = [];
        $i = 1;
        foreach ($streams as $s) {
            $servers[] = [
                'name' => ($s['label'] ?? '') !== '' ? (string)$s['label'] : ('Server ' . $i),
                'url' => (string)$s['url'],
            ];
            $i++;
        }

        $seasons[$seasonKey]['Episodes'][] = [
            'Episode' => (int)$ep['episode_number'],
            'Title' => (string)$ep['title'],
            'Description' => (string)($ep['description'] ?? ''),
            'Servers' => $servers,
        ];
    }

    return array_values($seasons);
}

function build_entry(array $row): array
{
    return [
        'Title' => (string)$row['title'],
        'Description' => (string)($row['description'] ?? ''),
        'Poster' => (string)($row['poster_url'] ?? ''),
        'Thumbnail' => (string)($row['poster_url'] ?? ''),
        'Backdrop' => (string)($row['backdrop_url'] ?? ''),
        'Year' => (string)($row['year'] ?? ''),
        'Rating' => (string)($row['rating'] ?? ''),
        'Country' => (string)($row['country'] ?? ''),
        'SubCategory' => (string)($row['sub_category'] ?? ''),
        'YouTube' => (string)($row['youtube_url'] ?? ''),
    ];
}

$types = [
    'movie' => 'Movies',
    'series' => 'TV Series',
    'live' => 'Live TV',
];

$out = ['Categories' => []];

foreach ($types as $type => $label) {
    $stmt = $pdo->prepare('SELECT id, title, description, poster_url, backdrop_url, year, rating, country, sub_category, youtube_url FROM media_items WHERE type = :type ORDER BY updated_at DESC, id DESC');
    $stmt->execute([':type' => $type]);
    $items = [];

    foreach ($stmt->fetchAll() as $row) {
        $entry = build_entry($row);

        if ($type === 'series') {
            $entry['Seasons'] = build_series_seasons($pdo, (int)$row['id'], $entry['Poster'] !== '' ? $entry['Poster'] : null);
        } else {
            $entry['Servers'] = build_servers($pdo, (int)$row['id']);
        }

        $items[] = $entry;
    }

    $out['Categories'][] = [
        'MainCategory' => $label,
        'Entries' => $items,
    ];
}

json_response($out);
