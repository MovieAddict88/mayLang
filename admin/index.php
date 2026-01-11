<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

auth_require_admin();
$user = auth_user();

$active = 'dashboard';
$title = 'Dashboard';
$subtitle = 'Monitor users and content across devices (smartwatch → smart TV).';

require_once __DIR__ . '/_header.php';

$pdo = db();

$counts = [
    'movies' => 0,
    'series' => 0,
    'live' => 0,
    'users' => 0,
    'logins_24h' => 0,
    'new_users_24h' => 0,
];

$counts['movies'] = (int)$pdo->query("SELECT COUNT(*) AS c FROM media_items WHERE type='movie'")->fetch()['c'];
$counts['series'] = (int)$pdo->query("SELECT COUNT(*) AS c FROM media_items WHERE type='series'")->fetch()['c'];
$counts['live'] = (int)$pdo->query("SELECT COUNT(*) AS c FROM media_items WHERE type='live'")->fetch()['c'];
$counts['users'] = (int)$pdo->query("SELECT COUNT(*) AS c FROM users")->fetch()['c'];
$counts['new_users_24h'] = (int)$pdo->query("SELECT COUNT(*) AS c FROM users WHERE created_at >= (NOW() - INTERVAL 1 DAY)")->fetch()['c'];
$counts['logins_24h'] = (int)$pdo->query("SELECT COUNT(*) AS c FROM users WHERE last_login_at IS NOT NULL AND last_login_at >= (NOW() - INTERVAL 1 DAY)")->fetch()['c'];

$recentUsersStmt = $pdo->query("SELECT id, email, role, created_at, last_login_at, last_login_ip FROM users ORDER BY created_at DESC LIMIT 12");
$recentUsers = $recentUsersStmt->fetchAll();

?>

<div class="grid">
    <section class="card span-4">
        <h3>Registered Users</h3>
        <div class="metric"><?php echo e((string)$counts['users']); ?></div>
        <div class="meta"><?php echo e((string)$counts['new_users_24h']); ?> joined in the last 24h</div>
    </section>

    <section class="card span-4">
        <h3>Logins (24h)</h3>
        <div class="metric"><?php echo e((string)$counts['logins_24h']); ?></div>
        <div class="meta">Users who signed in recently</div>
    </section>

    <section class="card span-4">
        <h3>Catalog Items</h3>
        <div class="metric"><?php echo e((string)($counts['movies'] + $counts['series'] + $counts['live'])); ?></div>
        <div class="meta">Movies: <?php echo e((string)$counts['movies']); ?> · Series: <?php echo e((string)$counts['series']); ?> · Live: <?php echo e((string)$counts['live']); ?></div>
    </section>

    <section class="card span-12">
        <h3>Latest Users</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Joined</th>
                    <th>Last login</th>
                    <th>Last IP</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentUsers as $u): ?>
                    <tr>
                        <td><?php echo e((string)$u['email']); ?></td>
                        <td><?php echo e((string)$u['role']); ?></td>
                        <td><?php echo e((string)$u['created_at']); ?></td>
                        <td><?php echo e((string)($u['last_login_at'] ?? '—')); ?></td>
                        <td><?php echo e((string)($u['last_login_ip'] ?? '—')); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>
