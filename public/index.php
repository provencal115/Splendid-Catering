<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/') ?: '/';
$method = $_SERVER['REQUEST_METHOD'];

// Remove base path if running in subdirectory
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
if ($scriptName !== '/' && str_starts_with($uri, $scriptName)) {
    $uri = substr($uri, strlen($scriptName)) ?: '/';
}

$routes = [
    'GET' => [
        '/' => 'pages/home.php',
        '/about' => 'pages/about.php',
        '/services' => 'pages/services.php',
        '/packages' => 'pages/packages.php',
        '/menu' => 'pages/menu.php',
        '/gallery' => 'pages/gallery.php',
        '/book' => 'pages/book.php',
        '/testimonials' => 'pages/testimonials.php',
        '/contact' => 'pages/contact.php',
        '/auth/login' => 'auth/login.php',
        '/auth/register' => 'auth/register.php',
        '/auth/logout' => 'auth/logout.php',
        '/dashboard/admin' => 'dashboards/admin/index.php',
        '/dashboard/customer' => 'dashboards/customer/index.php',
        '/dashboard/events' => 'dashboards/event-manager/index.php',
        '/dashboard/kitchen' => 'dashboards/kitchen/index.php',
        '/dashboard/delivery' => 'dashboards/delivery/index.php',
        '/dashboard/finance' => 'dashboards/finance/index.php',
        '/setup' => 'setup.php',
    ],
    'POST' => [
        '/auth/login' => 'auth/login.php',
        '/auth/register' => 'auth/register.php',
        '/api/quotation' => 'api/quotation.php',
        '/api/booking' => 'api/booking.php',
        '/api/payment' => 'api/payment.php',
        '/api/ai-assistant' => 'api/ai-assistant.php',
        '/api/ai-budget' => 'api/ai-budget.php',
        '/api/ai-menu' => 'api/ai-menu.php',
        '/api/ai-quantity' => 'api/ai-quantity.php',
        '/api/chat' => 'api/chat.php',
        '/api/review' => 'api/review.php',
        '/api/admin-action' => 'api/admin-action.php',
        '/api/kitchen-action' => 'api/kitchen-action.php',
        '/api/delivery-action' => 'api/delivery-action.php',
        '/api/finance-action' => 'api/finance-action.php',
        '/api/event-action' => 'api/event-action.php',
        '/contact' => 'pages/contact.php',
    ],
    'GET_API' => [
        '/api/availability' => 'api/availability.php',
        '/api/ai-forecast' => 'api/ai-forecast.php',
        '/api/invoice' => 'api/invoice.php',
    ],
];

// Merge GET API routes
foreach ($routes['GET_API'] as $path => $file) {
    $routes['GET'][$path] = $file;
}

$file = $routes[$method][$uri] ?? null;

if (!$file) {
    http_response_code(404);
    include ROOT_PATH . '/pages/404.php';
    exit;
}

$filePath = ROOT_PATH . '/' . $file;
if (!file_exists($filePath)) {
    http_response_code(500);
    echo 'Route file not found: ' . e($file);
    exit;
}

include $filePath;
