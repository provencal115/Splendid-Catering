<?php
$pageTitle = 'Catering Packages';
$packages = $pdo->query('SELECT * FROM packages WHERE is_active = 1 ORDER BY sort_order')->fetchAll();
include ROOT_PATH . '/includes/header.php';
?>

<?= renderPublicHero(
    'Catering Packages',
    'From intimate gatherings to grand galas — find the perfect package for your event.',
    'Curated Menus',
    'https://images.unsplash.com/photo-1544025162-d76694265947?w=1920&q=80',
    [
        ['url' => url('book'), 'label' => 'Book a Package', 'icon' => 'calendar-check', 'class' => 'btn-gold btn-lg'],
    ],
    true
) ?>

<section class="section">
    <div class="container">
        <?php if (empty($packages)): ?>
            <?= renderPublicEmptyState(
                'box-seam',
                'No packages available yet',
                'Custom catering packages can be arranged for your event size and budget.',
                url('book'),
                'Request a Quote'
            ) ?>
        <?php else: ?>
        <div class="row g-4 stagger-grid">
            <?php foreach ($packages as $i => $p):
                $features = json_decode($p['features'], true) ?: [];
                $highlight = $p['tier'] === 'gold';
            ?>
            <div class="col-lg-4 col-md-6" data-reveal data-reveal-delay="<?= ($i % 3) * 90 ?>">
                <article class="package-card-premium<?= $highlight ? ' border border-warning border-2' : '' ?>">
                    <span class="package-tier"><?= e(strtoupper($p['tier'])) ?></span>
                    <?php if ($highlight): ?><span class="badge bg-warning text-dark position-absolute" style="top:1rem;left:1rem;z-index:2;">Most Popular</span><?php endif; ?>
                    <div class="package-card-premium__media">
                        <img src="<?= e($p['image_url']) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
                    </div>
                    <div class="package-card-premium__body">
                        <h4><?= e($p['name']) ?></h4>
                        <div class="package-price"><?= formatMoney((float) $p['price_per_guest']) ?> <small>/ guest</small></div>
                        <p class="text-muted small">Min <?= (int) $p['min_guests'] ?> – Max <?= (int) $p['max_guests'] ?> guests</p>
                        <p><?= e($p['description']) ?></p>
                        <ul class="package-features">
                            <?php foreach ($features as $f): ?>
                            <li><i class="bi bi-check-circle-fill"></i> <?= e($f) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <a href="<?= e(url('book') . '?package=' . $p['slug']) ?>" class="btn btn-gold w-100 btn-interactive">Book This Package</a>
                    </div>
                </article>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
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
                    <button id="aiBudgetBtn" class="btn btn-light w-100 mt-2 btn-interactive">Get Recommendation</button>
                </div>
            </div>
            <div id="aiBudgetResult" class="ai-suggestion"></div>
        </div>
    </div>
</section>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
