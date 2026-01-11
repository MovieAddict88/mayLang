<?php

declare(strict_types=1);

$active = $active ?? 'dashboard';
$title = $title ?? 'Admin';
$subtitle = $subtitle ?? '';
$user = $user ?? auth_user();

function admin_nav_item(string $key, string $label, string $href, string $active): string
{
    $cls = $key === $active ? 'active' : '';
    return '<a class="' . $cls . '" href="' . e($href) . '">' . e($label) . '</a>';
}

?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($title); ?> - CineCraze Admin</title>
    <link rel="stylesheet" href="/assets/admin.css">
</head>
<body>
<div class="admin-shell">
    <header class="header">
        <div class="header-inner">
            <a class="brand" href="/admin/">
                <div class="brand-badge">CC</div>
                <span>CineCraze</span>
            </a>

            <nav class="nav" aria-label="Admin navigation">
                <?php echo admin_nav_item('dashboard', 'Dashboard', '/admin/', $active); ?>
                <?php echo admin_nav_item('tmdb', 'TMDB Generator', '/admin/tmdb.php', $active); ?>
                <?php echo admin_nav_item('manual', 'Manual', '/admin/manual.php', $active); ?>
                <?php echo admin_nav_item('bulk', 'Bulk', '/admin/bulk.php', $active); ?>
                <?php echo admin_nav_item('youtube', 'YouTube', '/admin/youtube.php', $active); ?>
                <?php echo admin_nav_item('data', 'Data Management', '/admin/data.php', $active); ?>
                <?php echo admin_nav_item('users', 'Users', '/admin/users.php', $active); ?>
            </nav>

            <div class="user">
                <div class="chip"><?php echo e((string)($user['email'] ?? '')); ?></div>
                <a class="btn secondary" href="/auth/logout.php">Logout</a>
            </div>
        </div>
    </header>

    <main class="main">
        <div class="page-title">
            <div>
                <h1><?php echo e($title); ?></h1>
                <?php if ($subtitle !== ''): ?>
                    <p><?php echo e($subtitle); ?></p>
                <?php endif; ?>
            </div>
        </div>
