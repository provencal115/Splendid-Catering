<?php
requireAuth(['delivery', 'admin']);
$input = json_decode(file_get_contents('php://input'), true) ?: [];

switch ($input['action'] ?? '') {
    case 'update_status':
        $pdo->prepare('UPDATE delivery_tasks SET status = ?, driver_id = ? WHERE id = ?')
            ->execute([$input['status'], currentUser()['id'], $input['task_id']]);
        $task = $pdo->prepare('SELECT booking_id FROM delivery_tasks WHERE id = ?');
        $task->execute([$input['task_id']]);
        $bookingId = $task->fetchColumn();
        $statusMap = ['picked_up' => 'in_preparation', 'in_transit' => 'in_transit', 'delivered' => 'delivered', 'setup_complete' => 'completed'];
        if (isset($statusMap[$input['status']])) {
            $pdo->prepare('UPDATE bookings SET status = ? WHERE id = ?')->execute([$statusMap[$input['status']], $bookingId]);
        }
        jsonResponse(['success' => true]);

    case 'report_delay':
        $pdo->prepare('UPDATE delivery_tasks SET delay_reason = ? WHERE id = ?')->execute([$input['reason'], $input['task_id']]);
        jsonResponse(['success' => true]);

    default:
        jsonResponse(['success' => false], 400);
}
