<?php
$role = currentUser()['role'];
$navItems = getDashboardNavigation($role);
$unreadCount = getUnreadNotificationCount($pdo, currentUser()['id']);
$pageTitle = $pageTitle ?? 'Dashboard';
$isDashboard = true;
include ROOT_PATH . '/includes/header.php';
?>
<div class="dashboard-layout" id="dashboardLayout">
    <aside class="dashboard-sidebar" id="dashboardSidebar">
        <div class="sidebar-brand">
            <a href="<?= url() ?>" class="sidebar-brand-link">
                <span class="brand-icon"><i class="bi bi-cup-hot-fill"></i></span>
                <span class="sidebar-brand-text">
                    <strong>Splendid Catering</strong>
                    <small><?= e($config['roles'][$role] ?? 'Dashboard') ?></small>
                </span>
            </a>
            <button type="button" class="sidebar-collapse-btn d-none d-lg-flex" id="sidebarCollapse" aria-label="Collapse sidebar">
                <i class="bi bi-chevron-left"></i>
            </button>
        </div>

        <div class="sidebar-profile">
            <div class="sidebar-avatar"><?= e(strtoupper(substr(currentUser()['first_name'], 0, 1) . substr(currentUser()['last_name'], 0, 1))) ?></div>
            <div class="sidebar-profile-info">
                <strong><?= e(currentUser()['first_name'] . ' ' . currentUser()['last_name']) ?></strong>
                <small><?= e(currentUser()['email']) ?></small>
            </div>
        </div>

        <nav class="sidebar-nav" aria-label="Dashboard navigation">
            <?php foreach ($navItems as $item):
                if (isset($item['section'])): ?>
                <div class="sidebar-section-label"><?= e($item['section']) ?></div>
                <?php continue; endif;
                $href = $item['url'] . (isset($item['hash']) ? '#' . $item['hash'] : '');
            ?>
            <a class="nav-link sidebar-link" href="<?= e($href) ?>" data-section="<?= e($item['hash'] ?? 'overview') ?>">
                <i class="bi bi-<?= e($item['icon']) ?>"></i>
                <span><?= e($item['label']) ?></span>
            </a>
            <?php endforeach; ?>
        </nav>

        <div class="sidebar-footer">
            <a class="nav-link sidebar-link" href="<?= url() ?>"><i class="bi bi-globe2"></i><span>Website</span></a>
            <a class="nav-link sidebar-link text-danger" href="<?= url('auth/logout') ?>"><i class="bi bi-box-arrow-left"></i><span>Logout</span></a>
        </div>
    </aside>

    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <main class="dashboard-main">
        <header class="dashboard-topbar">
            <div class="topbar-left">
                <button type="button" class="btn btn-icon d-lg-none" id="sidebarToggle" aria-label="Open menu"><i class="bi bi-list"></i></button>
                <div>
                    <h1 class="topbar-title"><?= e($pageTitle) ?></h1>
                    <nav class="topbar-breadcrumb" aria-label="Breadcrumb">
                        <span>Splendid Catering</span>
                        <i class="bi bi-chevron-right"></i>
                        <span><?= e($config['roles'][$role] ?? 'Dashboard') ?></span>
                    </nav>
                </div>
            </div>
            <div class="topbar-right">
                <?php if (hasRole(['admin', 'finance', 'event_manager'])): ?>
                <div class="topbar-search-wrap">
                    <i class="bi bi-search"></i>
                    <input type="search" id="globalSearch" class="topbar-search" placeholder="Search customers, bookings, menu..." autocomplete="off" aria-label="Search">
                    <div class="search-results" id="searchResults" hidden></div>
                </div>
                <?php endif; ?>

                <button type="button" class="btn btn-icon theme-toggle" id="themeToggle" aria-label="Toggle theme">
                    <i class="bi bi-sun-fill theme-icon-light"></i>
                    <i class="bi bi-moon-stars-fill theme-icon-dark"></i>
                </button>

                <div class="dropdown">
                    <button type="button" class="btn btn-icon position-relative" data-bs-toggle="dropdown" aria-label="Notifications">
                        <i class="bi bi-bell"></i>
                        <?php if ($unreadCount > 0): ?><span class="notification-badge"><?= $unreadCount > 9 ? '9+' : $unreadCount ?></span><?php endif; ?>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end notification-dropdown">
                        <div class="dropdown-header d-flex justify-content-between align-items-center">
                            <strong>Notifications</strong>
                            <button type="button" class="btn btn-link btn-sm p-0" id="markAllRead">Mark all read</button>
                        </div>
                        <div id="notificationList" class="notification-list">
                            <div class="text-center text-muted py-3 small">Loading...</div>
                        </div>
                    </div>
                </div>

                <div class="dropdown">
                    <button type="button" class="btn topbar-profile" data-bs-toggle="dropdown" aria-label="User menu">
                        <span class="topbar-avatar"><?= e(strtoupper(substr(currentUser()['first_name'], 0, 1))) ?></span>
                        <span class="d-none d-md-inline"><?= e(currentUser()['first_name']) ?></span>
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header"><?= e(currentUser()['first_name'] . ' ' . currentUser()['last_name']) ?></h6></li>
                        <li><a class="dropdown-item" href="<?= roleDashboardUrl($role) ?>#settings"><i class="bi bi-person"></i> My Profile</a></li>
                        <li><a class="dropdown-item" href="<?= roleDashboardUrl($role) ?>#settings"><i class="bi bi-gear"></i> Account Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= url('auth/logout') ?>"><i class="bi bi-box-arrow-left"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </header>
        <div class="dashboard-content">
