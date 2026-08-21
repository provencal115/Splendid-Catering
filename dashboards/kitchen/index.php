<?php
requireAuth('chef');
$pageTitle = 'Kitchen Dashboard';
$user = currentUser();
$userId = (int) $user['id'];

$orders = $pdo->query('
    SELECT ko.*, m.name AS item_name, m.image_url, b.reference_no, b.event_date, b.event_time, b.guest_count, b.venue
    FROM kitchen_orders ko
    JOIN menu_items m ON ko.menu_item_id = m.id
    JOIN bookings b ON ko.booking_id = b.id
    WHERE ko.status != \'served\'
    ORDER BY b.event_date ASC, b.event_time ASC, FIELD(ko.status, \'pending\', \'preparing\', \'ready\')
')->fetchAll();

$recipes = $pdo->query('
    SELECT r.*, m.name AS item_name, m.image_url, m.price,
           (SELECT COUNT(*) FROM recipe_ingredients ri WHERE ri.recipe_id = r.id) AS ingredient_count
    FROM recipes r
    JOIN menu_items m ON r.menu_item_id = m.id
    ORDER BY m.name ASC
')->fetchAll();

$recipeIngredients = [];
if (!empty($recipes)) {
    $recipeIds = array_column($recipes, 'id');
    $placeholders = implode(',', array_fill(0, count($recipeIds), '?'));
    $stmt = $pdo->prepare("SELECT * FROM recipe_ingredients WHERE recipe_id IN ($placeholders) ORDER BY ingredient_name");
    $stmt->execute($recipeIds);
    foreach ($stmt->fetchAll() as $ingredient) {
        $recipeIngredients[(int) $ingredient['recipe_id']][] = $ingredient;
    }
}

$inventory = $pdo->query('
    SELECT * FROM inventory_items
    ORDER BY current_stock / GREATEST(min_stock_level, 1) ASC
')->fetchAll();

$notifications = getUserNotifications($pdo, $userId, 30);
$kitchenUnread = getUnreadNotificationCount($pdo, $userId);

$pendingCount = count(array_filter($orders, fn($o) => $o['status'] === 'pending'));
$preparingCount = count(array_filter($orders, fn($o) => $o['status'] === 'preparing'));
$readyCount = count(array_filter($orders, fn($o) => $o['status'] === 'ready'));
$lowStock = array_filter($inventory, fn($i) => (float) $i['current_stock'] <= (float) $i['min_stock_level']);

$scheduleByDate = [];
foreach ($orders as $order) {
    $scheduleByDate[$order['event_date']][] = $order;
}
ksort($scheduleByDate);

$kitchenStatusBadge = static function (string $status): string {
    $map = [
        'pending' => ['Pending', 'warning'],
        'preparing' => ['Preparing', 'primary'],
        'ready' => ['Ready', 'success'],
        'served' => ['Served', 'secondary'],
    ];
    $info = $map[$status] ?? [ucfirst($status), 'secondary'];
    return '<span class="badge badge-soft badge-' . $info[1] . '">' . e($info[0]) . '</span>';
};

include ROOT_PATH . '/includes/dashboard-layout.php';
?>

<!-- Overview -->
<section class="dash-page dashboard-section" id="overview">
    <?= renderDashboardWelcome(
        'Welcome back, ' . $user['first_name'],
        'Monitor production orders, prep schedules, and inventory levels.',
        [
            ['url' => '#schedule', 'label' => 'Prep Schedule', 'icon' => 'clock-history', 'class' => 'btn-accent'],
            ['url' => '#inventory', 'label' => 'Inventory', 'icon' => 'box'],
            ['url' => '#recipes', 'label' => 'Recipes', 'icon' => 'book'],
        ],
        $kitchenUnread
    ) ?>

    <?= renderDashSectionHeading('Kitchen summary', 'Production status at a glance') ?>
    <div class="dash-stats-primary">
        <?= renderKpiCard('Pending Orders', (string) $pendingCount, 'hourglass-split', 'warning', null, '#schedule', 'View schedule') ?>
        <?= renderKpiCard('Preparing', (string) $preparingCount, 'fire', 'primary') ?>
        <?= renderKpiCard('Ready', (string) $readyCount, 'check-circle', 'success') ?>
        <?= renderKpiCard('Low Stock Items', (string) count($lowStock), 'exclamation-triangle', 'danger', null, '#inventory', 'View inventory') ?>
    </div>

    <?= renderDashSectionHeading('Production orders', 'Active kitchen orders') ?>
    <div class="panel-card">
        <div class="panel-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-fire"></i> Production Orders</h5>
            <span class="badge bg-primary"><?= count($orders) ?> active</span>
        </div>
        <div class="panel-body">
            <?php if (empty($orders)): ?>
                <?= renderEmptyState('egg-fried', 'No active orders', 'Kitchen production orders will appear here when events are confirmed.') ?>
            <?php else: ?>
            <div class="row g-4">
                <?php foreach ($orders as $order): ?>
                <div class="col-md-6 col-xl-4">
                    <div class="panel-card h-100">
                        <?php if (!empty($order['image_url'])): ?>
                        <div class="ratio ratio-16x9">
                            <img src="<?= e($order['image_url']) ?>" alt="<?= e($order['item_name']) ?>" class="object-fit-cover rounded-top">
                        </div>
                        <?php endif; ?>
                        <div class="panel-body">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <h6 class="mb-0"><?= e($order['item_name']) ?></h6>
                                <?= $kitchenStatusBadge($order['status']) ?>
                            </div>
                            <p class="small text-muted mb-1">
                                <strong><?= e($order['reference_no']) ?></strong> · Qty: <strong><?= (int) $order['quantity'] ?></strong>
                            </p>
                            <p class="small mb-3">
                                <?= e(date('M j, Y', strtotime($order['event_date']))) ?>
                                <?php if (!empty($order['event_time'])): ?>
                                    at <?= e(substr((string) $order['event_time'], 0, 5)) ?>
                                <?php endif; ?>
                                · <?= (int) $order['guest_count'] ?> guests
                            </p>
                            <div class="d-flex gap-2">
                                <?php if ($order['status'] === 'pending'): ?>
                                <button type="button" class="btn btn-sm btn-primary flex-fill" onclick="updateOrder(<?= (int) $order['id'] ?>, 'preparing')">Start Prep</button>
                                <?php elseif ($order['status'] === 'preparing'): ?>
                                <button type="button" class="btn btn-sm btn-success flex-fill" onclick="updateOrder(<?= (int) $order['id'] ?>, 'ready')">Mark Ready</button>
                                <?php endif; ?>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="reportShortage(<?= (int) $order['id'] ?>)" title="Report shortage">
                                    <i class="bi bi-exclamation-triangle"></i>
                                </button>
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

<!-- Prep Schedule -->
<section class="dashboard-section mt-4" id="schedule">
    <div class="panel-card">
        <div class="panel-header">
            <h5 class="mb-0"><i class="bi bi-clock-history"></i> Prep Schedule</h5>
        </div>
        <div class="panel-body">
            <?php if (empty($scheduleByDate)): ?>
                <?= renderEmptyState('clock-history', 'No prep scheduled', 'Orders grouped by event date will appear here.') ?>
            <?php else: ?>
                <?php foreach ($scheduleByDate as $date => $dayOrders): ?>
                <div class="panel-card mb-4">
                    <div class="panel-header">
                        <h6 class="mb-0"><i class="bi bi-calendar3"></i> <?= e(date('l, M j, Y', strtotime($date))) ?></h6>
                        <span class="badge bg-secondary"><?= count($dayOrders) ?> item<?= count($dayOrders) === 1 ? '' : 's' ?></span>
                    </div>
                    <div class="panel-body p-0">
                        <div class="table-responsive">
                            <table class="table data-table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Booking</th>
                                        <th>Item</th>
                                        <th>Qty</th>
                                        <th>Event Time</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($dayOrders as $order): ?>
                                    <tr>
                                        <td><strong><?= e($order['reference_no']) ?></strong></td>
                                        <td><?= e($order['item_name']) ?></td>
                                        <td><?= (int) $order['quantity'] ?></td>
                                        <td><?= !empty($order['event_time']) ? e(substr((string) $order['event_time'], 0, 5)) : '—' ?></td>
                                        <td><?= $kitchenStatusBadge($order['status']) ?></td>
                                        <td>
                                            <?php if ($order['status'] === 'pending'): ?>
                                            <button type="button" class="btn btn-sm btn-primary" onclick="updateOrder(<?= (int) $order['id'] ?>, 'preparing')">Start</button>
                                            <?php elseif ($order['status'] === 'preparing'): ?>
                                            <button type="button" class="btn btn-sm btn-success" onclick="updateOrder(<?= (int) $order['id'] ?>, 'ready')">Ready</button>
                                            <?php else: ?>
                                            <span class="text-muted small">—</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Recipes -->
<section class="dashboard-section mt-4" id="recipes">
    <div class="panel-card">
        <div class="panel-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-book"></i> Recipes</h5>
            <span class="badge bg-accent"><?= count($recipes) ?> recipes</span>
        </div>
        <div class="panel-body">
            <?php if (empty($recipes)): ?>
                <?= renderEmptyState('book', 'No recipes on file', 'Recipes linked to menu items will appear here.') ?>
            <?php else: ?>
            <div class="row g-4">
                <?php foreach ($recipes as $recipe):
                    $ingredients = $recipeIngredients[(int) $recipe['id']] ?? [];
                ?>
                <div class="col-lg-6">
                    <div class="panel-card h-100">
                        <div class="panel-header d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <h6 class="mb-0"><?= e($recipe['item_name']) ?></h6>
                                <small class="text-muted">
                                    Prep: <?= (int) ($recipe['prep_time_minutes'] ?? 0) ?> min
                                    · Cook: <?= (int) ($recipe['cook_time_minutes'] ?? 0) ?> min
                                    · Serves <?= (int) ($recipe['servings'] ?? 1) ?>
                                </small>
                            </div>
                            <span class="badge badge-soft badge-info"><?= count($ingredients) ?> ingredients</span>
                        </div>
                        <div class="panel-body">
                            <p class="small mb-3"><?= nl2br(e($recipe['instructions'])) ?></p>
                            <?php if (!empty($ingredients)): ?>
                            <h6 class="small text-uppercase text-muted">Ingredients</h6>
                            <ul class="small mb-0 ps-3">
                                <?php foreach ($ingredients as $ingredient): ?>
                                <li><?= e($ingredient['quantity'] . ' ' . $ingredient['unit'] . ' ' . $ingredient['ingredient_name']) ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Inventory -->
<section class="dashboard-section mt-4" id="inventory">
    <div class="panel-card">
        <div class="panel-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-box"></i> Inventory</h5>
            <?php if (count($lowStock) > 0): ?>
            <span class="badge badge-soft badge-danger"><?= count($lowStock) ?> low stock</span>
            <?php endif; ?>
        </div>
        <div class="panel-body p-0">
            <?php if (empty($inventory)): ?>
                <?= renderEmptyState('box', 'No inventory items', 'Inventory stock levels will appear here.') ?>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table data-table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Ingredient</th>
                            <th>Category</th>
                            <th>Stock</th>
                            <th>Min Level</th>
                            <th>Status</th>
                            <th>Supplier</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($inventory as $item):
                        $isLow = (float) $item['current_stock'] <= (float) $item['min_stock_level'];
                    ?>
                        <tr class="<?= $isLow ? 'table-warning' : '' ?>">
                            <td><strong><?= e($item['name']) ?></strong></td>
                            <td><?= e($item['category'] ?? '—') ?></td>
                            <td><?= e($item['current_stock'] . ' ' . $item['unit']) ?></td>
                            <td><?= e($item['min_stock_level'] . ' ' . $item['unit']) ?></td>
                            <td>
                                <?php if ($isLow): ?>
                                    <span class="badge badge-soft badge-danger">Low</span>
                                <?php else: ?>
                                    <span class="badge badge-soft badge-success">OK</span>
                                <?php endif; ?>
                            </td>
                            <td><?= e($item['supplier'] ?? '—') ?></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="requestRestock(<?= (int) $item['id'] ?>)">Request Restock</button>
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

<!-- Notifications -->
<section class="dashboard-section mt-4 mb-4" id="notifications">
    <div class="panel-card">
        <div class="panel-header">
            <h5 class="mb-0"><i class="bi bi-bell"></i> Notifications</h5>
        </div>
        <div class="panel-body">
            <?php if (empty($notifications)): ?>
                <?= renderEmptyState('bell-slash', 'No notifications', 'Kitchen alerts and updates will appear here.') ?>
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
    async function updateOrder(id, status) {
        await fetch(window.APP_BASE + '/api/kitchen-action', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'update_order', order_id: id, status })
        });
        location.reload();
    }

    async function reportShortage(id) {
        await fetch(window.APP_BASE + '/api/kitchen-action', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'report_shortage', order_id: id })
        });
        alert('Shortage reported to inventory manager.');
    }

    async function requestRestock(id) {
        await fetch(window.APP_BASE + '/api/kitchen-action', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'request_restock', item_id: id })
        });
        alert('Restock request submitted.');
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

    window.updateOrder = updateOrder;
    window.reportShortage = reportShortage;
    window.requestRestock = requestRestock;

    document.addEventListener('DOMContentLoaded', highlightSidebarSection);
    window.addEventListener('hashchange', highlightSidebarSection);
})();
</script>

<?php include ROOT_PATH . '/includes/dashboard-footer.php'; ?>
