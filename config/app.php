<?php

return [
    'name' => 'Splendid Catering',
    'tagline' => 'Smart Catering & Event Management',
    'url' => 'http://localhost:8080',
    'timezone' => 'Africa/Accra',
    'currency' => 'GHS',
    'currency_symbol' => '₵',
    'tax_rate' => 0.125, // 12.5% VAT Ghana
    'loyalty_points_per_100' => 10,
    'booking_deposit_percent' => 0.30,
    'quotation_valid_days' => 14,
    'support_email' => 'hello@splendidcaterery.com',
    'support_phone' => '+233 24 000 0000',
    'business' => [
        'address' => '14 Independence Avenue, Accra, Ghana',
        'latitude' => 5.5774737,
        'longitude' => -0.1873671,
        'hours' => 'Mon–Sat: 8AM – 8PM',
    ],
    /*
     * Leadership / founder profiles for the About Us page.
     * Add real entries only — leave empty until verified information is available.
     *
     * 'name'      => required to display
     * 'title'     => required to display
     * 'bio'       => required to display
     * 'image'     => required (local path or URL)
     * 'quote'     => optional
     * 'linkedin'  => optional
     * 'instagram' => optional
     */
    'leadership' => [],
    'google_maps_api_key' => getenv('GOOGLE_MAPS_API_KEY') ?: '',
    'sms_enabled' => false,
    'email_enabled' => true,
    'demo_mode' => true,
    'roles' => [
        'customer' => 'Customer',
        'admin' => 'Administrator',
        'event_manager' => 'Event Manager',
        'chef' => 'Chef / Kitchen Staff',
        'delivery' => 'Delivery & Setup',
        'finance' => 'Finance Manager',
    ],
    'payment_methods' => [
        'mtn_momo' => 'MTN Mobile Money',
        'telecel_cash' => 'Telecel Cash',
        'airteltigo' => 'AirtelTigo Money',
        'visa' => 'Visa',
        'mastercard' => 'Mastercard',
        'bank_transfer' => 'Bank Transfer',
        'cash' => 'Cash',
    ],
    'booking_statuses' => [
        'pending' => ['label' => 'Pending', 'color' => 'warning'],
        'confirmed' => ['label' => 'Confirmed', 'color' => 'info'],
        'in_preparation' => ['label' => 'In Preparation', 'color' => 'primary'],
        'ready' => ['label' => 'Ready', 'color' => 'success'],
        'in_transit' => ['label' => 'In Transit', 'color' => 'info'],
        'delivered' => ['label' => 'Delivered', 'color' => 'success'],
        'completed' => ['label' => 'Completed', 'color' => 'success'],
        'cancelled' => ['label' => 'Cancelled', 'color' => 'danger'],
    ],
];
