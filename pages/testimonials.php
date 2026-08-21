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

<section class="hero-section" style="min-height:40vh;">
    <div class="hero-bg" style="background-image:url('https://images.unsplash.com/photo-1464349095431-e9a21285b5ea?w=1920&q=80')"></div>
    <div class="container hero-content text-center">
        <h1 class="hero-title" data-aos="fade-up">Testimonials</h1>
        <p class="hero-subtitle mx-auto" data-aos="fade-up">Trusted by families, corporations, and institutions across Ghana</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row g-4">
            <?php foreach ($testimonials as $i => $t): ?>
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="<?= ($i % 3) * 80 ?>">
                <div class="testimonial-card">
                    <?= renderStars((int)$t['rating']) ?>
                    <p class="testimonial-quote"><?= e($t['quote']) ?></p>
                    <div class="d-flex align-items-center gap-3">
                        <img src="<?= e($t['image_url']) ?>" alt="" class="rounded-circle" width="48" height="48" style="object-fit:cover">
                        <div>
                            <strong><?= e($t['customer_name']) ?></strong>
                            <small class="d-block text-muted"><?= e($t['event_type']) ?></small>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if ($reviews): ?>
        <hr class="my-5">
        <h3 class="text-center mb-4">Recent Reviews</h3>
        <div class="row g-4">
            <?php foreach ($reviews as $r): ?>
            <div class="col-md-6">
                <div class="testimonial-card">
                    <?= renderStars((int)$r['rating']) ?>
                    <?php if ($r['title']): ?><strong><?= e($r['title']) ?></strong><?php endif; ?>
                    <p class="testimonial-quote"><?= e($r['comment']) ?></p>
                    <small class="text-muted">— <?= e($r['first_name'] . ' ' . $r['last_name']) ?> · <?= e($r['event_type']) ?></small>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
