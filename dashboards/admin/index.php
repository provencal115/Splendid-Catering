<?php
requireAuth('admin');
$pageTitle = 'Admin Dashboard';
$stats = getDashboardStats($pdo);

$bookings = $pdo->query('
    SELECT b.*, u.first_name, u.last_name, p.name as package_name
    FROM bookings b JOIN users u ON b.customer_id = u.id
    LEFT JOIN packages p ON b.package_id = p.id
    ORDER BY b.event_date DESC LIMIT 20
')->fetchAll();

$customers = $pdo->query("SELECT * FROM users WHERE role = 'customer' ORDER BY created_at DESC LIMIT 10")->fetchAll();
$staff = $pdo->query("SELECT * FROM users WHERE role != 'customer' ORDER BY role")->fetchAll();
$menuItems = $pdo->query('SELECT m.*, c.name as category FROM menu_items m JOIN menu_categories c ON m.category_id = c.id ORDER BY m.name LIMIT 15')->fetchAll();
$allPackages = $pdo->query('SELECT * FROM packages ORDER BY sort_order')->fetchAll();
$promos = $pdo->query('SELECT * FROM promotions ORDER BY created_at DESC')->fetchAll();
$allTestimonials = $pdo->query('SELECT * FROM testimonials ORDER BY created_at DESC')->fetchAll();
$allGallery = $pdo->query('SELECT * FROM gallery ORDER BY sort_order')->fetchAll();

include ROOT_PATH . '/includes/dashboard-layout.php';
?>

<div class="row g-4 mb-4">
    <div class="col-6 col-lg-3"><div class="stat-card primary"><div class="d-flex justify-content-between"><div><div class="stat-value"><?= $stats['total_customers'] ?></div><div class="stat-label">Customers</div></div><div class="stat-icon"><i class="bi bi-people"></i></div></div></div></div>
    <div class="col-6 col-lg-3"><div class="stat-card success"><div class="d-flex justify-content-between"><div><div class="stat-value"><?= $stats['total_bookings'] ?></div><div class="stat-label">Bookings</div></div><div class="stat-icon"><i class="bi bi-calendar-check"></i></div></div></div></div>
    <div class="col-6 col-lg-3"><div class="stat-card warning"><div class="d-flex justify-content-between"><div><div class="stat-value"><?= formatMoney($stats['total_revenue']) ?></div><div class="stat-label">Revenue</div></div><div class="stat-icon"><i class="bi bi-cash-stack"></i></div></div></div></div>
    <div class="col-6 col-lg-3"><div class="stat-card info"><div class="d-flex justify-content-between"><div><div class="stat-value"><?= $stats['customer_satisfaction'] ?>★</div><div class="stat-label">Satisfaction</div></div><div class="stat-icon"><i class="bi bi-star"></i></div></div></div></div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="dashboard-table" id="bookings">
            <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Recent Bookings</h6>
                <span class="badge bg-primary"><?= $stats['upcoming_events'] ?> upcoming</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead><tr><th>Ref</th><th>Customer</th><th>Event</th><th>Guests</th><th>Total</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php foreach ($bookings as $b): ?>
                    <tr>
                        <td><strong><?= e($b['reference_no']) ?></strong></td>
                        <td><?= e($b['first_name'] . ' ' . $b['last_name']) ?></td>
                        <td><?= e($b['event_date']) ?><br><small class="text-muted"><?= e($b['package_name'] ?? '') ?></small></td>
                        <td><?= (int)$b['guest_count'] ?></td>
                        <td><?= formatMoney((float)$b['total_amount']) ?></td>
                        <td><?= getStatusBadge($b['status']) ?></td>
                        <td>
                            <select class="form-select form-select-sm" onchange="updateBookingStatus(<?= $b['id'] ?>, this.value)">
                                <?php foreach ($config['booking_statuses'] as $key => $s): ?>
                                <option value="<?= $key ?>" <?= $b['status'] === $key ? 'selected' : '' ?>><?= e($s['label']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="dashboard-table mb-4">
            <div class="p-3 border-bottom"><h6 class="mb-0"><i class="bi bi-robot"></i> AI Sales Forecast</h6></div>
            <div class="p-3" id="aiForecastPanel">
                <p class="text-muted small">Loading forecast...</p>
            </div>
        </div>
        <div class="stat-card">
            <h6>Quick Stats</h6>
            <ul class="list-unstyled mb-0 small">
                <li class="d-flex justify-content-between py-2 border-bottom"><span>Today's Deliveries</span><strong><?= $stats['today_deliveries'] ?></strong></li>
                <li class="d-flex justify-content-between py-2 border-bottom"><span>Pending Payments</span><strong class="text-warning"><?= $stats['pending_payments'] ?></strong></li>
                <li class="d-flex justify-content-between py-2 border-bottom"><span>Popular Package</span><strong><?= e($stats['popular_package']) ?></strong></li>
                <li class="d-flex justify-content-between py-2"><span>Top Menu Item</span><strong><?= e($stats['popular_menu_item']) ?></strong></li>
            </ul>
        </div>
    </div>
</div>

<div class="dashboard-table mb-4" id="customers">
    <div class="p-3 border-bottom"><h6 class="mb-0">Customers</h6></div>
    <div class="table-responsive">
        <table class="table"><thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Loyalty</th><th>Joined</th></tr></thead>
        <tbody><?php foreach ($customers as $c): ?><tr><td><?= e($c['first_name'].' '.$c['last_name']) ?></td><td><?= e($c['email']) ?></td><td><?= e($c['phone']) ?></td><td><?= (int)$c['loyalty_points'] ?> pts</td><td><?= date('M j, Y', strtotime($c['created_at'])) ?></td></tr><?php endforeach; ?></tbody>
        </table>
    </div>
</div>

<div class="dashboard-table mb-4" id="staff">
    <div class="p-3 border-bottom"><h6 class="mb-0">Staff Accounts</h6></div>
    <div class="table-responsive">
        <table class="table"><thead><tr><th>Name</th><th>Role</th><th>Email</th><th>Status</th></tr></thead>
        <tbody><?php foreach ($staff as $s): ?><tr><td><?= e($s['first_name'].' '.$s['last_name']) ?></td><td><span class="badge bg-secondary"><?= e($config['roles'][$s['role']] ?? $s['role']) ?></span></td><td><?= e($s['email']) ?></td><td><?= $s['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>' ?></td></tr><?php endforeach; ?></tbody>
        </table>
    </div>
</div>

<div class="dashboard-table mb-4" id="menu">
    <div class="p-3 border-bottom"><h6 class="mb-0">Menu Items</h6></div>
    <div class="table-responsive">
        <table class="table"><thead><tr><th>Item</th><th>Category</th><th>Price</th><th>Dietary</th><th>Popularity</th></tr></thead>
        <tbody><?php foreach ($menuItems as $m): ?><tr><td><strong><?= e($m['name']) ?></strong></td><td><?= e($m['category']) ?></td><td><?= formatMoney((float)$m['price']) ?></td><td><?= getDietaryBadges($m) ?></td><td><div class="progress progress-thin"><div class="progress-bar bg-warning" style="width:<?= min(100,$m['popularity_score']) ?>%"></div></div></td></tr><?php endforeach; ?></tbody>
        </table>
    </div>
</div>

<script>
async function updateBookingStatus(id, status) {
    await fetch('/api/admin-action', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({action:'update_booking_status', booking_id:id, status}) });
}
fetch('/api/ai-forecast').then(r=>r.json()).then(d => {
    document.getElementById('aiForecastPanel').innerHTML = d.html || '<p>No forecast data</p>';
});
</script>

<?php include ROOT_PATH . '/includes/dashboard-footer.php'; ?>
