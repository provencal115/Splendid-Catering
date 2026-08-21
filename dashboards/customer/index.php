<?php
requireAuth('customer');
$pageTitle = 'My Dashboard';
$user = currentUser();
$userId = (int) $user['id'];

$bookingsStmt = $pdo->prepare('
    SELECT b.*, p.name AS package_name, s.name AS service_name
    FROM bookings b
    LEFT JOIN packages p ON b.package_id = p.id
    LEFT JOIN service_types s ON b.service_type_id = s.id
    WHERE b.customer_id = ?
    ORDER BY b.created_at DESC
');
$bookingsStmt->execute([$userId]);
$bookings = $bookingsStmt->fetchAll();

$quotationsStmt = $pdo->prepare('
    SELECT q.*, p.name AS package_name, s.name AS service_name
    FROM quotations q
    LEFT JOIN packages p ON q.package_id = p.id
    LEFT JOIN service_types s ON q.service_type_id = s.id
    WHERE q.customer_id = ?
    ORDER BY q.created_at DESC
');
$quotationsStmt->execute([$userId]);
$quotations = $quotationsStmt->fetchAll();

$paymentsStmt = $pdo->prepare('
    SELECT pay.*, b.reference_no AS booking_ref
    FROM payments pay
    JOIN bookings b ON pay.booking_id = b.id
    WHERE b.customer_id = ?
    ORDER BY pay.created_at DESC
');
$paymentsStmt->execute([$userId]);
$payments = $paymentsStmt->fetchAll();

$invoicesStmt = $pdo->prepare('
    SELECT i.*, b.reference_no AS booking_ref
    FROM invoices i
    JOIN bookings b ON i.booking_id = b.id
    WHERE i.customer_id = ?
    ORDER BY i.created_at DESC
');
$invoicesStmt->execute([$userId]);
$invoices = $invoicesStmt->fetchAll();

$reviewsStmt = $pdo->prepare('
    SELECT r.*, b.reference_no, b.event_type, b.event_date
    FROM reviews r
    JOIN bookings b ON r.booking_id = b.id
    WHERE r.customer_id = ?
    ORDER BY r.created_at DESC
');
$reviewsStmt->execute([$userId]);
$reviews = $reviewsStmt->fetchAll();

$reviewableStmt = $pdo->prepare('
    SELECT b.*, p.name AS package_name, s.name AS service_name
    FROM bookings b
    LEFT JOIN packages p ON b.package_id = p.id
    LEFT JOIN service_types s ON b.service_type_id = s.id
    LEFT JOIN reviews r ON r.booking_id = b.id
    WHERE b.customer_id = ? AND b.status = \'completed\' AND r.id IS NULL
    ORDER BY b.event_date DESC
');
$reviewableStmt->execute([$userId]);
$reviewableBookings = $reviewableStmt->fetchAll();

$totalBookings = count($bookings);
$loyaltyPoints = (int) ($user['loyalty_points'] ?? 0);
$pendingPayments = count(array_filter($bookings, fn ($b) => in_array($b['payment_status'], ['unpaid', 'partial'], true)));

$upcomingStmt = $pdo->prepare('
    SELECT COUNT(*) FROM bookings
    WHERE customer_id = ? AND event_date >= CURDATE() AND status NOT IN (\'cancelled\', \'completed\')
');
$upcomingStmt->execute([$userId]);
$upcomingEvents = (int) $upcomingStmt->fetchColumn();

$recentBookings = array_slice($bookings, 0, 5);
$depositPercent = (float) ($config['booking_deposit_percent'] ?? 0.30);
$loyaltyPer100 = (int) ($config['loyalty_points_per_100'] ?? 10);

$quoteStatusBadge = static function (string $status): string {
    $map = [
        'pending' => ['Pending', 'warning'],
        'approved' => ['Approved', 'success'],
        'rejected' => ['Rejected', 'danger'],
        'converted' => ['Converted', 'info'],
        'expired' => ['Expired', 'secondary'],
    ];
    $info = $map[$status] ?? [ucfirst($status), 'secondary'];
    return '<span class="badge badge-soft badge-' . $info[1] . '">' . e($info[0]) . '</span>';
};

include ROOT_PATH . '/includes/dashboard-layout.php';
?>

<!-- Overview -->
<section class="dashboard-section" id="overview">
    <div class="kpi-grid mb-4">
        <?= renderKpiCard('Total Bookings', (string) $totalBookings, 'calendar-check', 'primary', null, url('dashboard/customer') . '#bookings', 'View bookings') ?>
        <?= renderKpiCard('Loyalty Points', (string) $loyaltyPoints, 'gift', 'accent', null, url('dashboard/customer') . '#loyalty', 'Rewards info') ?>
        <?= renderKpiCard('Pending Payments', (string) $pendingPayments, 'exclamation-circle', 'warning', null, url('dashboard/customer') . '#payments', 'Payment history') ?>
        <?= renderKpiCard('Upcoming Events', (string) $upcomingEvents, 'calendar-event', 'info', null, url('dashboard/customer') . '#bookings', 'See schedule') ?>
    </div>

    <div class="panel-card mb-4">
        <div class="panel-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0"><i class="bi bi-calendar-check"></i> Recent Bookings</h5>
            <div class="d-flex gap-2">
                <a href="<?= e(url('book')) ?>" class="btn btn-sm btn-accent"><i class="bi bi-plus-lg"></i> Book an Event</a>
                <a href="#bookings" class="btn btn-sm btn-outline-secondary">View all</a>
            </div>
        </div>
        <div class="panel-body p-0">
            <?php if (empty($recentBookings)): ?>
                <?= renderEmptyState('calendar-x', 'No bookings yet', 'Plan your next event with Splendid Catering.', url('book'), 'Book Now') ?>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table data-table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Event</th>
                            <th>Date</th>
                            <th>Guests</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Payment</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($recentBookings as $b): ?>
                        <tr>
                            <td><strong><?= e($b['reference_no']) ?></strong></td>
                            <td>
                                <?= e($b['service_name'] ?? $b['event_type'] ?? 'Event') ?>
                                <?php if (!empty($b['package_name'])): ?>
                                    <br><small class="text-muted"><?= e($b['package_name']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= e(date('M j, Y', strtotime($b['event_date']))) ?>
                                <?php if (!empty($b['event_time'])): ?>
                                    <br><small class="text-muted"><?= e(substr((string) $b['event_time'], 0, 5)) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= (int) $b['guest_count'] ?></td>
                            <td><?= formatMoney((float) $b['total_amount']) ?></td>
                            <td><?= orderStatusBadge($b['status']) ?></td>
                            <td><?= paymentStatusBadge($b['payment_status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Bookings -->
<section class="dashboard-section mt-4" id="bookings">
    <div class="panel-card">
        <div class="panel-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0"><i class="bi bi-calendar-check"></i> My Bookings</h5>
            <a href="<?= e(url('book')) ?>" class="btn btn-sm btn-accent"><i class="bi bi-plus-lg"></i> New Booking</a>
        </div>
        <div class="panel-body p-0">
            <?php if (empty($bookings)): ?>
                <?= renderEmptyState('calendar-x', 'No bookings yet', 'Book your first catering event to get started.', url('book'), 'Book Now') ?>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table data-table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Event</th>
                            <th>Date</th>
                            <th>Guests</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($bookings as $b):
                        $depositAmount = round((float) $b['total_amount'] * $depositPercent, 2);
                    ?>
                        <tr>
                            <td>
                                <strong><?= e($b['reference_no']) ?></strong>
                                <?php if (!empty($b['qr_code'])): ?>
                                    <br><small class="text-muted"><i class="bi bi-qr-code"></i> <?= e($b['qr_code']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= e($b['service_name'] ?? $b['event_type'] ?? 'Event') ?>
                                <?php if (!empty($b['package_name'])): ?>
                                    <br><small class="text-muted"><?= e($b['package_name']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= e(date('M j, Y', strtotime($b['event_date']))) ?>
                                <?php if (!empty($b['event_time'])): ?>
                                    <br><small class="text-muted"><?= e(substr((string) $b['event_time'], 0, 5)) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= (int) $b['guest_count'] ?></td>
                            <td><?= formatMoney((float) $b['total_amount']) ?></td>
                            <td><?= orderStatusBadge($b['status']) ?></td>
                            <td><?= paymentStatusBadge($b['payment_status']) ?></td>
                            <td class="text-nowrap">
                                <?php if ($b['payment_status'] !== 'paid'): ?>
                                    <button type="button" class="btn btn-sm btn-accent" onclick="payDeposit(<?= (int) $b['id'] ?>, <?= $depositAmount ?>)">
                                        Pay Deposit
                                    </button>
                                <?php endif; ?>
                                <a href="<?= e(api_url('invoice') . '?booking_id=' . (int) $b['id']) ?>" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener" title="Download invoice">
                                    <i class="bi bi-download"></i>
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

<!-- Quotations -->
<section class="dashboard-section mt-4" id="quotes">
    <div class="panel-card">
        <div class="panel-header">
            <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Quotations</h5>
        </div>
        <div class="panel-body p-0">
            <?php if (empty($quotations)): ?>
                <?= renderEmptyState('file-earmark-text', 'No quotations yet', 'Request a quote when booking or contact us for a custom package.', url('book'), 'Get a Quote') ?>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table data-table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Event</th>
                            <th>Date</th>
                            <th>Guests</th>
                            <th>Total</th>
                            <th>Valid Until</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($quotations as $q): ?>
                        <tr>
                            <td><strong><?= e($q['reference_no']) ?></strong></td>
                            <td>
                                <?= e($q['service_name'] ?? $q['event_type'] ?? 'Event') ?>
                                <?php if (!empty($q['package_name'])): ?>
                                    <br><small class="text-muted"><?= e($q['package_name']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= e(date('M j, Y', strtotime($q['event_date']))) ?>
                                <?php if (!empty($q['event_time'])): ?>
                                    <br><small class="text-muted"><?= e(substr((string) $q['event_time'], 0, 5)) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= (int) $q['guest_count'] ?></td>
                            <td><?= formatMoney((float) $q['total_amount']) ?></td>
                            <td><?= !empty($q['valid_until']) ? e(date('M j, Y', strtotime($q['valid_until']))) : '—' ?></td>
                            <td><?= $quoteStatusBadge($q['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Payments -->
<section class="dashboard-section mt-4" id="payments">
    <div class="panel-card">
        <div class="panel-header">
            <h5 class="mb-0"><i class="bi bi-credit-card"></i> Payment History</h5>
        </div>
        <div class="panel-body p-0">
            <?php if (empty($payments)): ?>
                <?= renderEmptyState('credit-card', 'No payments yet', 'Your payment transactions will appear here after you make a deposit or full payment.') ?>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table data-table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Payment Ref</th>
                            <th>Booking</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td><strong><?= e($payment['reference_no']) ?></strong></td>
                            <td><?= e($payment['booking_ref']) ?></td>
                            <td><?= formatMoney((float) $payment['amount']) ?></td>
                            <td><?= e($config['payment_methods'][$payment['method']] ?? $payment['method']) ?></td>
                            <td>
                                <span class="badge badge-soft badge-<?= $payment['status'] === 'completed' ? 'success' : ($payment['status'] === 'pending' ? 'warning' : 'danger') ?>">
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
                <?= renderEmptyState('receipt', 'No invoices yet', 'Invoices for your bookings will appear here once generated.') ?>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table data-table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Booking</th>
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
                            <td><?= formatMoney((float) $invoice['total_amount']) ?></td>
                            <td><?= formatMoney((float) $invoice['amount_paid']) ?></td>
                            <td>
                                <span class="badge badge-soft badge-<?= $invoice['status'] === 'paid' ? 'success' : ($invoice['status'] === 'overdue' ? 'danger' : 'warning') ?>">
                                    <?= e(ucfirst($invoice['status'])) ?>
                                </span>
                            </td>
                            <td><?= !empty($invoice['due_date']) ? e(date('M j, Y', strtotime($invoice['due_date']))) : '—' ?></td>
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

<!-- Reviews -->
<section class="dashboard-section mt-4" id="reviews">
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="panel-card h-100">
                <div class="panel-header">
                    <h5 class="mb-0"><i class="bi bi-star"></i> My Reviews</h5>
                </div>
                <div class="panel-body">
                    <?php if (empty($reviews)): ?>
                        <?= renderEmptyState('star', 'No reviews yet', 'Share your experience after a completed event.') ?>
                    <?php else: ?>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($reviews as $review): ?>
                        <div class="border rounded p-3">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                                <div>
                                    <?= renderStars((int) $review['rating']) ?>
                                    <?php if (!empty($review['title'])): ?>
                                        <strong class="d-block mt-1"><?= e($review['title']) ?></strong>
                                    <?php endif; ?>
                                </div>
                                <span class="badge badge-soft badge-<?= $review['is_approved'] ? 'success' : 'warning' ?>">
                                    <?= $review['is_approved'] ? 'Published' : 'Pending approval' ?>
                                </span>
                            </div>
                            <p class="mb-2"><?= e($review['comment']) ?></p>
                            <small class="text-muted">
                                <?= e($review['event_type'] ?? 'Event') ?> · <?= e($review['reference_no']) ?>
                                · <?= e(date('M j, Y', strtotime($review['created_at']))) ?>
                            </small>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="panel-card h-100">
                <div class="panel-header">
                    <h5 class="mb-0"><i class="bi bi-pencil-square"></i> Write a Review</h5>
                </div>
                <div class="panel-body">
                    <?php if (empty($reviewableBookings)): ?>
                        <?= renderEmptyState('check-circle', 'Nothing to review', 'Reviews can be submitted for completed events you have not yet reviewed.') ?>
                    <?php else: ?>
                    <form id="reviewForm" class="review-form">
                        <div class="mb-3">
                            <label class="form-label" for="reviewBooking">Booking</label>
                            <select class="form-select" id="reviewBooking" name="booking_id" required>
                                <option value="">Select a completed booking</option>
                                <?php foreach ($reviewableBookings as $rb): ?>
                                <option value="<?= (int) $rb['id'] ?>">
                                    <?= e($rb['reference_no']) ?> — <?= e(date('M j, Y', strtotime($rb['event_date']))) ?>
                                    (<?= e($rb['package_name'] ?? $rb['service_name'] ?? 'Event') ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="reviewRating">Rating</label>
                            <select class="form-select" id="reviewRating" name="rating" required>
                                <option value="">Choose rating</option>
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                <option value="<?= $i ?>"><?= $i ?> star<?= $i > 1 ? 's' : '' ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="reviewTitle">Title <span class="text-muted">(optional)</span></label>
                            <input type="text" class="form-control" id="reviewTitle" name="title" maxlength="200" placeholder="Summarize your experience">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="reviewComment">Comment</label>
                            <textarea class="form-control" id="reviewComment" name="comment" rows="4" required placeholder="Tell us about your event..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-accent w-100" id="reviewSubmitBtn">
                            <i class="bi bi-send"></i> Submit Review
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Loyalty -->
<section class="dashboard-section mt-4 mb-4" id="loyalty">
    <div class="panel-card">
        <div class="panel-header">
            <h5 class="mb-0"><i class="bi bi-gift"></i> Loyalty Rewards</h5>
        </div>
        <div class="panel-body">
            <div class="row g-4 align-items-center">
                <div class="col-md-4 text-center">
                    <div class="display-4 fw-bold text-accent"><?= $loyaltyPoints ?></div>
                    <p class="text-muted mb-0">Available Points</p>
                </div>
                <div class="col-md-8">
                    <h6 class="mb-3">How it works</h6>
                    <ul class="mb-0 ps-3">
                        <li class="mb-2">Earn <strong><?= $loyaltyPer100 ?> points</strong> for every <?= e($config['currency_symbol']) ?>100 spent on catering.</li>
                        <li class="mb-2">Redeem <strong>500 points</strong> for <?= formatMoney(50) ?> off your next booking.</li>
                        <li>Points are added automatically when your booking payment is completed.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
async function payDeposit(bookingId, amount) {
    if (!confirm('Pay deposit of <?= e($config['currency_symbol']) ?>' + Number(amount).toFixed(2) + '?')) return;

    try {
        const res = await fetch((window.APP_BASE || '') + '/api/payment', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ booking_id: bookingId, method: 'mtn_momo', amount })
        });
        const result = await res.json();
        alert(result.message || (result.success ? 'Payment processed' : 'Payment failed'));
        if (result.success) location.reload();
    } catch {
        alert('Unable to process payment. Please try again.');
    }
}

document.getElementById('reviewForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.target;
    const btn = document.getElementById('reviewSubmitBtn');
    btn.disabled = true;

    try {
        const res = await fetch(<?= json_encode(api_url('review')) ?>, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                csrf_token: <?= json_encode(csrfToken()) ?>,
                booking_id: parseInt(form.booking_id.value, 10),
                rating: parseInt(form.rating.value, 10),
                title: form.title.value.trim(),
                comment: form.comment.value.trim()
            })
        });
        const result = await res.json();
        alert(result.message || (result.success ? 'Review submitted' : 'Unable to submit review'));
        if (result.success) location.reload();
    } catch {
        alert('Unable to submit review. Please try again.');
    } finally {
        btn.disabled = false;
    }
});
</script>

<?php include ROOT_PATH . '/includes/dashboard-footer.php'; ?>
