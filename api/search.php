<?php
requireAuth(['admin', 'finance', 'event_manager']);
$q = trim($_GET['q'] ?? '');
if (strlen($q) < 2) {
    jsonResponse(['success' => true, 'results' => []]);
}

$like = '%' . $q . '%';
$results = [];

// Customers
$stmt = $pdo->prepare("SELECT id, first_name, last_name, email FROM users WHERE role='customer' AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?) LIMIT 5");
$stmt->execute([$like, $like, $like]);
foreach ($stmt->fetchAll() as $r) {
    $results[] = ['type' => 'Customer', 'label' => $r['first_name'] . ' ' . $r['last_name'], 'sub' => $r['email'], 'url' => url('dashboard/admin') . '#customers'];
}

// Bookings
$stmt = $pdo->prepare("SELECT b.id, b.reference_no, u.first_name, u.last_name FROM bookings b JOIN users u ON b.customer_id=u.id WHERE b.reference_no LIKE ? OR u.first_name LIKE ? LIMIT 5");
$stmt->execute([$like, $like]);
foreach ($stmt->fetchAll() as $r) {
    $results[] = ['type' => 'Booking', 'label' => $r['reference_no'], 'sub' => $r['first_name'] . ' ' . $r['last_name'], 'url' => url('dashboard/admin') . '#bookings'];
}

// Menu
$stmt = $pdo->prepare("SELECT id, name, price FROM menu_items WHERE name LIKE ? LIMIT 5");
$stmt->execute([$like]);
foreach ($stmt->fetchAll() as $r) {
    $results[] = ['type' => 'Menu', 'label' => $r['name'], 'sub' => formatMoney((float)$r['price']), 'url' => url('dashboard/admin') . '#menu'];
}

jsonResponse(['success' => true, 'results' => $results]);
