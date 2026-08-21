<?php
requireAuth('event_manager');
$pageTitle = 'Event Manager Dashboard';
$user = currentUser();

$events = $pdo->prepare('
    SELECT b.*, u.first_name, u.last_name, u.phone, p.name as package_name
    FROM bookings b JOIN users u ON b.customer_id = u.id
    LEFT JOIN packages p ON b.package_id = p.id
    WHERE b.event_manager_id = ? OR b.status IN ("confirmed","in_preparation")
    ORDER BY b.event_date ASC
');
$events->execute([$user['id']]);
$events = $events->fetchAll();

include ROOT_PATH . '/includes/dashboard-layout.php';
?>

<div class="row g-4 mb-4">
    <div class="col-md-3"><div class="stat-card primary"><div class="stat-value"><?= count($events) ?></div><div class="stat-label">Assigned Events</div></div></div>
    <div class="col-md-3"><div class="stat-card warning"><div class="stat-value"><?= count(array_filter($events, fn($e) => $e['event_date'] >= date('Y-m-d'))) ?></div><div class="stat-label">Upcoming</div></div></div>
    <div class="col-md-3"><div class="stat-card success"><div class="stat-value"><?= count(array_filter($events, fn($e) => $e['status'] === 'completed')) ?></div><div class="stat-label">Completed</div></div></div>
    <div class="col-md-3"><div class="stat-card info"><div class="stat-value"><?= count(array_filter($events, fn($e) => $e['status'] === 'in_preparation')) ?></div><div class="stat-label">In Progress</div></div></div>
</div>

<?php foreach ($events as $event):
    $checklists = $pdo->prepare('SELECT * FROM event_checklists WHERE booking_id = ? ORDER BY sort_order');
    $checklists->execute([$event['id']]);
    $checklists = $checklists->fetchAll();
    $completed = count(array_filter($checklists, fn($c) => $c['is_completed']));
    $total = count($checklists) ?: 1;
?>
<div class="dashboard-table mb-4">
    <div class="p-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h6 class="mb-0"><?= e($event['reference_no']) ?> — <?= e($event['event_type'] ?? 'Event') ?></h6>
            <small class="text-muted"><?= e($event['event_date']) ?> · <?= (int)$event['guest_count'] ?> guests · <?= e($event['venue']) ?></small>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <?= getStatusBadge($event['status']) ?>
            <button class="btn btn-sm btn-success" onclick="completeEvent(<?= $event['id'] ?>)">Mark Complete</button>
        </div>
    </div>
    <div class="p-3">
        <div class="row g-4">
            <div class="col-md-4">
                <h6>Client Contact</h6>
                <p class="mb-1"><strong><?= e($event['first_name'].' '.$event['last_name']) ?></strong></p>
                <p class="mb-0 small"><i class="bi bi-telephone"></i> <?= e($event['phone']) ?></p>
            </div>
            <div class="col-md-4" id="checklists">
                <h6>Event Checklist (<?= $completed ?>/<?= $total ?>)</h6>
                <div class="progress progress-thin mb-2"><div class="progress-bar bg-success" style="width:<?= ($completed/$total)*100 ?>%"></div></div>
                <?php foreach ($checklists as $cl): ?>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" <?= $cl['is_completed'] ? 'checked' : '' ?> onchange="toggleChecklist(<?= $cl['id'] ?>, this.checked)">
                    <label class="form-check-label small <?= $cl['is_completed'] ? 'text-decoration-line-through text-muted' : '' ?>"><?= e($cl['title']) ?></label>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="col-md-4">
                <h6>Quick Actions</h6>
                <button class="btn btn-sm btn-outline-primary mb-1 w-100" onclick="assignStaff(<?= $event['id'] ?>, 'chef', 4)"><i class="bi bi-person"></i> Assign Chef</button>
                <button class="btn btn-sm btn-outline-primary mb-1 w-100" onclick="assignStaff(<?= $event['id'] ?>, 'delivery', 5)"><i class="bi bi-truck"></i> Assign Delivery</button>
                <button class="btn btn-sm btn-outline-secondary w-100" onclick="addNote(<?= $event['id'] ?>)"><i class="bi bi-journal-plus"></i> Add Note</button>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<script>
async function toggleChecklist(id, completed) {
    await fetch('/api/event-action', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({action:'toggle_checklist', id, completed}) });
}
async function completeEvent(id) {
    await fetch('/api/event-action', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({action:'complete_event', booking_id:id}) });
    location.reload();
}
async function assignStaff(bookingId, role, staffId) {
    await fetch('/api/event-action', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({action:'assign_staff', booking_id:bookingId, role, staff_id:staffId}) });
    alert('Staff assigned!');
}
async function addNote(bookingId) {
    const note = prompt('Enter event note:');
    if (note) await fetch('/api/event-action', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({action:'add_note', booking_id:bookingId, note}) });
}
</script>

<?php include ROOT_PATH . '/includes/dashboard-footer.php'; ?>
