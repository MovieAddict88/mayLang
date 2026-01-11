<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

if (!app_is_installed()) {
    redirect('/install/');
}

$user = auth_user();
if ($user) {
    redirect('/');
}

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate($_POST['csrf_token'] ?? null);

    $email = (string)($_POST['email'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    try {
        auth_register($email, $password);
        auth_login($email, $password);
        $success = 'Account created.';
        redirect('/');
    } catch (Throwable $e) {
        $errors[] = $e->getMessage();
    }
}

?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register - CineCraze</title>
    <link rel="stylesheet" href="/assets/app.css">
</head>
<body>
    <main class="container">
        <div class="card">
            <h1>Create account</h1>
            <p class="muted">Register to be visible in the admin dashboard.</p>

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
                    <span>Password (min 8 chars)</span>
                    <input type="password" name="password" required autocomplete="new-password">
                </label>

                <div class="actions" style="grid-column: 1 / -1;">
                    <button class="btn" type="submit">Register</button>
                    <a class="btn btn-secondary" href="/auth/login.php">I already have an account</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
