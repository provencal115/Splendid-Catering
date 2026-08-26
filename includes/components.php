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

function renderCinematicHomeHero(array $siteStats, string $ratingDisplay, array $slideshowImages): string
{
    $slidesHtml = '';
    foreach ($slideshowImages as $i => $slide) {
        $active = $i === 0 ? ' is-active' : '';
        $slidesHtml .= '<div class="hero-cinematic__slide' . $active . '" style="background-image:url(\'' . e($slide['url']) . '\')" role="img" aria-label="' . e($slide['alt']) . '"></div>';
    }

    return '<section class="hero-section hero-section--home hero-cinematic">
        <div class="hero-cinematic__slides" data-cinematic-hero>' . $slidesHtml . '</div>
        <div class="hero-cinematic__overlay" aria-hidden="true"></div>
        <div class="hero-glow hero-glow--left" aria-hidden="true"></div>
        <div class="hero-glow hero-glow--right" aria-hidden="true"></div>
        <div class="container hero-content">
            <div class="row align-items-center g-5">
                <div class="col-lg-7">
                    <div class="hero-animate hero-animate--cinematic">
                        <p class="hero-eyebrow">Premium Catering · Accra, Ghana</p>
                        <h1 class="hero-title hero-title--editorial">Crafting <em>Extraordinary</em> Culinary Experiences</h1>
                        <p class="hero-subtitle">Chef-crafted menus, seamless booking, and flawless execution for weddings, corporate events, and celebrations worth remembering.</p>
                        <div class="d-flex flex-wrap gap-3 hero-actions">
                            <a href="' . e(url('book')) . '" class="btn btn-gold btn-lg btn-interactive btn-arrow"><i class="bi bi-calendar-check"></i> Book Your Event</a>
                            <a href="' . e(url('menu')) . '" class="btn btn-outline-light btn-lg btn-interactive btn-arrow">Explore Menu <i class="bi bi-arrow-right"></i></a>
                        </div>
                        <div class="hero-stats">
                            <div class="hero-stat"><strong data-counter="' . (int) $siteStats['events_served'] . '" data-counter-suffix="+">0</strong><span>Events Served</span></div>
                            <div class="hero-stat"><strong>' . e($ratingDisplay) . '</strong><span>Client Rating</span></div>
                            <div class="hero-stat"><strong data-counter="' . (int) $siteStats['service_types'] . '">0</strong><span>Service Types</span></div>
                            <div class="hero-stat"><strong data-counter="' . (int) $siteStats['menu_items'] . '" data-counter-suffix="+">0</strong><span>Menu Items</span></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 d-none d-lg-block">
                    <div class="ai-panel mt-4" data-mask-reveal data-reveal-delay="300">
                        <h4><i class="bi bi-robot"></i> AI Event Planner</h4>
                        <p class="mb-3 opacity-75">Get instant recommendations for your event</p>
                        <div class="row g-2">
                            <div class="col-6"><input type="number" id="aiBudget" class="form-control" placeholder="Budget (₵)"></div>
                            <div class="col-6"><input type="number" id="aiGuests" class="form-control" placeholder="Guests"></div>
                        </div>
                        <button id="aiBudgetBtn" class="btn btn-light btn-sm mt-2 w-100 btn-interactive">Get AI Recommendation</button>
                        <div id="aiBudgetResult" class="ai-suggestion"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="hero-cinematic__scroll" aria-hidden="true"><span></span></div>
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
                        <span class="section-label">Signature Dishes</span>
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

    return '<section class="section section--warm home-about-strip">
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

    $header = renderSectionHeader('From Our Kitchen', 'Menu Highlights', 'Popular dishes loved by our clients');

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

function renderHomeGalleryPreview(PDO $pdo, int $limit = 4): string
{
    $limit = max(1, min(8, $limit));
    $items = $pdo->query('SELECT * FROM gallery ORDER BY is_featured DESC, sort_order LIMIT ' . $limit)->fetchAll();
    $header = renderSectionHeader('Gallery', 'Moments Worth Savoring', 'A glimpse into our events, tablescapes, and culinary artistry');

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

    $grid = '<div class="gallery-grid gallery-grid--preview stagger-grid">';
    foreach ($items as $i => $g) {
        $grid .= '<div class="gallery-item" data-reveal data-reveal-delay="' . ($i * 60) . '"
                 data-lightbox
                 data-src="' . e($g['image_url']) . '"
                 data-title="' . e($g['title']) . '"
                 data-category="' . e($g['category']) . '"
                 data-cursor-label="View">
            <article class="gallery-card-premium">
                <div class="gallery-card-premium__media">
                    <img src="' . e($g['image_url']) . '" alt="' . e($g['title']) . '" loading="lazy">
                </div>
                <div class="gallery-card-premium__overlay">
                    <h6>' . e($g['title']) . '</h6>
                    <small class="text-white-50">' . e($g['category']) . '</small>
                </div>
                <span class="gallery-card-premium__zoom" aria-hidden="true"><i class="bi bi-zoom-in"></i></span>
            </article>
        </div>';
    }
    $grid .= '</div>';

    return '<section class="section section-alt section--gallery-preview">
        <div class="container">
            ' . $header . '
            ' . $grid . '
            <div class="text-center mt-4" data-reveal>
                <a href="' . e(url('gallery')) . '" class="btn btn-outline-gold btn-interactive btn-arrow">View Full Gallery <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </section>';
}
