<?php

declare(strict_types=1);

function renderEmptyState(string $icon, string $title, string $message, ?string $actionUrl = null, ?string $actionLabel = null): string
{
    $action = '';
    if ($actionUrl && $actionLabel) {
        $action = '<a href="' . e($actionUrl) . '" class="btn btn-accent mt-3"><i class="bi bi-plus-lg"></i> ' . e($actionLabel) . '</a>';
    }
    return '<div class="empty-state">
        <div class="empty-state-icon"><i class="bi bi-' . e($icon) . '"></i></div>
        <h5>' . e($title) . '</h5>
        <p class="text-muted mb-0">' . e($message) . '</p>' . $action . '
    </div>';
}

function paymentStatusBadge(string $status): string
{
    $map = [
        'paid' => ['Paid', 'success'],
        'partial' => ['Partially Paid', 'warning'],
        'unpaid' => ['Unpaid', 'danger'],
        'refunded' => ['Refunded', 'secondary'],
    ];
    $info = $map[$status] ?? [ucfirst($status), 'secondary'];
    return '<span class="badge badge-soft badge-' . $info[1] . '">' . e($info[0]) . '</span>';
}

function orderStatusBadge(string $status): string
{
    global $config;
    $info = $config['booking_statuses'][$status] ?? ['label' => ucfirst(str_replace('_', ' ', $status)), 'color' => 'secondary'];
    $colorMap = ['warning' => 'warning', 'info' => 'info', 'primary' => 'primary', 'success' => 'success', 'danger' => 'danger'];
    $c = $colorMap[$info['color']] ?? 'secondary';
    return '<span class="badge badge-soft badge-' . $c . '">' . e($info['label']) . '</span>';
}

function renderKpiCard(string $label, string $value, string $icon, string $color = 'accent', ?string $trend = null, ?string $link = null, ?string $linkText = null): string
{
    $trendHtml = $trend ? '<span class="kpi-trend">' . e($trend) . '</span>' : '';
    $linkHtml = ($link && $linkText) ? '<a href="' . e($link) . '" class="kpi-link">' . e($linkText) . ' <i class="bi bi-arrow-right"></i></a>' : '';
    $tag = $link ? 'a' : 'div';
    $href = $link ? ' href="' . e($link) . '"' : '';
    return '<' . $tag . ' class="kpi-card kpi-' . e($color) . '"' . $href . '>
        <div class="kpi-card-inner">
            <div class="kpi-icon"><i class="bi bi-' . e($icon) . '"></i></div>
            <div class="kpi-body">
                <span class="kpi-label">' . e($label) . '</span>
                <span class="kpi-value">' . $value . '</span>
                ' . $trendHtml . '
                ' . $linkHtml . '
            </div>
        </div>
    </' . $tag . '>';
}

function getDashboardNavigation(string $role): array
{
    $base = roleDashboardUrl($role);
    return match ($role) {
        'admin' => [
            ['section' => 'Main'],
            ['icon' => 'grid-1x2', 'label' => 'Dashboard', 'url' => $base, 'hash' => 'overview'],
            ['icon' => 'bag-check', 'label' => 'Orders', 'url' => $base, 'hash' => 'orders'],
            ['icon' => 'calendar-event', 'label' => 'Bookings', 'url' => $base, 'hash' => 'bookings'],
            ['section' => 'Management'],
            ['icon' => 'people', 'label' => 'Customers', 'url' => $base, 'hash' => 'customers'],
            ['icon' => 'egg-fried', 'label' => 'Menu', 'url' => $base, 'hash' => 'menu'],
            ['icon' => 'box-seam', 'label' => 'Packages', 'url' => $base, 'hash' => 'packages'],
            ['icon' => 'stars', 'label' => 'Events', 'url' => $base, 'hash' => 'events'],
            ['section' => 'Finance'],
            ['icon' => 'credit-card', 'label' => 'Payments', 'url' => $base, 'hash' => 'payments'],
            ['icon' => 'receipt', 'label' => 'Invoices', 'url' => $base, 'hash' => 'invoices'],
            ['section' => 'Tools'],
            ['icon' => 'calendar3', 'label' => 'Calendar', 'url' => $base, 'hash' => 'calendar'],
            ['icon' => 'bell', 'label' => 'Notifications', 'url' => $base, 'hash' => 'notifications'],
            ['icon' => 'bar-chart-line', 'label' => 'Reports', 'url' => $base, 'hash' => 'reports'],
            ['icon' => 'gear', 'label' => 'Settings', 'url' => $base, 'hash' => 'settings'],
        ],
        'customer' => [
            ['section' => 'My Account'],
            ['icon' => 'house', 'label' => 'Dashboard', 'url' => $base, 'hash' => 'overview'],
            ['icon' => 'calendar-check', 'label' => 'My Bookings', 'url' => $base, 'hash' => 'bookings'],
            ['icon' => 'file-earmark-text', 'label' => 'Quotations', 'url' => $base, 'hash' => 'quotes'],
            ['icon' => 'credit-card', 'label' => 'Payments', 'url' => $base, 'hash' => 'payments'],
            ['icon' => 'receipt', 'label' => 'Invoices', 'url' => $base, 'hash' => 'invoices'],
            ['icon' => 'star', 'label' => 'Reviews', 'url' => $base, 'hash' => 'reviews'],
            ['icon' => 'gift', 'label' => 'Loyalty', 'url' => $base, 'hash' => 'loyalty'],
        ],
        'event_manager' => [
            ['section' => 'Events'],
            ['icon' => 'calendar-week', 'label' => 'My Events', 'url' => $base, 'hash' => 'overview'],
            ['icon' => 'list-check', 'label' => 'Checklists', 'url' => $base, 'hash' => 'checklists'],
            ['icon' => 'people', 'label' => 'Staff', 'url' => $base, 'hash' => 'staff'],
            ['icon' => 'journal-text', 'label' => 'Notes', 'url' => $base, 'hash' => 'notes'],
            ['icon' => 'calendar3', 'label' => 'Calendar', 'url' => $base, 'hash' => 'calendar'],
            ['icon' => 'bell', 'label' => 'Notifications', 'url' => $base, 'hash' => 'notifications'],
        ],
        'chef' => [
            ['section' => 'Kitchen'],
            ['icon' => 'fire', 'label' => 'Orders', 'url' => $base, 'hash' => 'overview'],
            ['icon' => 'clock-history', 'label' => 'Prep Schedule', 'url' => $base, 'hash' => 'schedule'],
            ['icon' => 'book', 'label' => 'Recipes', 'url' => $base, 'hash' => 'recipes'],
            ['icon' => 'box', 'label' => 'Inventory', 'url' => $base, 'hash' => 'inventory'],
            ['icon' => 'bell', 'label' => 'Notifications', 'url' => $base, 'hash' => 'notifications'],
        ],
        'delivery' => [
            ['section' => 'Logistics'],
            ['icon' => 'truck', 'label' => 'Deliveries', 'url' => $base, 'hash' => 'overview'],
            ['icon' => 'signpost-split', 'label' => 'Routes', 'url' => $base, 'hash' => 'routes'],
            ['icon' => 'camera', 'label' => 'Proof of Delivery', 'url' => $base, 'hash' => 'proof'],
            ['icon' => 'bell', 'label' => 'Notifications', 'url' => $base, 'hash' => 'notifications'],
        ],
        'finance' => [
            ['section' => 'Finance'],
            ['icon' => 'cash-stack', 'label' => 'Overview', 'url' => $base, 'hash' => 'overview'],
            ['icon' => 'credit-card-2-front', 'label' => 'Payments', 'url' => $base, 'hash' => 'payments'],
            ['icon' => 'receipt-cutoff', 'label' => 'Invoices', 'url' => $base, 'hash' => 'invoices'],
            ['icon' => 'arrow-return-left', 'label' => 'Refunds', 'url' => $base, 'hash' => 'refunds'],
            ['icon' => 'bar-chart-line', 'label' => 'Reports', 'url' => $base, 'hash' => 'reports'],
            ['icon' => 'bell', 'label' => 'Notifications', 'url' => $base, 'hash' => 'notifications'],
        ],
        default => [],
    };
}

function getAdminAnalytics(PDO $pdo): array
{
    $today = date('Y-m-d');
    $analytics = [];

    $analytics['orders_today'] = (int) $pdo->query("SELECT COUNT(*) FROM bookings WHERE DATE(created_at) = CURDATE()")->fetchColumn();
    $analytics['orders_pending'] = (int) $pdo->query("SELECT COUNT(*) FROM bookings WHERE status IN ('pending','confirmed')")->fetchColumn();
    $analytics['orders_completed'] = (int) $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'completed'")->fetchColumn();
    $analytics['orders_cancelled'] = (int) $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'cancelled'")->fetchColumn();

    $analytics['revenue_today'] = (float) $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='completed' AND DATE(created_at)=CURDATE()")->fetchColumn();
    $analytics['revenue_week'] = (float) $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='completed' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)")->fetchColumn();
    $analytics['revenue_month'] = (float) $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='completed' AND MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE())")->fetchColumn();
    $analytics['outstanding'] = (float) $pdo->query("SELECT COALESCE(SUM(total_amount - amount_paid),0) FROM bookings WHERE payment_status IN ('unpaid','partial')")->fetchColumn();

    $analytics['upcoming_events'] = (int) $pdo->query("SELECT COUNT(*) FROM bookings WHERE event_date >= CURDATE() AND status NOT IN ('cancelled','completed')")->fetchColumn();
    $analytics['active_bookings'] = (int) $pdo->query("SELECT COUNT(*) FROM bookings WHERE status IN ('confirmed','in_preparation','ready','in_transit')")->fetchColumn();
    $analytics['pending_quotes'] = (int) $pdo->query("SELECT COUNT(*) FROM quotations WHERE status = 'pending'")->fetchColumn();
    $analytics['confirmed_events'] = (int) $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'confirmed'")->fetchColumn();

    $analytics['total_customers'] = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn();
    $analytics['new_customers_month'] = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role='customer' AND MONTH(created_at)=MONTH(CURDATE())")->fetchColumn();
    $analytics['returning_customers'] = (int) $pdo->query("
        SELECT COUNT(*) FROM (SELECT customer_id FROM bookings GROUP BY customer_id HAVING COUNT(*) > 1) t
    ")->fetchColumn();

    // Revenue chart - last 7 days
    $analytics['revenue_chart'] = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-{$i} days"));
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='completed' AND DATE(created_at)=?");
        $stmt->execute([$date]);
        $analytics['revenue_chart'][] = ['label' => date('D', strtotime($date)), 'value' => (float) $stmt->fetchColumn()];
    }

    // Orders by status
    $statuses = ['pending', 'confirmed', 'in_preparation', 'ready', 'completed', 'cancelled'];
    $analytics['orders_chart'] = [];
    foreach ($statuses as $s) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM bookings WHERE status = ?');
        $stmt->execute([$s]);
        $analytics['orders_chart'][] = ['label' => ucfirst(str_replace('_', ' ', $s)), 'value' => (int) $stmt->fetchColumn()];
    }

    // Popular packages
    $analytics['popular_packages'] = $pdo->query('
        SELECT p.name, COUNT(b.id) as cnt FROM packages p
        LEFT JOIN bookings b ON b.package_id = p.id
        GROUP BY p.id ORDER BY cnt DESC LIMIT 5
    ')->fetchAll();

    // Booking trends - last 6 months
    $analytics['booking_trends'] = [];
    for ($i = 5; $i >= 0; $i--) {
        $month = date('Y-m', strtotime("-{$i} months"));
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE DATE_FORMAT(created_at,'%Y-%m') = ?");
        $stmt->execute([$month]);
        $analytics['booking_trends'][] = ['label' => date('M', strtotime($month . '-01')), 'value' => (int) $stmt->fetchColumn()];
    }

    return $analytics;
}

function getUserNotifications(PDO $pdo, int $userId, int $limit = 10): array
{
    $stmt = $pdo->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?');
    $stmt->bindValue(1, $userId, PDO::PARAM_INT);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getUnreadNotificationCount(PDO $pdo, int $userId): int
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
    $stmt->execute([$userId]);
    return (int) $stmt->fetchColumn();
}
