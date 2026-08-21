<?php
$pageTitle = 'Catering Packages';
$packages = $pdo->query('SELECT * FROM packages WHERE is_active = 1 ORDER BY sort_order')->fetchAll();
include ROOT_PATH . '/includes/header.php';
?>

<section class="hero-section" style="min-height:45vh;">
    <div class="hero-bg" style="background-image:url('https://images.unsplash.com/photo-1544025162-d76694265947?w=1920&q=80')"></div>
    <div class="container hero-content text-center">
        <h1 class="hero-title" data-aos="fade-up">Catering Packages</h1>
        <p class="hero-subtitle mx-auto" data-aos="fade-up">From intimate gatherings to grand galas — find the perfect package for your event</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row g-4">
            <?php foreach ($packages as $i => $p):
                $features = json_decode($p['features'], true) ?: [];
                $highlight = $p['tier'] === 'gold';
            ?>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?= ($i % 3) * 100 ?>">
                <div class="package-card <?= $highlight ? 'border border-warning border-3' : '' ?>">
                    <span class="package-tier"><?= e(strtoupper($p['tier'])) ?></span>
                    <?php if ($highlight): ?><span class="badge bg-warning text-dark position-absolute" style="top:1rem;left:1rem;z-index:2;">Most Popular</span><?php endif; ?>
                    <div class="card-image">
                        <img src="<?= e($p['image_url']) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
                    </div>
                    <div class="card-body-custom">
                        <h4><?= e($p['name']) ?></h4>
                        <div class="package-price"><?= formatMoney((float)$p['price_per_guest']) ?> <small>/ guest</small></div>
                        <p class="text-muted small">Min <?= (int)$p['min_guests'] ?> – Max <?= (int)$p['max_guests'] ?> guests</p>
                        <p><?= e($p['description']) ?></p>
                        <ul class="package-features">
                            <?php foreach ($features as $f): ?>
                            <li><i class="bi bi-check-circle-fill"></i> <?= e($f) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <a href="<?= url('book') ?>?package=<?= e($p['slug']) ?>" class="btn btn-gold w-100">Book This Package</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <div class="ai-panel" data-aos="fade-up">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h4><i class="bi bi-robot"></i> AI Budget Planner</h4>
                    <p class="mb-0 opacity-75">Not sure which package fits your budget? Let our AI recommend the perfect option.</p>
                </div>
                <div class="col-lg-4 mt-3 mt-lg-0">
                    <div class="row g-2">
                        <div class="col-6"><input type="number" id="aiBudget" class="form-control" placeholder="Budget ₵"></div>
                        <div class="col-6"><input type="number" id="aiGuests" class="form-control" placeholder="Guests"></div>
                    </div>
                    <button id="aiBudgetBtn" class="btn btn-light w-100 mt-2">Get Recommendation</button>
                </div>
            </div>
            <div id="aiBudgetResult" class="ai-suggestion"></div>
        </div>
    </div>
</section>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
