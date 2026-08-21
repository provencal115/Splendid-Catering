<?php
requireAuth('admin');
$input = json_decode(file_get_contents('php://input'), true) ?: [];
$action = $input['action'] ?? '';

switch ($action) {
    case 'update_booking_status':
        $pdo->prepare('UPDATE bookings SET status = ? WHERE id = ?')->execute([$input['status'], $input['booking_id']]);
        jsonResponse(['success' => true]);
    default:
        jsonResponse(['success' => false, 'message' => 'Unknown action'], 400);
}
