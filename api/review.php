<?php
requireAuth('customer');

if (!isPost()) {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$token = $input['csrf_token'] ?? $_POST['csrf_token'] ?? '';
if (!verifyCsrf($token)) {
    jsonResponse(['success' => false, 'message' => 'Invalid request'], 403);
}

$bookingId = (int) ($input['booking_id'] ?? 0);
$rating = (int) ($input['rating'] ?? 0);
$title = trim($input['title'] ?? '');
$comment = trim($input['comment'] ?? '');

if (!$bookingId || $rating < 1 || $rating > 5 || !$comment) {
    jsonResponse(['success' => false, 'message' => 'Booking, rating (1-5), and comment are required']);
}

$user = currentUser();
$stmt = $pdo->prepare('SELECT id FROM bookings WHERE id = ? AND customer_id = ?');
$stmt->execute([$bookingId, $user['id']]);
if (!$stmt->fetch()) {
    jsonResponse(['success' => false, 'message' => 'Booking not found'], 404);
}

$existing = $pdo->prepare('SELECT id FROM reviews WHERE booking_id = ?');
$existing->execute([$bookingId]);
if ($existing->fetch()) {
    jsonResponse(['success' => false, 'message' => 'You already reviewed this booking']);
}

$pdo->prepare('INSERT INTO reviews (booking_id, customer_id, rating, title, comment, is_approved) VALUES (?, ?, ?, ?, ?, 0)')
    ->execute([$bookingId, $user['id'], $rating, $title ?: null, $comment]);

jsonResponse(['success' => true, 'message' => 'Thank you! Your review has been submitted for approval.']);
