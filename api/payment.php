<?php
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$bookingId = (int) ($input['booking_id'] ?? 0);
$method = $input['method'] ?? 'mtn_momo';
$amount = (float) ($input['amount'] ?? 0);

if (!$bookingId || !$amount) {
    jsonResponse(['success' => false, 'message' => 'Invalid payment data']);
}

$stmt = $pdo->prepare('SELECT * FROM bookings WHERE id = ?');
$stmt->execute([$bookingId]);
$booking = $stmt->fetch();

if (!$booking) jsonResponse(['success' => false, 'message' => 'Booking not found']);

// Simulated payment processing
$payRef = generateReference('PAY');
$txnId = strtoupper($method) . '-TXN-' . random_int(100000000, 999999999);

$pdo->prepare('INSERT INTO payments (booking_id, reference_no, amount, method, status, transaction_id, verified_by, verified_at) VALUES (?, ?, ?, ?, "completed", ?, ?, NOW())')
    ->execute([$bookingId, $payRef, $amount, $method, $txnId, isLoggedIn() ? currentUser()['id'] : null]);

$newPaid = $booking['amount_paid'] + $amount;
$paymentStatus = $newPaid >= $booking['total_amount'] ? 'paid' : 'partial';

$pdo->prepare('UPDATE bookings SET amount_paid = ?, payment_status = ? WHERE id = ?')
    ->execute([$newPaid, $paymentStatus, $bookingId]);

$pdo->prepare('UPDATE invoices SET amount_paid = ?, status = ? WHERE booking_id = ?')
    ->execute([$newPaid, $paymentStatus === 'paid' ? 'paid' : 'sent', $bookingId]);

jsonResponse([
    'success' => true,
    'message' => 'Payment of ' . formatMoney($amount) . ' processed successfully via ' . ($config['payment_methods'][$method] ?? $method),
    'transaction_id' => $txnId,
    'reference' => $payRef,
]);
