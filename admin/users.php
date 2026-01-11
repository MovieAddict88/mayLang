<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

auth_require_admin();
$user = auth_user();

$active = 'users';
$title = 'Users';
$subtitle = 'All registered users (created via /auth/register.php).';

require_once __DIR__ . '/_header.php';

$pdo = db();

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate($_POST['csrf_token'] ?? null);

    $action = (string)($_POST['action'] ?? '');
    $id = (int)($_POST['id'] ?? 0);
    $role = (string)($_POST['role'] ?? 'user');

    if ($action === 'set_role' && $id > 0 && in_array($role, ['user', 'admin'], true)) {
        $pdo->prepare('UPDATE users SET role = :role WHERE id = :id')->execute([
            ':role' => $role,
            ':id' => $id,
        ]);
        $success = 'Role updated.';
    }
}

$users = $pdo->query('SELECT id, email, role, created_at, last_login_at, last_login_ip FROM users ORDER BY created_at DESC LIMIT 500')->fetchAll();

?>

<div class="grid">
    <section class="card span-12">
        <?php if ($success): ?>
            <div class="alert success" style="margin-bottom:12px;"><?php echo e($success); ?></div>
        <?php endif; ?>

        <table class="table">
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Joined</th>
                    <th>Last login</th>
                    <th>Last IP</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?php echo e((string)$u['email']); ?></td>
                        <td><?php echo e((string)$u['role']); ?></td>
                        <td><?php echo e((string)$u['created_at']); ?></td>
                        <td><?php echo e((string)($u['last_login_at'] ?? '—')); ?></td>
                        <td><?php echo e((string)($u['last_login_ip'] ?? '—')); ?></td>
                        <td style="width:1%;white-space:nowrap;">
                            <form method="post" style="display:inline-block;margin:0;">
                                <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
                                <input type="hidden" name="action" value="set_role">
                                <input type="hidden" name="id" value="<?php echo e((string)$u['id']); ?>">
                                <?php if ((string)$u['role'] === 'admin'): ?>
                                    <input type="hidden" name="role" value="user">
                                    <button class="btn secondary" type="submit">Make User</button>
                                <?php else: ?>
                                    <input type="hidden" name="role" value="admin">
                                    <button class="btn secondary" type="submit">Make Admin</button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>
