<?php
$pageTitle = 'About Us';
$content = $pdo->query("SELECT content FROM website_content WHERE page_key = 'about'")->fetchColumn();
$galleryImage = $pdo->query("SELECT image_url FROM gallery WHERE is_featured = 1 ORDER BY sort_order LIMIT 1")->fetchColumn()
    ?: 'https://images.unsplash.com/photo-1556910103-1c02745aae4d?w=800&q=80';
$kitchenImage = $pdo->query("SELECT image_url FROM menu_items WHERE is_active = 1 ORDER BY popularity_score DESC LIMIT 1")->fetchColumn()
    ?: 'https://images.unsplash.com/photo-1556910103-1c02745aae4d?w=600&q=80';
include ROOT_PATH . '/includes/header.php';
?>

<?= renderPublicHero(
    'About Splendid Catering',
    'Passion for food. Excellence in service. A legacy of unforgettable events.',
    'Our Story',
    $galleryImage,
    [['url' => url('book'), 'label' => 'Book an Event', 'icon' => 'calendar-check', 'class' => 'btn-gold btn-lg']],
    true
) ?>

<section class="section about-editorial">
    <div class="container">
        <div class="about-editorial__grid">
            <div class="about-editorial__visual" data-parallax-layer="0.06">
                <div class="about-editorial__main" data-mask-reveal>
                    <img src="<?= e($galleryImage) ?>" alt="Splendid Catering event" loading="lazy">
                </div>
                <div class="about-editorial__float" data-parallax-layer="-0.08" data-mask-reveal data-reveal-delay="150">
                    <img src="<?= e($kitchenImage) ?>" alt="Chef preparation" loading="lazy">
                </div>
            </div>
            <div class="about-editorial__content">
                <div data-reveal>
                    <span class="section-label">Who We Are</span>
                    <h2 class="section-title section-title--editorial">Ghana's Finest Catering Experience</h2>
                    <p class="lead-text"><?= e($content) ?></p>
                </div>
                <div class="about-editorial__stats stagger-grid">
                    <div class="stat-card-premium" data-reveal>
                        <div class="stat-value" data-counter="15" data-counter-suffix="+">0</div>
                        <div class="stat-label">Years Experience</div>
                    </div>
                    <div class="stat-card-premium" data-reveal data-reveal-delay="80">
                        <div class="stat-value" data-counter="50" data-counter-suffix="+">0</div>
                        <div class="stat-label">Expert Chefs</div>
                    </div>
                    <div class="stat-card-premium" data-reveal data-reveal-delay="160">
                        <div class="stat-value" data-counter="2500" data-counter-suffix="+">0</div>
                        <div class="stat-label">Events Served</div>
                    </div>
                    <div class="stat-card-premium" data-reveal data-reveal-delay="240">
                        <div class="stat-value" data-counter="98" data-counter-suffix="%">0</div>
                        <div class="stat-label">Satisfaction Rate</div>
                    </div>
                </div>
                <a href="<?= e(url('book')) ?>" class="btn btn-gold btn-interactive btn-arrow mt-4" data-reveal data-reveal-delay="300">Start Planning <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <?= renderSectionHeader('Our Values', 'What Drives Us', 'The principles behind every dish we serve and every event we deliver') ?>
        <div class="row g-4 stagger-grid">
            <?php
            $values = [
                ['icon' => 'award', 'title' => 'Culinary Excellence', 'desc' => 'Award-winning chefs crafting dishes that delight every palate'],
                ['icon' => 'shield-check', 'title' => 'Food Safety First', 'desc' => 'HACCP certified kitchen with strict hygiene protocols'],
                ['icon' => 'heart', 'title' => 'Client-Centric', 'desc' => 'Your vision is our mission — every detail matters'],
                ['icon' => 'lightning', 'title' => 'Innovation', 'desc' => 'Smart planning tools for seamless, waste-free events'],
            ];
            foreach ($values as $i => $v): ?>
            <div class="col-md-6 col-lg-3" data-mask-reveal data-reveal-delay="<?= $i * 80 ?>">
                <div class="feature-card feature-card--minimal text-center p-4 h-100">
                    <div class="service-card-premium__icon mx-auto mb-3"><i class="bi bi-<?= e($v['icon']) ?>"></i></div>
                    <h5 class="mt-1"><?= e($v['title']) ?></h5>
                    <p class="text-muted small mb-0"><?= e($v['desc']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
