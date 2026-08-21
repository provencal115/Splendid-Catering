<?php
requireAuth(['chef', 'admin']);
$input = json_decode(file_get_contents('php://input'), true) ?: [];

switch ($input['action'] ?? '') {
    case 'update_order':
        $status = $input['status'];
        $extra = $status === 'preparing' ? ', prep_started_at = NOW()' : ($status === 'ready' ? ', ready_at = NOW()' : '');
        $pdo->prepare("UPDATE kitchen_orders SET status = ?, chef_id = ?{$extra} WHERE id = ?")
            ->execute([$status, currentUser()['id'], $input['order_id']]);
        if ($status === 'ready') {
            $order = $pdo->prepare('SELECT booking_id FROM kitchen_orders WHERE id = ?');
            $order->execute([$input['order_id']]);
            $bookingId = $order->fetchColumn();
            $pending = $pdo->prepare('SELECT COUNT(*) FROM kitchen_orders WHERE booking_id = ? AND status != "ready"');
            $pending->execute([$bookingId]);
            if ((int)$pending->fetchColumn() === 0) {
                $pdo->prepare('UPDATE bookings SET status = "ready" WHERE id = ?')->execute([$bookingId]);
            }
        }
        jsonResponse(['success' => true]);

    case 'report_shortage':
        notify($pdo, 1, 'inventory', 'Ingredient Shortage', 'Kitchen reported shortage for order #' . $input['order_id'], url('dashboard/kitchen'));
        jsonResponse(['success' => true]);

    case 'request_restock':
        notify($pdo, 1, 'inventory', 'Restock Request', 'Restock requested for inventory item #' . $input['item_id'], url('dashboard/kitchen'));
        jsonResponse(['success' => true]);

    default:
        jsonResponse(['success' => false], 400);
}
