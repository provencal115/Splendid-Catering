<?php

$pageTitle = 'Home';
$pageDescription = 'Splendid Catering - Premium catering and event management in Ghana. Weddings, corporate events, and celebrations.';
$bodyClass = 'home-page';
$services = $pdo->query('SELECT * FROM service_types WHERE is_active = 1 ORDER BY sort_order LIMIT 6')->fetchAll();
$testimonials = $pdo->query('SELECT * FROM testimonials WHERE is_featured = 1 AND is_active = 1 LIMIT 6')->fetchAll();
$siteStats = getPublicSiteStats($pdo);
$ratingDisplay = $siteStats['avg_rating'] > 0 ? number_format($siteStats['avg_rating'], 1) . '★' : '—';
$heroSlides = getHeroSlideshowImages($pdo);
$ctaImage = $heroSlides[0]['url'] ?? 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=1920&q=80';

include ROOT_PATH . '/includes/header.php';

?>

<?= renderCinematicHomeHero($pdo, $siteStats, $ratingDisplay, $heroSlides) ?>

<?= renderHomeAboutStrip($pdo) ?>

<section class="section section-alt" id="events-occasions">
    <div class="container">
        <?= renderSectionHeader('Our Services', 'Catering Services & Occasions', 'Weddings, birthdays, corporate events, and private celebrations from our live service catalogue') ?>
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

<?= renderFoodShowcase($pdo) ?>

<?= renderHomeMenuSpecialties($pdo, 4) ?>

<?= renderHomePackagesPreview($pdo) ?>

<?= renderWhyChooseUs($pdo) ?>

<?= renderHomeGalleryPreview($pdo) ?>

<section class="section section-alt">
    <div class="container">
        <?= renderSectionHeader('Client Love', 'Customer Testimonials', 'Featured reviews from our testimonials catalogue') ?>
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

<?= renderHomeLocationPreview() ?>

<?= renderHomeBookingTeaser() ?>

<?= renderCinematicCta(
    $ctaImage,
    'Make Your Next Event Special',
    'Book chef-crafted catering with seamless planning, premium service, and flavors your guests will remember.'
) ?>

<?php include ROOT_PATH . '/includes/footer.php'; ?>