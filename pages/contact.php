<?php
$pageTitle = 'Contact Us';

if (isPost()) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        flash('danger', 'Invalid request.');
        redirect(url('contact'));
    }
    flash('success', 'Thank you! We will contact you within 24 hours.');
    redirect(url('contact'));
}

include ROOT_PATH . '/includes/header.php';
?>

<?= renderPublicHero(
    'Contact Us',
    "Let's plan your perfect event together — our team responds within 24 hours.",
    'Get in Touch',
    'https://images.unsplash.com/photo-1556910103-1c02745aae4d?w=1920&q=80',
    [
        ['url' => url('book'), 'label' => 'Book Catering', 'icon' => 'calendar-check', 'class' => 'btn-gold btn-lg'],
        ['url' => '#find-us', 'label' => 'Find Us', 'icon' => 'geo-alt', 'class' => 'btn-outline-light btn-lg'],
    ],
    true
) ?>

<section class="section">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-5" data-reveal>
                <?= renderSectionHeader('Reach Us', 'Contact Information', 'Use the details below or send us a message.') ?>
                <?= renderContactInfoCard() ?>
            </div>
            <div class="col-lg-7" data-reveal>
                <div class="surface-card rounded-4 shadow p-4 p-lg-5 h-100">
                    <span class="section-label">Send a Message</span>
                    <h3 class="section-title section-title--editorial mb-3">We'd Love to Hear From You</h3>
                    <form method="POST">
                        <?= csrfField() ?>
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">Name</label><input type="text" name="name" class="form-control" required></div>
                            <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
                            <div class="col-md-6"><label class="form-label">Phone</label><input type="tel" name="phone" class="form-control"></div>
                            <div class="col-md-6"><label class="form-label">Event Type</label>
                                <select name="event_type" class="form-select">
                                    <option>Wedding</option><option>Corporate</option><option>Birthday</option><option>Other</option>
                                </select>
                            </div>
                            <div class="col-12"><label class="form-label">Message</label><textarea name="message" class="form-control" rows="4" required></textarea></div>
                            <div class="col-12"><button type="submit" class="btn btn-gold btn-lg btn-interactive w-100"><i class="bi bi-send"></i> Send Message</button></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?= renderContactLocationSection() ?>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
