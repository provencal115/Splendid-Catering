<?php
requireAuth('finance');
$pageTitle = 'Finance Dashboard';

$dailyRevenue = (float) $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='completed' AND DATE(created_at)=CURDATE()")->fetchColumn();
$monthlyRevenue = (float) $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='completed' AND MONTH(created_at)=MONTH(CURDATE())")->fetchColumn();
$yearlyRevenue = (float) $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='completed' AND YEAR(created_at)=YEAR(CURDATE())")->fetchColumn();
$outstanding = (float) $pdo->query("SELECT COALESCE(SUM(total_amount - amount_paid),0) FROM bookings WHERE payment_status IN ('unpaid','partial')")->fetchColumn();

$payments = $pdo->query('
    SELECT p.*, b.reference_no, u.first_name, u.last_name
    FROM payments p JOIN bookings b ON p.booking_id = b.id JOIN users u ON b.customer_id = u.id
    ORDER BY p.created_at DESC LIMIT 20
')->fetchAll();

$invoices = $pdo->query('
    SELECT i.*, u.first_name, u.last_name, b.reference_no
    FROM invoices i JOIN users u ON i.customer_id = u.id JOIN bookings b ON i.booking_id = b.id
    ORDER BY i.created_at DESC LIMIT 15
')->fetchAll();

$pendingPayments = $pdo->query("SELECT p.*, b.reference_no FROM payments p JOIN bookings b ON p.booking_id = b.id WHERE p.status = 'pending'")->fetchAll();

include ROOT_PATH . '/includes/dashboard-layout.php';
?>

<div class="row g-4 mb-4">
    <div class="col-6 col-lg-3"><div class="stat-card success"><div class="stat-value"><?= formatMoney($dailyRevenue) ?></div><div class="stat-label">Today's Revenue</div></div></div>
    <div class="col-6 col-lg-3"><div class="stat-card primary"><div class="stat-value"><?= formatMoney($monthlyRevenue) ?></div><div class="stat-label">Monthly Revenue</div></div></div>
    <div class="col-6 col-lg-3"><div class="stat-card info"><div class="stat-value"><?= formatMoney($yearlyRevenue) ?></div><div class="stat-label">Yearly Revenue</div></div></div>
    <div class="col-6 col-lg-3"><div class="stat-card danger"><div class="stat-value"><?= formatMoney($outstanding) ?></div><div class="stat-label">Outstanding</div></div></div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="dashboard-table" id="payments">
            <div class="p-3 border-bottom d-flex justify-content-between">
                <h6 class="mb-0">Recent Payments</h6>
                <button class="btn btn-sm btn-outline-secondary" onclick="exportReport('payments')"><i class="bi bi-download"></i> Export</button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead><tr><th>Ref</th><th>Booking</th><th>Customer</th><th>Amount</th><th>Method</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php foreach ($payments as $p): ?>
                    <tr>
                        <td><?= e($p['reference_no']) ?></td>
                        <td><?= e($p['reference_no']) ?></td>
                        <td><?= e($p['first_name'].' '.$p['last_name']) ?></td>
                        <td><?= formatMoney((float)$p['amount']) ?></td>
                        <td><?= e($config['payment_methods'][$p['method']] ?? $p['method']) ?></td>
                        <td><span class="badge bg-<?= $p['status']==='completed'?'success':($p['status']==='pending'?'warning':'danger') ?>"><?= ucfirst($p['status']) ?></span></td>
                        <td>
                            <?php if ($p['status'] === 'pending'): ?>
                            <button class="btn btn-sm btn-success" onclick="verifyPayment(<?= $p['id'] ?>)">Verify</button>
                            <?php endif; ?>
                            <?php if ($p['status'] === 'completed'): ?>
                            <button class="btn btn-sm btn-outline-danger" onclick="processRefund(<?= $p['id'] ?>)">Refund</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="dashboard-table mb-4" id="reports">
            <div class="p-3 border-bottom"><h6 class="mb-0">Financial Reports</h6></div>
            <div class="p-3">
                <button class="btn btn-outline-primary w-100 mb-2" onclick="exportReport('daily')"><i class="bi bi-file-pdf"></i> Daily Report (PDF)</button>
                <button class="btn btn-outline-primary w-100 mb-2" onclick="exportReport('monthly')"><i class="bi bi-file-excel"></i> Monthly Report (Excel)</button>
                <button class="btn btn-outline-primary w-100" onclick="exportReport('yearly')"><i class="bi bi-file-bar-graph"></i> Yearly Summary</button>
            </div>
        </div>
        <?php if ($pendingPayments): ?>
        <div class="dashboard-table">
            <div class="p-3 border-bottom"><h6 class="mb-0 text-warning"><i class="bi bi-exclamation-circle"></i> Pending Verification</h6></div>
            <div class="p-3">
                <?php foreach ($pendingPayments as $pp): ?>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small"><?= e($pp['reference_no']) ?> · <?= formatMoney((float)$pp['amount']) ?></span>
                    <button class="btn btn-sm btn-success" onclick="verifyPayment(<?= $pp['id'] ?>)">Verify</button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="dashboard-table" id="invoices">
    <div class="p-3 border-bottom"><h6 class="mb-0">Invoices</h6></div>
    <div class="table-responsive">
        <table class="table"><thead><tr><th>Invoice #</th><th>Booking</th><th>Customer</th><th>Total</th><th>Paid</th><th>Status</th><th>Due</th></tr></thead>
        <tbody><?php foreach ($invoices as $inv): ?><tr><td><?= e($inv['invoice_no']) ?></td><td><?= e($inv['reference_no']) ?></td><td><?= e($inv['first_name'].' '.$inv['last_name']) ?></td><td><?= formatMoney((float)$inv['total_amount']) ?></td><td><?= formatMoney((float)$inv['amount_paid']) ?></td><td><span class="badge bg-<?= $inv['status']==='paid'?'success':'warning' ?>"><?= ucfirst($inv['status']) ?></span></td><td><?= e($inv['due_date']) ?></td></tr><?php endforeach; ?></tbody>
    </div>
</div>

<script>
async function verifyPayment(id) {
    await fetch('/api/finance-action', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({action:'verify_payment', payment_id:id}) });
    location.reload();
}
async function processRefund(id) {
    if (confirm('Process refund for this payment?')) {
        await fetch('/api/finance-action', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({action:'refund', payment_id:id}) });
        location.reload();
    }
}
function exportReport(type) {
    alert('Report exported: ' + type + '_report_' + new Date().toISOString().slice(0,10) + '.pdf (simulated)');
}
</script>

<?php include ROOT_PATH . '/includes/dashboard-footer.php'; ?>
