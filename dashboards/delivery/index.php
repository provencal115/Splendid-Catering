<?php
requireAuth('delivery');
$pageTitle = 'Delivery & Setup Dashboard';
$user = currentUser();
$userId = (int) $user['id'];

$tasks = $pdo->prepare('
    SELECT dt.*, b.reference_no, b.venue, b.event_date, b.event_time, b.guest_count,
           u.first_name, u.last_name, u.phone,
           d.first_name AS driver_first, d.last_name AS driver_last
    FROM delivery_tasks dt
    JOIN bookings b ON dt.booking_id = b.id
    JOIN users u ON b.customer_id = u.id
    LEFT JOIN users d ON dt.driver_id = d.id
    WHERE dt.driver_id = ? OR dt.driver_id IS NULL
    ORDER BY COALESCE(dt.pickup_time, b.event_date) ASC
');
$tasks->execute([$userId]);
$tasks = $tasks->fetchAll();

$proofTasks = array_values(array_filter($tasks, fn($t) => !empty($t['proof_photo_url'])));

$notifications = getUserNotifications($pdo, $userId, 30);

$scheduledCount = count(array_filter($tasks, fn($t) => $t['status'] === 'scheduled'));
$inTransitCount = count(array_filter($tasks, fn($t) => in_array($t['status'], ['picked_up', 'in_transit'], true)));
$completedCount = count(array_filter($tasks, fn($t) => in_array($t['status'], ['delivered', 'setup_complete'], true)));

$deliveryStatusBadge = static function (string $status): string {
    $map = [
        'scheduled' => ['Scheduled', 'warning'],
        'picked_up' => ['Picked Up', 'info'],
        'in_transit' => ['In Transit', 'primary'],
        'delivered' => ['Delivered', 'success'],
        'setup_complete' => ['Setup Complete', 'success'],
    ];
    $info = $map[$status] ?? [ucfirst(str_replace('_', ' ', $status)), 'secondary'];
    return '<span class="badge badge-soft badge-' . $info[1] . '">' . e($info[0]) . '</span>';
};

include ROOT_PATH . '/includes/dashboard-layout.php';
?>

<!-- Overview -->
<section class="dashboard-section" id="overview">
    <div class="kpi-grid mb-4">
        <?= renderKpiCard('Total Deliveries', (string) count($tasks), 'truck', 'primary', null, '#routes', 'View routes') ?>
        <?= renderKpiCard('Scheduled', (string) $scheduledCount, 'calendar-check', 'warning') ?>
        <?= renderKpiCard('In Transit', (string) $inTransitCount, 'signpost-split', 'info') ?>
        <?= renderKpiCard('Completed', (string) $completedCount, 'check-circle', 'success', null, '#proof', 'View proof') ?>
    </div>

    <div class="panel-card">
        <div class="panel-header">
            <h5 class="mb-0"><i class="bi bi-truck"></i> Active Deliveries</h5>
        </div>
        <div class="panel-body">
            <?php if (empty($tasks)): ?>
                <?= renderEmptyState('truck', 'No delivery tasks', 'Assigned delivery routes will appear here.') ?>
            <?php else: ?>
                <?php foreach ($tasks as $task): ?>
                <div class="panel-card mb-4">
                    <div class="panel-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h6 class="mb-0"><?= e($task['reference_no']) ?></h6>
                            <small class="text-muted">
                                <?= e(date('M j, Y', strtotime($task['event_date']))) ?>
                                <?php if (!empty($task['event_time'])): ?>
                                    at <?= e(substr((string) $task['event_time'], 0, 5)) ?>
                                <?php endif; ?>
                            </small>
                        </div>
                        <?= $deliveryStatusBadge($task['status']) ?>
                    </div>
                    <div class="panel-body">
                        <div class="row g-4">
                            <div class="col-md-5">
                                <h6><i class="bi bi-geo-alt"></i> Delivery Location</h6>
                                <p class="mb-2"><?= e($task['venue']) ?></p>
                                <p class="small mb-1"><strong>Client:</strong> <?= e($task['first_name'] . ' ' . $task['last_name']) ?></p>
                                <?php if (!empty($task['phone'])): ?>
                                <p class="small mb-1"><i class="bi bi-telephone"></i> <?= e($task['phone']) ?></p>
                                <?php endif; ?>
                                <p class="small mb-0"><strong>Guests:</strong> <?= (int) $task['guest_count'] ?></p>
                                <a href="https://maps.google.com/?q=<?= urlencode($task['venue']) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary mt-3">
                                    <i class="bi bi-map"></i> Open in Google Maps
                                </a>
                            </div>
                            <div class="col-md-7">
                                <h6>Delivery Actions</h6>
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <?php if ($task['status'] === 'scheduled'): ?>
                                    <button type="button" class="btn btn-sm btn-primary" onclick="updateDelivery(<?= (int) $task['id'] ?>, 'picked_up')">
                                        <i class="bi bi-box-seam"></i> Confirm Pickup
                                    </button>
                                    <?php endif; ?>
                                    <?php if (in_array($task['status'], ['picked_up', 'scheduled'], true)): ?>
                                    <button type="button" class="btn btn-sm btn-info" onclick="updateDelivery(<?= (int) $task['id'] ?>, 'in_transit')">
                                        <i class="bi bi-truck"></i> Start Transit
                                    </button>
                                    <?php endif; ?>
                                    <?php if ($task['status'] === 'in_transit'): ?>
                                    <button type="button" class="btn btn-sm btn-success" onclick="updateDelivery(<?= (int) $task['id'] ?>, 'delivered')">
                                        <i class="bi bi-check-circle"></i> Confirm Delivery
                                    </button>
                                    <?php endif; ?>
                                    <?php if ($task['status'] === 'delivered'): ?>
                                    <button type="button" class="btn btn-sm btn-gold" onclick="updateDelivery(<?= (int) $task['id'] ?>, 'setup_complete')">
                                        <i class="bi bi-check2-all"></i> Setup Complete
                                    </button>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-sm btn-outline-warning" onclick="reportDelay(<?= (int) $task['id'] ?>)">
                                        <i class="bi bi-clock"></i> Report Delay
                                    </button>
                                </div>
                                <?php if (!empty($task['delay_reason'])): ?>
                                <p class="small text-warning mb-0"><i class="bi bi-exclamation-circle"></i> Delay: <?= e($task['delay_reason']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Routes -->
<section class="dashboard-section mt-4" id="routes">
    <div class="panel-card">
        <div class="panel-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-signpost-split"></i> Delivery Routes</h5>
            <span class="badge bg-primary"><?= count($tasks) ?> routes</span>
        </div>
        <div class="panel-body p-0">
            <?php if (empty($tasks)): ?>
                <?= renderEmptyState('signpost-split', 'No routes assigned', 'Delivery routes will be listed here when tasks are assigned.') ?>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table data-table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Booking</th>
                            <th>Event Date</th>
                            <th>Pickup</th>
                            <th>Venue</th>
                            <th>Driver</th>
                            <th>Status</th>
                            <th>Map</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($tasks as $task): ?>
                        <tr>
                            <td><strong><?= e($task['reference_no']) ?></strong></td>
                            <td>
                                <?= e(date('M j, Y', strtotime($task['event_date']))) ?>
                                <?php if (!empty($task['event_time'])): ?>
                                    <br><small class="text-muted"><?= e(substr((string) $task['event_time'], 0, 5)) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= $task['pickup_time'] ? e(date('M j, g:i A', strtotime($task['pickup_time']))) : '—' ?>
                            </td>
                            <td><?= e($task['venue']) ?></td>
                            <td>
                                <?= e(trim(($task['driver_first'] ?? '') . ' ' . ($task['driver_last'] ?? '')) ?: 'Unassigned') ?>
                            </td>
                            <td><?= $deliveryStatusBadge($task['status']) ?></td>
                            <td>
                                <a href="https://maps.google.com/?q=<?= urlencode($task['venue']) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-map"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Proof of Delivery -->
<section class="dashboard-section mt-4" id="proof">
    <div class="panel-card">
        <div class="panel-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-camera"></i> Proof of Delivery</h5>
            <span class="badge bg-accent"><?= count($proofTasks) ?> uploaded</span>
        </div>
        <div class="panel-body">
            <?php if (empty($proofTasks)): ?>
                <?= renderEmptyState('camera', 'No proof photos yet', 'Delivery proof photos will appear here once uploaded.') ?>
            <?php else: ?>
            <div class="row g-4">
                <?php foreach ($proofTasks as $task): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="panel-card h-100">
                        <div class="ratio ratio-4x3">
                            <img src="<?= e($task['proof_photo_url']) ?>" alt="Proof for <?= e($task['reference_no']) ?>" class="object-fit-cover rounded-top">
                        </div>
                        <div class="panel-body">
                            <h6 class="mb-1"><?= e($task['reference_no']) ?></h6>
                            <p class="small text-muted mb-2"><?= e($task['venue']) ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <?= $deliveryStatusBadge($task['status']) ?>
                                <small class="text-muted">
                                    <?= $task['delivery_time'] ? e(date('M j, Y', strtotime($task['delivery_time']))) : e(date('M j, Y', strtotime($task['event_date']))) ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Notifications -->
<section class="dashboard-section mt-4 mb-4" id="notifications">
    <div class="panel-card">
        <div class="panel-header">
            <h5 class="mb-0"><i class="bi bi-bell"></i> Notifications</h5>
        </div>
        <div class="panel-body">
            <?php if (empty($notifications)): ?>
                <?= renderEmptyState('bell-slash', 'No notifications', 'Delivery alerts and updates will appear here.') ?>
            <?php else: ?>
            <div class="notification-list-admin">
                <?php foreach ($notifications as $note): ?>
                <div class="notification-item d-flex gap-3 py-3 border-bottom <?= $note['is_read'] ? '' : 'notification-unread' ?>">
                    <div class="flex-shrink-0">
                        <span class="badge bg-<?= $note['is_read'] ? 'secondary' : 'primary' ?> rounded-circle p-2"><i class="bi bi-bell"></i></span>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start">
                            <strong><?= e($note['title']) ?></strong>
                            <small class="text-muted"><?= e(timeAgo($note['created_at'])) ?></small>
                        </div>
                        <p class="mb-1 small text-muted"><?= e($note['message']) ?></p>
                        <?php if (!empty($note['link'])): ?>
                            <a href="<?= e($note['link']) ?>" class="small">View details</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
(function () {
    async function updateDelivery(id, status) {
        await fetch(window.APP_BASE + '/api/delivery-action', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'update_status', task_id: id, status })
        });
        location.reload();
    }

    async function reportDelay(id) {
        const reason = prompt('Reason for delay:');
        if (!reason) return;
        await fetch(window.APP_BASE + '/api/delivery-action', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'report_delay', task_id: id, reason })
        });
        location.reload();
    }

    function highlightSidebarSection() {
        const hash = (location.hash || '#overview').replace('#', '');
        document.querySelectorAll('.sidebar-link[data-section]').forEach(link => {
            link.classList.toggle('active', link.dataset.section === hash);
        });
        const target = document.getElementById(hash);
        if (target && location.hash) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    window.updateDelivery = updateDelivery;
    window.reportDelay = reportDelay;

    document.addEventListener('DOMContentLoaded', highlightSidebarSection);
    window.addEventListener('hashchange', highlightSidebarSection);
})();
</script>

<?php include ROOT_PATH . '/includes/dashboard-footer.php'; ?>
