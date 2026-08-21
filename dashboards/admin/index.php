<?php
requireAuth('admin');
$pageTitle = 'Admin Dashboard';

$analytics = getAdminAnalytics($pdo);
$today = date('Y-m-d');
$calendarMonth = (int) date('n');
$calendarYear = (int) date('Y');

$recentOrders = $pdo->query('
    SELECT b.*, u.first_name, u.last_name, p.name AS package_name
    FROM bookings b
    JOIN users u ON b.customer_id = u.id
    LEFT JOIN packages p ON b.package_id = p.id
    ORDER BY b.created_at DESC
    LIMIT 10
')->fetchAll();

$upcomingEvents = $pdo->query('
    SELECT b.*, u.first_name, u.last_name, p.name AS package_name, s.name AS service_name
    FROM bookings b
    JOIN users u ON b.customer_id = u.id
    LEFT JOIN packages p ON b.package_id = p.id
    LEFT JOIN service_types s ON b.service_type_id = s.id
    WHERE b.event_date >= CURDATE()
    ORDER BY b.event_date ASC, b.event_time ASC
    LIMIT 6
')->fetchAll();

$allBookings = $pdo->query('
    SELECT b.*, u.first_name, u.last_name, p.name AS package_name
    FROM bookings b
    JOIN users u ON b.customer_id = u.id
    LEFT JOIN packages p ON b.package_id = p.id
    ORDER BY b.event_date DESC, b.created_at DESC
')->fetchAll();

$customers = $pdo->query("
    SELECT u.*,
        (SELECT COUNT(*) FROM bookings b WHERE b.customer_id = u.id) AS booking_count,
        (SELECT COALESCE(SUM(b.total_amount), 0) FROM bookings b WHERE b.customer_id = u.id) AS total_spend
    FROM users u
    WHERE u.role = 'customer'
    ORDER BY u.created_at DESC
")->fetchAll();

$menuItems = $pdo->query('
    SELECT m.*, c.name AS category
    FROM menu_items m
    JOIN menu_categories c ON m.category_id = c.id
    ORDER BY c.sort_order, m.name
')->fetchAll();

$packages = $pdo->query('SELECT * FROM packages ORDER BY sort_order, name')->fetchAll();

$eventsTimeline = $pdo->query('
    SELECT b.*, u.first_name, u.last_name, p.name AS package_name, s.name AS service_name
    FROM bookings b
    JOIN users u ON b.customer_id = u.id
    LEFT JOIN packages p ON b.package_id = p.id
    LEFT JOIN service_types s ON b.service_type_id = s.id
    WHERE b.event_date >= CURDATE() AND b.status NOT IN (\'cancelled\', \'completed\')
    ORDER BY b.event_date ASC, b.event_time ASC
    LIMIT 20
')->fetchAll();

$payments = $pdo->query('
    SELECT pay.*, b.reference_no AS booking_ref, u.first_name, u.last_name
    FROM payments pay
    JOIN bookings b ON pay.booking_id = b.id
    JOIN users u ON b.customer_id = u.id
    ORDER BY pay.created_at DESC
')->fetchAll();

$invoices = $pdo->query('
    SELECT i.*, u.first_name, u.last_name, b.reference_no AS booking_ref
    FROM invoices i
    JOIN users u ON i.customer_id = u.id
    JOIN bookings b ON i.booking_id = b.id
    ORDER BY i.created_at DESC
')->fetchAll();

$calendarBookings = $pdo->prepare('
    SELECT b.id, b.reference_no, b.event_date, b.event_type, b.status, u.first_name, u.last_name
    FROM bookings b
    JOIN users u ON b.customer_id = u.id
    WHERE YEAR(b.event_date) = ? AND MONTH(b.event_date) = ?
    ORDER BY b.event_date ASC
');
$calendarBookings->execute([$calendarYear, $calendarMonth]);
$calendarBookings = $calendarBookings->fetchAll();

$bookingsByDate = [];
foreach ($calendarBookings as $cb) {
    $bookingsByDate[$cb['event_date']][] = $cb;
}

$adminUserId = (int) (currentUser()['id'] ?? 1);
$notifications = $pdo->prepare('
    SELECT n.*, u.first_name, u.last_name
    FROM notifications n
    LEFT JOIN users u ON n.user_id = u.id
    WHERE n.user_id = ? OR n.user_id = 1
    ORDER BY n.created_at DESC
    LIMIT 30
');
$notifications->execute([$adminUserId]);
$notifications = $notifications->fetchAll();
if (empty($notifications)) {
    $notifications = $pdo->query('
        SELECT n.*, u.first_name, u.last_name
        FROM notifications n
        LEFT JOIN users u ON n.user_id = u.id
        ORDER BY n.created_at DESC
        LIMIT 30
    ')->fetchAll();
}

$websiteContent = $pdo->query('SELECT page_key, title, updated_at FROM website_content ORDER BY page_key')->fetchAll();

$renderBookingsTable = static function (array $bookings) use ($config): void {
    if (empty($bookings)) {
        echo renderEmptyState('calendar-x', 'No bookings yet', 'Bookings will appear here once customers start placing orders.');
        return;
    }
    ?>
    <div class="table-responsive">
        <table class="table data-table table-hover mb-0">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Customer</th>
                    <th>Event Date</th>
                    <th>Package</th>
                    <th>Guests</th>
                    <th>Amount</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($bookings as $b): ?>
                <tr>
                    <td><strong><?= e($b['reference_no']) ?></strong></td>
                    <td><?= e($b['first_name'] . ' ' . $b['last_name']) ?></td>
                    <td>
                        <?= e(date('M j, Y', strtotime($b['event_date']))) ?>
                        <?php if (!empty($b['event_time'])): ?>
                            <br><small class="text-muted"><?= e(substr((string) $b['event_time'], 0, 5)) ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?= e($b['package_name'] ?? '—') ?></td>
                    <td><?= (int) $b['guest_count'] ?></td>
                    <td><?= formatMoney((float) $b['total_amount']) ?></td>
                    <td><?= paymentStatusBadge($b['payment_status']) ?></td>
                    <td><?= orderStatusBadge($b['status']) ?></td>
                    <td>
                        <select class="form-select form-select-sm" aria-label="Update booking status" onchange="updateBookingStatus(<?= (int) $b['id'] ?>, this.value)">
                            <?php foreach ($config['booking_statuses'] as $key => $statusInfo): ?>
                                <option value="<?= e($key) ?>" <?= $b['status'] === $key ? 'selected' : '' ?>><?= e($statusInfo['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
};

include ROOT_PATH . '/includes/dashboard-layout.php';
?>

<!-- Overview -->
<section class="dashboard-section" id="overview">
    <div class="kpi-grid mb-4">
        <?= renderKpiCard("Today's Orders", (string) $analytics['orders_today'], 'bag-check', 'primary') ?>
        <?= renderKpiCard('Pending Orders', (string) $analytics['orders_pending'], 'hourglass-split', 'warning') ?>
        <?= renderKpiCard("Today's Revenue", formatMoney($analytics['revenue_today']), 'cash-coin', 'success') ?>
        <?= renderKpiCard('Monthly Revenue', formatMoney($analytics['revenue_month']), 'graph-up-arrow', 'accent') ?>
        <?= renderKpiCard('Upcoming Events', (string) $analytics['upcoming_events'], 'calendar-event', 'info') ?>
        <?= renderKpiCard('Total Customers', (string) $analytics['total_customers'], 'people', 'primary') ?>
        <?= renderKpiCard('Outstanding Payments', formatMoney($analytics['outstanding']), 'exclamation-circle', 'danger') ?>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-7">
            <div class="panel-card h-100">
                <div class="panel-header">
                    <h5 class="mb-0"><i class="bi bi-graph-up"></i> Revenue — Last 7 Days</h5>
                </div>
                <div class="panel-body">
                    <canvas id="revenueChart" height="120" aria-label="Revenue chart last 7 days"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="panel-card h-100">
                <div class="panel-header">
                    <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Orders by Status</h5>
                </div>
                <div class="panel-body">
                    <canvas id="ordersChart" height="180" aria-label="Orders by status chart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="panel-card">
                <div class="panel-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-receipt"></i> Recent Orders</h5>
                    <a href="#orders" class="btn btn-sm btn-outline-secondary">View all</a>
                </div>
                <div class="panel-body p-0">
                    <?php if (empty($recentOrders)): ?>
                        <?= renderEmptyState('inbox', 'No recent orders', 'New customer orders will show up here.') ?>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table data-table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Reference</th>
                                    <th>Customer</th>
                                    <th>Event Date</th>
                                    <th>Package</th>
                                    <th>Amount</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td><strong><?= e($order['reference_no']) ?></strong></td>
                                    <td><?= e($order['first_name'] . ' ' . $order['last_name']) ?></td>
                                    <td><?= e(date('M j, Y', strtotime($order['event_date']))) ?></td>
                                    <td><?= e($order['package_name'] ?? '—') ?></td>
                                    <td><?= formatMoney((float) $order['total_amount']) ?></td>
                                    <td><?= paymentStatusBadge($order['payment_status']) ?></td>
                                    <td><?= orderStatusBadge($order['status']) ?></td>
                                    <td>
                                        <a href="<?= e(api_url('invoice') . '?booking_id=' . (int) $order['id']) ?>" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener">
                                            <i class="bi bi-file-earmark-pdf"></i> Invoice
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
        </div>
        <div class="col-lg-4">
            <div class="panel-card">
                <div class="panel-header">
                    <h5 class="mb-0"><i class="bi bi-stars"></i> Upcoming Events</h5>
                </div>
                <div class="panel-body">
                    <?php if (empty($upcomingEvents)): ?>
                        <?= renderEmptyState('calendar-x', 'No upcoming events', 'Scheduled events will appear here.') ?>
                    <?php else: ?>
                        <div class="event-cards">
                        <?php foreach ($upcomingEvents as $event): ?>
                            <div class="event-card mb-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong><?= e($event['event_type'] ?? 'Event') ?></strong>
                                        <div class="text-muted small"><?= e($event['first_name'] . ' ' . $event['last_name']) ?></div>
                                    </div>
                                    <?= orderStatusBadge($event['status']) ?>
                                </div>
                                <div class="mt-2 small">
                                    <i class="bi bi-calendar3"></i> <?= e(date('D, M j, Y', strtotime($event['event_date']))) ?>
                                    <?php if (!empty($event['event_time'])): ?>
                                        · <?= e(substr((string) $event['event_time'], 0, 5)) ?>
                                    <?php endif; ?>
                                </div>
                                <div class="small text-muted">
                                    <?= e($event['package_name'] ?? $event['service_name'] ?? 'Custom package') ?>
                                    · <?= (int) $event['guest_count'] ?> guests
                                </div>
                                <div class="small mt-1"><strong><?= formatMoney((float) $event['total_amount']) ?></strong></div>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Orders -->
<section class="dashboard-section mt-4" id="orders">
    <div class="panel-card">
        <div class="panel-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-bag-check"></i> All Orders</h5>
            <span class="badge bg-primary"><?= count($allBookings) ?> total</span>
        </div>
        <div class="panel-body p-0">
            <?php $renderBookingsTable($allBookings); ?>
        </div>
    </div>
</section>

<!-- Bookings -->
<section class="dashboard-section mt-4" id="bookings">
    <div class="panel-card">
        <div class="panel-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-calendar-event"></i> All Bookings</h5>
            <span class="badge bg-info"><?= (int) $analytics['active_bookings'] ?> active</span>
        </div>
        <div class="panel-body p-0">
            <?php $renderBookingsTable($allBookings); ?>
        </div>
    </div>
</section>

<!-- Customers -->
<section class="dashboard-section mt-4" id="customers">
    <div class="panel-card">
        <div class="panel-header">
            <h5 class="mb-0"><i class="bi bi-people"></i> Customers</h5>
        </div>
        <div class="panel-body p-0">
            <?php if (empty($customers)): ?>
                <?= renderEmptyState('person-x', 'No customers yet', 'Customer accounts will appear here after registration.') ?>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table data-table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Bookings</th>
                            <th>Total Spend</th>
                            <th>Loyalty</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td><strong><?= e($customer['first_name'] . ' ' . $customer['last_name']) ?></strong></td>
                            <td><?= e($customer['email']) ?></td>
                            <td><?= e($customer['phone'] ?? '—') ?></td>
                            <td><?= (int) $customer['booking_count'] ?></td>
                            <td><?= formatMoney((float) $customer['total_spend']) ?></td>
                            <td><?= (int) $customer['loyalty_points'] ?> pts</td>
                            <td><?= e(date('M j, Y', strtotime($customer['created_at']))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Menu -->
<section class="dashboard-section mt-4" id="menu">
    <div class="panel-card">
        <div class="panel-header">
            <h5 class="mb-0"><i class="bi bi-egg-fried"></i> Menu Items</h5>
        </div>
        <div class="panel-body p-0">
            <?php if (empty($menuItems)): ?>
                <?= renderEmptyState('egg-fried', 'No menu items', 'Add menu items to display them here.') ?>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table data-table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Dietary</th>
                            <th>Availability</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($menuItems as $item): ?>
                        <tr>
                            <td><strong><?= e($item['name']) ?></strong></td>
                            <td><?= e($item['category']) ?></td>
                            <td><?= formatMoney((float) $item['price']) ?></td>
                            <td><?= getDietaryBadges($item) ?: '<span class="text-muted small">—</span>' ?></td>
                            <td>
                                <?php if ($item['is_active']): ?>
                                    <span class="badge badge-soft badge-success"><i class="bi bi-check-circle"></i> Available</span>
                                <?php else: ?>
                                    <span class="badge badge-soft badge-secondary"><i class="bi bi-x-circle"></i> Unavailable</span>
                                <?php endif; ?>
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

<!-- Packages -->
<section class="dashboard-section mt-4" id="packages">
    <div class="panel-card">
        <div class="panel-header">
            <h5 class="mb-0"><i class="bi bi-box-seam"></i> Catering Packages</h5>
        </div>
        <div class="panel-body">
            <?php if (empty($packages)): ?>
                <?= renderEmptyState('box-seam', 'No packages', 'Create catering packages to offer to customers.') ?>
            <?php else: ?>
            <div class="row g-4">
                <?php foreach ($packages as $pkg):
                    $features = json_decode($pkg['features'] ?? '[]', true) ?: [];
                ?>
                <div class="col-md-6 col-xl-4">
                    <div class="panel-card h-100 package-card">
                        <div class="panel-header">
                            <h6 class="mb-0"><?= e($pkg['name']) ?></h6>
                            <span class="badge bg-secondary text-uppercase"><?= e($pkg['tier']) ?></span>
                        </div>
                        <div class="panel-body">
                            <div class="mb-2">
                                <span class="h4 mb-0"><?= formatMoney((float) $pkg['price_per_guest']) ?></span>
                                <span class="text-muted small">/ guest</span>
                            </div>
                            <p class="text-muted small"><?= e($pkg['description'] ?? '') ?></p>
                            <?php if (!empty($features)): ?>
                            <ul class="small mb-0 ps-3">
                                <?php foreach ($features as $feature): ?>
                                    <li><?= e(is_string($feature) ? $feature : ($feature['name'] ?? json_encode($feature))) ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                            <div class="mt-3 small text-muted">
                                <?= (int) $pkg['min_guests'] ?>–<?= (int) $pkg['max_guests'] ?> guests
                                · <?= $pkg['is_active'] ? 'Active' : 'Inactive' ?>
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

<!-- Events -->
<section class="dashboard-section mt-4" id="events">
    <div class="panel-card">
        <div class="panel-header">
            <h5 class="mb-0"><i class="bi bi-stars"></i> Events Timeline</h5>
        </div>
        <div class="panel-body">
            <?php if (empty($eventsTimeline)): ?>
                <?= renderEmptyState('calendar-event', 'No upcoming events', 'Confirmed and pending events will appear on the timeline.') ?>
            <?php else: ?>
            <div class="events-timeline">
                <?php foreach ($eventsTimeline as $event): ?>
                <div class="timeline-item d-flex gap-3 mb-4">
                    <div class="timeline-date text-center flex-shrink-0">
                        <div class="fw-bold"><?= e(date('M', strtotime($event['event_date']))) ?></div>
                        <div class="h4 mb-0"><?= e(date('j', strtotime($event['event_date']))) ?></div>
                    </div>
                    <div class="timeline-content flex-grow-1 panel-card p-3">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                            <div>
                                <strong><?= e($event['event_type'] ?? 'Event') ?></strong>
                                <span class="text-muted"> — <?= e($event['reference_no']) ?></span>
                                <div class="small text-muted"><?= e($event['first_name'] . ' ' . $event['last_name']) ?></div>
                            </div>
                            <?= orderStatusBadge($event['status']) ?>
                        </div>
                        <div class="small mt-2">
                            <i class="bi bi-geo-alt"></i> <?= e($event['venue']) ?>
                        </div>
                        <div class="small text-muted mt-1">
                            <?= e($event['package_name'] ?? $event['service_name'] ?? 'Custom') ?>
                            · <?= (int) $event['guest_count'] ?> guests
                            · <?= formatMoney((float) $event['total_amount']) ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Payments -->
<section class="dashboard-section mt-4" id="payments">
    <div class="panel-card">
        <div class="panel-header">
            <h5 class="mb-0"><i class="bi bi-credit-card"></i> Payments</h5>
        </div>
        <div class="panel-body p-0">
            <?php if (empty($payments)): ?>
                <?= renderEmptyState('credit-card', 'No payments recorded', 'Payment transactions will appear here.') ?>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table data-table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Booking</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td><?= e($payment['reference_no']) ?></td>
                            <td><?= e($payment['booking_ref']) ?></td>
                            <td><?= e($payment['first_name'] . ' ' . $payment['last_name']) ?></td>
                            <td><?= formatMoney((float) $payment['amount']) ?></td>
                            <td><?= e($config['payment_methods'][$payment['method']] ?? $payment['method']) ?></td>
                            <td>
                                <span class="badge bg-<?= $payment['status'] === 'completed' ? 'success' : ($payment['status'] === 'pending' ? 'warning' : 'danger') ?>">
                                    <?= e(ucfirst($payment['status'])) ?>
                                </span>
                            </td>
                            <td><?= e(date('M j, Y', strtotime($payment['created_at']))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Invoices -->
<section class="dashboard-section mt-4" id="invoices">
    <div class="panel-card">
        <div class="panel-header">
            <h5 class="mb-0"><i class="bi bi-receipt"></i> Invoices</h5>
        </div>
        <div class="panel-body p-0">
            <?php if (empty($invoices)): ?>
                <?= renderEmptyState('receipt', 'No invoices yet', 'Invoices generated from bookings will appear here.') ?>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table data-table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Booking</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Paid</th>
                            <th>Status</th>
                            <th>Due Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($invoices as $invoice): ?>
                        <tr>
                            <td><strong><?= e($invoice['invoice_no']) ?></strong></td>
                            <td><?= e($invoice['booking_ref']) ?></td>
                            <td><?= e($invoice['first_name'] . ' ' . $invoice['last_name']) ?></td>
                            <td><?= formatMoney((float) $invoice['total_amount']) ?></td>
                            <td><?= formatMoney((float) $invoice['amount_paid']) ?></td>
                            <td>
                                <span class="badge bg-<?= $invoice['status'] === 'paid' ? 'success' : ($invoice['status'] === 'overdue' ? 'danger' : 'warning') ?>">
                                    <?= e(ucfirst($invoice['status'])) ?>
                                </span>
                            </td>
                            <td><?= $invoice['due_date'] ? e(date('M j, Y', strtotime($invoice['due_date']))) : '—' ?></td>
                            <td>
                                <a href="<?= e(api_url('invoice') . '?booking_id=' . (int) $invoice['booking_id']) ?>" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener">
                                    <i class="bi bi-download"></i> PDF
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

<!-- Calendar -->
<section class="dashboard-section mt-4" id="calendar">
    <div class="panel-card">
        <div class="panel-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-calendar3"></i> <?= e(date('F Y')) ?> Calendar</h5>
            <span class="text-muted small"><?= count($calendarBookings) ?> booking<?= count($calendarBookings) === 1 ? '' : 's' ?> this month</span>
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
                            $dayBookings = $bookingsByDate[$dateKey] ?? [];
                            $isToday = $dateKey === $today;
                            ?>
                            <div class="col">
                                <div class="calendar-day p-2 <?= $isToday ? 'calendar-day-today' : '' ?> <?= !empty($dayBookings) ? 'has-bookings' : '' ?>">
                                    <div class="calendar-day-num small fw-semibold"><?= $dayCounter ?></div>
                                    <?php foreach (array_slice($dayBookings, 0, 2) as $db): ?>
                                        <div class="calendar-booking small text-truncate" title="<?= e($db['reference_no'] . ' — ' . $db['first_name'] . ' ' . $db['last_name']) ?>">
                                            <?= e($db['reference_no']) ?>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if (count($dayBookings) > 2): ?>
                                        <div class="small text-muted">+<?= count($dayBookings) - 2 ?> more</div>
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
<section class="dashboard-section mt-4" id="notifications">
    <div class="panel-card">
        <div class="panel-header">
            <h5 class="mb-0"><i class="bi bi-bell"></i> Notifications</h5>
        </div>
        <div class="panel-body">
            <?php if (empty($notifications)): ?>
                <?= renderEmptyState('bell-slash', 'No notifications', 'System alerts and updates will appear here.') ?>
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
                        <div class="small text-muted">
                            <?= e($note['first_name'] ? $note['first_name'] . ' ' . $note['last_name'] : 'System') ?>
                            · <?= e(ucfirst(str_replace('_', ' ', $note['type']))) ?>
                        </div>
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

<!-- Reports -->
<section class="dashboard-section mt-4" id="reports">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="panel-card">
                <div class="panel-header">
                    <h5 class="mb-0"><i class="bi bi-bar-chart-line"></i> Summary Statistics</h5>
                </div>
                <div class="panel-body">
                    <div class="row g-3">
                        <div class="col-sm-6 col-lg-4">
                            <div class="stat-card success h-100">
                                <div class="stat-label">Revenue (Week)</div>
                                <div class="stat-value"><?= formatMoney($analytics['revenue_week']) ?></div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            <div class="stat-card primary h-100">
                                <div class="stat-label">Completed Orders</div>
                                <div class="stat-value"><?= (int) $analytics['orders_completed'] ?></div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            <div class="stat-card warning h-100">
                                <div class="stat-label">Cancelled Orders</div>
                                <div class="stat-value"><?= (int) $analytics['orders_cancelled'] ?></div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            <div class="stat-card info h-100">
                                <div class="stat-label">New Customers (Month)</div>
                                <div class="stat-value"><?= (int) $analytics['new_customers_month'] ?></div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            <div class="stat-card primary h-100">
                                <div class="stat-label">Returning Customers</div>
                                <div class="stat-value"><?= (int) $analytics['returning_customers'] ?></div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            <div class="stat-card danger h-100">
                                <div class="stat-label">Pending Quotes</div>
                                <div class="stat-value"><?= (int) $analytics['pending_quotes'] ?></div>
                            </div>
                        </div>
                    </div>
                    <?php if (!empty($analytics['popular_packages'])): ?>
                    <div class="mt-4">
                        <h6>Popular Packages</h6>
                        <ul class="list-unstyled mb-0">
                            <?php foreach ($analytics['popular_packages'] as $pp): ?>
                                <li class="d-flex justify-content-between py-2 border-bottom">
                                    <span><?= e($pp['name']) ?></span>
                                    <strong><?= (int) $pp['cnt'] ?> bookings</strong>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="panel-card">
                <div class="panel-header">
                    <h5 class="mb-0"><i class="bi bi-download"></i> Export Reports</h5>
                </div>
                <div class="panel-body">
                    <button type="button" class="btn btn-outline-primary w-100 mb-2" onclick="exportReport('revenue')">
                        <i class="bi bi-file-earmark-bar-graph"></i> Revenue Report
                    </button>
                    <button type="button" class="btn btn-outline-primary w-100 mb-2" onclick="exportReport('bookings')">
                        <i class="bi bi-calendar-check"></i> Bookings Report
                    </button>
                    <button type="button" class="btn btn-outline-primary w-100 mb-2" onclick="exportReport('customers')">
                        <i class="bi bi-people"></i> Customer Report
                    </button>
                    <button type="button" class="btn btn-outline-primary w-100" onclick="exportReport('payments')">
                        <i class="bi bi-credit-card"></i> Payments Report
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Settings -->
<section class="dashboard-section mt-4 mb-4" id="settings">
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="panel-card">
                <div class="panel-header">
                    <h5 class="mb-0"><i class="bi bi-building"></i> Site Information</h5>
                </div>
                <div class="panel-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Brand</dt>
                        <dd class="col-sm-8">Splendid Catering</dd>
                        <dt class="col-sm-4">Platform</dt>
                        <dd class="col-sm-8"><?= e($config['name']) ?></dd>
                        <dt class="col-sm-4">Tagline</dt>
                        <dd class="col-sm-8"><?= e($config['tagline']) ?></dd>
                        <dt class="col-sm-4">Support Email</dt>
                        <dd class="col-sm-8"><?= e($config['support_email']) ?></dd>
                        <dt class="col-sm-4">Support Phone</dt>
                        <dd class="col-sm-8"><?= e($config['support_phone']) ?></dd>
                        <dt class="col-sm-4">Currency</dt>
                        <dd class="col-sm-8"><?= e($config['currency']) ?> (<?= e($config['currency_symbol']) ?>)</dd>
                        <dt class="col-sm-4">Tax Rate</dt>
                        <dd class="col-sm-8"><?= ($config['tax_rate'] * 100) ?>%</dd>
                        <dt class="col-sm-4">Demo Mode</dt>
                        <dd class="col-sm-8"><?= $config['demo_mode'] ? 'Enabled' : 'Disabled' ?></dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="panel-card">
                <div class="panel-header">
                    <h5 class="mb-0"><i class="bi bi-file-text"></i> Website Content Keys</h5>
                </div>
                <div class="panel-body p-0">
                    <?php if (empty($websiteContent)): ?>
                        <?= renderEmptyState('file-text', 'No content keys', 'Website CMS content keys will appear here.') ?>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table data-table mb-0">
                            <thead>
                                <tr>
                                    <th>Page Key</th>
                                    <th>Title</th>
                                    <th>Last Updated</th>
                                    <th>Link</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($websiteContent as $content): ?>
                                <tr>
                                    <td><code><?= e($content['page_key']) ?></code></td>
                                    <td><?= e($content['title'] ?? '—') ?></td>
                                    <td><?= $content['updated_at'] ? e(date('M j, Y', strtotime($content['updated_at']))) : '—' ?></td>
                                    <td><a href="<?= e(url($content['page_key'])) ?>" target="_blank" rel="noopener" class="small">View page</a></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<script type="application/json" id="revenueChartData"><?= json_encode($analytics['revenue_chart'], JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
<script type="application/json" id="ordersChartData"><?= json_encode($analytics['orders_chart'], JSON_HEX_TAG | JSON_HEX_AMP) ?></script>

<script>
(function () {
    const revenueData = JSON.parse(document.getElementById('revenueChartData').textContent);
    const ordersData = JSON.parse(document.getElementById('ordersChartData').textContent);
    window.__chartData = { revenue: revenueData, orders: ordersData };

    function initCharts() {
        const revenueEl = document.getElementById('revenueChart');
        const ordersEl = document.getElementById('ordersChart');
        if (!revenueEl || !ordersEl || typeof Chart === 'undefined') return;

        const gridColor = getComputedStyle(document.documentElement).getPropertyValue('--cream-dark').trim() || '#e9ecef';
        const textColor = getComputedStyle(document.documentElement).getPropertyValue('--text-light').trim() || '#6c757d';

        new Chart(revenueEl, {
            type: 'line',
            data: {
                labels: window.__chartData.revenue.map(d => d.label),
                datasets: [{
                    label: 'Revenue',
                    data: window.__chartData.revenue.map(d => d.value),
                    borderColor: '#C9A962',
                    backgroundColor: 'rgba(201, 169, 98, 0.15)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 4,
                    pointBackgroundColor: '#C9A962'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: gridColor }, ticks: { color: textColor } },
                    y: { grid: { color: gridColor }, ticks: { color: textColor }, beginAtZero: true }
                }
            }
        });

        new Chart(ordersEl, {
            type: 'doughnut',
            data: {
                labels: window.__chartData.orders.map(d => d.label),
                datasets: [{
                    data: window.__chartData.orders.map(d => d.value),
                    backgroundColor: ['#F39C12', '#3498DB', '#667eea', '#2ecc71', '#27ae60', '#e74c3c'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: textColor, boxWidth: 12, padding: 12 }
                    }
                }
            }
        });
    }

    async function updateBookingStatus(id, status) {
        try {
            const res = await fetch(window.APP_BASE + '/api/admin-action', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'update_booking_status', booking_id: id, status })
            });
            const data = await res.json();
            if (!data.success) {
                alert(data.message || 'Failed to update booking status.');
            }
        } catch (err) {
            alert('Unable to update booking status. Please try again.');
        }
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

    function exportReport(type) {
        alert('Report exported: splendid_catering_' + type + '_' + new Date().toISOString().slice(0, 10) + '.csv (simulated)');
    }

    window.initCharts = initCharts;
    window.updateBookingStatus = updateBookingStatus;
    window.exportReport = exportReport;

    document.addEventListener('DOMContentLoaded', () => {
        initCharts();
        highlightSidebarSection();
    });
    window.addEventListener('hashchange', highlightSidebarSection);
})();
</script>

<?php include ROOT_PATH . '/includes/dashboard-footer.php'; ?>
