<?php
requireAuth('delivery');
$pageTitle = 'Delivery & Setup Dashboard';
$user = currentUser();

$tasks = $pdo->prepare('
    SELECT dt.*, b.reference_no, b.venue, b.event_date, b.event_time, b.guest_count,
           u.first_name, u.last_name, u.phone
    FROM delivery_tasks dt
    JOIN bookings b ON dt.booking_id = b.id
    JOIN users u ON b.customer_id = u.id
    WHERE dt.driver_id = ? OR dt.driver_id IS NULL
    ORDER BY dt.pickup_time ASC
');
$tasks->execute([$user['id']]);
$tasks = $tasks->fetchAll();

include ROOT_PATH . '/includes/dashboard-layout.php';
?>

<div class="row g-4 mb-4">
    <div class="col-md-4"><div class="stat-card primary"><div class="stat-value"><?= count($tasks) ?></div><div class="stat-label">Total Deliveries</div></div></div>
    <div class="col-md-4"><div class="stat-card warning"><div class="stat-value"><?= count(array_filter($tasks, fn($t) => $t['status'] === 'scheduled')) ?></div><div class="stat-label">Scheduled</div></div></div>
    <div class="col-md-4"><div class="stat-card success"><div class="stat-value"><?= count(array_filter($tasks, fn($t) => $t['status'] === 'setup_complete')) ?></div><div class="stat-label">Completed</div></div></div>
</div>

<?php foreach ($tasks as $task): ?>
<div class="dashboard-table mb-4">
    <div class="p-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h6 class="mb-0"><i class="bi bi-truck"></i> <?= e($task['reference_no']) ?></h6>
            <small class="text-muted"><?= e($task['event_date']) ?> at <?= e(substr($task['event_time'] ?? '', 0, 5)) ?></small>
        </div>
        <span class="badge bg-<?= match($task['status']) { 'scheduled'=>'warning', 'picked_up'=>'info', 'in_transit'=>'primary', 'delivered','setup_complete'=>'success', default=>'secondary' } ?>"><?= ucfirst(str_replace('_', ' ', $task['status'])) ?></span>
    </div>
    <div class="p-3">
        <div class="row g-4">
            <div class="col-md-5">
                <h6><i class="bi bi-geo-alt"></i> Delivery Location</h6>
                <p class="mb-2"><?= e($task['venue']) ?></p>
                <p class="small mb-2"><strong>Client:</strong> <?= e($task['first_name'].' '.$task['last_name']) ?> · <?= e($task['phone']) ?></p>
                <p class="small"><strong>Guests:</strong> <?= (int)$task['guest_count'] ?></p>
                <a href="https://maps.google.com/?q=<?= urlencode($task['venue']) ?>" target="_blank" class="btn btn-sm btn-outline-primary mt-2"><i class="bi bi-map"></i> Open in Google Maps</a>
            </div>
            <div class="col-md-7">
                <h6>Delivery Actions</h6>
                <div class="d-flex flex-wrap gap-2">
                    <?php if ($task['status'] === 'scheduled'): ?>
                    <button class="btn btn-sm btn-primary" onclick="updateDelivery(<?= $task['id'] ?>, 'picked_up')"><i class="bi bi-box-seam"></i> Confirm Pickup</button>
                    <?php endif; ?>
                    <?php if (in_array($task['status'], ['picked_up', 'scheduled'])): ?>
                    <button class="btn btn-sm btn-info" onclick="updateDelivery(<?= $task['id'] ?>, 'in_transit')"><i class="bi bi-truck"></i> Start Transit</button>
                    <?php endif; ?>
                    <?php if ($task['status'] === 'in_transit'): ?>
                    <button class="btn btn-sm btn-success" onclick="updateDelivery(<?= $task['id'] ?>, 'delivered')"><i class="bi bi-check-circle"></i> Confirm Delivery</button>
                    <?php endif; ?>
                    <?php if ($task['status'] === 'delivered'): ?>
                    <button class="btn btn-sm btn-gold" onclick="updateDelivery(<?= $task['id'] ?>, 'setup_complete')"><i class="bi bi-check2-all"></i> Setup Complete</button>
                    <?php endif; ?>
                    <button class="btn btn-sm btn-outline-warning" onclick="reportDelay(<?= $task['id'] ?>)"><i class="bi bi-clock"></i> Report Delay</button>
                </div>
                <?php if ($task['proof_photo_url']): ?>
                <p class="small mt-2 text-success"><i class="bi bi-camera"></i> Proof of delivery uploaded</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<script>
async function updateDelivery(id, status) {
    await fetch('/api/delivery-action', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({action:'update_status', task_id:id, status}) });
    location.reload();
}
async function reportDelay(id) {
    const reason = prompt('Reason for delay:');
    if (reason) await fetch('/api/delivery-action', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({action:'report_delay', task_id:id, reason}) });
}
</script>

<?php include ROOT_PATH . '/includes/dashboard-footer.php'; ?>
