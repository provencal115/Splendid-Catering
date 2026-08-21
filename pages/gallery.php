<?php
$pageTitle = 'Gallery';
$gallery = $pdo->query('SELECT * FROM gallery ORDER BY is_featured DESC, sort_order')->fetchAll();
include ROOT_PATH . '/includes/header.php';
?>

<section class="hero-section" style="min-height:40vh;">
    <div class="hero-bg" style="background-image:url('https://images.unsplash.com/photo-1559339352-11d035aa65de?w=1920&q=80')"></div>
    <div class="container hero-content text-center">
        <h1 class="hero-title" data-aos="fade-up">Event Gallery</h1>
        <p class="hero-subtitle mx-auto" data-aos="fade-up">A glimpse into our culinary artistry and event excellence</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row g-4">
            <?php foreach ($gallery as $i => $g): ?>
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="<?= ($i % 3) * 80 ?>">
                <div class="gallery-card">
                    <div class="card-image" style="position:relative;">
                        <img src="<?= e($g['image_url']) ?>" alt="<?= e($g['title']) ?>" loading="lazy">
                        <div class="gallery-overlay">
                            <div>
                                <h6><?= e($g['title']) ?></h6>
                                <small class="text-white-50"><?= e($g['category']) ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
