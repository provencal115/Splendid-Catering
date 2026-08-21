<?php
requireAuth('event_manager');
$pageTitle = 'Event Manager Dashboard';
$user = currentUser();
$userId = (int) $user['id'];
$today = date('Y-m-d');
$calendarMonth = (int) date('n');
$calendarYear = (int) date('Y');

$events = $pdo->prepare('
    SELECT b.*, u.first_name, u.last_name, u.phone, p.name AS package_name
    FROM bookings b
    JOIN users u ON b.customer_id = u.id
    LEFT JOIN packages p ON b.package_id = p.id
    WHERE b.event_manager_id = ? OR b.status IN (\'confirmed\', \'in_preparation\')
    ORDER BY b.event_date ASC, b.event_time ASC
');
$events->execute([$userId]);
$events = $events->fetchAll();

$allChecklists = $pdo->prepare('
    SELECT ec.*, b.reference_no, b.event_type, b.event_date
    FROM event_checklists ec
    JOIN bookings b ON ec.booking_id = b.id
    WHERE b.event_manager_id = ? OR b.status IN (\'confirmed\', \'in_preparation\')
    ORDER BY b.event_date ASC, ec.sort_order ASC, ec.id ASC
');
$allChecklists->execute([$userId]);
$allChecklists = $allChecklists->fetchAll();

$checklistsByBooking = [];
foreach ($allChecklists as $item) {
    $checklistsByBooking[(int) $item['booking_id']][] = $item;
}

$staffAssignments = $pdo->prepare('
    SELECT sa.*, b.reference_no, b.event_type, b.event_date,
           s.first_name AS staff_first, s.last_name AS staff_last, s.phone AS staff_phone,
           ab.first_name AS assigned_by_first, ab.last_name AS assigned_by_last
    FROM staff_assignments sa
    JOIN bookings b ON sa.booking_id = b.id
    JOIN users s ON sa.staff_id = s.id
    LEFT JOIN users ab ON sa.assigned_by = ab.id
    WHERE b.event_manager_id = ? OR b.status IN (\'confirmed\', \'in_preparation\')
    ORDER BY b.event_date ASC, sa.role ASC
');
$staffAssignments->execute([$userId]);
$staffAssignments = $staffAssignments->fetchAll();

$eventNotes = $pdo->prepare('
    SELECT en.*, b.reference_no, b.event_type, b.event_date,
           u.first_name, u.last_name
    FROM event_notes en
    JOIN bookings b ON en.booking_id = b.id
    JOIN users u ON en.user_id = u.id
    WHERE b.event_manager_id = ? OR b.status IN (\'confirmed\', \'in_preparation\')
    ORDER BY en.created_at DESC
');
$eventNotes->execute([$userId]);
$eventNotes = $eventNotes->fetchAll();

$availableStaff = $pdo->query("
    SELECT id, first_name, last_name, role
    FROM users
    WHERE role IN ('chef', 'delivery') AND is_active = 1
    ORDER BY role, first_name
")->fetchAll();

$calendarEvents = $pdo->prepare('
    SELECT b.id, b.reference_no, b.event_date, b.event_type, b.status, b.venue,
           u.first_name, u.last_name
    FROM bookings b
    JOIN users u ON b.customer_id = u.id
    WHERE (b.event_manager_id = ? OR b.status IN (\'confirmed\', \'in_preparation\'))
      AND YEAR(b.event_date) = ? AND MONTH(b.event_date) = ?
    ORDER BY b.event_date ASC
');
$calendarEvents->execute([$userId, $calendarYear, $calendarMonth]);
$calendarEvents = $calendarEvents->fetchAll();

$eventsByDate = [];
foreach ($calendarEvents as $event) {
    $eventsByDate[$event['event_date']][] = $event;
}

$notifications = getUserNotifications($pdo, $userId, 30);

$upcomingCount = count(array_filter($events, fn($e) => $e['event_date'] >= $today));
$inProgressCount = count(array_filter($events, fn($e) => in_array($e['status'], ['confirmed', 'in_preparation'], true)));
$completedCount = count(array_filter($events, fn($e) => $e['status'] === 'completed'));
$totalChecklistItems = count($allChecklists);
$completedChecklistItems = count(array_filter($allChecklists, fn($c) => (int) $c['is_completed'] === 1));
$checklistProgress = $totalChecklistItems > 0 ? round(($completedChecklistItems / $totalChecklistItems) * 100) : 0;

include ROOT_PATH . '/includes/dashboard-layout.php';
?>

<!-- Overview -->
<section class="dashboard-section" id="overview">
    <div class="kpi-grid mb-4">
        <?= renderKpiCard('Assigned Events', (string) count($events), 'calendar-week', 'primary', null, '#checklists', 'View checklists') ?>
        <?= renderKpiCard('Upcoming', (string) $upcomingCount, 'calendar-event', 'info') ?>
        <?= renderKpiCard('In Progress', (string) $inProgressCount, 'hourglass-split', 'warning') ?>
        <?= renderKpiCard('Completed', (string) $completedCount, 'check-circle', 'success') ?>
        <?= renderKpiCard('Checklist Progress', $checklistProgress . '%', 'list-check', 'accent', $completedChecklistItems . ' of ' . $totalChecklistItems . ' done') ?>
    </div>

    <div class="panel-card">
        <div class="panel-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-calendar-week"></i> My Events</h5>
            <span class="badge bg-primary"><?= count($events) ?> total</span>
        </div>
        <div class="panel-body p-0">
            <?php if (empty($events)): ?>
                <?= renderEmptyState('calendar-x', 'No assigned events', 'Events assigned to you will appear here.') ?>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table data-table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Event</th>
                            <th>Date</th>
                            <th>Client</th>
                            <th>Venue</th>
                            <th>Guests</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($events as $event):
                        $bookingChecklists = $checklistsByBooking[(int) $event['id']] ?? [];
                        $done = count(array_filter($bookingChecklists, fn($c) => (int) $c['is_completed'] === 1));
                        $total = count($bookingChecklists);
                    ?>
                        <tr>
                            <td><strong><?= e($event['reference_no']) ?></strong></td>
                            <td>
                                <?= e($event['event_type'] ?? 'Event') ?>
                                <?php if (!empty($event['package_name'])): ?>
                                    <br><small class="text-muted"><?= e($event['package_name']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= e(date('M j, Y', strtotime($event['event_date']))) ?>
                                <?php if (!empty($event['event_time'])): ?>
                                    <br><small class="text-muted"><?= e(substr((string) $event['event_time'], 0, 5)) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= e($event['first_name'] . ' ' . $event['last_name']) ?>
                                <?php if (!empty($event['phone'])): ?>
                                    <br><small class="text-muted"><?= e($event['phone']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= e($event['venue']) ?></td>
                            <td><?= (int) $event['guest_count'] ?></td>
                            <td><?= orderStatusBadge($event['status']) ?></td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    <a href="#checklists" class="btn btn-sm btn-outline-secondary" title="Checklist">
                                        <i class="bi bi-list-check"></i> <?= $done ?>/<?= max($total, 0) ?>
                                    </a>
                                    <?php if ($event['status'] !== 'completed'): ?>
                                    <button type="button" class="btn btn-sm btn-success" onclick="completeEvent(<?= (int) $event['id'] ?>)">
                                        <i class="bi bi-check2-all"></i> Complete
                                    </button>
                                    <?php endif; ?>
                                </div>
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

<!-- Checklists -->
<section class="dashboard-section mt-4" id="checklists">
    <div class="panel-card">
        <div class="panel-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-list-check"></i> Event Checklists</h5>
            <span class="badge bg-accent"><?= $completedChecklistItems ?>/<?= max($totalChecklistItems, 0) ?> complete</span>
        </div>
        <div class="panel-body">
            <?php if (empty($checklistsByBooking)): ?>
                <?= renderEmptyState('list-check', 'No checklist items', 'Checklist tasks for your events will appear here.') ?>
            <?php else: ?>
                <?php foreach ($checklistsByBooking as $bookingId => $items):
                    $first = $items[0];
                    $done = count(array_filter($items, fn($c) => (int) $c['is_completed'] === 1));
                    $total = count($items) ?: 1;
                ?>
                <div class="panel-card mb-4">
                    <div class="panel-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h6 class="mb-0"><?= e($first['reference_no']) ?> — <?= e($first['event_type'] ?? 'Event') ?></h6>
                            <small class="text-muted"><?= e(date('M j, Y', strtotime($first['event_date']))) ?></small>
                        </div>
                        <span class="badge badge-soft badge-<?= $done === $total ? 'success' : 'warning' ?>"><?= $done ?>/<?= $total ?> done</span>
                    </div>
                    <div class="panel-body">
                        <div class="progress progress-thin mb-3">
                            <div class="progress-bar bg-success" style="width:<?= ($done / $total) * 100 ?>%"></div>
                        </div>
                        <?php foreach ($items as $cl): ?>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="cl-<?= (int) $cl['id'] ?>"
                                   <?= (int) $cl['is_completed'] === 1 ? 'checked' : '' ?>
                                   onchange="toggleChecklist(<?= (int) $cl['id'] ?>, this.checked)">
                            <label class="form-check-label <?= (int) $cl['is_completed'] === 1 ? 'text-decoration-line-through text-muted' : '' ?>" for="cl-<?= (int) $cl['id'] ?>">
                                <?= e($cl['title']) ?>
                            </label>
                        </div>
                        <?php endforeach; ?>
                        <div class="d-flex flex-wrap gap-2 mt-3 pt-2 border-top">
                            <?php
                            $chefs = array_filter($availableStaff, fn($s) => $s['role'] === 'chef');
                            $drivers = array_filter($availableStaff, fn($s) => $s['role'] === 'delivery');
                            $defaultChef = $chefs ? (int) reset($chefs)['id'] : 0;
                            $defaultDriver = $drivers ? (int) reset($drivers)['id'] : 0;
                            ?>
                            <?php if ($defaultChef): ?>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="assignStaff(<?= (int) $bookingId ?>, 'chef', <?= $defaultChef ?>)">
                                <i class="bi bi-person"></i> Assign Chef
                            </button>
                            <?php endif; ?>
                            <?php if ($defaultDriver): ?>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="assignStaff(<?= (int) $bookingId ?>, 'delivery', <?= $defaultDriver ?>)">
                                <i class="bi bi-truck"></i> Assign Delivery
                            </button>
                            <?php endif; ?>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addNote(<?= (int) $bookingId ?>)">
                                <i class="bi bi-journal-plus"></i> Add Note
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Staff -->
<section class="dashboard-section mt-4" id="staff">
    <div class="panel-card">
        <div class="panel-header">
            <h5 class="mb-0"><i class="bi bi-people"></i> Staff Assignments</h5>
        </div>
        <div class="panel-body p-0">
            <?php if (empty($staffAssignments)): ?>
                <?= renderEmptyState('people', 'No staff assigned', 'Assign chefs and delivery drivers from the checklists section.') ?>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table data-table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Date</th>
                            <th>Role</th>
                            <th>Staff Member</th>
                            <th>Assigned By</th>
                            <th>Assigned At</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($staffAssignments as $assignment): ?>
                        <tr>
                            <td>
                                <strong><?= e($assignment['reference_no']) ?></strong>
                                <br><small class="text-muted"><?= e($assignment['event_type'] ?? 'Event') ?></small>
                            </td>
                            <td><?= e(date('M j, Y', strtotime($assignment['event_date']))) ?></td>
                            <td><span class="badge badge-soft badge-primary"><?= e(ucfirst(str_replace('_', ' ', $assignment['role']))) ?></span></td>
                            <td>
                                <?= e($assignment['staff_first'] . ' ' . $assignment['staff_last']) ?>
                                <?php if (!empty($assignment['staff_phone'])): ?>
                                    <br><small class="text-muted"><?= e($assignment['staff_phone']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= e(trim(($assignment['assigned_by_first'] ?? '') . ' ' . ($assignment['assigned_by_last'] ?? '')) ?: '—') ?></td>
                            <td><?= e(date('M j, Y g:i A', strtotime($assignment['assigned_at']))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Notes -->
<section class="dashboard-section mt-4" id="notes">
    <div class="panel-card">
        <div class="panel-header">
            <h5 class="mb-0"><i class="bi bi-journal-text"></i> Event Notes</h5>
        </div>
        <div class="panel-body">
            <?php if (empty($eventNotes)): ?>
                <?= renderEmptyState('journal-text', 'No notes yet', 'Add notes from an event checklist to keep your team informed.') ?>
            <?php else: ?>
                <?php foreach ($eventNotes as $note): ?>
                <div class="notification-item d-flex gap-3 py-3 border-bottom">
                    <div class="flex-shrink-0">
                        <span class="badge bg-accent rounded-circle p-2"><i class="bi bi-journal-text"></i></span>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                            <div>
                                <strong><?= e($note['reference_no']) ?></strong>
                                <span class="text-muted"> — <?= e($note['event_type'] ?? 'Event') ?></span>
                                <div class="small text-muted"><?= e(date('M j, Y', strtotime($note['event_date']))) ?></div>
                            </div>
                            <small class="text-muted"><?= e(timeAgo($note['created_at'])) ?></small>
                        </div>
                        <p class="mb-1 mt-2"><?= e($note['note']) ?></p>
                        <div class="small text-muted">By <?= e($note['first_name'] . ' ' . $note['last_name']) ?></div>
                        <?php if (!empty($note['photo_url'])): ?>
                            <img src="<?= e($note['photo_url']) ?>" alt="Note attachment" class="img-fluid rounded mt-2" style="max-height:120px;">
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Calendar -->
<section class="dashboard-section mt-4" id="calendar">
    <div class="panel-card">
        <div class="panel-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-calendar3"></i> <?= e(date('F Y')) ?> Calendar</h5>
            <span class="text-muted small"><?= count($calendarEvents) ?> event<?= count($calendarEvents) === 1 ? '' : 's' ?> this month</span>
        </div>
        <div class="panel-body">
            <?php
            $firstDay = mktime(0, 0, 0, $calendarMonth, 1, $calendarYear);
            $daysInMonth = (int) date('t', $firstDay);
            $startWeekday = (int) date('w', $firstDay);
            ?>
            <div class="calendar-grid">
                <div class="calendar-weekdays row g-1 mb-1 text-center small fw-semibold text-muted">
                    <?php foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $weekday): ?>
                        <div class="col"><?= $weekday ?></div>
                    <?php endforeach; ?>
                </div>
                <div class="calendar-days">
                    <?php
                    $dayCounter = 1;
                    $cells = (int) ceil(($startWeekday + $daysInMonth) / 7) * 7;
                    for ($cell = 0; $cell < $cells; $cell++):
                        $isCurrentMonth = $cell >= $startWeekday && $dayCounter <= $daysInMonth;
                        if ($cell % 7 === 0) {
                            echo $cell > 0 ? '</div>' : '';
                            echo '<div class="row g-1 mb-1">';
                        }
                        if ($isCurrentMonth) {
                            $dateKey = sprintf('%04d-%02d-%02d', $calendarYear, $calendarMonth, $dayCounter);
                            $dayEvents = $eventsByDate[$dateKey] ?? [];
                            $isToday = $dateKey === $today;
                            ?>
                            <div class="col">
                                <div class="calendar-day p-2 <?= $isToday ? 'calendar-day-today' : '' ?> <?= !empty($dayEvents) ? 'has-bookings' : '' ?>">
                                    <div class="calendar-day-num small fw-semibold"><?= $dayCounter ?></div>
                                    <?php foreach (array_slice($dayEvents, 0, 2) as $de): ?>
                                        <div class="calendar-booking small text-truncate" title="<?= e($de['reference_no'] . ' — ' . $de['first_name'] . ' ' . $de['last_name']) ?>">
                                            <?= e($de['reference_no']) ?>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if (count($dayEvents) > 2): ?>
                                        <div class="small text-muted">+<?= count($dayEvents) - 2 ?> more</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php
                            $dayCounter++;
                        } else {
                            echo '<div class="col"><div class="calendar-day calendar-day-empty p-2"></div></div>';
                        }
                    endfor;
                    echo '</div>';
                    ?>
                </div>
            </div>
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
                <?= renderEmptyState('bell-slash', 'No notifications', 'Updates about your events will appear here.') ?>
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
    async function toggleChecklist(id, completed) {
        await fetch(window.APP_BASE + '/api/event-action', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'toggle_checklist', id, completed })
        });
    }

    async function completeEvent(id) {
        if (!confirm('Mark this event as completed?')) return;
        await fetch(window.APP_BASE + '/api/event-action', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'complete_event', booking_id: id })
        });
        location.reload();
    }

    async function assignStaff(bookingId, role, staffId) {
        await fetch(window.APP_BASE + '/api/event-action', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'assign_staff', booking_id: bookingId, role, staff_id: staffId })
        });
        alert('Staff assigned successfully.');
        location.reload();
    }

    async function addNote(bookingId) {
        const note = prompt('Enter event note:');
        if (!note) return;
        await fetch(window.APP_BASE + '/api/event-action', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'add_note', booking_id: bookingId, note })
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

    window.toggleChecklist = toggleChecklist;
    window.completeEvent = completeEvent;
    window.assignStaff = assignStaff;
    window.addNote = addNote;

    document.addEventListener('DOMContentLoaded', highlightSidebarSection);
    window.addEventListener('hashchange', highlightSidebarSection);
})();
</script>

<?php include ROOT_PATH . '/includes/dashboard-footer.php'; ?>
