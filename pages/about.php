<?php
$pageTitle = 'About Us';
$content = $pdo->query("SELECT content FROM website_content WHERE page_key = 'about'")->fetchColumn();
include ROOT_PATH . '/includes/header.php';
?>

<section class="hero-section" style="min-height:50vh;">
    <div class="hero-bg" style="background-image:url('https://images.unsplash.com/photo-1559339352-11d035aa65de?w=1920&q=80')"></div>
    <div class="container hero-content text-center">
        <span class="hero-badge" data-aos="fade-up">Our Story</span>
        <h1 class="hero-title" data-aos="fade-up">About Splendid Catering</h1>
        <p class="hero-subtitle mx-auto" data-aos="fade-up">Passion for food. Excellence in service. Innovation in technology.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6" data-aos="fade-right">
                <img src="https://images.unsplash.com/photo-1556910103-1c02745aae4d?w=800&q=80" alt="Our kitchen" class="img-fluid rounded-4 shadow-lg">
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <span class="section-label">Who We Are</span>
                <h2 class="section-title">Ghana's Smartest Catering Company</h2>
                <p><?= e($content) ?></p>
                <div class="row g-3 mt-3">
                    <div class="col-6"><div class="stat-card"><div class="stat-value">15+</div><div class="stat-label">Years Experience</div></div></div>
                    <div class="col-6"><div class="stat-card"><div class="stat-value">50+</div><div class="stat-label">Expert Chefs</div></div></div>
                    <div class="col-6"><div class="stat-card"><div class="stat-value">2,500+</div><div class="stat-label">Events Served</div></div></div>
                    <div class="col-6"><div class="stat-card"><div class="stat-value">98%</div><div class="stat-label">Satisfaction Rate</div></div></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-label">Our Values</span>
            <h2 class="section-title">What Drives Us</h2>
        </div>
        <div class="row g-4">
            <?php
            $values = [
                ['icon' => 'award', 'title' => 'Culinary Excellence', 'desc' => 'Award-winning chefs crafting dishes that delight every palate'],
                ['icon' => 'shield-check', 'title' => 'Food Safety First', 'desc' => 'HACCP certified kitchen with strict hygiene protocols'],
                ['icon' => 'heart', 'title' => 'Client-Centric', 'desc' => 'Your vision is our mission — every detail matters'],
                ['icon' => 'lightning', 'title' => 'Innovation', 'desc' => 'AI-powered planning for smarter, waste-free events'],
            ];
            foreach ($values as $i => $v): ?>
            <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="<?= $i * 80 ?>">
                <div class="feature-card text-center p-4">
                    <i class="bi bi-<?= $v['icon'] ?>" style="font-size:2rem;color:var(--gold)"></i>
                    <h5 class="mt-3"><?= e($v['title']) ?></h5>
                    <p class="text-muted small mb-0"><?= e($v['desc']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
