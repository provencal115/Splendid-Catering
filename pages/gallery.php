<?php
$pageTitle = 'Gallery';
$gallery = $pdo->query('SELECT * FROM gallery ORDER BY is_featured DESC, sort_order')->fetchAll();
$categories = array_values(array_unique(array_filter(array_column($gallery, 'category'))));
$heroImage = !empty($gallery) ? $gallery[0]['image_url'] : 'https://images.unsplash.com/photo-1559339352-11d035aa65de?w=1920&q=80';
include ROOT_PATH . '/includes/header.php';
?>

<?= renderPublicHero(
    'Event Gallery',
    'A glimpse into our culinary artistry, elegant tablescapes, and unforgettable celebrations.',
    'Visual Feast',
    $heroImage,
    [
        ['url' => url('book'), 'label' => 'Book Your Event', 'icon' => 'calendar-check', 'class' => 'btn-gold btn-lg'],
    ],
    true
) ?>

<section class="section gallery-section">
    <div class="container">
        <?php if (empty($gallery)): ?>
            <?= renderPublicEmptyState(
                'images',
                'No gallery images yet',
                'Photos from our events and culinary work will appear here soon.',
                url('book'),
                'Book Your Event'
            ) ?>
        <?php else: ?>
        <?php if (count($categories) > 1): ?>
        <div class="gallery-filters" data-reveal>
            <button type="button" class="gallery-filter-btn active" data-filter="all">All</button>
            <?php foreach ($categories as $cat): ?>
            <button type="button" class="gallery-filter-btn" data-filter="<?= e($cat) ?>"><?= e($cat) ?></button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?= renderGalleryGrid($gallery) ?>
        <?php endif; ?>
    </div>
</section>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
