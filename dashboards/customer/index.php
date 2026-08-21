<?php
requireAuth('customer');
$pageTitle = 'My Dashboard';
$user = currentUser();

$bookings = $pdo->prepare('SELECT b.*, p.name as package_name, s.name as service_name FROM bookings b LEFT JOIN packages p ON b.package_id = p.id LEFT JOIN service_types s ON b.service_type_id = s.id WHERE b.customer_id = ? ORDER BY b.created_at DESC');
$bookings->execute([$user['id']]);
$bookings = $bookings->fetchAll();

$quotations = $pdo->prepare('SELECT q.*, p.name as package_name FROM quotations q LEFT JOIN packages p ON q.package_id = p.id WHERE q.customer_id = ? ORDER BY q.created_at DESC');
$quotations->execute([$user['id']]);
$quotations = $quotations->fetchAll();

$payments = $pdo->prepare('SELECT pay.*, b.reference_no FROM payments pay JOIN bookings b ON pay.booking_id = b.id WHERE b.customer_id = ? ORDER BY pay.created_at DESC');
$payments->execute([$user['id']]);
$payments = $payments->fetchAll();

$invoices = $pdo->prepare('SELECT * FROM invoices WHERE customer_id = ? ORDER BY created_at DESC');
$invoices->execute([$user['id']]);
$invoices = $invoices->fetchAll();

include ROOT_PATH . '/includes/dashboard-layout.php';
?>

<div class="row g-4 mb-4">
    <div class="col-md-4"><div class="stat-card primary"><div class="stat-value"><?= count($bookings) ?></div><div class="stat-label">Total Bookings</div></div></div>
    <div class="col-md-4"><div class="stat-card success"><div class="stat-value"><?= (int)$user['loyalty_points'] ?></div><div class="stat-label">Loyalty Points</div></div></div>
    <div class="col-md-4"><div class="stat-card warning"><div class="stat-value"><?= count(array_filter($bookings, fn($b) => in_array($b['payment_status'], ['unpaid','partial']))) ?></div><div class="stat-label">Pending Payments</div></div></div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 id="bookings">My Bookings</h5>
    <a href="<?= url('book') ?>" class="btn btn-gold btn-sm"><i class="bi bi-plus"></i> New Booking</a>
</div>

<div class="dashboard-table mb-4">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead><tr><th>Reference</th><th>Event</th><th>Date</th><th>Guests</th><th>Total</th><th>Status</th><th>Payment</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if (empty($bookings)): ?>
            <tr><td colspan="8" class="text-center text-muted py-4">No bookings yet. <a href="<?= url('book') ?>">Book your first event</a></td></tr>
            <?php else: foreach ($bookings as $b): ?>
            <tr>
                <td><strong><?= e($b['reference_no']) ?></strong><?php if ($b['qr_code']): ?><br><small class="text-muted"><i class="bi bi-qr-code"></i> <?= e($b['qr_code']) ?></small><?php endif; ?></td>
                <td><?= e($b['service_name'] ?? $b['event_type']) ?><br><small><?= e($b['package_name'] ?? '') ?></small></td>
                <td><?= e($b['event_date']) ?><br><small><?= e(substr($b['event_time'] ?? '', 0, 5)) ?></small></td>
                <td><?= (int)$b['guest_count'] ?></td>
                <td><?= formatMoney((float)$b['total_amount']) ?></td>
                <td><?= getStatusBadge($b['status']) ?></td>
                <td><span class="badge bg-<?= $b['payment_status'] === 'paid' ? 'success' : 'warning' ?>"><?= ucfirst($b['payment_status']) ?></span></td>
                <td>
                    <?php if ($b['payment_status'] !== 'paid'): ?>
                    <button class="btn btn-sm btn-gold" onclick="payDeposit(<?= $b['id'] ?>, <?= $b['total_amount'] * 0.3 ?>)">Pay Deposit</button>
                    <?php endif; ?>
                    <a href="/api/invoice?booking_id=<?= $b['id'] ?>" class="btn btn-sm btn-outline-secondary" target="_blank"><i class="bi bi-download"></i></a>
                </td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="dashboard-table mb-4" id="payments">
    <div class="p-3 border-bottom"><h6 class="mb-0">Payment History</h6></div>
    <div class="table-responsive">
        <table class="table"><thead><tr><th>Ref</th><th>Booking</th><th>Amount</th><th>Method</th><th>Status</th><th>Date</th></tr></thead>
        <tbody><?php foreach ($payments as $p): ?><tr><td><?= e($p['reference_no']) ?></td><td><?= e($p['reference_no']) ?></td><td><?= formatMoney((float)$p['amount']) ?></td><td><?= e($config['payment_methods'][$p['method']] ?? $p['method']) ?></td><td><span class="badge bg-<?= $p['status']==='completed'?'success':'warning' ?>"><?= ucfirst($p['status']) ?></span></td><td><?= date('M j, Y', strtotime($p['created_at'])) ?></td></tr><?php endforeach; ?></tbody>
        </table>
    </div>
</div>

<div id="loyalty" class="stat-card">
    <h6><i class="bi bi-gift"></i> Loyalty Rewards</h6>
    <p class="mb-0">You have <strong><?= (int)$user['loyalty_points'] ?></strong> points. Earn 10 points for every ₵100 spent. Redeem 500 points for ₵50 off your next booking!</p>
</div>

<script>
async function payDeposit(bookingId, amount) {
    if (!confirm('Pay deposit of ₵' + amount.toFixed(2) + '?')) return;
    const result = await processPayment(bookingId, 'mtn_momo', amount);
    alert(result.message || 'Payment processed');
    if (result.success) location.reload();
}
</script>

<?php include ROOT_PATH . '/includes/dashboard-footer.php'; ?>
