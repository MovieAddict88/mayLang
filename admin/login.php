<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

if (!app_is_installed()) {
    redirect('/install/');
}

$user = auth_user();
if ($user && ($user['role'] ?? '') === 'admin') {
    redirect('/admin/');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate($_POST['csrf_token'] ?? null);

    $email = (string)($_POST['email'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    if (!auth_login($email, $password)) {
        $errors[] = 'Invalid email or password.';
    } else {
        $user = auth_user();
        if (!$user || ($user['role'] ?? '') !== 'admin') {
            auth_logout();
            $errors[] = 'This account does not have admin access.';
        } else {
            redirect('/admin/');
        }
    }
}

?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login - CineCraze</title>
    <link rel="stylesheet" href="/assets/app.css">
</head>
<body>
    <main class="container">
        <div class="card">
            <h1>Admin Login</h1>
            <p class="muted">Sign in with your admin account.</p>

            <?php if ($errors): ?>
                <div class="alert danger">
                    <ul>
                        <?php foreach ($errors as $err): ?>
                            <li><?php echo e($err); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" class="form-grid">
                <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">

                <label style="grid-column: 1 / -1;">
                    <span>Email</span>
                    <input type="email" name="email" required autocomplete="email" value="<?php echo e((string)($_POST['email'] ?? '')); ?>">
                </label>

                <label style="grid-column: 1 / -1;">
                    <span>Password</span>
                    <input type="password" name="password" required autocomplete="current-password">
                </label>

                <div class="actions" style="grid-column: 1 / -1;">
                    <button class="btn" type="submit">Login</button>
                    <a class="btn btn-secondary" href="/">Back to site</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
