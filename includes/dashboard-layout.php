<?php
$role = currentUser()['role'];
$sidebarItems = match ($role) {
    'admin' => [
        ['icon' => 'speedometer2', 'label' => 'Overview', 'url' => url('dashboard/admin')],
        ['icon' => 'calendar-event', 'label' => 'Bookings', 'url' => url('dashboard/admin') . '#bookings'],
        ['icon' => 'people', 'label' => 'Customers', 'url' => url('dashboard/admin') . '#customers'],
        ['icon' => 'person-badge', 'label' => 'Staff', 'url' => url('dashboard/admin') . '#staff'],
        ['icon' => 'egg-fried', 'label' => 'Menu Items', 'url' => url('dashboard/admin') . '#menu'],
        ['icon' => 'box-seam', 'label' => 'Packages', 'url' => url('dashboard/admin') . '#packages'],
        ['icon' => 'tag', 'label' => 'Promotions', 'url' => url('dashboard/admin') . '#promos'],
        ['icon' => 'chat-quote', 'label' => 'Testimonials', 'url' => url('dashboard/admin') . '#testimonials'],
        ['icon' => 'images', 'label' => 'Gallery', 'url' => url('dashboard/admin') . '#gallery'],
        ['icon' => 'graph-up', 'label' => 'AI Forecast', 'url' => url('dashboard/admin') . '#forecast'],
    ],
    'customer' => [
        ['icon' => 'house', 'label' => 'My Dashboard', 'url' => url('dashboard/customer')],
        ['icon' => 'calendar-check', 'label' => 'My Bookings', 'url' => url('dashboard/customer') . '#bookings'],
        ['icon' => 'file-earmark-text', 'label' => 'Quotations', 'url' => url('dashboard/customer') . '#quotes'],
        ['icon' => 'credit-card', 'label' => 'Payments', 'url' => url('dashboard/customer') . '#payments'],
        ['icon' => 'receipt', 'label' => 'Invoices', 'url' => url('dashboard/customer') . '#invoices'],
        ['icon' => 'star', 'label' => 'Reviews', 'url' => url('dashboard/customer') . '#reviews'],
        ['icon' => 'gift', 'label' => 'Loyalty Points', 'url' => url('dashboard/customer') . '#loyalty'],
    ],
    'event_manager' => [
        ['icon' => 'calendar-week', 'label' => 'My Events', 'url' => url('dashboard/events')],
        ['icon' => 'list-check', 'label' => 'Checklists', 'url' => url('dashboard/events') . '#checklists'],
        ['icon' => 'people', 'label' => 'Staff Assignments', 'url' => url('dashboard/events') . '#staff'],
        ['icon' => 'journal-text', 'label' => 'Event Notes', 'url' => url('dashboard/events') . '#notes'],
    ],
    'chef' => [
        ['icon' => 'fire', 'label' => 'Kitchen Orders', 'url' => url('dashboard/kitchen')],
        ['icon' => 'clock', 'label' => 'Prep Schedule', 'url' => url('dashboard/kitchen') . '#schedule'],
        ['icon' => 'book', 'label' => 'Recipes', 'url' => url('dashboard/kitchen') . '#recipes'],
        ['icon' => 'box', 'label' => 'Inventory', 'url' => url('dashboard/kitchen') . '#inventory'],
    ],
    'delivery' => [
        ['icon' => 'truck', 'label' => 'Deliveries', 'url' => url('dashboard/delivery')],
        ['icon' => 'geo-alt', 'label' => 'Routes', 'url' => url('dashboard/delivery') . '#routes'],
        ['icon' => 'camera', 'label' => 'Proof of Delivery', 'url' => url('dashboard/delivery') . '#proof'],
    ],
    'finance' => [
        ['icon' => 'cash-stack', 'label' => 'Overview', 'url' => url('dashboard/finance')],
        ['icon' => 'credit-card-2-front', 'label' => 'Payments', 'url' => url('dashboard/finance') . '#payments'],
        ['icon' => 'receipt-cutoff', 'label' => 'Invoices', 'url' => url('dashboard/finance') . '#invoices'],
        ['icon' => 'arrow-return-left', 'label' => 'Refunds', 'url' => url('dashboard/finance') . '#refunds'],
        ['icon' => 'file-earmark-bar-graph', 'label' => 'Reports', 'url' => url('dashboard/finance') . '#reports'],
    ],
    default => [],
};
?>
<?php
$pageTitle = $pageTitle ?? 'Dashboard';
$isDashboard = true;
include ROOT_PATH . '/includes/header.php';
?>
<div class="dashboard-layout">
    <aside class="dashboard-sidebar">
        <div class="sidebar-brand">
            <span class="brand-icon"><i class="bi bi-cup-hot-fill"></i></span>
            <div>
                <strong style="color:white;font-size:0.95rem;">Splendid Caterery</strong>
                <small style="display:block;color:var(--gold);font-size:0.7rem;"><?= e($config['roles'][$role] ?? 'Dashboard') ?></small>
            </div>
        </div>
        <nav class="sidebar-nav">
            <?php foreach ($sidebarItems as $item): ?>
            <a class="nav-link" href="<?= e($item['url']) ?>">
                <i class="bi bi-<?= e($item['icon']) ?>"></i> <?= e($item['label']) ?>
            </a>
            <?php endforeach; ?>
            <hr style="border-color:rgba(255,255,255,0.1);margin:1rem 1.5rem;">
            <a class="nav-link" href="<?= url() ?>"><i class="bi bi-globe"></i> View Website</a>
            <a class="nav-link" href="<?= url('auth/logout') ?>"><i class="bi bi-box-arrow-left"></i> Logout</a>
        </nav>
    </aside>
    <main class="dashboard-main">
        <header class="dashboard-header">
            <div>
                <button class="btn btn-sm btn-outline-secondary d-lg-none" id="sidebarToggle"><i class="bi bi-list"></i></button>
                <h5 class="mb-0 d-inline ms-2"><?= e($pageTitle) ?></h5>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted small">Welcome, <strong><?= e(currentUser()['first_name']) ?></strong></span>
                <div class="rounded-circle bg-gold d-flex align-items-center justify-content-center" style="width:38px;height:38px;background:var(--gold);color:white;font-weight:600;">
                    <?= e(strtoupper(substr(currentUser()['first_name'], 0, 1))) ?>
                </div>
            </div>
        </header>
        <div class="dashboard-content">
