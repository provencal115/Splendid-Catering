<?php
$input = json_decode(file_get_contents('php://input'), true) ?: [];
$budget = (float) ($input['budget'] ?? 0);
$guests = (int) ($input['guests'] ?? 0);

if (!$budget || !$guests) {
    jsonResponse(['success' => false, 'message' => 'Please enter budget and guest count']);
}

$perGuestBudget = $budget / $guests;
$packages = $pdo->query('SELECT * FROM packages WHERE is_active = 1 ORDER BY price_per_guest')->fetchAll();

$recommended = null;
foreach ($packages as $pkg) {
    if ($pkg['price_per_guest'] <= $perGuestBudget * 1.1) {
        $recommended = $pkg;
    }
}

if (!$recommended) {
    $recommended = $packages[0];
    $message = "Your budget of " . formatMoney($budget) . " for {$guests} guests (₵" . number_format($perGuestBudget, 2) . "/guest) is below our packages. We recommend starting with our Basic Package and customizing.";
} else {
    $total = calculateQuotation((float)$recommended['price_per_guest'], $guests);
    $message = "Based on your budget of " . formatMoney($budget) . " for {$guests} guests, we recommend the <strong>{$recommended['name']}</strong> at " . formatMoney((float)$recommended['price_per_guest']) . "/guest. Estimated total: <strong>" . formatMoney($total['total_amount']) . "</strong> (including tax).";
}

$features = json_decode($recommended['features'], true) ?: [];
$featureList = implode('', array_map(fn($f) => "<li>{$f}</li>", array_slice($features, 0, 4)));

$html = "<p>{$message}</p><ul class='mb-2'>{$featureList}</ul><a href='" . url('book') . "?package={$recommended['slug']}' class='btn btn-light btn-sm'>Book {$recommended['name']}</a>";

jsonResponse(['success' => true, 'message' => strip_tags($message), 'html' => $html, 'package' => $recommended['name']]);
