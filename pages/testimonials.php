<?php
$pageTitle = 'Client Reviews';
$testimonials = $pdo->query('SELECT * FROM testimonials WHERE is_active = 1 ORDER BY is_featured DESC')->fetchAll();
$reviews = $pdo->query('
    SELECT r.*, u.first_name, u.last_name, b.event_type
    FROM reviews r JOIN users u ON r.customer_id = u.id
    JOIN bookings b ON r.booking_id = b.id
    WHERE r.is_approved = 1 ORDER BY r.created_at DESC LIMIT 12
')->fetchAll();
include ROOT_PATH . '/includes/header.php';
?>

<?= renderPublicHero(
    'Testimonials',
    'Trusted by families, corporations, and institutions across Ghana.',
    'Client Love',
    'https://images.unsplash.com/photo-1464349095431-e9a21285b5ea?w=1920&q=80',
    [
        ['url' => url('book'), 'label' => 'Book Your Event', 'icon' => 'calendar-check', 'class' => 'btn-gold btn-lg'],
    ],
    true
) ?>

<section class="section">
    <div class="container">
        <?php if (empty($testimonials) && empty($reviews)): ?>
            <?= renderPublicEmptyState(
                'chat-quote',
                'No reviews yet',
                'Client reviews and testimonials will appear here once events are completed.',
                url('book'),
                'Book Your Event'
            ) ?>
        <?php else: ?>

        <?php if (!empty($testimonials)): ?>
        <div class="testimonial-carousel mb-5" data-testimonial-carousel>
            <div class="testimonial-carousel__track">
                <?php foreach ($testimonials as $i => $t): ?>
                    <?= renderTestimonialSlide($t, $i === 0) ?>
                <?php endforeach; ?>
            </div>
            <div class="testimonial-carousel__nav">
                <button type="button" class="testimonial-carousel__btn" data-carousel-prev aria-label="Previous review"><i class="bi bi-chevron-left"></i></button>
                <div class="testimonial-carousel__dots"></div>
                <button type="button" class="testimonial-carousel__btn" data-carousel-next aria-label="Next review"><i class="bi bi-chevron-right"></i></button>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($reviews): ?>
        <?= renderSectionHeader('Recent Reviews', 'Verified Client Feedback', 'Reviews from completed bookings on our platform') ?>
        <div class="row g-4 stagger-grid">
            <?php foreach ($reviews as $i => $r): ?>
            <div class="col-md-6" data-reveal data-reveal-delay="<?= ($i % 2) * 80 ?>">
                <article class="testimonial-card-premium">
                    <?= renderStars((int) $r['rating']) ?>
                    <?php if ($r['title']): ?><strong class="d-block mb-2"><?= e($r['title']) ?></strong><?php endif; ?>
                    <p class="testimonial-quote"><?= e($r['comment']) ?></p>
                    <small class="text-muted">— <?= e($r['first_name'] . ' ' . $r['last_name']) ?> · <?= e($r['event_type']) ?></small>
                </article>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php endif; ?>
    </div>
</section>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
