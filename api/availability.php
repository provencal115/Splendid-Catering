<?php
$days = $pdo->query('SELECT * FROM availability_blocks WHERE block_date >= CURDATE() ORDER BY block_date LIMIT 90')->fetchAll();

$result = [];
foreach ($days as $day) {
    $ratio = $day['current_bookings'] / max($day['max_bookings'], 1);
    $status = $ratio >= 1 ? 'unavailable' : ($ratio >= 0.66 ? 'limited' : 'available');
    $result[] = [
        'date' => $day['block_date'],
        'status' => $status,
        'slots' => max(0, $day['max_bookings'] - $day['current_bookings']),
    ];
}

jsonResponse(['success' => true, 'days' => $result]);
