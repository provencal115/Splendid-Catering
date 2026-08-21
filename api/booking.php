<?php
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$guestCount = (int) ($input['guest_count'] ?? 0);
$packageId = (int) ($input['package_id'] ?? 0);
$serviceTypeId = (int) ($input['service_type_id'] ?? 0);
$eventDate = $input['event_date'] ?? '';
$eventTime = $input['event_time'] ?? '14:00';
$venue = trim($input['venue'] ?? '');
$contactEmail = trim($input['contact_email'] ?? '');
$contactName = trim($input['contact_name'] ?? '');
$contactPhone = trim($input['contact_phone'] ?? '');
$paymentMethod = $input['payment_method'] ?? 'mtn_momo';
$dietary = $input['dietary'] ?? [];
$specialRequests = trim($input['special_requests'] ?? '');
$promoCode = trim($input['promo_code'] ?? '');

if (!$guestCount || !$packageId || !$eventDate || !$venue || !$contactEmail) {
    jsonResponse(['success' => false, 'message' => 'Please fill all required fields']);
}

$stmt = $pdo->prepare('SELECT * FROM packages WHERE id = ?');
$stmt->execute([$packageId]);
$package = $stmt->fetch();
if (!$package) jsonResponse(['success' => false, 'message' => 'Invalid package']);

// Find or create customer
$customerId = null;
if (isLoggedIn()) {
    $customerId = currentUser()['id'];
} else {
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$contactEmail]);
    $existing = $stmt->fetch();
    if ($existing) {
        $customerId = $existing['id'];
    } else {
        $names = explode(' ', $contactName, 2);
        $userId = register($pdo, [
            'first_name' => $names[0] ?? 'Guest',
            'last_name' => $names[1] ?? 'Customer',
            'email' => $contactEmail,
            'phone' => $contactPhone,
            'password' => bin2hex(random_bytes(8)),
        ]);
        $customerId = $userId;
    }
}

$discount = 0;
if ($promoCode) {
    $stmt = $pdo->prepare("SELECT * FROM promotions WHERE code = ? AND is_active = 1");
    $stmt->execute([strtoupper($promoCode)]);
    $promo = $stmt->fetch();
    if ($promo) {
        $sub = $package['price_per_guest'] * $guestCount;
        $discount = $promo['discount_type'] === 'percentage' ? $sub * ($promo['discount_value']/100) : $promo['discount_value'];
    }
}

$calc = calculateQuotation((float)$package['price_per_guest'], $guestCount, $discount);
$ref = generateReference('SC');
$qrCode = 'SC-QR-' . substr($ref, 2);

$stmt = $pdo->prepare('
    INSERT INTO bookings (reference_no, customer_id, service_type_id, package_id, event_manager_id, event_type, event_date, event_time, venue, guest_count, dietary_preferences, special_requests, subtotal, discount_amount, tax_amount, total_amount, status, payment_status, qr_code, promo_code)
    VALUES (?, ?, ?, ?, 3, (SELECT name FROM service_types WHERE id = ?), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "confirmed", "partial", ?, ?)
');
$stmt->execute([
    $ref, $customerId, $serviceTypeId ?: null, $packageId, $serviceTypeId ?: null,
    $eventDate, $eventTime, $venue, $guestCount,
    json_encode($dietary), $specialRequests,
    $calc['subtotal'], $calc['discount_amount'], $calc['tax_amount'], $calc['total_amount'],
    $qrCode, $promoCode ?: null
]);
$bookingId = (int) $pdo->lastInsertId();

// Process deposit payment (simulated)
$deposit = $calc['deposit'];
$payRef = generateReference('PAY');
$pdo->prepare('INSERT INTO payments (booking_id, reference_no, amount, method, status, transaction_id) VALUES (?, ?, ?, ?, "completed", ?)')
    ->execute([$bookingId, $payRef, $deposit, $paymentMethod, strtoupper($paymentMethod) . '-TXN-' . random_int(100000, 999999)]);

$pdo->prepare('UPDATE bookings SET amount_paid = ? WHERE id = ?')->execute([$deposit, $bookingId]);

// Create invoice
$invRef = generateReference('INV');
$pdo->prepare('INSERT INTO invoices (invoice_no, booking_id, customer_id, subtotal, tax_amount, discount_amount, total_amount, amount_paid, status, due_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, "sent", DATE_ADD(CURDATE(), INTERVAL 7 DAY))')
    ->execute([$invRef, $bookingId, $customerId, $calc['subtotal'], $calc['tax_amount'], $calc['discount_amount'], $calc['total_amount'], $deposit]);

// Kitchen orders
$menuItems = $pdo->query('SELECT id FROM menu_items WHERE is_active = 1 ORDER BY popularity_score DESC LIMIT 5')->fetchAll();
foreach ($menuItems as $item) {
    $pdo->prepare('INSERT INTO kitchen_orders (booking_id, menu_item_id, quantity, status) VALUES (?, ?, ?, "pending")')
        ->execute([$bookingId, $item['id'], $guestCount]);
}

// Delivery task
$pdo->prepare('INSERT INTO delivery_tasks (booking_id, driver_id, pickup_time, status) VALUES (?, 5, ?, "scheduled")')
    ->execute([$bookingId, $eventDate . ' ' . $eventTime]);

// Event checklists
$checks = ['Confirm guest count', 'Verify dietary requirements', 'Kitchen prep scheduled', 'Delivery route confirmed', 'Setup team briefed'];
foreach ($checks as $i => $check) {
    $pdo->prepare('INSERT INTO event_checklists (booking_id, title, sort_order) VALUES (?, ?, ?)')->execute([$bookingId, $check, $i+1]);
}

// Loyalty points
$points = (int) floor($deposit / 100) * 10;
$pdo->prepare('UPDATE users SET loyalty_points = loyalty_points + ? WHERE id = ?')->execute([$points, $customerId]);

notify($pdo, $customerId, 'booking', 'Booking Confirmed!', "Your booking {$ref} has been confirmed. Deposit of " . formatMoney($deposit) . " received.", url('dashboard/customer'));

if (!isLoggedIn() && $customerId) {
    autoLogin($pdo, $customerId);
}

jsonResponse([
    'success' => true,
    'message' => 'Booking confirmed! Reference: ' . $ref,
    'reference' => $ref,
    'redirect' => url('dashboard/customer'),
]);
