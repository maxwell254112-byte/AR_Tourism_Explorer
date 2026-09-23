<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';
$pageTitle = $pageTitle ?? app_title();
$pageDescription = $pageDescription ?? setting('site_tagline', 'Discover destinations through interactive augmented reality.');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($pageDescription) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/public.css')) ?>">
</head>
<body>
<a class="visually-hidden-focusable skip-link" href="#main">Skip to content</a>
<header class="site-header">
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="<?= e(url('index.php')) ?>">
                <span class="brand-mark"><i class="fa-solid fa-vr-cardboard"></i></span>
                <span><?= e(app_title()) ?></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                    <li class="nav-item"><a class="nav-link <?= active_nav('index.php') ?>" href="<?= e(url('index.php')) ?>">Home</a></li>
                    <li class="nav-item"><a class="nav-link <?= active_nav('destinations.php') ?>" href="<?= e(url('destinations.php')) ?>">Destinations</a></li>
                    <li class="nav-item"><a class="nav-link <?= active_nav('attractions.php') ?>" href="<?= e(url('attractions.php')) ?>">Attractions</a></li>
                    <li class="nav-item"><a class="nav-link <?= active_nav('ar.php') ?>" href="<?= e(url('ar.php')) ?>">AR Experience</a></li>
                    <li class="nav-item"><a class="nav-link <?= active_nav('qr.php') ?>" href="<?= e(url('qr.php')) ?>">QR Codes</a></li>
                    <li class="nav-item"><a class="nav-link <?= active_nav('about.php') ?>" href="<?= e(url('about.php')) ?>">About</a></li>
                    <li class="nav-item"><a class="nav-link <?= active_nav('contact.php') ?>" href="<?= e(url('contact.php')) ?>">Contact</a></li>
                    <li class="nav-item"><a class="btn btn-ar ms-lg-2" href="<?= e(url('ar.php')) ?>"><i class="fa-solid fa-camera me-2"></i>Start AR</a></li>
                </ul>
            </div>
        </div>
    </nav>
</header>
<main id="main">
