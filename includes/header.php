<?php
$pageTitle = $pageTitle ?? $config['name'];
$pageDescription = $pageDescription ?? 'Premium catering and event management in Ghana';
$bodyClass = $bodyClass ?? '';
$isDashboard = $isDashboard ?? false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e($pageDescription) ?>">
    <title><?= e($pageTitle) ?> | <?= e($config['name']) ?></title>
    <script>
    (function(){var t=localStorage.getItem('sc-theme');if(!t&&window.matchMedia('(prefers-color-scheme: dark)').matches)t='dark';document.documentElement.setAttribute('data-theme',t||'light');})();
    window.APP_BASE = <?= json_encode(BASE_URL) ?>;
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <?php if (!$isDashboard): ?><link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet"><?php endif; ?>
    <link href="<?= asset('css/style.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/theme.css') ?>" rel="stylesheet">
    <?php if ($isDashboard): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <?php endif; ?>
</head>
<body class="<?= e($bodyClass) ?> <?= $isDashboard ? 'dashboard-body' : '' ?>">
<?php if (!$isDashboard): ?>
<nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNav">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= url() ?>">
            <span class="brand-icon"><i class="bi bi-cup-hot-fill"></i></span>
            <span class="brand-text"><strong>Splendid</strong><small>Catering</small></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item"><a class="nav-link" href="<?= url() ?>">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= url('about') ?>">About</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= url('services') ?>">Services</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= url('packages') ?>">Packages</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= url('menu') ?>">Menu</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= url('gallery') ?>">Gallery</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= url('testimonials') ?>">Reviews</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= url('contact') ?>">Contact</a></li>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-outline-light btn-sm theme-toggle-public" id="themeTogglePublic" aria-label="Toggle theme">
                    <i class="bi bi-sun-fill theme-icon-light"></i>
                    <i class="bi bi-moon-stars-fill theme-icon-dark"></i>
                </button>
                <?php if (isLoggedIn()): ?>
                    <a href="<?= roleDashboardUrl(currentUser()['role']) ?>" class="btn btn-outline-light btn-sm"><i class="bi bi-speedometer2"></i> Dashboard</a>
                    <a href="<?= url('auth/logout') ?>" class="btn btn-gold btn-sm">Logout</a>
                <?php else: ?>
                    <a href="<?= url('auth/login') ?>" class="btn btn-outline-light btn-sm">Login</a>
                    <a href="<?= url('book') ?>" class="btn btn-gold btn-sm"><i class="bi bi-calendar-check"></i> Book Now</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
<?php endif; ?>

<?php $flash = getFlash(); if ($flash): ?>
<div class="flash-message alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
    <?= e($flash['message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
