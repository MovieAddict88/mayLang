<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

auth_require_admin();
$user = auth_user();

$active = 'bulk';
$title = 'Bulk';
$subtitle = 'Import your existing playlist.json into MySQL (Movies / TV Series / Live TV).';

$pdo = db();

$errors = [];
$success = null;
$stats = null;

function detect_type(string $mainCategory): ?string
{
    $c = strtolower($mainCategory);
    if (strpos($c, 'movie') !== false) {
        return 'movie';
    }
    if (strpos($c, 'series') !== false || strpos($c, 'tv') !== false) {
        return 'series';
    }
    if (strpos($c, 'live') !== false) {
        return 'live';
    }
    return null;
}

function normalize_server_row($s, int $i): array
{
    $label = null;
    $url = null;

    if (is_array($s)) {
        $label = $s['name'] ?? $s['Name'] ?? $s['label'] ?? $s['Label'] ?? null;
        $url = $s['url'] ?? $s['Url'] ?? $s['link'] ?? $s['Link'] ?? null;
    } elseif (is_string($s)) {
        $url = $s;
    }

    $labelStr = is_string($label) ? trim($label) : '';
    $urlStr = is_string($url) ? trim($url) : '';

    return [
        'label' => $labelStr !== '' ? $labelStr : ('Server ' . $i),
        'url' => $urlStr,
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate($_POST['csrf_token'] ?? null);

    $truncate = !empty($_POST['truncate']);

    if (empty($_FILES['playlist_file']) || !is_uploaded_file($_FILES['playlist_file']['tmp_name'])) {
        $errors[] = 'Please upload a playlist.json file.';
    } else {
        $raw = file_get_contents($_FILES['playlist_file']['tmp_name']);
        if ($raw === false) {
            $errors[] = 'Failed to read upload.';
        } else {
            $data = json_decode($raw, true);
            if (!is_array($data) || empty($data['Categories']) || !is_array($data['Categories'])) {
                $errors[] = 'Invalid JSON format. Expected {"Categories": [...] }.';
            }
        }
    }

    if (!$errors) {
        $pdo->beginTransaction();
        try {
            if ($truncate) {
                $pdo->exec('DELETE FROM media_items');
            }

            $stats = [
                'movie' => 0,
                'series' => 0,
                'live' => 0,
                'episodes' => 0,
                'streams' => 0,
            ];

            foreach ($data['Categories'] as $cat) {
                $mainCategory = (string)($cat['MainCategory'] ?? '');
                $type = detect_type($mainCategory);
                if (!$type) {
                    continue;
                }

                $entries = $cat['Entries'] ?? [];
                if (!is_array($entries)) {
                    continue;
                }

                foreach ($entries as $entry) {
                    if (!is_array($entry)) {
                        continue;
                    }

                    $titleIn = trim((string)($entry['Title'] ?? ''));
                    if ($titleIn === '') {
                        continue;
                    }

                    $pdo->prepare('INSERT INTO media_items (type, title, description, poster_url, backdrop_url, year, rating, country, sub_category, youtube_url, created_at, updated_at) VALUES (:type, :title, :d, :p, :b, :y, :r, :c, :s, :yt, NOW(), NOW())')
                        ->execute([
                            ':type' => $type,
                            ':title' => $titleIn,
                            ':d' => (string)($entry['Description'] ?? ''),
                            ':p' => (string)($entry['Poster'] ?? $entry['Thumbnail'] ?? ''),
                            ':b' => (string)($entry['Backdrop'] ?? ''),
                            ':y' => (string)($entry['Year'] ?? ''),
                            ':r' => (string)($entry['Rating'] ?? ''),
                            ':c' => (string)($entry['Country'] ?? ''),
                            ':s' => (string)($entry['SubCategory'] ?? ''),
                            ':yt' => (string)($entry['YouTube'] ?? $entry['Youtube'] ?? $entry['youtube'] ?? ''),
                        ]);

                    $mediaId = (int)$pdo->lastInsertId();
                    $stats[$type]++;

                    if ($type === 'series') {
                        $seasons = $entry['Seasons'] ?? [];
                        if (is_array($seasons)) {
                            foreach ($seasons as $season) {
                                if (!is_array($season)) {
                                    continue;
                                }
                                $seasonNumber = (int)($season['Season'] ?? 1);
                                $episodes = $season['Episodes'] ?? [];
                                if (!is_array($episodes)) {
                                    continue;
                                }

                                foreach ($episodes as $ep) {
                                    if (!is_array($ep)) {
                                        continue;
                                    }
                                    $episodeNumber = (int)($ep['Episode'] ?? 1);
                                    $epTitle = trim((string)($ep['Title'] ?? ('Episode ' . $episodeNumber)));

                                    $pdo->prepare('INSERT INTO media_episodes (media_id, season_number, episode_number, title, description, created_at) VALUES (:m, :s, :e, :t, :d, NOW())')
                                        ->execute([
                                            ':m' => $mediaId,
                                            ':s' => $seasonNumber,
                                            ':e' => $episodeNumber,
                                            ':t' => $epTitle,
                                            ':d' => (string)($ep['Description'] ?? ''),
                                        ]);

                                    $episodeId = (int)$pdo->lastInsertId();
                                    $stats['episodes']++;

                                    $servers = $ep['Servers'] ?? [];
                                    if (is_array($servers)) {
                                        $i = 1;
                                        foreach ($servers as $s) {
                                            $server = normalize_server_row($s, $i);
                                            if ($server['url'] === '') {
                                                $i++;
                                                continue;
                                            }
                                            $pdo->prepare('INSERT INTO media_episode_streams (episode_id, label, url, sort_order, created_at) VALUES (:e, :l, :u, :o, NOW())')
                                                ->execute([
                                                    ':e' => $episodeId,
                                                    ':l' => $server['label'],
                                                    ':u' => $server['url'],
                                                    ':o' => $i - 1,
                                                ]);
                                            $stats['streams']++;
                                            $i++;
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        $servers = $entry['Servers'] ?? [];
                        if (is_array($servers)) {
                            $i = 1;
                            foreach ($servers as $s) {
                                $server = normalize_server_row($s, $i);
                                if ($server['url'] === '') {
                                    $i++;
                                    continue;
                                }
                                $pdo->prepare('INSERT INTO media_streams (media_id, label, url, sort_order, created_at) VALUES (:m, :l, :u, :o, NOW())')
                                    ->execute([
                                        ':m' => $mediaId,
                                        ':l' => $server['label'],
                                        ':u' => $server['url'],
                                        ':o' => $i - 1,
                                    ]);
                                $stats['streams']++;
                                $i++;
                            }
                        }
                    }
                }
            }

            $pdo->commit();
            $success = 'Import completed.';
        } catch (Throwable $e) {
            $pdo->rollBack();
            $errors[] = $e->getMessage();
        }
    }
}

require_once __DIR__ . '/_header.php';

?>

<div class="grid">
    <section class="card span-8">
        <?php if ($success): ?>
            <div class="alert success"><?php echo e($success); ?></div>
        <?php endif; ?>

        <?php if ($errors): ?>
            <div class="alert danger">
                <ul style="margin:0;padding-left:18px;">
                    <?php foreach ($errors as $err): ?>
                        <li><?php echo e($err); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($stats): ?>
            <div class="alert success">
                <strong>Imported</strong>
                <div class="meta">Movies: <?php echo e((string)$stats['movie']); ?> · Series: <?php echo e((string)$stats['series']); ?> · Live: <?php echo e((string)$stats['live']); ?> · Episodes: <?php echo e((string)$stats['episodes']); ?> · Streams: <?php echo e((string)$stats['streams']); ?></div>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" class="form">
            <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">

            <div class="field">
                <label>playlist.json file</label>
                <input type="file" name="playlist_file" accept="application/json,.json" required>
            </div>

            <div class="field">
                <label><input type="checkbox" name="truncate" value="1"> Delete existing catalog before import</label>
            </div>

            <div class="actions">
                <button class="btn" type="submit">Import</button>
                <a class="btn secondary" href="/admin/data.php">Open Data Management</a>
            </div>
        </form>
    </section>

    <aside class="card span-4">
        <h3>What this does</h3>
        <div class="meta">
            <p>• Imports Movies / TV Series / Live TV into MySQL.</p>
            <p>• TV Series seasons/episodes are stored with multiple servers per episode.</p>
            <p>• After import, the public site can load content directly from <code>/api/playlist.php</code>.</p>
        </div>
    </aside>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>
