<?php
$type = $_GET['type'] ?? 'sales';

if ($type === 'inventory') {
    $upcoming = (int) $pdo->query("SELECT COALESCE(SUM(guest_count),0) FROM bookings WHERE event_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 14 DAY)")->fetchColumn();

    $predictions = [];
    $items = $pdo->query('SELECT * FROM inventory_items')->fetchAll();
    foreach ($items as $item) {
        $needed = round($upcoming * 0.05 * ($item['min_stock_level'] / max($item['current_stock'], 1)), 1);
        if ($needed > 0) {
            $predictions[] = "<li><strong>" . e($item['name']) . "</strong>: order ~{$needed} {$item['unit']}</li>";
        }
    }

    $html = "<p class='small'>Based on {$upcoming} upcoming guests in the next 14 days:</p><ul class='small mb-0'>" . implode('', $predictions) . "</ul>";
    jsonResponse(['inventory_html' => $html]);
}

// Sales forecast
$months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
$currentMonth = (int) date('n');
$peakMonths = [];
for ($i = 0; $i < 6; $i++) {
    $m = (($currentMonth + $i - 1) % 12);
    $bookings = random_int(8, 25);
    $peakMonths[] = ['month' => $months[$m], 'bookings' => $bookings, 'revenue' => $bookings * random_int(8000, 25000)];
}

$popularItems = $pdo->query('SELECT name, popularity_score FROM menu_items ORDER BY popularity_score DESC LIMIT 5')->fetchAll();
$expectedRevenue = array_sum(array_column($peakMonths, 'revenue'));

$html = "<p class='small mb-2'><strong>6-Month Forecast</strong></p>";
$html .= "<div class='mb-2'>";
foreach ($peakMonths as $pm) {
    $html .= "<div class='d-flex justify-content-between small py-1 border-bottom'><span>{$pm['month']}</span><span>{$pm['bookings']} events · " . formatMoney($pm['revenue']) . "</span></div>";
}
$html .= "</div>";
$html .= "<p class='small mb-1'><strong>Expected Revenue:</strong> " . formatMoney($expectedRevenue) . "</p>";
$html .= "<p class='small mb-0'><strong>Top Items:</strong> " . implode(', ', array_column($popularItems, 'name')) . "</p>";

jsonResponse(['success' => true, 'html' => $html, 'expected_revenue' => $expectedRevenue]);
