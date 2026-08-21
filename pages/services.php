<?php
$pageTitle = 'Our Services';
$services = $pdo->query('SELECT * FROM service_types WHERE is_active = 1 ORDER BY sort_order')->fetchAll();
include ROOT_PATH . '/includes/header.php';
?>

<section class="hero-section" style="min-height:45vh;">
    <div class="hero-bg" style="background-image:url('https://images.unsplash.com/photo-1519225421980-715cb0215aed?w=1920&q=80')"></div>
    <div class="container hero-content text-center">
        <h1 class="hero-title" data-aos="fade-up">Our Catering Services</h1>
        <p class="hero-subtitle mx-auto" data-aos="fade-up">Professional catering solutions for every celebration and corporate need</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row g-4">
            <?php foreach ($services as $i => $s): ?>
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="<?= ($i % 3) * 100 ?>">
                <div class="service-card">
                    <div class="card-image">
                        <img src="<?= e($s['image_url']) ?>" alt="<?= e($s['name']) ?>" loading="lazy">
                    </div>
                    <div class="card-body-custom">
                        <h5><i class="bi bi-<?= e($s['icon']) ?>" style="color:var(--gold)"></i> <?= e($s['name']) ?></h5>
                        <p class="text-muted"><?= e($s['description']) ?></p>
                        <a href="<?= url('book') ?>?service=<?= e($s['slug']) ?>" class="btn btn-gold btn-sm">Request Quote <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
