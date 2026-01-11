<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

if (app_is_installed()) {
    ?><!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>CineCraze Installer</title>
        <link rel="stylesheet" href="/assets/app.css">
    </head>
    <body>
        <main class="container">
            <div class="card">
                <h1>Already installed</h1>
                <p>CineCraze is already installed.</p>
                <div class="actions">
                    <a class="btn" href="/">Go to site</a>
                    <a class="btn btn-secondary" href="/admin/">Go to admin</a>
                </div>
            </div>
        </main>
    </body>
    </html><?php
    exit;
}

$errors = [];
$success = null;

function install_run_schema(PDO $pdo, string $schemaPath): void
{
    $sql = file_get_contents($schemaPath);
    if ($sql === false) {
        throw new RuntimeException('Failed to read schema file.');
    }

    $sql = preg_replace('/^\s*--.*$/m', '', $sql);
    $statements = array_filter(array_map('trim', explode(";", (string)$sql)));

    foreach ($statements as $stmtSql) {
        if ($stmtSql === '') {
            continue;
        }
        $pdo->exec($stmtSql);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate($_POST['csrf_token'] ?? null);

    $dbHost = trim((string)($_POST['db_host'] ?? 'localhost'));
    $dbPort = (int)($_POST['db_port'] ?? 3306);
    $dbName = trim((string)($_POST['db_name'] ?? ''));
    $dbUser = trim((string)($_POST['db_user'] ?? ''));
    $dbPass = (string)($_POST['db_pass'] ?? '');

    $baseUrl = trim((string)($_POST['base_url'] ?? ''));
    $tmdbKey = trim((string)($_POST['tmdb_api_key'] ?? ''));

    $adminEmail = trim((string)($_POST['admin_email'] ?? ''));
    $adminPass = (string)($_POST['admin_password'] ?? '');

    if ($dbName === '') {
        $errors[] = 'Database name is required.';
    }
    if ($dbUser === '') {
        $errors[] = 'Database user is required.';
    }
    if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid admin email is required.';
    }
    if (strlen($adminPass) < 8) {
        $errors[] = 'Admin password must be at least 8 characters.';
    }

    if (!$errors) {
        try {
            $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
            $pdo = new PDO($dsn, $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            install_run_schema($pdo, __DIR__ . '/schema.sql');

            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email');
            $stmt->execute([':email' => strtolower($adminEmail)]);
            $existing = $stmt->fetch();

            if (!$existing) {
                $hash = password_hash_str($adminPass);
                $pdo->prepare('INSERT INTO users (email, password_hash, role, created_at) VALUES (:email, :hash, :role, NOW())')
                    ->execute([
                        ':email' => strtolower($adminEmail),
                        ':hash' => $hash,
                        ':role' => 'admin',
                    ]);
            }

            $config = [
                'db' => [
                    'host' => $dbHost,
                    'port' => $dbPort,
                    'name' => $dbName,
                    'user' => $dbUser,
                    'pass' => $dbPass,
                    'charset' => 'utf8mb4',
                ],
                'app' => [
                    'base_url' => $baseUrl,
                    'name' => 'CineCraze',
                    'tmdb_api_key' => $tmdbKey,
                ],
            ];

            $configPhp = "<?php\n\ndeclare(strict_types=1);\n\nreturn " . var_export($config, true) . ";\n";

            if (@file_put_contents(CONFIG_FILE, $configPhp) === false) {
                throw new RuntimeException('Could not write config/config.php. Please make the config directory writable.');
            }

            if (@file_put_contents(INSTALLED_FILE, (string)time()) === false) {
                throw new RuntimeException('Could not write config/.installed.');
            }

            $success = 'Installation complete. You can now log in to the admin panel.';
        } catch (Throwable $e) {
            $errors[] = $e->getMessage();
        }
    }
}

?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CineCraze Installer</title>
    <link rel="stylesheet" href="/assets/app.css">
</head>
<body>
    <main class="container">
        <div class="card">
            <h1>CineCraze Installation</h1>
            <p class="muted">Works on free & paid hosting: no Composer, no frameworks. You just need PHP + MySQL.</p>

            <?php if ($success): ?>
                <div class="alert success"><?php echo e($success); ?></div>
                <div class="actions">
                    <a class="btn" href="/admin/login.php">Admin Login</a>
                    <a class="btn btn-secondary" href="/">Open Site</a>
                </div>
            <?php else: ?>

                <?php if ($errors): ?>
                    <div class="alert danger">
                        <strong>Please fix the following:</strong>
                        <ul>
                            <?php foreach ($errors as $err): ?>
                                <li><?php echo e($err); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post" class="form-grid">
                    <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">

                    <h2>Database</h2>

                    <label>
                        <span>Host</span>
                        <input name="db_host" value="<?php echo e((string)($_POST['db_host'] ?? 'localhost')); ?>" required>
                    </label>

                    <label>
                        <span>Port</span>
                        <input name="db_port" value="<?php echo e((string)($_POST['db_port'] ?? '3306')); ?>" required>
                    </label>

                    <label>
                        <span>Database name</span>
                        <input name="db_name" value="<?php echo e((string)($_POST['db_name'] ?? '')); ?>" required>
                    </label>

                    <label>
                        <span>User</span>
                        <input name="db_user" value="<?php echo e((string)($_POST['db_user'] ?? '')); ?>" required>
                    </label>

                    <label>
                        <span>Password</span>
                        <input name="db_pass" type="password" value="<?php echo e((string)($_POST['db_pass'] ?? '')); ?>">
                    </label>

                    <h2>Site</h2>

                    <label>
                        <span>Base URL (optional)</span>
                        <input name="base_url" value="<?php echo e((string)($_POST['base_url'] ?? '')); ?>" placeholder="https://example.com">
                    </label>

                    <label>
                        <span>TMDB API Key (optional)</span>
                        <input name="tmdb_api_key" value="<?php echo e((string)($_POST['tmdb_api_key'] ?? '')); ?>">
                    </label>

                    <h2>Admin account</h2>

                    <label>
                        <span>Admin email</span>
                        <input name="admin_email" value="<?php echo e((string)($_POST['admin_email'] ?? '')); ?>" required>
                    </label>

                    <label>
                        <span>Admin password</span>
                        <input name="admin_password" type="password" required>
                    </label>

                    <div class="actions">
                        <button class="btn" type="submit">Install</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
