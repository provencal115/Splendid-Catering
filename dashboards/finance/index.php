<?php
requireAuth('finance');
$pageTitle = 'Finance Dashboard';
$user = currentUser();
$userId = (int) $user['id'];

$dailyRevenue = (float) $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='completed' AND DATE(created_at)=CURDATE()")->fetchColumn();
$monthlyRevenue = (float) $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='completed' AND MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE())")->fetchColumn();
$yearlyRevenue = (float) $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='completed' AND YEAR(created_at)=YEAR(CURDATE())")->fetchColumn();
$outstanding = (float) $pdo->query("SELECT COALESCE(SUM(total_amount - amount_paid),0) FROM bookings WHERE payment_status IN ('unpaid','partial')")->fetchColumn();
$pendingVerification = (int) $pdo->query("SELECT COUNT(*) FROM payments WHERE status='pending'")->fetchColumn();
$refundedTotal = (float) $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='refunded'")->fetchColumn();

$payments = $pdo->query('
    SELECT p.*, b.reference_no AS booking_ref, u.first_name, u.last_name
    FROM payments p
    JOIN bookings b ON p.booking_id = b.id
    JOIN users u ON b.customer_id = u.id
    ORDER BY p.created_at DESC
')->fetchAll();

$invoices = $pdo->query('
    SELECT i.*, u.first_name, u.last_name, b.reference_no AS booking_ref
    FROM invoices i
    JOIN users u ON i.customer_id = u.id
    JOIN bookings b ON i.booking_id = b.id
    ORDER BY i.created_at DESC
')->fetchAll();

$refunds = $pdo->query('
    SELECT p.*, b.reference_no AS booking_ref, u.first_name, u.last_name
    FROM payments p
    JOIN bookings b ON p.booking_id = b.id
    JOIN users u ON b.customer_id = u.id
    WHERE p.status = \'refunded\'
    ORDER BY p.created_at DESC
')->fetchAll();

$pendingPayments = array_values(array_filter($payments, fn($p) => $p['status'] === 'pending'));

$notifications = getUserNotifications($pdo, $userId, 30);

$revenueChart = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-{$i} days"));
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='completed' AND DATE(created_at)=?");
    $stmt->execute([$date]);
    $revenueChart[] = ['label' => date('D', strtotime($date)), 'value' => (float) $stmt->fetchColumn()];
}

$paymentStatusBadge = static function (string $status): string {
    $map = [
        'completed' => ['Completed', 'success'],
        'pending' => ['Pending', 'warning'],
        'failed' => ['Failed', 'danger'],
        'refunded' => ['Refunded', 'secondary'],
    ];
    $info = $map[$status] ?? [ucfirst($status), 'secondary'];
    return '<span class="badge badge-soft badge-' . $info[1] . '">' . e($info[0]) . '</span>';
};

include ROOT_PATH . '/includes/dashboard-layout.php';
?>

<!-- Overview -->
<section class="dashboard-section" id="overview">
    <div class="kpi-grid mb-4">
        <?= renderKpiCard("Today's Revenue", formatMoney($dailyRevenue), 'cash-coin', 'success') ?>
        <?= renderKpiCard('Monthly Revenue', formatMoney($monthlyRevenue), 'graph-up-arrow', 'primary') ?>
        <?= renderKpiCard('Yearly Revenue', formatMoney($yearlyRevenue), 'cash-stack', 'info') ?>
        <?= renderKpiCard('Outstanding', formatMoney($outstanding), 'exclamation-circle', 'danger', null, '#payments', 'View payments') ?>
        <?= renderKpiCard('Pending Verification', (string) $pendingVerification, 'hourglass-split', 'warning', null, '#payments', 'Review') ?>
        <?= renderKpiCard('Total Refunded', formatMoney($refundedTotal), 'arrow-return-left', 'secondary', null, '#refunds', 'View refunds') ?>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="panel-card h-100">
                <div class="panel-header">
                    <h5 class="mb-0"><i class="bi bi-graph-up"></i> Revenue — Last 7 Days</h5>
                </div>
                <div class="panel-body">
                    <canvas id="financeRevenueChart" height="120" aria-label="Revenue chart last 7 days"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="panel-card h-100">
                <div class="panel-header">
                    <h5 class="mb-0"><i class="bi bi-exclamation-circle"></i> Pending Verification</h5>
                </div>
                <div class="panel-body">
                    <?php if (empty($pendingPayments)): ?>
                        <?= renderEmptyState('check-circle', 'All clear', 'No payments awaiting verification.') ?>
                    <?php else: ?>
                        <?php foreach (array_slice($pendingPayments, 0, 5) as $pp): ?>
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                            <div>
                                <strong class="small"><?= e($pp['booking_ref']) ?></strong>
                                <div class="text-muted small"><?= formatMoney((float) $pp['amount']) ?></div>
                            </div>
                            <button type="button" class="btn btn-sm btn-success" onclick="verifyPayment(<?= (int) $pp['id'] ?>)">Verify</button>
                        </div>
                        <?php endforeach; ?>
                        <?php if (count($pendingPayments) > 5): ?>
                        <a href="#payments" class="small">View all <?= count($pendingPayments) ?> pending payments</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Payments -->
<section class="dashboard-section mt-4" id="payments">
    <div class="panel-card">
        <div class="panel-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-credit-card-2-front"></i> Payments</h5>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportReport('payments')">
                <i class="bi bi-download"></i> Export
            </button>
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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td><strong><?= e($payment['reference_no']) ?></strong></td>
                            <td><?= e($payment['booking_ref']) ?></td>
                            <td><?= e($payment['first_name'] . ' ' . $payment['last_name']) ?></td>
                            <td><?= formatMoney((float) $payment['amount']) ?></td>
                            <td><?= e($config['payment_methods'][$payment['method']] ?? $payment['method']) ?></td>
                            <td><?= $paymentStatusBadge($payment['status']) ?></td>
                            <td><?= e(date('M j, Y', strtotime($payment['created_at']))) ?></td>
                            <td>
                                <?php if ($payment['status'] === 'pending'): ?>
                                <button type="button" class="btn btn-sm btn-success" onclick="verifyPayment(<?= (int) $payment['id'] ?>)">Verify</button>
                                <?php elseif ($payment['status'] === 'completed'): ?>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="processRefund(<?= (int) $payment['id'] ?>)">Refund</button>
                                <?php else: ?>
                                <span class="text-muted small">—</span>
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

<!-- Invoices -->
<section class="dashboard-section mt-4" id="invoices">
    <div class="panel-card">
        <div class="panel-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-receipt-cutoff"></i> Invoices</h5>
            <span class="badge bg-primary"><?= count($invoices) ?> total</span>
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
                                <span class="badge badge-soft badge-<?= $invoice['status'] === 'paid' ? 'success' : ($invoice['status'] === 'overdue' ? 'danger' : 'warning') ?>">
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

<!-- Refunds -->
<section class="dashboard-section mt-4" id="refunds">
    <div class="panel-card">
        <div class="panel-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-arrow-return-left"></i> Refunded Payments</h5>
            <span class="badge badge-soft badge-secondary"><?= count($refunds) ?> refunds · <?= formatMoney($refundedTotal) ?></span>
        </div>
        <div class="panel-body p-0">
            <?php if (empty($refunds)): ?>
                <?= renderEmptyState('arrow-return-left', 'No refunds processed', 'Refunded payments will be listed here.') ?>
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
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($refunds as $refund): ?>
                        <tr>
                            <td><strong><?= e($refund['reference_no']) ?></strong></td>
                            <td><?= e($refund['booking_ref']) ?></td>
                            <td><?= e($refund['first_name'] . ' ' . $refund['last_name']) ?></td>
                            <td><?= formatMoney((float) $refund['amount']) ?></td>
                            <td><?= e($config['payment_methods'][$refund['method']] ?? $refund['method']) ?></td>
                            <td><?= e(date('M j, Y', strtotime($refund['created_at']))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
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
                    <h5 class="mb-0"><i class="bi bi-bar-chart-line"></i> Financial Summary</h5>
                </div>
                <div class="panel-body">
                    <div class="row g-3 mb-4">
                        <div class="col-sm-6 col-lg-3">
                            <div class="stat-card success h-100">
                                <div class="stat-label">Today</div>
                                <div class="stat-value"><?= formatMoney($dailyRevenue) ?></div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="stat-card primary h-100">
                                <div class="stat-label">This Month</div>
                                <div class="stat-value"><?= formatMoney($monthlyRevenue) ?></div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="stat-card info h-100">
                                <div class="stat-label">This Year</div>
                                <div class="stat-value"><?= formatMoney($yearlyRevenue) ?></div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="stat-card danger h-100">
                                <div class="stat-label">Outstanding</div>
                                <div class="stat-value"><?= formatMoney($outstanding) ?></div>
                            </div>
                        </div>
                    </div>
                    <canvas id="financeReportsChart" height="140" aria-label="Weekly revenue report chart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="panel-card">
                <div class="panel-header">
                    <h5 class="mb-0"><i class="bi bi-download"></i> Export Reports</h5>
                </div>
                <div class="panel-body">
                    <button type="button" class="btn btn-outline-primary w-100 mb-2" onclick="exportReport('daily')">
                        <i class="bi bi-file-pdf"></i> Daily Report (PDF)
                    </button>
                    <button type="button" class="btn btn-outline-primary w-100 mb-2" onclick="exportReport('monthly')">
                        <i class="bi bi-file-excel"></i> Monthly Report (Excel)
                    </button>
                    <button type="button" class="btn btn-outline-primary w-100 mb-2" onclick="exportReport('yearly')">
                        <i class="bi bi-file-bar-graph"></i> Yearly Summary
                    </button>
                    <button type="button" class="btn btn-outline-primary w-100" onclick="exportReport('payments')">
                        <i class="bi bi-credit-card"></i> Payments Report
                    </button>
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
                <?= renderEmptyState('bell-slash', 'No notifications', 'Finance alerts and updates will appear here.') ?>
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

<script type="application/json" id="financeChartData"><?= json_encode($revenueChart, JSON_HEX_TAG | JSON_HEX_AMP) ?></script>

<script>
(function () {
    const chartData = JSON.parse(document.getElementById('financeChartData').textContent);

    function initFinanceCharts() {
        const overviewEl = document.getElementById('financeRevenueChart');
        const reportsEl = document.getElementById('financeReportsChart');
        if (typeof Chart === 'undefined') return;

        const gridColor = getComputedStyle(document.documentElement).getPropertyValue('--cream-dark').trim() || '#e9ecef';
        const textColor = getComputedStyle(document.documentElement).getPropertyValue('--text-light').trim() || '#6c757d';
        const chartConfig = {
            type: 'line',
            data: {
                labels: chartData.map(d => d.label),
                datasets: [{
                    label: 'Revenue',
                    data: chartData.map(d => d.value),
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
        };

        if (overviewEl) new Chart(overviewEl, chartConfig);
        if (reportsEl) new Chart(reportsEl, JSON.parse(JSON.stringify(chartConfig)));
    }

    async function verifyPayment(id) {
        await fetch(window.APP_BASE + '/api/finance-action', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'verify_payment', payment_id: id })
        });
        location.reload();
    }

    async function processRefund(id) {
        if (!confirm('Process refund for this payment?')) return;
        await fetch(window.APP_BASE + '/api/finance-action', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'refund', payment_id: id })
        });
        location.reload();
    }

    function exportReport(type) {
        alert('Report exported: splendid_catering_' + type + '_' + new Date().toISOString().slice(0, 10) + '.csv (simulated)');
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

    window.verifyPayment = verifyPayment;
    window.processRefund = processRefund;
    window.exportReport = exportReport;

    document.addEventListener('DOMContentLoaded', () => {
        initFinanceCharts();
        highlightSidebarSection();
    });
    window.addEventListener('hashchange', highlightSidebarSection);
})();
</script>

<?php include ROOT_PATH . '/includes/dashboard-footer.php'; ?>
