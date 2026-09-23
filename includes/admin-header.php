<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';
require_admin();
$admin = current_admin();
$pageTitle = $pageTitle ?? 'Dashboard';
$adminSection = $adminSection ?? '';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> · Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
</head>
<body class="admin-body">
<button class="sidebar-toggle d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar" aria-label="Open menu">
    <i class="fa-solid fa-bars"></i>
</button>
<aside class="admin-sidebar offcanvas-lg offcanvas-start" id="adminSidebar" tabindex="-1">
    <div class="sidebar-brand">
        <i class="fa-solid fa-vr-cardboard"></i>
        <div>
            <strong>AR TOURISM</strong>
            <small>Administrator</small>
        </div>
    </div>
    <nav class="sidebar-nav">
        <a class="<?= $adminSection === 'dashboard' ? 'active' : '' ?>" href="<?= e(admin_url('index.php')) ?>"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
        <p class="nav-label">Tourism</p>
        <a class="<?= $adminSection === 'destinations' ? 'active' : '' ?>" href="<?= e(admin_url('destinations/index.php')) ?>"><i class="fa-solid fa-location-dot"></i> Destinations</a>
        <a class="<?= $adminSection === 'attractions' ? 'active' : '' ?>" href="<?= e(admin_url('attractions/index.php')) ?>"><i class="fa-solid fa-landmark"></i> Attractions</a>
        <a class="<?= $adminSection === 'categories' ? 'active' : '' ?>" href="<?= e(admin_url('categories/index.php')) ?>"><i class="fa-solid fa-tags"></i> Categories</a>
        <p class="nav-label">AR Management</p>
        <a class="<?= $adminSection === 'posters' ? 'active' : '' ?>" href="<?= e(admin_url('ar-posters/index.php')) ?>"><i class="fa-solid fa-image"></i> AR Posters</a>
        <a class="<?= $adminSection === 'compile' ? 'active' : '' ?>" href="<?= e(admin_url('ar-posters/compile.php')) ?>"><i class="fa-solid fa-cube"></i> Target Compilation</a>
        <p class="nav-label">System</p>
        <a class="<?= $adminSection === 'users' ? 'active' : '' ?>" href="<?= e(admin_url('users/index.php')) ?>"><i class="fa-solid fa-users"></i> Users</a>
        <a class="<?= $adminSection === 'logs' ? 'active' : '' ?>" href="<?= e(admin_url('logs/index.php')) ?>"><i class="fa-solid fa-clock-rotate-left"></i> Activity Logs</a>
        <a class="<?= $adminSection === 'settings' ? 'active' : '' ?>" href="<?= e(admin_url('settings/index.php')) ?>"><i class="fa-solid fa-gear"></i> Settings</a>
        <a href="<?= e(url('index.php')) ?>"><i class="fa-solid fa-globe"></i> View Website</a>
        <a href="<?= e(admin_url('logout.php')) ?>"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </nav>
</aside>
<div class="admin-main">
    <header class="admin-top">
        <div>
            <h1><?= e($pageTitle) ?></h1>
            <p>Signed in as <?= e($admin['username'] ?? 'admin') ?></p>
        </div>
        <a class="btn btn-outline-light btn-sm" href="<?= e(url('ar.php')) ?>">Preview AR</a>
    </header>
    <div class="admin-content">
        <?php $flash = flash_get(); if ($flash): ?>
            <div class="alert alert-<?= e($flash['type'] === 'success' ? 'success' : 'danger') ?>"><?= e($flash['message']) ?></div>
        <?php endif; ?>
        <?php if (!is_secure_context()): ?>
            <div class="alert alert-warning"><i class="fa-solid fa-shield-halved me-2"></i>Camera access requires HTTPS. Please access this website using an HTTPS URL when testing AR on a smartphone.</div>
        <?php endif; ?>
