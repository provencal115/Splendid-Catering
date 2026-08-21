<?php
requireAuth(['event_manager', 'admin']);
$input = json_decode(file_get_contents('php://input'), true) ?: [];
$action = $input['action'] ?? '';

switch ($action) {
    case 'toggle_checklist':
        $pdo->prepare('UPDATE event_checklists SET is_completed = ?, completed_by = ?, completed_at = ' . ($input['completed'] ? 'NOW()' : 'NULL') . ' WHERE id = ?')
            ->execute([(int)$input['completed'], currentUser()['id'], $input['id']]);
        jsonResponse(['success' => true]);

    case 'complete_event':
        $pdo->prepare('UPDATE bookings SET status = "completed" WHERE id = ?')->execute([$input['booking_id']]);
        jsonResponse(['success' => true]);

    case 'assign_staff':
        $pdo->prepare('DELETE FROM staff_assignments WHERE booking_id = ? AND role = ?')
            ->execute([$input['booking_id'], $input['role']]);
        $pdo->prepare('INSERT INTO staff_assignments (booking_id, staff_id, role, assigned_by) VALUES (?, ?, ?, ?)')
            ->execute([$input['booking_id'], $input['staff_id'], $input['role'], currentUser()['id']]);
        if ($input['role'] === 'chef') {
            $pdo->prepare('UPDATE kitchen_orders SET chef_id = ? WHERE booking_id = ?')->execute([$input['staff_id'], $input['booking_id']]);
        }
        if ($input['role'] === 'delivery') {
            $pdo->prepare('UPDATE delivery_tasks SET driver_id = ? WHERE booking_id = ?')->execute([$input['staff_id'], $input['booking_id']]);
        }
        jsonResponse(['success' => true]);

    case 'add_note':
        $pdo->prepare('INSERT INTO event_notes (booking_id, user_id, note) VALUES (?, ?, ?)')
            ->execute([$input['booking_id'], currentUser()['id'], $input['note']]);
        jsonResponse(['success' => true]);

    default:
        jsonResponse(['success' => false], 400);
}
