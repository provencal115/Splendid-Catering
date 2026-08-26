<?php
$pageTitle = 'Home';
$pageDescription = 'Splendid Catering - Premium catering and event management in Ghana. Weddings, corporate events, and celebrations.';
$services = $pdo->query('SELECT * FROM service_types WHERE is_active = 1 ORDER BY sort_order LIMIT 6')->fetchAll();
$packages = $pdo->query('SELECT * FROM packages WHERE is_active = 1 ORDER BY sort_order LIMIT 3')->fetchAll();
$testimonials = $pdo->query('SELECT * FROM testimonials WHERE is_featured = 1 AND is_active = 1 LIMIT 6')->fetchAll();
$siteStats = getPublicSiteStats($pdo);
$ratingDisplay = $siteStats['avg_rating'] > 0 ? number_format($siteStats['avg_rating'], 1) . '★' : '—';
$heroSlides = getHeroSlideshowImages($pdo);
$ctaImage = $heroSlides[0]['url'] ?? 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=1920&q=80';
include ROOT_PATH . '/includes/header.php';
?>

<?= renderCinematicHomeHero($siteStats, $ratingDisplay, $heroSlides) ?>

<?= renderFoodShowcase($pdo) ?>

<section class="section section-alt">
    <div class="container">
        <?= renderSectionHeader('Our Services', 'Catering for Every Occasion', 'Editorial-style experiences for weddings, corporate events, and private celebrations') ?>
        <?php if (empty($services)): ?>
            <?= renderPublicEmptyState(
                'calendar-event',
                'Services coming soon',
                'We are updating our service offerings. Contact us to discuss your event needs.',
                url('contact'),
                'Get in Touch'
            ) ?>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($services as $i => $s): ?>
            <?= renderServiceEditorialTile($s, $i) ?>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-5" data-reveal>
            <a href="<?= e(url('services')) ?>" class="btn btn-gold btn-interactive btn-arrow">View All Services <i class="bi bi-arrow-right"></i></a>
        </div>
        <?php endif; ?>
    </div>
</section>

<?= renderHomeAboutStrip($pdo) ?>

<?= renderHomeMenuSpecialties($pdo) ?>

<?= renderHomeGalleryPreview($pdo) ?>

<section class="section">
    <div class="container">
        <?= renderSectionHeader('Popular Packages', 'Curated for Every Guest Count', 'Premium menus and service tiers designed for your event scale') ?>
        <?php if (empty($packages)): ?>
            <?= renderPublicEmptyState(
                'box-seam',
                'No packages available yet',
                'Custom packages can be arranged for your event. Speak with our team to get started.',
                url('book'),
                'Request a Quote'
            ) ?>
        <?php else: ?>
        <div class="row g-4 justify-content-center stagger-grid">
            <?php foreach ($packages as $i => $p):
                $features = json_decode($p['features'], true) ?: [];
            ?>
            <div class="col-md-6 col-lg-4" data-reveal data-reveal-delay="<?= $i * 100 ?>">
                <article class="package-card-premium package-card-premium--editorial">
                    <span class="package-tier"><?= e(ucfirst($p['tier'])) ?></span>
                    <div class="package-card-premium__media">
                        <img src="<?= e($p['image_url']) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
                        <div class="package-card-premium__hover">
                            <a href="<?= e(url('book') . '?package=' . $p['slug']) ?>" class="btn btn-gold btn-interactive">Select Package</a>
                        </div>
                    </div>
                    <div class="package-card-premium__body">
                        <h5><?= e($p['name']) ?></h5>
                        <div class="package-price"><?= formatMoney((float) $p['price_per_guest']) ?> <small>/ guest</small></div>
                        <p class="text-muted small"><?= e($p['description']) ?></p>
                        <ul class="package-features">
                            <?php foreach (array_slice($features, 0, 3) as $f): ?>
                            <li><i class="bi bi-check-circle-fill"></i> <?= e($f) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </article>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4" data-reveal>
            <a href="<?= e(url('packages')) ?>" class="btn btn-outline-gold btn-interactive">View All Packages</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <?= renderSectionHeader('Client Love', 'Stories From Our Table', 'Trusted by families, corporations, and institutions across Ghana') ?>
        <?php if (empty($testimonials)): ?>
            <?= renderPublicEmptyState(
                'chat-quote',
                'No featured reviews yet',
                'Client testimonials will appear here. Book an event and share your experience with us.',
                url('book'),
                'Book Your Event'
            ) ?>
        <?php else: ?>
        <div class="testimonial-carousel testimonial-carousel--fade" data-testimonial-carousel data-carousel-fade>
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
        <div class="text-center mt-4" data-reveal>
            <a href="<?= e(url('testimonials')) ?>" class="btn btn-outline-gold btn-interactive">Read All Reviews</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<?= renderCinematicCta(
    $ctaImage,
    'Make Your Next Event Special',
    'Book chef-crafted catering with seamless planning, premium service, and flavors your guests will remember.'
) ?>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
