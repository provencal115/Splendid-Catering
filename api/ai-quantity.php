<?php
$input = json_decode(file_get_contents('php://input'), true) ?: [];
$guests = (int) ($input['guests'] ?? 100);
$eventType = $input['event_type'] ?? 'wedding';

// AI quantity prediction based on event type and historical patterns
$multipliers = [
    'wedding' => ['food' => 1.15, 'appetizer' => 1.3, 'dessert' => 0.8],
    'corporate' => ['food' => 0.9, 'appetizer' => 1.0, 'dessert' => 0.6],
    'birthday' => ['food' => 1.0, 'appetizer' => 1.2, 'dessert' => 1.0],
    'outdoor-bbq' => ['food' => 1.25, 'appetizer' => 0.8, 'dessert' => 0.5],
    'default' => ['food' => 1.0, 'appetizer' => 1.0, 'dessert' => 0.7],
];

$mult = $multipliers[$eventType] ?? $multipliers['default'];

$predictions = [
    'main_course' => (int) ceil($guests * $mult['food']),
    'starters' => (int) ceil($guests * $mult['appetizer']),
    'desserts' => (int) ceil($guests * $mult['dessert']),
    'drinks' => (int) ceil($guests * 2.5),
    'rice_per_kg' => round($guests * 0.15 * $mult['food'], 1),
    'chicken_per_kg' => round($guests * 0.2 * $mult['food'], 1),
];

$wasteReduction = round((1 - $mult['food']) * 100);

jsonResponse([
    'success' => true,
    'predictions' => $predictions,
    'message' => "For {$guests} guests at a {$eventType}, prepare {$predictions['main_course']} main portions, {$predictions['starters']} starters, and {$predictions['desserts']} desserts. Estimated waste reduction: {$wasteReduction}% vs standard catering.",
]);
