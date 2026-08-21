<?php
requireAuth(['finance', 'admin']);
$input = json_decode(file_get_contents('php://input'), true) ?: [];

switch ($input['action'] ?? '') {
    case 'verify_payment':
        $pdo->prepare('UPDATE payments SET status = "completed", verified_by = ?, verified_at = NOW() WHERE id = ?')
            ->execute([currentUser()['id'], $input['payment_id']]);
        $pay = $pdo->prepare('SELECT booking_id, amount FROM payments WHERE id = ?');
        $pay->execute([$input['payment_id']]);
        $payment = $pay->fetch();
        if ($payment) {
            $pdo->prepare('UPDATE bookings SET amount_paid = amount_paid + ?, payment_status = IF(amount_paid + ? >= total_amount, "paid", "partial") WHERE id = ?')
                ->execute([$payment['amount'], $payment['amount'], $payment['booking_id']]);
        }
        jsonResponse(['success' => true]);

    case 'refund':
        $pdo->prepare('UPDATE payments SET status = "refunded" WHERE id = ?')->execute([$input['payment_id']]);
        jsonResponse(['success' => true]);

    default:
        jsonResponse(['success' => false], 400);
}
