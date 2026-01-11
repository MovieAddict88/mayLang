<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

auth_require_admin();
$user = auth_user();

$active = 'manual';
$title = 'Manual';
$subtitle = 'Add or edit catalog entries (database-driven).';

$pdo = db();

$id = (int)($_GET['id'] ?? 0);
$item = null;
$streamsText = '';

if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM media_items WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $item = $stmt->fetch();

    if ($item) {
        $s = $pdo->prepare('SELECT label, url FROM media_streams WHERE media_id = :id ORDER BY sort_order ASC, id ASC');
        $s->execute([':id' => $id]);
        $lines = [];
        foreach ($s->fetchAll() as $row) {
            $label = trim((string)($row['label'] ?? ''));
            $url = trim((string)($row['url'] ?? ''));
            if ($label !== '') {
                $lines[] = $label . ' | ' . $url;
            } else {
                $lines[] = $url;
            }
        }
        $streamsText = implode("\n", $lines);
    }
}

$errors = [];
$success = null;

function parse_streams(string $input): array
{
    $out = [];
    $lines = preg_split('/\r\n|\r|\n/', trim($input));
    if (!$lines) {
        return [];
    }

    $sort = 0;
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }

        $label = null;
        $url = $line;

        if (strpos($line, '|') !== false) {
            [$a, $b] = array_map('trim', explode('|', $line, 2));
            $label = $a;
            $url = $b;
        }

        $out[] = [
            'label' => $label !== '' ? $label : null,
            'url' => $url,
            'sort_order' => $sort,
        ];
        $sort++;
    }

    return $out;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate($_POST['csrf_token'] ?? null);

    $type = (string)($_POST['type'] ?? 'movie');
    if (!in_array($type, ['movie', 'series', 'live'], true)) {
        $type = 'movie';
    }

    $titleIn = trim((string)($_POST['title'] ?? ''));
    $descIn = trim((string)($_POST['description'] ?? ''));
    $posterIn = trim((string)($_POST['poster_url'] ?? ''));
    $backdropIn = trim((string)($_POST['backdrop_url'] ?? ''));
    $yearIn = trim((string)($_POST['year'] ?? ''));
    $ratingIn = trim((string)($_POST['rating'] ?? ''));
    $countryIn = trim((string)($_POST['country'] ?? ''));
    $subCatIn = trim((string)($_POST['sub_category'] ?? ''));
    $youtubeIn = trim((string)($_POST['youtube_url'] ?? ''));

    if ($titleIn === '') {
        $errors[] = 'Title is required.';
    }

    $streamsIn = (string)($_POST['streams'] ?? '');
    $streams = parse_streams($streamsIn);

    if ($type !== 'series' && count($streams) === 0) {
        $errors[] = 'At least one stream URL is recommended for movies/live.';
    }

    if (!$errors) {
        if ($id > 0) {
            $pdo->prepare('UPDATE media_items SET type=:type, title=:title, description=:d, poster_url=:p, backdrop_url=:b, year=:y, rating=:r, country=:c, sub_category=:s, youtube_url=:yt, updated_at=NOW() WHERE id=:id')
                ->execute([
                    ':type' => $type,
                    ':title' => $titleIn,
                    ':d' => $descIn !== '' ? $descIn : null,
                    ':p' => $posterIn !== '' ? $posterIn : null,
                    ':b' => $backdropIn !== '' ? $backdropIn : null,
                    ':y' => $yearIn !== '' ? $yearIn : null,
                    ':r' => $ratingIn !== '' ? $ratingIn : null,
                    ':c' => $countryIn !== '' ? $countryIn : null,
                    ':s' => $subCatIn !== '' ? $subCatIn : null,
                    ':yt' => $youtubeIn !== '' ? $youtubeIn : null,
                    ':id' => $id,
                ]);

            $pdo->prepare('DELETE FROM media_streams WHERE media_id = :id')->execute([':id' => $id]);

            foreach ($streams as $stream) {
                $pdo->prepare('INSERT INTO media_streams (media_id, label, url, sort_order, created_at) VALUES (:m, :l, :u, :s, NOW())')
                    ->execute([
                        ':m' => $id,
                        ':l' => $stream['label'],
                        ':u' => $stream['url'],
                        ':s' => (int)$stream['sort_order'],
                    ]);
            }

            $success = 'Saved.';
        } else {
            $pdo->prepare('INSERT INTO media_items (type, title, description, poster_url, backdrop_url, year, rating, country, sub_category, youtube_url, created_at, updated_at) VALUES (:type, :title, :d, :p, :b, :y, :r, :c, :s, :yt, NOW(), NOW())')
                ->execute([
                    ':type' => $type,
                    ':title' => $titleIn,
                    ':d' => $descIn !== '' ? $descIn : null,
                    ':p' => $posterIn !== '' ? $posterIn : null,
                    ':b' => $backdropIn !== '' ? $backdropIn : null,
                    ':y' => $yearIn !== '' ? $yearIn : null,
                    ':r' => $ratingIn !== '' ? $ratingIn : null,
                    ':c' => $countryIn !== '' ? $countryIn : null,
                    ':s' => $subCatIn !== '' ? $subCatIn : null,
                    ':yt' => $youtubeIn !== '' ? $youtubeIn : null,
                ]);

            $newId = (int)$pdo->lastInsertId();
            foreach ($streams as $stream) {
                $pdo->prepare('INSERT INTO media_streams (media_id, label, url, sort_order, created_at) VALUES (:m, :l, :u, :s, NOW())')
                    ->execute([
                        ':m' => $newId,
                        ':l' => $stream['label'],
                        ':u' => $stream['url'],
                        ':s' => (int)$stream['sort_order'],
                    ]);
            }

            redirect('/admin/manual.php?id=' . $newId);
        }
    }
}

$item = $item ?: [
    'type' => 'movie',
    'title' => '',
    'description' => '',
    'poster_url' => '',
    'backdrop_url' => '',
    'year' => '',
    'rating' => '',
    'country' => '',
    'sub_category' => '',
    'youtube_url' => '',
];

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

        <form method="post" class="form">
            <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">

            <div class="field">
                <label>Type</label>
                <select name="type">
                    <option value="movie" <?php echo (($item['type'] ?? '') === 'movie') ? 'selected' : ''; ?>>Movie</option>
                    <option value="series" <?php echo (($item['type'] ?? '') === 'series') ? 'selected' : ''; ?>>TV Series</option>
                    <option value="live" <?php echo (($item['type'] ?? '') === 'live') ? 'selected' : ''; ?>>Live TV</option>
                </select>
            </div>

            <div class="field">
                <label>Title</label>
                <input name="title" value="<?php echo e((string)($item['title'] ?? '')); ?>" required>
            </div>

            <div class="field">
                <label>Description</label>
                <textarea name="description"><?php echo e((string)($item['description'] ?? '')); ?></textarea>
            </div>

            <div class="grid" style="grid-template-columns:repeat(12,minmax(0,1fr));gap:10px;">
                <div class="field" style="grid-column:span 6;">
                    <label>Poster URL</label>
                    <input name="poster_url" value="<?php echo e((string)($item['poster_url'] ?? '')); ?>">
                </div>
                <div class="field" style="grid-column:span 6;">
                    <label>Backdrop URL</label>
                    <input name="backdrop_url" value="<?php echo e((string)($item['backdrop_url'] ?? '')); ?>">
                </div>
                <div class="field" style="grid-column:span 3;">
                    <label>Year</label>
                    <input name="year" value="<?php echo e((string)($item['year'] ?? '')); ?>">
                </div>
                <div class="field" style="grid-column:span 3;">
                    <label>Rating</label>
                    <input name="rating" value="<?php echo e((string)($item['rating'] ?? '')); ?>" placeholder="0-10">
                </div>
                <div class="field" style="grid-column:span 3;">
                    <label>Country</label>
                    <input name="country" value="<?php echo e((string)($item['country'] ?? '')); ?>">
                </div>
                <div class="field" style="grid-column:span 3;">
                    <label>SubCategory</label>
                    <input name="sub_category" value="<?php echo e((string)($item['sub_category'] ?? '')); ?>" placeholder="e.g. Action">
                </div>
            </div>

            <div class="field">
                <label>YouTube URL (optional)</label>
                <input name="youtube_url" value="<?php echo e((string)($item['youtube_url'] ?? '')); ?>" placeholder="https://youtube.com/watch?v=...">
            </div>

            <div class="field">
                <label>Streams (movies/live)</label>
                <textarea name="streams" placeholder="Server 1 | https://...\nServer 2 | https://...\nhttps://... (label optional)"><?php echo e((string)($streamsText !== '' ? $streamsText : (string)($_POST['streams'] ?? ''))); ?></textarea>
                <div class="meta">For TV series episodes, use Bulk import (playlist JSON) to bring full Seasons/Episodes into the database.</div>
            </div>

            <div class="actions">
                <button class="btn" type="submit">Save</button>
                <a class="btn secondary" href="/admin/data.php">Open Data Management</a>
            </div>
        </form>
    </section>

    <aside class="card span-4">
        <h3>Tips</h3>
        <div class="meta">
            <p><strong>Works everywhere:</strong> the admin UI is responsive down to very small screens, and the public site fetches content from the DB API.</p>
            <p><strong>Fast migration:</strong> use Bulk to import your existing playlist.json (monolithic or segmented merged file).</p>
            <p><strong>Series:</strong> store episodes with multiple servers per episode via Bulk import.</p>
        </div>
    </aside>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>
