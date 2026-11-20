<?php

declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function formatMoney(float $amount): string
{
    global $config;
    return $config['currency_symbol'] . number_format($amount, 2);
}

function generateReference(string $prefix): string
{
    return $prefix . date('Y') . str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT);
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function isPost(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function jsonResponse(array $data, int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return BASE_URL . '/assets/' . ltrim($path, '/');
}

function api_url(string $endpoint): string
{
    return BASE_URL . '/api/' . ltrim($endpoint, '/');
}

function csrfToken(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(?string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">';
}

function calculateTax(float $subtotal): float
{
    global $config;
    return round($subtotal * $config['tax_rate'], 2);
}

function calculateQuotation(float $pricePerGuest, int $guests, float $discount = 0): array
{
    $subtotal = round($pricePerGuest * $guests, 2);
    $discountAmount = round($discount, 2);
    $taxable = max(0, $subtotal - $discountAmount);
    $tax = calculateTax($taxable);
    $total = $taxable + $tax;

    return [
        'subtotal' => $subtotal,
        'discount_amount' => $discountAmount,
        'tax_amount' => $tax,
        'total_amount' => $total,
        'deposit' => round($total * 0.30, 2),
    ];
}

function getStatusBadge(string $status): string
{
    global $config;
    $statuses = $config['booking_statuses'];
    $info = $statuses[$status] ?? ['label' => ucfirst($status), 'color' => 'secondary'];
    return '<span class="badge bg-' . e($info['color']) . '">' . e($info['label']) . '</span>';
}

function timeAgo(string $datetime): string
{
    $time = strtotime($datetime);
    $diff = time() - $time;

    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return date('M j, Y', $time);
}

function paginate(PDO $pdo, string $sql, array $params, int $page, int $perPage = 10): array
{
    $countSql = preg_replace('/SELECT .+ FROM/i', 'SELECT COUNT(*) FROM', $sql, 1);
    $countSql = preg_replace('/ORDER BY .+$/i', '', $countSql);

    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $total = (int) $stmt->fetchColumn();

    $offset = ($page - 1) * $perPage;
    $sql .= " LIMIT {$perPage} OFFSET {$offset}";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll();

    return [
        'items' => $items,
        'total' => $total,
        'page' => $page,
        'per_page' => $perPage,
        'total_pages' => (int) ceil($total / $perPage),
    ];
}

function notify(PDO $pdo, int $userId, string $type, string $title, string $message, ?string $link = null): void
{
    $stmt = $pdo->prepare('INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$userId, $type, $title, $message, $link]);
}

function getDashboardStats(PDO $pdo): array
{
    $stats = [];

    $stats['total_customers'] = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
    $stats['total_bookings'] = (int) $pdo->query('SELECT COUNT(*) FROM bookings')->fetchColumn();
    $stats['total_revenue'] = (float) $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'completed'")->fetchColumn();
    $stats['upcoming_events'] = (int) $pdo->query("SELECT COUNT(*) FROM bookings WHERE event_date >= CURDATE() AND status NOT IN ('cancelled', 'completed')")->fetchColumn();
    $stats['today_deliveries'] = (int) $pdo->query("SELECT COUNT(*) FROM delivery_tasks WHERE DATE(pickup_time) = CURDATE()")->fetchColumn();
    $stats['pending_payments'] = (int) $pdo->query("SELECT COUNT(*) FROM bookings WHERE payment_status IN ('unpaid', 'partial')")->fetchColumn();

    $avgRating = $pdo->query('SELECT COALESCE(AVG(rating), 0) FROM reviews WHERE is_approved = 1')->fetchColumn();
    $stats['customer_satisfaction'] = round((float) $avgRating, 1);

    $popularPackage = $pdo->query('
        SELECT p.name, COUNT(b.id) as cnt FROM bookings b
        JOIN packages p ON b.package_id = p.id
        GROUP BY p.id ORDER BY cnt DESC LIMIT 1
    ')->fetch();
    $stats['popular_package'] = $popularPackage['name'] ?? 'N/A';

    $popularItem = $pdo->query('
        SELECT m.name, m.popularity_score FROM menu_items m
        ORDER BY m.popularity_score DESC LIMIT 1
    ')->fetch();
    $stats['popular_menu_item'] = $popularItem['name'] ?? 'N/A';

    return $stats;
}

function getDietaryBadges(array $item): string
{
    $badges = [];
    if ($item['is_vegan']) $badges[] = '<span class="badge diet-badge vegan">Vegan</span>';
    elseif ($item['is_vegetarian']) $badges[] = '<span class="badge diet-badge vegetarian">Vegetarian</span>';
    if ($item['is_halal']) $badges[] = '<span class="badge diet-badge halal">Halal</span>';
    if ($item['is_gluten_free']) $badges[] = '<span class="badge diet-badge gluten-free">Gluten-Free</span>';
    return implode(' ', $badges);
}

function renderStars(int $rating): string
{
    $html = '<div class="star-rating">';
    for ($i = 1; $i <= 5; $i++) {
        $html .= $i <= $rating ? '<i class="bi bi-star-fill"></i>' : '<i class="bi bi-star"></i>';
    }
    $html .= '</div>';
    return $html;
}

function getImageUrl(?string $url, string $fallback = ''): string
{
    if ($url) return $url;
    return $fallback ?: 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=600&q=80';
}

function roleDashboardUrl(string $role): string
{
    return match ($role) {
        'admin' => url('dashboard/admin'),
        'customer' => url('dashboard/customer'),
        'event_manager' => url('dashboard/events'),
        'chef' => url('dashboard/kitchen'),
        'delivery' => url('dashboard/delivery'),
        'finance' => url('dashboard/finance'),
        default => url(),
    };
}

function getBusinessLocation(): array
{
    global $config;

    return [
        'name' => $config['name'],
        'address' => $config['business']['address'],
        'latitude' => (float) $config['business']['latitude'],
        'longitude' => (float) $config['business']['longitude'],
        'phone' => $config['support_phone'],
        'email' => $config['support_email'],
        'hours' => $config['business']['hours'],
    ];
}

function businessDirectionsUrl(?array $location = null): string
{
    $loc = $location ?? getBusinessLocation();

    if ($loc['latitude'] && $loc['longitude']) {
        return 'https://www.google.com/maps/dir/?api=1&destination='
            . rawurlencode($loc['latitude'] . ',' . $loc['longitude']);
    }

    return 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode($loc['address']);
}

function resolveMediaUrl(?string $url): string
{
    if (!$url) {
        return '';
    }

    $url = trim($url);
    if (preg_match('#^https?://#i', $url) || str_starts_with($url, '//')) {
        return $url;
    }

    if (str_starts_with($url, '/')) {
        return rtrim(BASE_URL, '/') . $url;
    }

    return asset(ltrim($url, '/'));
}

function getLeadershipTeam(?PDO $pdo = null): array
{
    global $config;

    $team = $config['leadership'] ?? [];
    if (!is_array($team)) {
        $team = [];
    }

    if (empty($team) && $pdo instanceof PDO) {
        $json = $pdo->query("SELECT content FROM website_content WHERE page_key = 'leadership' LIMIT 1")->fetchColumn();
        if (is_string($json) && $json !== '') {
            $decoded = json_decode($json, true);
            if (is_array($decoded)) {
                $team = $decoded;
            }
        }
    }

    $team = array_values(array_filter($team, static function ($member): bool {
        if (!is_array($member)) {
            return false;
        }

        return trim($member['name'] ?? '') !== ''
            && trim($member['title'] ?? '') !== ''
            && trim($member['bio'] ?? '') !== ''
            && trim($member['image'] ?? '') !== '';
    }));

    foreach ($team as &$member) {
        $member['image'] = resolveMediaUrl($member['image']);
        $member['featured'] = !empty($member['featured']);
    }
    unset($member);

    return $team;
}

function getAuthHeroImage(PDO $pdo): string
{
    $image = $pdo->query('SELECT image_url FROM gallery WHERE is_featured = 1 ORDER BY sort_order LIMIT 1')->fetchColumn()
        ?: $pdo->query('SELECT image_url FROM menu_items WHERE is_active = 1 ORDER BY popularity_score DESC LIMIT 1')->fetchColumn();

    return resolveMediaUrl($image ?: 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=1600&q=80');
}

/**
 * Home hero video + poster (local assets with database poster fallback).
 *
 * Place final files in public/assets/videos/:
 * - hero-catering.mp4 (required for video playback)
 * - hero-catering.webm (optional)
 * - hero-catering-poster.jpg (optional; uses gallery image if missing)
 */
function getHomeHeroMedia(PDO $pdo): array
{
    $baseDir = ROOT_PATH . '/public/assets/videos';
    $mp4File = $baseDir . '/hero-catering.mp4';
    $webmFile = $baseDir . '/hero-catering.webm';
    $posterFile = $baseDir . '/hero-catering-poster.jpg';

    $dbPoster = $pdo->query('SELECT image_url FROM gallery WHERE is_featured = 1 ORDER BY sort_order LIMIT 1')->fetchColumn()
        ?: $pdo->query('SELECT image_url FROM menu_items WHERE is_active = 1 ORDER BY popularity_score DESC LIMIT 1')->fetchColumn();

    $posterUrl = file_exists($posterFile)
        ? asset('videos/hero-catering-poster.jpg')
        : resolveMediaUrl($dbPoster ?: 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=1920&q=80');

    return [
        'has_video' => file_exists($mp4File),
        'mp4' => file_exists($mp4File) ? asset('videos/hero-catering.mp4') : null,
        'webm' => file_exists($webmFile) ? asset('videos/hero-catering.webm') : null,
        'poster' => $posterUrl,
    ];
}
