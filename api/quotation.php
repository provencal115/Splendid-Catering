<?php
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$guests = (int) ($input['guest_count'] ?? 0);
$packageId = (int) ($input['package_id'] ?? 0);
$promoCode = trim($input['promo_code'] ?? '');

if (!$guests || !$packageId) {
    jsonResponse(['success' => false, 'message' => 'Guest count and package required']);
}

$stmt = $pdo->prepare('SELECT * FROM packages WHERE id = ?');
$stmt->execute([$packageId]);
$package = $stmt->fetch();

if (!$package) {
    jsonResponse(['success' => false, 'message' => 'Package not found']);
}

$discount = 0;
if ($promoCode) {
    $stmt = $pdo->prepare("SELECT * FROM promotions WHERE code = ? AND is_active = 1 AND (valid_until IS NULL OR valid_until >= CURDATE())");
    $stmt->execute([strtoupper($promoCode)]);
    $promo = $stmt->fetch();
    if ($promo) {
        $subtotal = $package['price_per_guest'] * $guests;
        $discount = $promo['discount_type'] === 'percentage'
            ? $subtotal * ($promo['discount_value'] / 100)
            : $promo['discount_value'];
    }
}

$calc = calculateQuotation((float)$package['price_per_guest'], $guests, $discount);

jsonResponse([
    'success' => true,
    'subtotal' => formatMoney($calc['subtotal']),
    'tax' => formatMoney($calc['tax_amount']),
    'total' => formatMoney($calc['total_amount']),
    'deposit' => formatMoney($calc['deposit']),
    'raw' => $calc,
]);
