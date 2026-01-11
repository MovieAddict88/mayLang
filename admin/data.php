<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

auth_require_admin();
$user = auth_user();

$active = 'data';
$title = 'Data Management';
$subtitle = 'Browse, delete, and export your catalog stored in MySQL.';

$pdo = db();

$type = (string)($_GET['type'] ?? 'all');
$allowedTypes = ['all', 'movie', 'series', 'live'];
if (!in_array($type, $allowedTypes, true)) {
    $type = 'all';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate($_POST['csrf_token'] ?? null);

    $action = (string)($_POST['action'] ?? '');
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare('DELETE FROM media_items WHERE id = :id')->execute([':id' => $id]);
        }
        redirect('/admin/data.php?type=' . urlencode($type));
    }

    if ($action === 'export') {
        require_once __DIR__ . '/../api/playlist.php';
        exit;
    }
}

$sql = 'SELECT id, type, title, year, rating, country, updated_at FROM media_items';
$params = [];
if ($type !== 'all') {
    $sql .= ' WHERE type = :type';
    $params[':type'] = $type;
}
$sql .= ' ORDER BY updated_at DESC, id DESC LIMIT 200';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();

require_once __DIR__ . '/_header.php';

?>

<div class="grid">
    <section class="card span-12">
        <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;justify-content:space-between;">
            <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
                <a class="btn secondary" href="/admin/data.php?type=all">All</a>
                <a class="btn secondary" href="/admin/data.php?type=movie">Movies</a>
                <a class="btn secondary" href="/admin/data.php?type=series">TV Series</a>
                <a class="btn secondary" href="/admin/data.php?type=live">Live TV</a>
            </div>
            <div class="actions" style="margin:0;">
                <form method="post" style="margin:0;">
                    <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
                    <input type="hidden" name="action" value="export">
                    <button class="btn" type="submit">Export playlist JSON</button>
                </form>
            </div>
        </div>

        <table class="table" style="margin-top:12px;">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Year</th>
                    <th>Rating</th>
                    <th>Country</th>
                    <th>Updated</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo e((string)$item['title']); ?></td>
                        <td><?php echo e((string)$item['type']); ?></td>
                        <td><?php echo e((string)($item['year'] ?? '')); ?></td>
                        <td><?php echo e((string)($item['rating'] ?? '')); ?></td>
                        <td><?php echo e((string)($item['country'] ?? '')); ?></td>
                        <td><?php echo e((string)$item['updated_at']); ?></td>
                        <td style="width:1%;white-space:nowrap;">
                            <a class="btn secondary" href="/admin/manual.php?id=<?php echo e((string)$item['id']); ?>">Edit</a>
                            <form method="post" style="display:inline-block;margin:0;">
                                <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo e((string)$item['id']); ?>">
                                <button class="btn secondary" type="submit" onclick="return confirm('Delete this item?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if (!$items): ?>
            <p class="meta" style="margin-top:10px;">No items yet. Use Manual or Bulk import to add content.</p>
        <?php endif; ?>
    </section>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>
