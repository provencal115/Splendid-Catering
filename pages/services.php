<?php
$pageTitle = 'Our Services';
$services = $pdo->query('SELECT * FROM service_types WHERE is_active = 1 ORDER BY sort_order')->fetchAll();
$heroImage = $services[0]['image_url'] ?? 'https://images.unsplash.com/photo-1519225421980-715cb0215aed?w=1920&q=80';
include ROOT_PATH . '/includes/header.php';
?>

<?= renderPublicHero(
    'Our Catering Services',
    'Professional catering solutions for weddings, corporate events, private dinners, and celebrations.',
    'Every Occasion',
    $heroImage,
    [
        ['url' => url('book'), 'label' => 'Request Quote', 'icon' => 'calendar-check', 'class' => 'btn-gold btn-lg'],
        ['url' => url('menu'), 'label' => 'View Menu', 'icon' => 'egg-fried', 'class' => 'btn-outline-light btn-lg'],
    ],
    true
) ?>

<section class="section">
    <div class="container">
        <?= renderSectionHeader('What We Offer', 'Tailored Catering Experiences', 'Large-format editorial tiles for every type of celebration') ?>
        <?php if (empty($services)): ?>
            <?= renderPublicEmptyState(
                'calendar-event',
                'No services available yet',
                'Our service listings are being updated. Contact us to discuss your event.',
                url('contact'),
                'Contact Us'
            ) ?>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($services as $i => $s): ?>
            <?= renderServiceEditorialTile($s, $i) ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php
$ctaImg = $services[1]['image_url'] ?? $heroImage;
echo renderCinematicCta($ctaImg, 'Ready to Plan Your Event?', 'Speak with our team to design a menu and service package tailored to your vision.');
?>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
