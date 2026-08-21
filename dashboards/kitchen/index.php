<?php
requireAuth('chef');
$pageTitle = 'Kitchen Dashboard';
$user = currentUser();

$orders = $pdo->query('
    SELECT ko.*, m.name as item_name, m.image_url, b.reference_no, b.event_date, b.guest_count
    FROM kitchen_orders ko
    JOIN menu_items m ON ko.menu_item_id = m.id
    JOIN bookings b ON ko.booking_id = b.id
    WHERE ko.status != "served"
    ORDER BY b.event_date ASC, ko.status ASC
')->fetchAll();

$inventory = $pdo->query('SELECT * FROM inventory_items ORDER BY current_stock / GREATEST(min_stock_level, 1) ASC')->fetchAll();
$lowStock = array_filter($inventory, fn($i) => $i['current_stock'] <= $i['min_stock_level']);

include ROOT_PATH . '/includes/dashboard-layout.php';
?>

<div class="row g-4 mb-4">
    <div class="col-md-3"><div class="stat-card warning"><div class="stat-value"><?= count(array_filter($orders, fn($o) => $o['status'] === 'pending')) ?></div><div class="stat-label">Pending Orders</div></div></div>
    <div class="col-md-3"><div class="stat-card primary"><div class="stat-value"><?= count(array_filter($orders, fn($o) => $o['status'] === 'preparing')) ?></div><div class="stat-label">Preparing</div></div></div>
    <div class="col-md-3"><div class="stat-card success"><div class="stat-value"><?= count(array_filter($orders, fn($o) => $o['status'] === 'ready')) ?></div><div class="stat-label">Ready</div></div></div>
    <div class="col-md-3"><div class="stat-card danger"><div class="stat-value"><?= count($lowStock) ?></div><div class="stat-label">Low Stock Items</div></div></div>
</div>

<h5 class="mb-3">Production Orders</h5>
<div class="row g-4 mb-4">
    <?php foreach ($orders as $o): ?>
    <div class="col-md-6 col-lg-4">
        <div class="feature-card">
            <div class="card-image" style="aspect-ratio:16/9;">
                <img src="<?= e($o['image_url']) ?>" alt="<?= e($o['item_name']) ?>">
            </div>
            <div class="card-body-custom">
                <div class="d-flex justify-content-between">
                    <h6><?= e($o['item_name']) ?></h6>
                    <span class="badge bg-<?= match($o['status']) { 'pending'=>'warning', 'preparing'=>'primary', 'ready'=>'success', default=>'secondary' } ?>"><?= ucfirst($o['status']) ?></span>
                </div>
                <p class="small text-muted mb-2"><?= e($o['reference_no']) ?> · Qty: <strong><?= (int)$o['quantity'] ?></strong></p>
                <p class="small mb-3">Event: <?= e($o['event_date']) ?> · <?= (int)$o['guest_count'] ?> guests</p>
                <div class="d-flex gap-2">
                    <?php if ($o['status'] === 'pending'): ?>
                    <button class="btn btn-sm btn-primary flex-fill" onclick="updateOrder(<?= $o['id'] ?>, 'preparing')">Start Prep</button>
                    <?php elseif ($o['status'] === 'preparing'): ?>
                    <button class="btn btn-sm btn-success flex-fill" onclick="updateOrder(<?= $o['id'] ?>, 'ready')">Mark Ready</button>
                    <?php endif; ?>
                    <button class="btn btn-sm btn-outline-danger" onclick="reportShortage(<?= $o['id'] ?>)"><i class="bi bi-exclamation-triangle"></i></button>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="dashboard-table" id="inventory">
    <div class="p-3 border-bottom"><h6 class="mb-0"><i class="bi bi-robot"></i> AI Inventory Prediction</h6></div>
    <div class="p-3" id="inventoryPrediction">Loading predictions...</div>
    <div class="table-responsive">
        <table class="table"><thead><tr><th>Ingredient</th><th>Stock</th><th>Min Level</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
        <?php foreach ($inventory as $item): ?>
        <tr class="<?= $item['current_stock'] <= $item['min_stock_level'] ? 'table-warning' : '' ?>">
            <td><?= e($item['name']) ?></td>
            <td><?= $item['current_stock'] ?> <?= e($item['unit']) ?></td>
            <td><?= $item['min_stock_level'] ?> <?= e($item['unit']) ?></td>
            <td><?= $item['current_stock'] <= $item['min_stock_level'] ? '<span class="badge bg-danger">Low</span>' : '<span class="badge bg-success">OK</span>' ?></td>
            <td><button class="btn btn-sm btn-outline-primary" onclick="requestRestock(<?= $item['id'] ?>)">Request Restock</button></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </div>
</div>

<script>
async function updateOrder(id, status) {
    await fetch('/api/kitchen-action', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({action:'update_order', order_id:id, status}) });
    location.reload();
}
async function reportShortage(id) {
    await fetch('/api/kitchen-action', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({action:'report_shortage', order_id:id}) });
    alert('Shortage reported to inventory manager');
}
async function requestRestock(id) {
    await fetch('/api/kitchen-action', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({action:'request_restock', item_id:id}) });
    alert('Restock request submitted');
}
fetch('/api/ai-forecast?type=inventory').then(r=>r.json()).then(d => {
    document.getElementById('inventoryPrediction').innerHTML = d.inventory_html || '';
});
</script>

<?php include ROOT_PATH . '/includes/dashboard-footer.php'; ?>
