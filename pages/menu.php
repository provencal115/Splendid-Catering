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

<?= renderPublicHero(
    'Our Menu',
    'Exquisite dishes crafted by award-winning chefs — every plate designed to impress.',
    'Chef-Curated Collection',
    'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=1920&q=80',
    [
        ['url' => url('book'), 'label' => 'Book Catering', 'icon' => 'calendar-check', 'class' => 'btn-gold btn-lg'],
        ['url' => url('packages'), 'label' => 'View Packages', 'icon' => 'box-seam', 'class' => 'btn-outline-light btn-lg'],
    ],
    true
) ?>

<section class="section">
    <div class="container">
        <?php
        $hasMenuItems = !empty($items);
        if (!$hasMenuItems): ?>
            <?= renderPublicEmptyState(
                'egg-fried',
                'No menu items available',
                'Our menu is being updated. Contact us to discuss custom dishes for your event.',
                url('contact'),
                'Contact Us'
            ) ?>
        <?php else: ?>
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
        <div class="mb-5">
            <div class="d-flex align-items-center gap-3 mb-4" data-aos="fade-up">
                <div class="image-frame" style="width:64px;height:64px;border-radius:50%;flex-shrink:0;">
                    <img src="<?= e($cat['image_url']) ?>" alt="" width="64" height="64" style="object-fit:cover;height:100%;">
                </div>
                <div>
                    <h3 class="mb-0"><?= e($cat['name']) ?></h3>
                    <small class="text-muted"><?= e($cat['description']) ?></small>
                </div>
            </div>
            <div class="row g-4 stagger-grid">
                <?php foreach ($catItems as $i => $item): ?>
                <div class="col-md-6 col-lg-4 menu-card-item"
                     data-reveal data-reveal-delay="<?= ($i % 3) * 80 ?>"
                     data-vegetarian="<?= (int) $item['is_vegetarian'] ?>"
                     data-vegan="<?= (int) $item['is_vegan'] ?>"
                     data-halal="<?= (int) $item['is_halal'] ?>"
                     data-gluten-free="<?= (int) $item['is_gluten_free'] ?>">
                    <?= renderFoodCard($item, $cat['name']) ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<section class="section section-alt">
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
            <button id="aiMenuBtn" class="btn btn-light mt-3 btn-interactive">Get AI Menu Suggestion</button>
            <div id="aiMenuResult" class="ai-suggestion"></div>
        </div>
    </div>
</section>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
