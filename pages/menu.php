<?php
$pageTitle = 'Our Menu';
$categories = $pdo->query('SELECT * FROM menu_categories WHERE is_active = 1 ORDER BY sort_order')->fetchAll();
$items = $pdo->query('
    SELECT m.*, c.name as category_name, c.slug as category_slug
    FROM menu_items m JOIN menu_categories c ON m.category_id = c.id
    WHERE m.is_active = 1 ORDER BY c.sort_order, m.popularity_score DESC
')->fetchAll();
include ROOT_PATH . '/includes/header.php';
?>

<section class="hero-section" style="min-height:40vh;">
    <div class="hero-bg" style="background-image:url('https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=1920&q=80')"></div>
    <div class="container hero-content text-center">
        <h1 class="hero-title" data-aos="fade-up">Our Menu</h1>
        <p class="hero-subtitle mx-auto" data-aos="fade-up">Exquisite dishes crafted by award-winning chefs</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="diet-filters" data-aos="fade-up">
            <button class="diet-filter-btn active" data-filter="all">All Items</button>
            <button class="diet-filter-btn" data-filter="vegetarian">Vegetarian</button>
            <button class="diet-filter-btn" data-filter="vegan">Vegan</button>
            <button class="diet-filter-btn" data-filter="halal">Halal</button>
            <button class="diet-filter-btn" data-filter="glutenFree">Gluten-Free</button>
        </div>

        <?php foreach ($categories as $cat):
            $catItems = array_filter($items, fn($i) => $i['category_id'] == $cat['id']);
            if (empty($catItems)) continue;
        ?>
        <div class="mb-5" data-aos="fade-up">
            <div class="d-flex align-items-center gap-3 mb-4">
                <img src="<?= e($cat['image_url']) ?>" alt="" width="60" height="60" class="rounded-circle" style="object-fit:cover">
                <div>
                    <h3 class="mb-0"><?= e($cat['name']) ?></h3>
                    <small class="text-muted"><?= e($cat['description']) ?></small>
                </div>
            </div>
            <div class="row g-4">
                <?php foreach ($catItems as $item): ?>
                <div class="col-md-6 col-lg-4 menu-card-item"
                     data-vegetarian="<?= (int)$item['is_vegetarian'] ?>"
                     data-vegan="<?= (int)$item['is_vegan'] ?>"
                     data-halal="<?= (int)$item['is_halal'] ?>"
                     data-gluten-free="<?= (int)$item['is_gluten_free'] ?>">
                    <div class="menu-card">
                        <div class="card-image">
                            <img src="<?= e($item['image_url']) ?>" alt="<?= e($item['name']) ?>" loading="lazy">
                        </div>
                        <div class="card-body-custom">
                            <div class="d-flex justify-content-between align-items-start">
                                <h5><?= e($item['name']) ?></h5>
                                <span class="menu-price"><?= formatMoney((float)$item['price']) ?></span>
                            </div>
                            <p class="text-muted small"><?= e($item['description']) ?></p>
                            <div class="mb-2"><?= getDietaryBadges($item) ?></div>
                            <?php if ($item['popularity_score'] > 90): ?>
                            <span class="badge bg-warning text-dark"><i class="bi bi-fire"></i> Popular</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="section bg-white">
    <div class="container">
        <div class="ai-panel" data-aos="fade-up">
            <h4><i class="bi bi-robot"></i> AI Menu Recommendation</h4>
            <div class="row g-3 mt-2">
                <div class="col-md-3"><input type="number" id="menuBudget" class="form-control" placeholder="Budget (₵)"></div>
                <div class="col-md-2"><input type="number" id="menuGuests" class="form-control" placeholder="Guests"></div>
                <div class="col-md-3">
                    <select id="menuEventType" class="form-select">
                        <option value="wedding">Wedding</option>
                        <option value="corporate">Corporate</option>
                        <option value="birthday">Birthday</option>
                        <option value="outdoor-bbq">Outdoor BBQ</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="d-flex flex-wrap gap-2">
                        <label class="btn btn-sm btn-outline-light"><input type="checkbox" name="preferences[]" value="vegetarian" class="d-none"> Veg</label>
                        <label class="btn btn-sm btn-outline-light"><input type="checkbox" name="preferences[]" value="vegan" class="d-none"> Vegan</label>
                        <label class="btn btn-sm btn-outline-light"><input type="checkbox" name="preferences[]" value="halal" class="d-none"> Halal</label>
                    </div>
                </div>
            </div>
            <button id="aiMenuBtn" class="btn btn-light mt-3">Get AI Menu Suggestion</button>
            <div id="aiMenuResult" class="ai-suggestion"></div>
        </div>
    </div>
</section>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
