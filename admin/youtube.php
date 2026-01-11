<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

auth_require_admin();
$user = auth_user();

$active = 'youtube';
$title = 'YouTube';
$subtitle = 'Attach trailers (YouTube URL) to any media item.';

require_once __DIR__ . '/_header.php';

$pdo = db();

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate($_POST['csrf_token'] ?? null);

    $id = (int)($_POST['media_id'] ?? 0);
    $youtube = trim((string)($_POST['youtube_url'] ?? ''));

    if ($id <= 0) {
        $errors[] = 'Select an item.';
    }

    if (!$errors) {
        $pdo->prepare('UPDATE media_items SET youtube_url = :yt, updated_at = NOW() WHERE id = :id')
            ->execute([
                ':yt' => $youtube !== '' ? $youtube : null,
                ':id' => $id,
            ]);
        $success = 'Saved.';
    }
}

$items = $pdo->query('SELECT id, type, title, youtube_url FROM media_items ORDER BY updated_at DESC, id DESC LIMIT 300')->fetchAll();

?>

<div class="grid">
    <section class="card span-6">
        <?php if ($success): ?>
            <div class="alert success" style="margin-bottom:12px;"><?php echo e($success); ?></div>
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

            <div class="field">
                <label>Media item</label>
                <select name="media_id" required>
                    <option value="">Select…</option>
                    <?php foreach ($items as $it): ?>
                        <option value="<?php echo e((string)$it['id']); ?>">
                            [<?php echo e((string)$it['type']); ?>] <?php echo e((string)$it['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field">
                <label>YouTube URL</label>
                <input name="youtube_url" placeholder="https://www.youtube.com/watch?v=...">
            </div>

            <div class="actions">
                <button class="btn" type="submit">Save</button>
                <a class="btn secondary" href="/admin/data.php">Open Data Management</a>
            </div>
        </form>
    </section>

    <section class="card span-6">
        <h3>Quick overview</h3>
        <table class="table" style="margin-top:12px;">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>YouTube</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $it): ?>
                    <?php if (empty($it['youtube_url'])) continue; ?>
                    <tr>
                        <td><?php echo e((string)$it['title']); ?></td>
                        <td style="max-width:420px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            <?php echo e((string)$it['youtube_url']); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="meta" style="margin-top:10px;">The public site can use this field to show trailers (optional).</div>
    </section>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>
