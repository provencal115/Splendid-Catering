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
    $trendHtml = $trend ? '<span class="kpi-meta">' . e($trend) . '</span>' : '';
    $linkHtml = ($link && $linkText) ? '<span class="kpi-link">' . e($linkText) . ' <i class="bi bi-arrow-right"></i></span>' : '';
    $tag = $link ? 'a' : 'div';
    $href = $link ? ' href="' . e($link) . '"' : '';
    return '<' . $tag . ' class="kpi-card kpi-' . e($color) . '"' . $href . '>
        <div class="kpi-card-inner">
            <div class="kpi-card-top">
                <span class="kpi-label">' . e($label) . '</span>
                <div class="kpi-icon"><i class="bi bi-' . e($icon) . '"></i></div>
            </div>
            <div class="kpi-body">
                <span class="kpi-value">' . $value . '</span>
                ' . $trendHtml . '
                ' . $linkHtml . '
            </div>
        </div>
    </' . $tag . '>';
}

function renderDashboardWelcome(string $greeting, string $subtitle, array $actions = [], ?int $notificationCount = null): string
{
    $actionsHtml = '';
    foreach ($actions as $action) {
        $class = $action['class'] ?? 'btn-outline-secondary';
        $icon = !empty($action['icon']) ? '<i class="bi bi-' . e($action['icon']) . '"></i> ' : '';
        $actionsHtml .= '<a href="' . e($action['url']) . '" class="btn btn-sm ' . e($class) . '">' . $icon . e($action['label']) . '</a>';
    }

    $notifBadge = ($notificationCount !== null && $notificationCount > 0)
        ? '<span class="dash-welcome-badge"><i class="bi bi-bell-fill"></i> ' . (int) $notificationCount . ' unread notification' . ($notificationCount === 1 ? '' : 's') . '</span>'
        : '';

    return '<div class="dash-welcome">
        <div class="dash-welcome-content">
            <p class="dash-welcome-eyebrow">' . e(date('l, F j, Y')) . '</p>
            <h2 class="dash-welcome-title">' . e($greeting) . '</h2>
            <p class="dash-welcome-subtitle">' . e($subtitle) . '</p>
            ' . $notifBadge . '
        </div>
        <div class="dash-welcome-actions">' . $actionsHtml . '</div>
    </div>';
}

function renderDashSectionHeading(string $title, ?string $subtitle = null, ?string $actionUrl = null, ?string $actionLabel = null): string
{
    $action = ($actionUrl && $actionLabel)
        ? '<a href="' . e($actionUrl) . '" class="dash-section-link">' . e($actionLabel) . ' <i class="bi bi-arrow-right"></i></a>'
        : '';
    $sub = $subtitle ? '<p class="dash-section-desc">' . e($subtitle) . '</p>' : '';

    return '<div class="dash-section-head">
        <div>
            <h3 class="dash-section-title">' . e($title) . '</h3>
            ' . $sub . '
        </div>
        ' . $action . '
    </div>';
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

function getPublicSiteStats(PDO $pdo): array
{
    $eventsServed = (int) $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'completed'")->fetchColumn();
    $menuItems = (int) $pdo->query('SELECT COUNT(*) FROM menu_items WHERE is_active = 1')->fetchColumn();
    $serviceTypes = (int) $pdo->query('SELECT COUNT(*) FROM service_types WHERE is_active = 1')->fetchColumn();
    $avgRating = round((float) $pdo->query('SELECT COALESCE(AVG(rating), 0) FROM testimonials WHERE is_active = 1')->fetchColumn(), 1);

    return [
        'events_served' => $eventsServed,
        'menu_items' => $menuItems,
        'service_types' => $serviceTypes,
        'avg_rating' => $avgRating,
    ];
}

function getHeroSlideshowImages(PDO $pdo, int $limit = 5): array
{
    $images = [];
    $stmt = $pdo->query('SELECT image_url, title FROM gallery WHERE is_featured = 1 ORDER BY sort_order LIMIT 3');
    foreach ($stmt->fetchAll() as $row) {
        $images[] = ['url' => $row['image_url'], 'alt' => $row['title']];
    }
    $stmt = $pdo->query('SELECT image_url, name FROM menu_items WHERE is_active = 1 ORDER BY popularity_score DESC LIMIT 3');
    foreach ($stmt->fetchAll() as $row) {
        $images[] = ['url' => $row['image_url'], 'alt' => $row['name']];
    }
    if (empty($images)) {
        $images[] = ['url' => 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=1920&q=80', 'alt' => 'Splendid Catering'];
    }
    return array_slice($images, 0, $limit);
}

function renderCinematicHomeHero(PDO $pdo, array $siteStats, string $ratingDisplay, array $slideshowImages): string
{
    $media = getHomeHeroMedia($pdo);

    $videoHtml = '';
    if ($media['has_video']) {
        $webmSource = $media['webm']
            ? '<source src="' . e($media['webm']) . '" type="video/webm">'
            : '';
        $videoHtml = '<video class="hero-cinematic__video" data-hero-video autoplay muted loop playsinline preload="metadata" poster="' . e($media['poster']) . '" aria-hidden="true">
            <source src="' . e($media['mp4']) . '" type="video/mp4">
            ' . $webmSource . '
        </video>';
    }

    $posterClass = $media['has_video'] ? ' hero-cinematic__poster--under-video' : ' hero-cinematic__poster--active';

    return '<section class="hero-section hero-section--home hero-cinematic hero-cinematic--video" id="home-hero">
        <div class="hero-cinematic__media" aria-hidden="true">
            ' . $videoHtml . '
            <div class="hero-cinematic__poster' . $posterClass . '" data-hero-poster style="background-image:url(\'' . e($media['poster']) . '\')"></div>
        </div>
        <div class="hero-cinematic__overlay hero-cinematic__overlay--video" aria-hidden="true"></div>
        <div class="container hero-content hero-content--video">
            <div class="hero-animate hero-animate--luxury">
                <p class="hero-brand-mark"><span class="hero-brand-mark__icon"><i class="bi bi-cup-hot-fill"></i></span> SPLENDID CATERING</p>
                <h1 class="hero-title hero-title--luxury">Every Occasion Deserves Something Extraordinary.</h1>
                <p class="hero-subtitle hero-subtitle--luxury">Exceptional catering, beautifully prepared and thoughtfully served.</p>
                <div class="d-flex flex-wrap gap-3 hero-actions justify-content-center justify-content-lg-start">
                    <a href="' . e(url('book')) . '" class="btn btn-gold btn-lg btn-interactive btn-arrow px-4">Book Your Event <i class="bi bi-arrow-right"></i></a>
                    <a href="' . e(url('menu')) . '" class="btn btn-outline-light btn-lg btn-interactive px-4">Explore Our Menu</a>
                </div>
            </div>
        </div>
        <a href="#about-splendid" class="hero-cinematic__scroll" aria-label="Scroll to content"><span></span></a>
    </section>';
}

function renderPublicEmptyState(string $icon, string $title, string $message, ?string $actionUrl = null, ?string $actionLabel = null): string
{
    return '<div class="public-empty-state">' . renderEmptyState($icon, $title, $message, $actionUrl, $actionLabel) . '</div>';
}

function renderFoodShowcase(PDO $pdo): string
{
    $items = $pdo->query('
        SELECT m.*, c.name AS category_name
        FROM menu_items m
        JOIN menu_categories c ON m.category_id = c.id
        WHERE m.is_active = 1
        ORDER BY m.popularity_score DESC
        LIMIT 5
    ')->fetchAll();

    if (empty($items)) {
        return '<section class="section food-showcase section--warm" id="food-showcase">
            <div class="container">
                ' . renderSectionHeader('Signature Dishes', 'Food That Tells a Story', 'Chef-crafted plates designed to impress every guest at your table') . '
                ' . renderPublicEmptyState(
                    'egg-fried',
                    'Menu items coming soon',
                    'Our chefs are finalizing the menu. Contact us for custom catering in the meantime.',
                    url('contact'),
                    'Contact Us'
                ) . '
            </div>
        </section>';
    }

    $featured = $items[0];
    $secondary = array_slice($items, 1, 4);

    $secondaryHtml = '';
    foreach ($secondary as $i => $item) {
        $secondaryHtml .= '<figure class="food-showcase__thumb" data-parallax-layer="' . ($i % 2 === 0 ? '0.06' : '-0.04') . '" data-mask-reveal data-reveal-delay="' . ($i * 100) . '">
            <img src="' . e($item['image_url']) . '" alt="' . e($item['name']) . '" loading="lazy">
            <figcaption>' . e($item['name']) . '</figcaption>
        </figure>';
    }

    $gridHtml = $secondaryHtml !== ''
        ? '<div class="food-showcase__grid">' . $secondaryHtml . '</div>'
        : '';

    return '<section class="section food-showcase" id="food-showcase">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6">
                    <div class="food-showcase__featured" data-parallax-layer="0.08">
                        <div class="food-showcase__featured-mask" data-mask-reveal>
                            <img src="' . e($featured['image_url']) . '" alt="' . e($featured['name']) . '" loading="lazy">
                        </div>
                        <div class="food-showcase__badge">' . e($featured['category_name']) . '</div>
                    </div>
                    ' . $gridHtml . '
                </div>
                <div class="col-lg-6">
                    <div class="food-showcase__copy" data-reveal>
                        <span class="section-label">Signature Menu</span>
                        <h2 class="section-title section-title--editorial">Food That Tells a Story</h2>
                        <p class="section-subtitle text-start">Every plate is crafted by award-winning chefs using premium ingredients — from West African classics to international fine dining.</p>
                        <p class="text-muted">Featured: <strong>' . e($featured['name']) . '</strong> — ' . e($featured['description']) . '</p>
                        <div class="d-flex flex-wrap gap-3 mt-4">
                            <a href="' . e(url('menu')) . '" class="btn btn-gold btn-interactive btn-arrow">View Full Menu <i class="bi bi-arrow-right"></i></a>
                            <a href="' . e(url('book')) . '" class="btn btn-outline-gold btn-interactive">Book Catering</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>';
}

function serviceIconClass(string $icon): string
{
    $name = str_starts_with($icon, 'bi-') ? substr($icon, 3) : $icon;
    return 'bi bi-' . $name;
}

function renderServiceEditorialTile(array $service, int $index = 0): string
{
    $delay = ($index % 3) * 90;
    return '<div class="col-md-6 col-lg-4">
        <a href="' . e(url('book') . '?service=' . $service['slug']) . '" class="service-tile" data-mask-reveal data-reveal-delay="' . $delay . '" data-cursor-label="Explore">
            <div class="service-tile__media">
                <img src="' . e($service['image_url']) . '" alt="' . e($service['name']) . '" loading="lazy">
            </div>
            <div class="service-tile__overlay">
                <span class="service-tile__icon"><i class="' . e(serviceIconClass($service['icon'])) . '"></i></span>
                <h3>' . e($service['name']) . '</h3>
                <p>' . e(substr($service['description'], 0, 120)) . '…</p>
                <span class="service-tile__cta">Request Quote <i class="bi bi-arrow-right"></i></span>
            </div>
        </a>
    </div>';
}

function renderCinematicCta(string $bgImage, string $title, string $subtitle): string
{
    return '<section class="cta-cinematic" data-parallax-section>
        <div class="cta-cinematic__bg" data-cinematic-cta style="background-image:url(\'' . e($bgImage) . '\')"></div>
        <div class="cta-cinematic__overlay"></div>
        <div class="container position-relative text-center" data-reveal>
            <h2 class="cta-cinematic__title">' . e($title) . '</h2>
            <p class="cta-cinematic__subtitle">' . e($subtitle) . '</p>
            <div class="d-flex flex-wrap justify-content-center gap-3">
                <a href="' . e(url('book')) . '" class="btn btn-gold btn-lg btn-interactive btn-arrow"><i class="bi bi-calendar-check"></i> Book Your Event</a>
                <a href="' . e(url('contact')) . '" class="btn btn-outline-light btn-lg btn-interactive">Contact Us</a>
            </div>
        </div>
    </section>';
}

function renderSectionHeader(string $label, string $title, ?string $subtitle = null, string $class = 'section-header-premium'): string
{
    $sub = $subtitle ? '<p class="section-subtitle">' . e($subtitle) . '</p>' : '';
    return '<div class="' . e($class) . '" data-reveal data-reveal-type="title">
        <span class="section-label">' . e($label) . '</span>
        <h2 class="section-title section-title--editorial">' . e($title) . '</h2>
        ' . $sub . '
    </div>';
}

function renderPublicHero(
    string $title,
    ?string $subtitle = null,
    ?string $badge = null,
    ?string $bgImage = null,
    array $actions = [],
    bool $centered = false,
    string $size = 'compact'
): string {
    $bg = $bgImage ?: 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=1920&q=80';
    $sizeClass = $size === 'home' ? 'hero-section--home' : 'hero-section--compact';
    $alignClass = $centered ? ' text-center' : '';
    $subtitleClass = $centered ? ' mx-auto' : '';

    $badgeHtml = $badge ? '<div class="hero-badge"><i class="bi bi-stars"></i> ' . e($badge) . '</div>' : '';
    $actionsHtml = '';
    foreach ($actions as $action) {
        $class = ($action['class'] ?? 'btn-outline-light btn-lg') . ' btn-interactive';
        $icon = !empty($action['icon']) ? '<i class="bi bi-' . e($action['icon']) . '"></i> ' : '';
        $actionsHtml .= '<a href="' . e($action['url']) . '" class="btn ' . e($class) . '">' . $icon . e($action['label']) . '</a>';
    }
    $actionsBlock = $actionsHtml ? '<div class="d-flex flex-wrap gap-3 hero-actions">' . $actionsHtml . '</div>' : '';

    return '<section class="hero-section ' . e($sizeClass) . ' hero-cinematic hero-cinematic--page">
        <div class="hero-cinematic__slides" data-cinematic-hero>
            <div class="hero-cinematic__slide is-active" style="background-image:url(\'' . e($bg) . '\')"></div>
        </div>
        <div class="hero-cinematic__overlay" aria-hidden="true"></div>
        <div class="container hero-content' . $alignClass . '">
            <div class="hero-animate hero-animate--cinematic' . ($centered ? ' mx-auto' : '') . '" style="' . ($centered ? 'max-width:760px;' : '') . '">
                ' . ($badge ? '<p class="hero-eyebrow">' . e($badge) . '</p>' : '') . '
                <h1 class="hero-title hero-title--editorial">' . $title . '</h1>
                ' . ($subtitle ? '<p class="hero-subtitle' . $subtitleClass . '">' . e($subtitle) . '</p>' : '') . '
                ' . $actionsBlock . '
            </div>
        </div>
    </section>';
}

function renderFoodCard(array $item, ?string $categoryName = null, bool $showBookBtn = true): string
{
    $category = $categoryName ?: ($item['category_name'] ?? 'Menu');
    $bookBtn = $showBookBtn
        ? '<a href="' . e(url('book')) . '" class="food-card__order btn btn-gold btn-sm btn-interactive"><i class="bi bi-calendar-check"></i> Book Catering</a>'
        : '';
    $popular = ((int) ($item['popularity_score'] ?? 0) > 90)
        ? '<span class="food-card__popular"><i class="bi bi-fire"></i> Popular</span>'
        : '';

    return '<article class="food-card food-card--editorial" data-mask-reveal>
        <div class="food-card__media">
            <img src="' . e($item['image_url']) . '" alt="' . e($item['name']) . '" loading="lazy">
            <div class="food-card__overlay">
                ' . $bookBtn . '
            </div>
            <span class="food-card__category">' . e($category) . '</span>
            ' . $popular . '
        </div>
        <div class="food-card__body">
            <div class="food-card__head">
                <h5>' . e($item['name']) . '</h5>
                <span class="food-card__price">' . formatMoney((float) $item['price']) . '</span>
            </div>
            <p class="food-card__desc">' . e($item['description']) . '</p>
            <div class="food-card__footer">' . getDietaryBadges($item) . '</div>
        </div>
    </article>';
}

function renderServiceCardPremium(array $service, int $delay = 0): string
{
    static $index = 0;
    $html = renderServiceEditorialTile($service, $index);
    $index++;
    return $html;
}

function renderTestimonialSlide(array $testimonial, bool $active = false): string
{
    $activeClass = $active ? ' is-active' : '';
    return '<div class="testimonial-carousel__slide' . $activeClass . '">
        <article class="testimonial-card-premium">
            ' . renderStars((int) $testimonial['rating']) . '
            <p class="testimonial-quote">' . e($testimonial['quote']) . '</p>
            <div class="d-flex align-items-center gap-3">
                <img src="' . e($testimonial['image_url']) . '" alt="" class="rounded-circle" width="52" height="52" style="object-fit:cover">
                <div>
                    <strong>' . e($testimonial['customer_name']) . '</strong>
                    <small class="d-block text-muted">' . e($testimonial['event_type']) . '</small>
                </div>
            </div>
        </article>
    </div>';
}

function renderHomeAboutStrip(PDO $pdo): string
{
    $content = $pdo->query("SELECT content FROM website_content WHERE page_key = 'about'")->fetchColumn();
    if (!$content) {
        return '';
    }

    $galleryImage = $pdo->query('SELECT image_url FROM gallery WHERE is_featured = 1 ORDER BY sort_order LIMIT 1')->fetchColumn()
        ?: $pdo->query('SELECT image_url FROM menu_items WHERE is_active = 1 ORDER BY popularity_score DESC LIMIT 1')->fetchColumn();
    if (!$galleryImage) {
        return '';
    }

    $kitchenImage = $pdo->query('SELECT image_url FROM menu_items WHERE is_active = 1 ORDER BY popularity_score DESC LIMIT 1 OFFSET 1')->fetchColumn()
        ?: $galleryImage;
    $snippet = mb_strlen($content) > 280 ? mb_substr($content, 0, 277) . '…' : $content;

    return '<section class="section section--warm home-about-strip" id="about-splendid">
        <div class="container">
            <div class="about-editorial__grid">
                <div class="about-editorial__visual" data-parallax-layer="0.05">
                    <div class="about-editorial__main" data-mask-reveal>
                        <img src="' . e($galleryImage) . '" alt="Splendid Catering event" loading="lazy">
                    </div>
                    <div class="about-editorial__float" data-parallax-layer="-0.06" data-mask-reveal data-reveal-delay="120">
                        <img src="' . e($kitchenImage) . '" alt="Chef preparation" loading="lazy">
                    </div>
                </div>
                <div class="about-editorial__content">
                    <div data-reveal>
                        <span class="section-label">Our Story</span>
                        <h2 class="section-title section-title--editorial">Crafted With Passion, Served With Pride</h2>
                        <p class="lead-text">' . e($snippet) . '</p>
                    </div>
                    <a href="' . e(url('about')) . '" class="btn btn-outline-gold btn-interactive btn-arrow mt-4" data-reveal data-reveal-delay="120">Read Our Story <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </section>';
}

function renderHomeMenuSpecialties(PDO $pdo, int $limit = 4): string
{
    $limit = max(1, min(8, $limit));
    $items = $pdo->query('
        SELECT m.*, c.name AS category_name
        FROM menu_items m
        JOIN menu_categories c ON m.category_id = c.id
        WHERE m.is_active = 1
        ORDER BY m.popularity_score DESC
        LIMIT ' . $limit . '
    ')->fetchAll();

    $header = renderSectionHeader('Signature Menu', 'Menu Highlights', 'Popular dishes with live pricing from our kitchen catalogue');

    if (empty($items)) {
        return '<section class="section">
            <div class="container">
                ' . $header . '
                ' . renderPublicEmptyState(
                    'egg-fried',
                    'No menu items available yet',
                    'Check back soon or contact us to discuss a custom menu for your event.',
                    url('book'),
                    'Book Catering'
                ) . '
            </div>
        </section>';
    }

    $cards = '';
    foreach ($items as $i => $item) {
        $cards .= '<div class="col-md-6 col-lg-3" data-reveal data-reveal-delay="' . ($i * 70) . '">' . renderFoodCard($item) . '</div>';
    }

    return '<section class="section">
        <div class="container">
            ' . $header . '
            <div class="row g-4 stagger-grid">' . $cards . '</div>
            <div class="text-center mt-5" data-reveal>
                <a href="' . e(url('menu')) . '" class="btn btn-gold btn-interactive btn-arrow">Explore Full Menu <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </section>';
}

function renderHomePackagesPreview(PDO $pdo, int $limit = 3): string
{
    $limit = max(1, min(6, $limit));
    $packages = $pdo->query('SELECT * FROM packages WHERE is_active = 1 ORDER BY sort_order LIMIT ' . $limit)->fetchAll();
    $header = renderSectionHeader('Packages', 'Catering Packages', 'Per-guest pricing and inclusions from our live package catalogue');

    if (empty($packages)) {
        return '<section class="section section-alt" id="packages-preview">
            <div class="container">
                ' . $header . '
                ' . renderPublicEmptyState(
                    'box-seam',
                    'No packages available yet',
                    'Custom packages can be arranged for your event. Speak with our team to get started.',
                    url('book'),
                    'Request a Quote'
                ) . '
            </div>
        </section>';
    }

    $cards = '';
    foreach ($packages as $i => $p) {
        $features = json_decode($p['features'], true) ?: [];
        $cards .= '<div class="col-md-6 col-lg-4" data-reveal data-reveal-delay="' . ($i * 100) . '">
            <article class="package-card-premium package-card-premium--editorial">
                <span class="package-tier">' . e(ucfirst($p['tier'])) . '</span>
                <div class="package-card-premium__media">
                    <img src="' . e($p['image_url']) . '" alt="' . e($p['name']) . '" loading="lazy">
                    <div class="package-card-premium__hover">
                        <a href="' . e(url('book') . '?package=' . $p['slug']) . '" class="btn btn-gold btn-interactive">Select Package</a>
                    </div>
                </div>
                <div class="package-card-premium__body">
                    <h5>' . e($p['name']) . '</h5>
                    <div class="package-price">' . formatMoney((float) $p['price_per_guest']) . ' <small>/ guest</small></div>
                    <p class="text-muted small">' . e($p['description']) . '</p>
                    <ul class="package-features">';
        foreach (array_slice($features, 0, 3) as $f) {
            $cards .= '<li><i class="bi bi-check-circle-fill"></i> ' . e($f) . '</li>';
        }
        $cards .= '</ul>
                </div>
            </article>
        </div>';
    }

    return '<section class="section section-alt" id="packages-preview">
        <div class="container">
            ' . $header . '
            <div class="row g-4 justify-content-center stagger-grid">' . $cards . '</div>
            <div class="text-center mt-4" data-reveal>
                <a href="' . e(url('packages')) . '" class="btn btn-outline-gold btn-interactive">View All Packages</a>
            </div>
        </div>
    </section>';
}

function renderHomeOccasionsPreview(PDO $pdo, int $limit = 4): string
{
    $limit = max(1, min(8, $limit));
    $services = $pdo->query('SELECT * FROM service_types WHERE is_active = 1 ORDER BY sort_order LIMIT ' . $limit)->fetchAll();
    $header = renderSectionHeader('Occasions', 'Special Events We Cater', 'Weddings, corporate functions, and private celebrations from our active service list');

    if (empty($services)) {
        return '';
    }

    $tiles = '';
    foreach ($services as $i => $s) {
        $tiles .= renderServiceEditorialTile($s, $i);
    }

    return '<section class="section home-occasions" id="occasions">
        <div class="container">
            ' . $header . '
            <div class="row g-4">' . $tiles . '</div>
            <div class="text-center mt-4" data-reveal>
                <a href="' . e(url('services')) . '" class="btn btn-gold btn-interactive btn-arrow">All Services <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </section>';
}

function renderWhyChooseUs(PDO $pdo): string
{
    $stats = getPublicSiteStats($pdo);
    $menuCount = (int) $stats['menu_items'];
    $serviceCount = (int) $stats['service_types'];
    $eventsCount = (int) $stats['events_served'];
    $ratingNote = $stats['avg_rating'] > 0
        ? 'Verified client reviews average ' . number_format($stats['avg_rating'], 1) . ' stars.'
        : 'Client feedback is published on our testimonials page.';

    $pillars = [
        [
            'icon' => 'egg-fried',
            'title' => 'Live Menu Catalogue',
            'desc' => $menuCount > 0
                ? $menuCount . ' active menu items with pricing managed in our system.'
                : 'Menu items and pricing are managed in our live kitchen catalogue.',
        ],
        [
            'icon' => 'calendar-event',
            'title' => 'Event Booking Platform',
            'desc' => 'Online quotes, availability checks, and booking workflows connected to our database.',
        ],
        [
            'icon' => 'people',
            'title' => 'Multiple Service Lines',
            'desc' => $serviceCount > 0
                ? $serviceCount . ' catering service types available for weddings, corporate, and private events.'
                : 'Catering service types configured for a range of event formats.',
        ],
        [
            'icon' => 'stars',
            'title' => 'Transparent Operations',
            'desc' => $eventsCount > 0
                ? $eventsCount . ' completed bookings on record. ' . $ratingNote
                : $ratingNote,
        ],
    ];

    $cards = '';
    foreach ($pillars as $i => $p) {
        $cards .= '<div class="col-md-6 col-lg-3" data-reveal data-reveal-delay="' . ($i * 80) . '">
            <div class="feature-card feature-card--minimal why-choose-card text-center p-4 h-100">
                <div class="service-card-premium__icon mx-auto mb-3"><i class="bi bi-' . e($p['icon']) . '"></i></div>
                <h5 class="mt-1">' . e($p['title']) . '</h5>
                <p class="text-muted small mb-0">' . e($p['desc']) . '</p>
            </div>
        </div>';
    }

    return '<section class="section section--warm why-choose-section" id="why-choose-us">
        <div class="container">
            ' . renderSectionHeader('Why Splendid', 'Why Choose Us', 'Platform capabilities and live data behind every Splendid Catering experience') . '
            <div class="row g-4 stagger-grid">' . $cards . '</div>
        </div>
    </section>';
}

function renderHomeBookingTeaser(): string
{
    $loc = getBusinessLocation();

    return '<section class="section home-booking-teaser" id="book-contact">
        <div class="container">
            <div class="home-booking-teaser__panel" data-reveal>
                <div class="row g-4 align-items-center">
                    <div class="col-lg-7">
                        <span class="section-label">Book & Enquire</span>
                        <h2 class="section-title section-title--editorial mb-3">Start Your Catering Journey</h2>
                        <p class="text-muted mb-0">Use our online booking wizard for instant quotes, or reach our team directly for consultations. Office hours: <strong>' . e($loc['hours']) . '</strong>.</p>
                    </div>
                    <div class="col-lg-5">
                        <div class="d-flex flex-column flex-sm-row gap-2 justify-content-lg-end">
                            <a href="' . e(url('book')) . '" class="btn btn-gold btn-lg btn-interactive flex-grow-1"><i class="bi bi-calendar-check"></i> Book Catering</a>
                            <a href="' . e(url('contact')) . '" class="btn btn-outline-gold btn-lg btn-interactive flex-grow-1"><i class="bi bi-envelope"></i> Contact Us</a>
                        </div>
                        <p class="small text-muted mt-3 mb-0 text-lg-end"><i class="bi bi-geo-alt-fill text-gold me-1"></i>' . e($loc['address']) . '</p>
                    </div>
                </div>
            </div>
        </div>
    </section>';
}

function renderHomeGalleryPreview(PDO $pdo, int $limit = 7): string
{
    return renderHomeGalleryEditorial($pdo, $limit);
}

function renderHomeGalleryEditorial(PDO $pdo, int $limit = 7): string
{
    $limit = max(5, min(10, $limit));
    $items = $pdo->query('SELECT * FROM gallery ORDER BY is_featured DESC, sort_order LIMIT ' . $limit)->fetchAll();
    $header = renderSectionHeader('Gallery', 'Moments Worth Savoring', 'Real events, real food — a visual taste of the Splendid Catering experience');

    if (empty($items)) {
        return '<section class="section section-alt section--gallery-preview">
            <div class="container">
                ' . $header . '
                ' . renderPublicEmptyState(
                    'images',
                    'Gallery updates coming soon',
                    'Event photos will appear here as we capture more celebrations.',
                    url('book'),
                    'Book Your Event'
                ) . '
            </div>
        </section>';
    }

    $featured = array_shift($items);
    $featuredSlides = array_slice(
        array_merge([$featured], array_filter($items, fn($g) => !empty($g['is_featured']))),
        0,
        3
    );
    $slidesJson = htmlspecialchars(json_encode(array_map(static fn($g) => [
        'src' => resolveMediaUrl($g['image_url']),
        'title' => $g['title'],
        'category' => $g['category'],
    ], $featuredSlides)), ENT_QUOTES, 'UTF-8');

    $featuredHtml = '<article class="gallery-editorial__featured gallery-fx--ken-burns" data-home-gallery-featured data-slides="' . $slidesJson . '"
        data-lightbox data-src="' . e(resolveMediaUrl($featured['image_url'])) . '"
        data-title="' . e($featured['title']) . '" data-category="' . e($featured['category']) . '" data-cursor-label="View">
        <div class="gallery-editorial__featured-inner">
            <img src="' . e(resolveMediaUrl($featured['image_url'])) . '" alt="' . e($featured['title']) . '" class="gallery-editorial__featured-img is-active" loading="eager">
            <div class="gallery-editorial__featured-overlay">
                <span class="gallery-editorial__label">Featured</span>
                <h3>' . e($featured['title']) . '</h3>
                <p>' . e($featured['category']) . '</p>
                <span class="gallery-editorial__view">View Gallery <i class="bi bi-arrow-right"></i></span>
            </div>
        </div>
    </article>';

    $supportHtml = '';
    $effects = ['gallery-fx--slow-zoom', '', 'gallery-fx--float', 'gallery-fx--hover', 'gallery-fx--reveal', 'gallery-fx--hover-reveal', ''];
    foreach (array_slice($items, 0, 6) as $i => $g) {
        $fx = $effects[$i % count($effects)];
        $supportHtml .= '<article class="gallery-editorial__item ' . e($fx) . '" data-reveal data-reveal-delay="' . ($i * 50) . '"
            data-lightbox data-src="' . e(resolveMediaUrl($g['image_url'])) . '"
            data-title="' . e($g['title']) . '" data-category="' . e($g['category']) . '" data-cursor-label="View">
            <div class="gallery-editorial__thumb">
                <img src="' . e(resolveMediaUrl($g['image_url'])) . '" alt="' . e($g['title']) . '" loading="lazy">
                <div class="gallery-card-premium__overlay">
                    <h6>' . e($g['title']) . '</h6>
                    <small class="text-white-50">' . e($g['category']) . '</small>
                </div>
            </div>
        </article>';
    }

    return '<section class="section section-alt home-gallery-editorial" id="home-gallery">
        <div class="container">
            ' . $header . '
            <div class="gallery-editorial" data-reveal>
                ' . $featuredHtml . '
                <div class="gallery-editorial__support">' . $supportHtml . '</div>
            </div>
            <div class="text-center mt-4" data-reveal>
                <a href="' . e(url('gallery')) . '" class="btn btn-outline-gold btn-interactive btn-arrow">Explore Full Gallery <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </section>';
}

function renderHomeLocationPreview(): string
{
    return '<section class="section section--warm home-location-preview" id="visit-us">
        <div class="container">
            ' . renderSectionHeader('Visit Us', 'Find Our Location', 'Plan a visit or get directions to our Accra kitchen and event planning team.') . '
            <div class="row g-4 align-items-stretch">
                <div class="col-lg-5 d-flex flex-column gap-3">
                    ' . renderContactInfoCard(null, true) . '
                    <div data-reveal>' . renderDirectionsButton(null, 'btn btn-gold btn-interactive w-100') . '</div>
                </div>
                <div class="col-lg-7">
                    ' . renderBusinessLocationMap('homeBusinessMap', 'home', 15) . '
                </div>
            </div>
        </div>
    </section>';
}

function renderAuthLayout(string $mode, string $formHtml, PDO $pdo): string
{
    global $config;
    $heroImage = getAuthHeroImage($pdo);
    $isLogin = $mode === 'login';
    $title = $isLogin ? 'Welcome Back' : 'Create Your Account';
    $subtitle = $isLogin
        ? 'Sign in to manage bookings, menus, and your Splendid Catering account.'
        : 'Join Splendid Catering to book events and manage your celebrations.';
    $switchLabel = $isLogin ? "Don't have an account?" : 'Already have an account?';
    $switchUrl = $isLogin ? url('auth/register') : url('auth/login');
    $switchText = $isLogin ? 'Create Account' : 'Sign In';

    return '<div class="auth-split">
        <div class="auth-split__visual" aria-hidden="true">
            <div class="auth-split__visual-bg" data-auth-ken-burns style="background-image:url(\'' . e($heroImage) . '\')"></div>
            <div class="auth-split__visual-overlay"></div>
            <div class="auth-split__brand">
                <a href="' . e(url()) . '" class="auth-split__logo">
                    <span class="brand-icon"><i class="bi bi-cup-hot-fill"></i></span>
                    <span><strong>Splendid</strong> Catering</span>
                </a>
                <p class="auth-split__tagline">' . e($config['tagline']) . '</p>
            </div>
        </div>
        <div class="auth-split__panel">
            <div class="auth-split__panel-inner">
                <button type="button" class="btn btn-sm btn-outline-secondary auth-split__theme" id="themeToggleAuth" aria-label="Toggle theme">
                    <i class="bi bi-sun-fill theme-icon-light"></i>
                    <i class="bi bi-moon-stars-fill theme-icon-dark"></i>
                </button>
                <div class="auth-split__mobile-brand d-lg-none">
                    <span class="brand-icon"><i class="bi bi-cup-hot-fill"></i></span>
                    <strong>Splendid Catering</strong>
                </div>
                <div class="auth-split__mobile-visual d-lg-none">
                    <img src="' . e($heroImage) . '" alt="" loading="eager">
                </div>
                <div class="auth-form-wrap">
                    <span class="section-label">Account</span>
                    <h1 class="auth-form-wrap__title">' . e($title) . '</h1>
                    <p class="auth-form-wrap__subtitle text-muted">' . e($subtitle) . '</p>
                    ' . $formHtml . '
                    <p class="auth-form-wrap__switch text-center mt-4 mb-0">
                        ' . e($switchLabel) . '
                        <a href="' . e($switchUrl) . '" class="auth-form-wrap__switch-link">' . e($switchText) . ' <i class="bi bi-arrow-right"></i></a>
                    </p>
                </div>
            </div>
        </div>
    </div>';
}

function renderContactInfoCard(?array $location = null, bool $compact = false): string
{
    $loc = $location ?? getBusinessLocation();
    $compactClass = $compact ? ' contact-info-card--compact' : '';

    $items = [
        ['icon' => 'geo-alt-fill', 'label' => 'Address', 'value' => $loc['address']],
        ['icon' => 'telephone-fill', 'label' => 'Phone', 'value' => $loc['phone'], 'href' => 'tel:' . preg_replace('/\s+/', '', $loc['phone'])],
        ['icon' => 'envelope-fill', 'label' => 'Email', 'value' => $loc['email'], 'href' => 'mailto:' . $loc['email']],
        ['icon' => 'clock-fill', 'label' => 'Hours', 'value' => $loc['hours']],
    ];

    $html = '<div class="contact-info-card' . $compactClass . '">';
    foreach ($items as $item) {
        $valueHtml = !empty($item['href'])
            ? '<a href="' . e($item['href']) . '">' . e($item['value']) . '</a>'
            : e($item['value']);
        $html .= '<div class="contact-info-card__item" data-reveal>
            <span class="contact-info-card__icon"><i class="bi bi-' . e($item['icon']) . '"></i></span>
            <div>
                <span class="contact-info-card__label">' . e($item['label']) . '</span>
                <span class="contact-info-card__value">' . $valueHtml . '</span>
            </div>
        </div>';
    }
    $html .= '</div>';

    return $html;
}

function renderDirectionsButton(?array $location = null, string $class = 'btn btn-gold btn-lg btn-interactive'): string
{
    $loc = $location ?? getBusinessLocation();

    return '<a href="' . e(businessDirectionsUrl($loc)) . '" class="' . e($class) . ' location-directions-btn" target="_blank" rel="noopener noreferrer">
        <i class="bi bi-signpost-split-fill"></i> Get Directions
    </a>';
}

function renderBusinessMap(string $mapId, string $variant = 'default', int $zoom = 15): string
{
    return renderBusinessLocationMap($mapId, $variant, $zoom);
}

function renderBusinessLocationMap(string $mapId, string $variant = 'default', int $zoom = 15): string
{
    $loc = getBusinessLocation();
    $locationJson = htmlspecialchars(json_encode($loc, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8');

    return '<div class="location-map-block location-map-block--' . e($variant) . '" data-reveal>
        <div class="location-map-wrap" data-business-map data-map-id="' . e($mapId) . '" data-location="' . $locationJson . '" data-zoom="' . $zoom . '">
            <div id="' . e($mapId) . '" class="location-map" role="region" aria-label="Interactive map showing ' . e($loc['name']) . ' location"></div>
            <div class="location-map__loading" aria-hidden="true"><span class="spinner-border spinner-border-sm"></span> Loading map…</div>
        </div>
    </div>';
}

function renderAboutLocationSection(PDO $pdo, ?string $imageUrl = null): string
{
    $loc = getBusinessLocation();
    $blurb = $pdo->query("SELECT content FROM website_content WHERE page_key = 'contact'")->fetchColumn()
        ?: 'Visit our kitchen and event planning team in Accra.';
    $image = $imageUrl ?: getImageUrl(null);

    return '<section class="section section--warm location-about" id="our-location">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-5">
                    <div class="location-about__copy" data-reveal>
                        <span class="section-label">Find Us</span>
                        <h2 class="section-title section-title--editorial">Our Location</h2>
                        <p class="lead-text">' . e($blurb) . '</p>
                        <p class="text-muted mb-4"><i class="bi bi-geo-alt-fill text-gold me-2"></i>' . e($loc['address']) . '</p>
                        <div class="location-about__image image-frame mb-4" data-reveal data-reveal-delay="80">
                            <img src="' . e($image) . '" alt="Splendid Catering location" loading="lazy">
                        </div>
                        ' . renderDirectionsButton($loc, 'btn btn-gold btn-interactive btn-arrow') . '
                    </div>
                </div>
                <div class="col-lg-7">
                    ' . renderBusinessMap('aboutBusinessMap', 'about', 15) . '
                </div>
            </div>
        </div>
    </section>';
}

function renderContactLocationSection(): string
{
    return '<section class="section location-contact" id="find-us">
        <div class="container">
            ' . renderSectionHeader('Find Us', 'Contact Information & Directions', 'Have questions or want to book catering? Reach us directly or visit our location.') . '
            <div class="row g-5 align-items-stretch">
                <div class="col-lg-5 d-flex flex-column">
                    ' . renderContactInfoCard() . '
                    <div class="mt-4" data-reveal>
                        ' . renderDirectionsButton(null, 'btn btn-gold btn-lg btn-interactive w-100') . '
                    </div>
                </div>
                <div class="col-lg-7">
                    ' . renderBusinessMap('contactBusinessMap', 'contact', 16) . '
                </div>
            </div>
        </div>
    </section>';
}

function renderLeadershipCard(array $member, int $index = 0, bool $featured = false): string
{
    $delayBase = $index * 90;
    $featuredClass = $featured ? ' leadership-card--featured' : '';
    $socialHtml = '';

    if (!empty($member['linkedin'])) {
        $socialHtml .= '<a href="' . e($member['linkedin']) . '" class="leadership-card__social" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn profile"><i class="bi bi-linkedin"></i></a>';
    }
    if (!empty($member['instagram'])) {
        $socialHtml .= '<a href="' . e($member['instagram']) . '" class="leadership-card__social" target="_blank" rel="noopener noreferrer" aria-label="Instagram profile"><i class="bi bi-instagram"></i></a>';
    }

    $quoteHtml = !empty($member['quote'])
        ? '<blockquote class="leadership-card__quote" data-reveal data-reveal-delay="' . ($delayBase + 200) . '">&ldquo;' . e($member['quote']) . '&rdquo;</blockquote>'
        : '';

    $socialBlock = $socialHtml !== ''
        ? '<div class="leadership-card__socials" data-reveal data-reveal-delay="' . ($delayBase + 280) . '">' . $socialHtml . '</div>'
        : '';

    return '<article class="leadership-card' . $featuredClass . '">
        <div class="leadership-card__media" data-reveal data-reveal-delay="' . ($delayBase + 60) . '">
            <img src="' . e($member['image']) . '" alt="' . e($member['name']) . '" loading="lazy">
            <div class="leadership-card__media-overlay"></div>
            ' . $socialBlock . '
        </div>
        <div class="leadership-card__body">
            <h3 class="leadership-card__name" data-reveal data-reveal-delay="' . ($delayBase + 120) . '">' . e($member['name']) . '</h3>
            <p class="leadership-card__title" data-reveal data-reveal-delay="' . ($delayBase + 160) . '">' . e($member['title']) . '</p>
            ' . $quoteHtml . '
            <p class="leadership-card__bio" data-reveal data-reveal-delay="' . ($delayBase + 240) . '">' . e($member['bio']) . '</p>
        </div>
    </article>';
}

function renderLeadershipSection(PDO $pdo, ?string $foodImage = null): string
{
    $team = getLeadershipTeam($pdo);
    $heading = renderSectionHeader(
        'Our Leadership',
        'Meet The People Behind The Brand',
        'The professionals guiding our culinary vision, operations, and guest experience'
    );

    if (empty($team)) {
        return '<section class="section section-alt leadership-section" id="leadership">
            <div class="container">
                ' . $heading . '
                <div class="leadership-placeholder" data-reveal>
                    <div class="leadership-placeholder__visual">
                        <div class="leadership-placeholder__icon"><i class="bi bi-person-badge"></i></div>
                    </div>
                    <div class="leadership-placeholder__content">
                        <h3>Meet The Team Behind Splendid Catering</h3>
                        <p class="text-muted mb-3">Founder and leadership profiles will appear here with professional portraits, titles, and biographies as soon as they are published.</p>
                        <div class="leadership-placeholder__fields">
                            <span>Portrait</span><span>Name</span><span>Title</span><span>Story</span>
                        </div>
                        <a href="' . e(url('contact')) . '" class="btn btn-outline-gold btn-interactive mt-3">Contact Our Team</a>
                    </div>
                </div>
            </div>
        </section>';
    }

    $count = count($team);
    $featuredIndex = 0;
    foreach ($team as $i => $member) {
        if (!empty($member['featured'])) {
            $featuredIndex = $i;
            break;
        }
    }

    if ($count === 1 || ($count > 1 && !empty($team[$featuredIndex]['featured']))) {
        $member = $team[$featuredIndex];
        $others = array_values(array_filter($team, static fn($m, $i) => $i !== $featuredIndex, ARRAY_FILTER_USE_BOTH));
        $othersHtml = '';
        if (!empty($others)) {
            $othersHtml = '<div class="leadership-team-grid mt-5"><div class="row g-4">';
            foreach ($others as $i => $other) {
                $othersHtml .= '<div class="col-md-6 col-lg-4">' . renderLeadershipCard($other, $i + 1) . '</div>';
            }
            $othersHtml .= '</div></div>';
        }

        $foodSide = $foodImage && empty($others)
            ? '<div class="col-lg-5" data-reveal data-reveal-delay="120">
                <div class="leadership-food-frame image-frame">
                    <img src="' . e(resolveMediaUrl($foodImage)) . '" alt="Catering excellence" loading="lazy">
                </div>
            </div>'
            : '';

        return '<section class="section section-alt leadership-section" id="leadership">
            <div class="container">
                ' . $heading . '
                <div class="row g-5 align-items-start">
                    <div class="' . ($foodSide ? 'col-lg-7' : 'col-12') . '">
                        ' . renderLeadershipCard($member, 0, true) . '
                    </div>
                    ' . $foodSide . '
                </div>
                ' . $othersHtml . '
            </div>
        </section>';
    }

    $cards = '';
    foreach ($team as $i => $member) {
        $cards .= '<div class="col-md-6 col-lg-4">' . renderLeadershipCard($member, $i) . '</div>';
    }

    return '<section class="section section-alt leadership-section" id="leadership">
        <div class="container">
            ' . $heading . '
            <div class="row g-4">' . $cards . '</div>
        </div>
    </section>';
}

function renderAboutStatsStrip(PDO $pdo): string
{
    $stats = getPublicSiteStats($pdo);
    $ratingDisplay = $stats['avg_rating'] > 0 ? number_format($stats['avg_rating'], 1) . '★' : '—';

    return '<div class="about-editorial__stats stagger-grid">
        <div class="stat-card-premium" data-reveal>
            <div class="stat-value" data-counter="' . (int) $stats['events_served'] . '" data-counter-suffix="+">0</div>
            <div class="stat-label">Events Served</div>
        </div>
        <div class="stat-card-premium" data-reveal data-reveal-delay="80">
            <div class="stat-value" data-counter="' . (int) $stats['menu_items'] . '" data-counter-suffix="+">0</div>
            <div class="stat-label">Menu Items</div>
        </div>
        <div class="stat-card-premium" data-reveal data-reveal-delay="160">
            <div class="stat-value" data-counter="' . (int) $stats['service_types'] . '" data-counter-suffix="+">0</div>
            <div class="stat-label">Service Types</div>
        </div>
        <div class="stat-card-premium" data-reveal data-reveal-delay="240">
            <div class="stat-value">' . e($ratingDisplay) . '</div>
            <div class="stat-label">Client Rating</div>
        </div>
    </div>';
}

function renderAboutFoodSpotlight(string $imageUrl, string $alt = 'Culinary craftsmanship'): string
{
    return '<section class="section about-food-spotlight">
        <div class="container">
            <div class="about-food-spotlight__grid" data-reveal>
                <div class="about-food-spotlight__copy">
                    <span class="section-label">Our Craft</span>
                    <h2 class="section-title section-title--editorial">The Food Behind Every Celebration</h2>
                    <p class="lead-text mb-0">From intimate gatherings to grand events, every dish reflects the care, creativity, and standards our kitchen is known for.</p>
                </div>
                <div class="about-food-spotlight__media image-frame">
                    <img src="' . e($imageUrl) . '" alt="' . e($alt) . '" loading="lazy">
                </div>
            </div>
        </div>
    </section>';
}

function getGalleryEffectClass(int $index, array $item, ?array $nextItem = null): string
{
    $effects = [
        'gallery-fx--slow-zoom',
        '',
        'gallery-fx--float',
        'gallery-fx--hover',
        'gallery-fx--reveal',
        'gallery-fx--ken-burns',
        '',
        'gallery-fx--hover-reveal',
    ];
    $class = $effects[$index % count($effects)];

    if (!empty($item['is_featured']) && $nextItem && !empty($nextItem['image_url'])) {
        $class .= ' gallery-fx--crossfade';
    }

    return trim($class);
}

function renderGalleryGrid(array $gallery): string
{
    if (empty($gallery)) {
        return '';
    }

    $html = '<div class="gallery-grid gallery-grid--compact stagger-grid">';
    foreach ($gallery as $i => $g) {
        $next = $gallery[$i + 1] ?? null;
        $fxClass = getGalleryEffectClass($i, $g, $next);
        $revealAttr = str_contains($fxClass, 'gallery-fx--reveal') ? ' data-gallery-reveal' : '';
        $crossfadeSecondary = (!empty($g['is_featured']) && $next && !empty($next['image_url']))
            ? '<img src="' . e($next['image_url']) . '" alt="" class="gallery-card-premium__img gallery-card-premium__img--alt" loading="lazy" aria-hidden="true">'
            : '';

        $html .= '<div class="gallery-item ' . e($fxClass) . '"
                 data-reveal data-reveal-delay="' . (($i % 4) * 60) . '"
                 data-gallery-category="' . e($g['category']) . '"
                 data-cursor-label="View"
                 data-lightbox
                 data-src="' . e($g['image_url']) . '"
                 data-title="' . e($g['title']) . '"
                 data-category="' . e($g['category']) . '"' . $revealAttr . '>
            <article class="gallery-card-premium">
                <div class="gallery-card-premium__media">
                    <img src="' . e($g['image_url']) . '" alt="' . e($g['title']) . '" class="gallery-card-premium__img gallery-card-premium__img--primary" loading="lazy">
                    ' . $crossfadeSecondary . '
                </div>
                <div class="gallery-card-premium__overlay">
                    <h6>' . e($g['title']) . '</h6>
                    <small class="text-white-50">' . e($g['category']) . '</small>
                </div>
                <span class="gallery-card-premium__zoom" aria-hidden="true"><i class="bi bi-zoom-in"></i></span>
            </article>
        </div>';
    }
    $html .= '</div>';

    return $html;
}
