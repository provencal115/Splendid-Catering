<?php
$input = json_decode(file_get_contents('php://input'), true) ?: [];
$budget = (float) ($input['budget'] ?? 0);
$guests = (int) ($input['guests'] ?? 0);
$eventType = $input['event_type'] ?? 'wedding';
$preferences = $input['preferences'] ?? [];

$sql = 'SELECT * FROM menu_items WHERE is_active = 1';
$params = [];

if (in_array('vegetarian', $preferences)) $sql .= ' AND is_vegetarian = 1';
if (in_array('vegan', $preferences)) $sql .= ' AND is_vegan = 1';
if (in_array('halal', $preferences)) $sql .= ' AND is_halal = 1';

$sql .= ' ORDER BY popularity_score DESC LIMIT 8';
$items = $pdo->query($sql)->fetchAll();

if (empty($items)) {
    $items = $pdo->query('SELECT * FROM menu_items WHERE is_active = 1 ORDER BY popularity_score DESC LIMIT 8')->fetchAll();
}

$perGuest = $guests > 0 ? $budget / $guests : 0;
$suggestions = [];

// AI logic: pick items based on event type
$starters = array_filter($items, fn($i) => in_array($i['category_id'], [1, 7]));
$mains = array_filter($items, fn($i) => in_array($i['category_id'], [2, 4, 5, 9, 10]));
$desserts = array_filter($items, fn($i) => $i['category_id'] == 3);

$pick = fn($arr, $n) => array_slice(array_values($arr), 0, $n);

$menu = [
    'Starters' => $pick($starters ?: $items, 2),
    'Main Course' => $pick($mains ?: $items, 3),
    'Desserts' => $pick($desserts ?: $items, 1),
];

$html = "<p><strong>AI Menu for " . ucfirst(str_replace('-', ' ', $eventType)) . "</strong> — {$guests} guests, budget " . formatMoney($budget) . "</p>";
$html .= "<div class='row g-2'>";

foreach ($menu as $course => $courseItems) {
    $html .= "<div class='col-md-4'><strong>{$course}</strong><ul class='small mb-0'>";
    foreach ($courseItems as $item) {
        $html .= "<li>" . e($item['name']) . " — " . formatMoney((float)$item['price']) . "</li>";
    }
    $html .= "</ul></div>";
}

$html .= "</div>";
$html .= "<p class='small mt-2 mb-0 opacity-75'><i class='bi bi-lightbulb'></i> Tip: Add our " . ($eventType === 'wedding' ? 'Champagne toast' : 'beverage package') . " for a complete experience!</p>";

jsonResponse(['success' => true, 'html' => $html]);
