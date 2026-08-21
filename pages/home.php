<?php
$pageTitle = 'Home';
$pageDescription = 'Splendid Caterery - Premium catering and event management in Ghana. Weddings, corporate events, and celebrations.';
$services = $pdo->query('SELECT * FROM service_types WHERE is_active = 1 ORDER BY sort_order LIMIT 6')->fetchAll();
$packages = $pdo->query('SELECT * FROM packages WHERE is_active = 1 ORDER BY sort_order LIMIT 3')->fetchAll();
$testimonials = $pdo->query('SELECT * FROM testimonials WHERE is_featured = 1 AND is_active = 1 LIMIT 3')->fetchAll();
include ROOT_PATH . '/includes/header.php';
?>

<section class="hero-section">
    <div class="hero-bg" style="background-image:url('https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=1920&q=80')"></div>
    <div class="container hero-content">
        <div class="row align-items-center">
            <div class="col-lg-7" data-aos="fade-right">
                <div class="hero-badge"><i class="bi bi-stars"></i> Ghana's Premier Smart Catering Platform</div>
                <h1 class="hero-title">Crafting <span>Extraordinary</span> Culinary Experiences</h1>
                <p class="hero-subtitle">From intimate gatherings to grand celebrations, Splendid Caterery delivers world-class cuisine with AI-powered planning, seamless booking, and flawless execution.</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="<?= url('book') ?>" class="btn btn-gold btn-lg"><i class="bi bi-calendar-check"></i> Book Your Event</a>
                    <a href="<?= url('packages') ?>" class="btn btn-outline-light btn-lg">View Packages</a>
                </div>
                <div class="hero-stats">
                    <div class="hero-stat"><strong>2,500+</strong><span>Events Catered</span></div>
                    <div class="hero-stat"><strong>4.9★</strong><span>Customer Rating</span></div>
                    <div class="hero-stat"><strong>12</strong><span>Service Types</span></div>
                    <div class="hero-stat"><strong>50+</strong><span>Menu Items</span></div>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-block" data-aos="fade-left">
                <div class="ai-panel mt-4">
                    <h4><i class="bi bi-robot"></i> AI Event Planner</h4>
                    <p class="mb-3 opacity-75">Get instant recommendations for your event</p>
                    <div class="row g-2">
                        <div class="col-6"><input type="number" id="aiBudget" class="form-control" placeholder="Budget (₵)"></div>
                        <div class="col-6"><input type="number" id="aiGuests" class="form-control" placeholder="Guests"></div>
                    </div>
                    <button id="aiBudgetBtn" class="btn btn-light btn-sm mt-2 w-100">Get AI Recommendation</button>
                    <div id="aiBudgetResult" class="ai-suggestion"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-label">Why Choose Us</span>
            <h2 class="section-title">World-Class Catering Technology</h2>
            <p class="section-subtitle">We combine culinary excellence with smart technology to deliver unforgettable events</p>
        </div>
        <div class="row g-4">
            <?php
            $features = [
                ['icon' => 'robot', 'title' => 'AI Menu Recommendations', 'desc' => 'Smart suggestions based on budget, guests, and dietary preferences'],
                ['icon' => 'calendar-check', 'title' => 'Live Booking Calendar', 'desc' => 'Real-time availability with instant confirmation and QR codes'],
                ['icon' => 'credit-card', 'title' => 'Secure Payments', 'desc' => 'Mobile Money, cards, and bank transfers with instant receipts'],
                ['icon' => 'truck', 'title' => 'GPS Delivery Tracking', 'desc' => 'Live tracking with proof of delivery and setup photos'],
                ['icon' => 'graph-up-arrow', 'title' => 'Smart Analytics', 'desc' => 'AI-powered sales forecasts and inventory predictions'],
                ['icon' => 'headset', 'title' => '24/7 AI Assistant', 'desc' => 'Instant answers about menus, pricing, and availability'],
            ];
            foreach ($features as $i => $f): ?>
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="<?= $i * 100 ?>">
                <div class="feature-card">
                    <div class="card-body-custom text-center p-4">
                        <div class="stat-icon mx-auto mb-3" style="background:rgba(201,169,98,0.15);color:var(--gold);width:60px;height:60px;border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;">
                            <i class="bi bi-<?= $f['icon'] ?>"></i>
                        </div>
                        <h5><?= e($f['title']) ?></h5>
                        <p class="text-muted mb-0"><?= e($f['desc']) ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section bg-white">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-label">Our Services</span>
            <h2 class="section-title">Catering for Every Occasion</h2>
        </div>
        <div class="row g-4">
            <?php foreach ($services as $i => $s): ?>
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="<?= $i * 80 ?>">
                <div class="service-card">
                    <div class="card-image">
                        <img src="<?= e($s['image_url']) ?>" alt="<?= e($s['name']) ?>" loading="lazy">
                    </div>
                    <div class="card-body-custom">
                        <h5><i class="bi bi-<?= e($s['icon']) ?> text-gold" style="color:var(--gold)"></i> <?= e($s['name']) ?></h5>
                        <p class="text-muted small"><?= e(substr($s['description'], 0, 100)) ?>...</p>
                        <a href="<?= url('book') ?>?service=<?= e($s['slug']) ?>" class="btn btn-outline-gold btn-sm">Get Quote</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="<?= url('services') ?>" class="btn btn-gold">View All Services</a>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-label">Popular Packages</span>
            <h2 class="section-title">Choose Your Perfect Package</h2>
        </div>
        <div class="row g-4 justify-content-center">
            <?php foreach ($packages as $i => $p):
                $features = json_decode($p['features'], true) ?: [];
            ?>
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="<?= $i * 100 ?>">
                <div class="package-card">
                    <span class="package-tier"><?= e(ucfirst($p['tier'])) ?></span>
                    <div class="card-image">
                        <img src="<?= e($p['image_url']) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
                    </div>
                    <div class="card-body-custom">
                        <h5><?= e($p['name']) ?></h5>
                        <div class="package-price"><?= formatMoney((float)$p['price_per_guest']) ?> <small>/ guest</small></div>
                        <p class="text-muted small"><?= e($p['description']) ?></p>
                        <ul class="package-features">
                            <?php foreach (array_slice($features, 0, 4) as $f): ?>
                            <li><i class="bi bi-check-circle-fill"></i> <?= e($f) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <a href="<?= url('book') ?>?package=<?= e($p['slug']) ?>" class="btn btn-gold w-100">Select Package</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section bg-white">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-label">Client Love</span>
            <h2 class="section-title">What Our Clients Say</h2>
        </div>
        <div class="row g-4">
            <?php foreach ($testimonials as $i => $t): ?>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="<?= $i * 100 ?>">
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
    </div>
</section>

<section class="cta-section">
    <div class="container text-center position-relative" data-aos="zoom-in">
        <h2 class="display-font mb-3">Ready to Create Your Perfect Event?</h2>
        <p class="mb-4 opacity-75 mx-auto" style="max-width:500px;">Get an instant AI-powered quote and book your catering in minutes. Our team is ready to make your celebration unforgettable.</p>
        <div class="d-flex flex-wrap justify-content-center gap-3">
            <a href="<?= url('book') ?>" class="btn btn-gold btn-lg"><i class="bi bi-lightning-fill"></i> Get Instant Quote</a>
            <a href="<?= url('contact') ?>" class="btn btn-outline-light btn-lg">Contact Us</a>
        </div>
    </div>
</section>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
