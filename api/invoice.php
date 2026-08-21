<?php
$bookingId = (int) ($_GET['booking_id'] ?? 0);
if (!$bookingId) { http_response_code(400); exit('Booking ID required'); }

$stmt = $pdo->prepare('
    SELECT b.*, u.first_name, u.last_name, u.email, u.phone, p.name as package_name
    FROM bookings b JOIN users u ON b.customer_id = u.id
    LEFT JOIN packages p ON b.package_id = p.id WHERE b.id = ?
');
$stmt->execute([$bookingId]);
$booking = $stmt->fetch();

if (!$booking) { http_response_code(404); exit('Not found'); }

if (isLoggedIn() && !hasRole(['admin', 'finance']) && currentUser()['id'] != $booking['customer_id']) {
    http_response_code(403); exit('Access denied');
}

header('Content-Type: text/html');
?>
<!DOCTYPE html>
<html><head><title>Invoice - <?= e($booking['reference_no']) ?></title>
<style>
body{font-family:Arial,sans-serif;max-width:800px;margin:40px auto;padding:20px;color:#333}
.header{text-align:center;border-bottom:3px solid #C9A962;padding-bottom:20px;margin-bottom:30px}
.header h1{color:#C9A962;margin:0}.gold{color:#C9A962}
table{width:100%;border-collapse:collapse;margin:20px 0}
th,td{padding:10px;text-align:left;border-bottom:1px solid #eee}
.total{font-size:1.5em;color:#C9A962;font-weight:bold}
@media print{body{margin:0}}
</style></head><body>
<div class="header">
    <h1>Splendid Caterery</h1>
    <p>Smart Catering & Event Management</p>
    <p><strong>INVOICE</strong></p>
</div>
<p><strong>Reference:</strong> <?= e($booking['reference_no']) ?><br>
<strong>Date:</strong> <?= date('F j, Y') ?><br>
<strong>QR Code:</strong> <?= e($booking['qr_code'] ?? 'N/A') ?></p>
<h3>Bill To</h3>
<p><?= e($booking['first_name'].' '.$booking['last_name']) ?><br>
<?= e($booking['email']) ?><br><?= e($booking['phone']) ?></p>
<h3>Event Details</h3>
<p><?= e($booking['event_type']) ?> · <?= e($booking['event_date']) ?> · <?= (int)$booking['guest_count'] ?> guests<br>
Venue: <?= e($booking['venue']) ?><br>Package: <?= e($booking['package_name'] ?? 'Custom') ?></p>
<table>
<tr><th>Description</th><th>Amount</th></tr>
<tr><td>Catering Services (<?= (int)$booking['guest_count'] ?> guests)</td><td><?= formatMoney((float)$booking['subtotal']) ?></td></tr>
<?php if ($booking['discount_amount'] > 0): ?>
<tr><td>Discount</td><td>-<?= formatMoney((float)$booking['discount_amount']) ?></td></tr>
<?php endif; ?>
<tr><td>VAT (12.5%)</td><td><?= formatMoney((float)$booking['tax_amount']) ?></td></tr>
<tr><td><strong>Total</strong></td><td class="total"><?= formatMoney((float)$booking['total_amount']) ?></td></tr>
<tr><td>Amount Paid</td><td><?= formatMoney((float)$booking['amount_paid']) ?></td></tr>
<tr><td><strong>Balance Due</strong></td><td><strong><?= formatMoney((float)$booking['total_amount'] - $booking['amount_paid']) ?></strong></td></tr>
</table>
<p style="text-align:center;color:#999;margin-top:40px">Thank you for choosing Splendid Caterery!</p>
<script>window.onload=()=>window.print()</script>
</body></html>
