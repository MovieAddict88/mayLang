<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/tmdb.php';

auth_require_admin();
$user = auth_user();

$active = 'tmdb';
$title = 'TMDB Generator';
$subtitle = 'Generate entries from TMDB into the database.';

$pdo = db();

$errors = [];
$success = null;
$result = null;

function tmdb_to_media(array $tmdb, string $mode): array
{
    $isMovie = $mode === 'movie';
    $title = (string)($isMovie ? ($tmdb['title'] ?? '') : ($tmdb['name'] ?? ''));
    $overview = (string)($tmdb['overview'] ?? '');
    $posterPath = (string)($tmdb['poster_path'] ?? '');
    $backdropPath = (string)($tmdb['backdrop_path'] ?? '');

    $releaseDate = (string)($isMovie ? ($tmdb['release_date'] ?? '') : ($tmdb['first_air_date'] ?? ''));
    $year = $releaseDate !== '' ? substr($releaseDate, 0, 4) : '';

    $country = '';
    if ($isMovie && !empty($tmdb['production_countries'][0]['name'])) {
        $country = (string)$tmdb['production_countries'][0]['name'];
    }

    $genres = [];
    if (!empty($tmdb['genres']) && is_array($tmdb['genres'])) {
        foreach ($tmdb['genres'] as $g) {
            if (isset($g['name'])) {
                $genres[] = (string)$g['name'];
            }
        }
    }

    $poster = $posterPath !== '' ? ('https://image.tmdb.org/t/p/w500' . $posterPath) : '';
    $backdrop = $backdropPath !== '' ? ('https://image.tmdb.org/t/p/w780' . $backdropPath) : '';

    return [
        'type' => $isMovie ? 'movie' : 'series',
        'tmdb_id' => (int)($tmdb['id'] ?? 0),
        'title' => $title,
        'description' => $overview,
        'poster_url' => $poster,
        'backdrop_url' => $backdrop,
        'year' => $year,
        'rating' => isset($tmdb['vote_average']) ? (string)$tmdb['vote_average'] : '',
        'country' => $country,
        'sub_category' => $genres ? $genres[0] : '',
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate($_POST['csrf_token'] ?? null);

    $action = (string)($_POST['action'] ?? 'fetch');

    if ($action === 'fetch') {
        $mode = (string)($_POST['mode'] ?? 'movie');
        if (!in_array($mode, ['movie', 'tv'], true)) {
            $mode = 'movie';
        }
        $tmdbId = (int)($_POST['tmdb_id'] ?? 0);

        if ($tmdbId <= 0) {
            $errors[] = 'TMDB ID is required.';
        } else {
            try {
                $path = $mode === 'movie' ? ('/movie/' . $tmdbId) : ('/tv/' . $tmdbId);
                $result = tmdb_get($path);
            } catch (Throwable $e) {
                $errors[] = $e->getMessage();
            }
        }
    }

    if ($action === 'import') {
        $type = (string)($_POST['type'] ?? 'movie');
        if (!in_array($type, ['movie', 'series'], true)) {
            $type = 'movie';
        }

        $payload = [
            'type' => $type,
            'tmdb_id' => (int)($_POST['tmdb_id'] ?? 0),
            'title' => trim((string)($_POST['title'] ?? '')),
            'description' => trim((string)($_POST['description'] ?? '')),
            'poster_url' => trim((string)($_POST['poster_url'] ?? '')),
            'backdrop_url' => trim((string)($_POST['backdrop_url'] ?? '')),
            'year' => trim((string)($_POST['year'] ?? '')),
            'rating' => trim((string)($_POST['rating'] ?? '')),
            'country' => trim((string)($_POST['country'] ?? '')),
            'sub_category' => trim((string)($_POST['sub_category'] ?? '')),
        ];

        if ($payload['title'] === '') {
            $errors[] = 'Title is required.';
        }

        if (!$errors) {
            $pdo->prepare('INSERT INTO media_items (type, title, description, poster_url, backdrop_url, year, rating, country, sub_category, tmdb_id, created_at, updated_at) VALUES (:type, :title, :d, :p, :b, :y, :r, :c, :s, :tmdb, NOW(), NOW())')
                ->execute([
                    ':type' => $payload['type'],
                    ':title' => $payload['title'],
                    ':d' => $payload['description'] !== '' ? $payload['description'] : null,
                    ':p' => $payload['poster_url'] !== '' ? $payload['poster_url'] : null,
                    ':b' => $payload['backdrop_url'] !== '' ? $payload['backdrop_url'] : null,
                    ':y' => $payload['year'] !== '' ? $payload['year'] : null,
                    ':r' => $payload['rating'] !== '' ? $payload['rating'] : null,
                    ':c' => $payload['country'] !== '' ? $payload['country'] : null,
                    ':s' => $payload['sub_category'] !== '' ? $payload['sub_category'] : null,
                    ':tmdb' => $payload['tmdb_id'] ?: null,
                ]);

            $newId = (int)$pdo->lastInsertId();
            $success = 'Imported into database. Add streams in Manual.';
            redirect('/admin/manual.php?id=' . $newId);
        }
    }
}

$hasKey = tmdb_api_key() !== '';

require_once __DIR__ . '/_header.php';

?>

<div class="grid">
    <section class="card span-6">
        <?php if (!$hasKey): ?>
            <div class="alert danger" style="margin-bottom:12px;">TMDB key is missing. Set <strong>TMDB API Key</strong> during install or in <code>config/config.php</code>.</div>
        <?php endif; ?>

        <?php if ($errors): ?>
            <div class="alert danger" style="margin-bottom:12px;">
                <ul style="margin:0;padding-left:18px;">
                    <?php foreach ($errors as $err): ?>
                        <li><?php echo e($err); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" class="form">
            <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
            <input type="hidden" name="action" value="fetch">

            <div class="field">
                <label>Type</label>
                <select name="mode">
                    <option value="movie">Movie</option>
                    <option value="tv">TV</option>
                </select>
            </div>

            <div class="field">
                <label>TMDB ID</label>
                <input name="tmdb_id" inputmode="numeric" placeholder="e.g. 550" required>
            </div>

            <div class="actions">
                <button class="btn" type="submit" <?php echo !$hasKey ? 'disabled' : ''; ?>>Fetch</button>
                <a class="btn secondary" href="https://www.themoviedb.org" target="_blank" rel="noreferrer">Open TMDB</a>
            </div>
        </form>
    </section>

    <section class="card span-6">
        <h3>Result</h3>
        <div class="meta">After fetching, you can import the entry. Streams/episodes should be added via Manual or Bulk.</div>

        <?php if ($result): ?>
            <?php
                $mode = (string)($_POST['mode'] ?? 'movie');
                $media = tmdb_to_media($result, $mode === 'movie' ? 'movie' : 'tv');
            ?>

            <form method="post" class="form" style="margin-top:12px;">
                <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
                <input type="hidden" name="action" value="import">

                <input type="hidden" name="type" value="<?php echo e($media['type']); ?>">
                <input type="hidden" name="tmdb_id" value="<?php echo e((string)$media['tmdb_id']); ?>">

                <div class="field">
                    <label>Title</label>
                    <input name="title" value="<?php echo e($media['title']); ?>" required>
                </div>

                <div class="field">
                    <label>Description</label>
                    <textarea name="description"><?php echo e($media['description']); ?></textarea>
                </div>

                <div class="field">
                    <label>Poster URL</label>
                    <input name="poster_url" value="<?php echo e($media['poster_url']); ?>">
                </div>

                <div class="field">
                    <label>Backdrop URL</label>
                    <input name="backdrop_url" value="<?php echo e($media['backdrop_url']); ?>">
                </div>

                <div class="grid" style="grid-template-columns:repeat(12,minmax(0,1fr));gap:10px;">
                    <div class="field" style="grid-column:span 3;">
                        <label>Year</label>
                        <input name="year" value="<?php echo e($media['year']); ?>">
                    </div>
                    <div class="field" style="grid-column:span 3;">
                        <label>Rating</label>
                        <input name="rating" value="<?php echo e($media['rating']); ?>">
                    </div>
                    <div class="field" style="grid-column:span 3;">
                        <label>Country</label>
                        <input name="country" value="<?php echo e($media['country']); ?>">
                    </div>
                    <div class="field" style="grid-column:span 3;">
                        <label>SubCategory</label>
                        <input name="sub_category" value="<?php echo e($media['sub_category']); ?>">
                    </div>
                </div>

                <div class="actions">
                    <button class="btn" type="submit">Import to DB</button>
                </div>
            </form>
        <?php else: ?>
            <div class="meta" style="margin-top:10px;">No result yet.</div>
        <?php endif; ?>
    </section>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>
