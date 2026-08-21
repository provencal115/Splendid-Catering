<?php
$input = json_decode(file_get_contents('php://input'), true) ?: [];
$message = strtolower(trim($input['message'] ?? ''));

$responses = [
    'price' => 'Our packages range from ₵45/guest (Basic) to ₵350/guest (VIP). Use our instant quote calculator on the Book Now page for exact pricing!',
    'package' => 'We offer 5 tiers: Basic, Silver, Gold, Platinum, and VIP. Gold is our most popular for weddings and corporate events. Would you like a recommendation?',
    'wedding' => 'For weddings, we recommend our Gold or Platinum packages. They include live cooking stations, premium desserts, and a dedicated event coordinator. Shall I help you book?',
    'book' => 'You can book online at our Book Now page! Select your event details, choose a package, and pay a 30% deposit to confirm. Need help with a specific date?',
    'menu' => 'Our menu features 23+ items across 10 categories including local Ghanaian dishes, continental cuisine, BBQ, and seafood. All items show dietary badges (vegan, halal, etc.).',
    'payment' => 'We accept MTN Mobile Money, Telecel Cash, AirtelTigo, Visa, Mastercard, Bank Transfer, and Cash. Online payments are processed securely with instant receipts.',
    'available' => 'Check our live availability calendar on the booking page. We typically accept up to 3 events per day. Weekends fill up fast — book early!',
    'halal' => 'Yes! Many of our dishes are halal-certified. Use the dietary filters on our Menu page or specify requirements when booking.',
    'vegan' => 'We have excellent vegan options including Mushroom Risotto, Quinoa Power Bowl, Tropical Smoothies, and Bofrot with Honey. Our AI menu recommender can suggest more!',
    'contact' => 'Reach us at ' . $config['support_phone'] . ' or ' . $config['support_email'] . '. We are available Mon-Sat, 8AM-8PM.',
    'hello' => 'Hello! Welcome to Splendid Caterery! I can help with packages, menus, pricing, availability, and booking. What would you like to know?',
    'hi' => 'Hi there! 👋 How can I help you plan your event today?',
];

$reply = 'Thank you for your question! For detailed assistance, please visit our Book Now page or contact us at ' . $config['support_phone'] . '. I can help with packages, menus, pricing, and booking.';

foreach ($responses as $keyword => $response) {
    if (str_contains($message, $keyword)) {
        $reply = $response;
        break;
    }
}

// Log chat
$sessionId = $_SESSION['chat_session'] ??= bin2hex(random_bytes(16));
$pdo->prepare('INSERT INTO chat_messages (session_id, sender_id, sender_type, message) VALUES (?, ?, "customer", ?)')->execute([$sessionId, currentUser()['id'] ?? null, $input['message'] ?? '']);
$pdo->prepare('INSERT INTO chat_messages (session_id, sender_type, message) VALUES (?, "ai", ?)')->execute([$sessionId, $reply]);

jsonResponse(['success' => true, 'reply' => $reply]);
