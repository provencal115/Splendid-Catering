<?php
requireAuth();
$user = currentUser();
$action = $_GET['action'] ?? 'list';

if ($action === 'mark_read' && isPost()) {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $id = (int) ($input['id'] ?? 0);
    if ($id) {
        $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?')->execute([$id, $user['id']]);
    }
    jsonResponse(['success' => true]);
}

if ($action === 'mark_all_read' && isPost()) {
    $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?')->execute([$user['id']]);
    jsonResponse(['success' => true]);
}

$notifications = getUserNotifications($pdo, $user['id'], 20);
$unread = getUnreadNotificationCount($pdo, $user['id']);

jsonResponse([
    'success' => true,
    'unread' => $unread,
    'notifications' => array_map(fn($n) => [
        'id' => $n['id'],
        'title' => $n['title'],
        'message' => $n['message'],
        'type' => $n['type'],
        'link' => $n['link'],
        'is_read' => (bool) $n['is_read'],
        'time' => timeAgo($n['created_at']),
    ], $notifications),
]);
