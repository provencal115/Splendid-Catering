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

<section class="hero-section" style="min-height:35vh;">
    <div class="hero-bg" style="background-image:url('https://images.unsplash.com/photo-1556910103-1c02745aae4d?w=1920&q=80')"></div>
    <div class="container hero-content text-center">
        <h1 class="hero-title" data-aos="fade-up">Contact Us</h1>
        <p class="hero-subtitle mx-auto" data-aos="fade-up">Let's plan your perfect event together</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-5" data-aos="fade-right">
                <h3>Get in Touch</h3>
                <p class="text-muted">Have questions about our services, packages, or availability? Our team is ready to help.</p>
                <ul class="list-unstyled mt-4">
                    <li class="mb-3 d-flex gap-3"><i class="bi bi-geo-alt-fill" style="color:var(--gold);font-size:1.25rem"></i><div><strong>Address</strong><br>14 Independence Avenue, Accra, Ghana</div></li>
                    <li class="mb-3 d-flex gap-3"><i class="bi bi-telephone-fill" style="color:var(--gold);font-size:1.25rem"></i><div><strong>Phone</strong><br><?= e($config['support_phone']) ?></div></li>
                    <li class="mb-3 d-flex gap-3"><i class="bi bi-envelope-fill" style="color:var(--gold);font-size:1.25rem"></i><div><strong>Email</strong><br><?= e($config['support_email']) ?></div></li>
                    <li class="mb-3 d-flex gap-3"><i class="bi bi-clock-fill" style="color:var(--gold);font-size:1.25rem"></i><div><strong>Hours</strong><br>Mon–Sat: 8AM – 8PM</div></li>
                </ul>
            </div>
            <div class="col-lg-7" data-aos="fade-left">
                <div class="bg-white rounded-4 shadow p-4 p-lg-5">
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
                            <div class="col-12"><button type="submit" class="btn btn-gold btn-lg"><i class="bi bi-send"></i> Send Message</button></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
