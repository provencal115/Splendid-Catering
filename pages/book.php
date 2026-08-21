<?php
$pageTitle = 'Book Your Event';
$services = $pdo->query('SELECT * FROM service_types WHERE is_active = 1 ORDER BY sort_order')->fetchAll();
$packages = $pdo->query('SELECT * FROM packages WHERE is_active = 1 ORDER BY sort_order')->fetchAll();
$selectedService = $_GET['service'] ?? '';
$selectedPackage = $_GET['package'] ?? '';
include ROOT_PATH . '/includes/header.php';
?>

<section class="hero-section" style="min-height:35vh;">
    <div class="hero-bg" style="background-image:url('https://images.unsplash.com/photo-1476225420860-9f2d3192a4b9?w=1920&q=80')"></div>
    <div class="container hero-content text-center">
        <h1 class="hero-title" data-aos="fade-up">Book Your Event</h1>
        <p class="hero-subtitle mx-auto" data-aos="fade-up">Instant quotes, live availability, and secure online booking</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="booking-wizard" id="bookingWizard" data-aos="fade-up">
                    <div class="wizard-steps">
                        <div class="wizard-step active">1. Event Details</div>
                        <div class="wizard-step">2. Menu & Package</div>
                        <div class="wizard-step">3. Preferences</div>
                        <div class="wizard-step">4. Confirm & Pay</div>
                    </div>
                    <form class="wizard-content">
                        <?= csrfField() ?>
                        <!-- Step 1 -->
                        <div class="wizard-pane" data-step="1">
                            <h4 class="mb-4">Tell us about your event</h4>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Event Type</label>
                                    <select name="service_type_id" class="form-select" required>
                                        <option value="">Select service...</option>
                                        <?php foreach ($services as $s): ?>
                                        <option value="<?= $s['id'] ?>" <?= $selectedService === $s['slug'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Number of Guests</label>
                                    <input type="number" name="guest_count" class="form-control" min="10" max="500" required placeholder="e.g. 100">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Event Date</label>
                                    <input type="date" name="event_date" class="form-control" required min="<?= date('Y-m-d', strtotime('+3 days')) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Event Time</label>
                                    <input type="time" name="event_time" class="form-control" required value="14:00">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Venue Address</label>
                                    <textarea name="venue" class="form-control" rows="2" required placeholder="Full venue address including city"></textarea>
                                </div>
                            </div>
                            <div class="text-end mt-4"><button type="button" class="btn btn-gold" data-next>Next <i class="bi bi-arrow-right"></i></button></div>
                        </div>
                        <!-- Step 2 -->
                        <div class="wizard-pane" data-step="2" style="display:none">
                            <h4 class="mb-4">Select your package</h4>
                            <div class="row g-3">
                                <?php foreach ($packages as $p): ?>
                                <div class="col-md-6">
                                    <label class="w-100">
                                        <input type="radio" name="package_id" value="<?= $p['id'] ?>" class="d-none package-radio" <?= $selectedPackage === $p['slug'] ? 'checked' : '' ?> required>
                                        <div class="package-card p-3 cursor-pointer package-select-card">
                                            <strong><?= e($p['name']) ?></strong>
                                            <div class="package-price"><?= formatMoney((float)$p['price_per_guest']) ?>/guest</div>
                                            <small class="text-muted"><?= e(substr($p['description'], 0, 60)) ?>...</small>
                                        </div>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-outline-secondary" data-prev><i class="bi bi-arrow-left"></i> Back</button>
                                <button type="button" class="btn btn-gold" data-next>Next <i class="bi bi-arrow-right"></i></button>
                            </div>
                        </div>
                        <!-- Step 3 -->
                        <div class="wizard-pane" data-step="3" style="display:none">
                            <h4 class="mb-4">Dietary preferences & special requests</h4>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Dietary Requirements</label>
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php foreach (['Vegetarian','Vegan','Halal','Gluten-Free','Nut Allergy','Dairy-Free'] as $d): ?>
                                        <label class="btn btn-outline-secondary btn-sm">
                                            <input type="checkbox" name="dietary[]" value="<?= strtolower(str_replace([' ','-'],'',$d)) ?>" class="d-none"> <?= $d ?>
                                        </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Special Requests</label>
                                    <textarea name="special_requests" class="form-control" rows="3" placeholder="Theme colors, specific dishes, setup requirements..."></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Promo Code (optional)</label>
                                    <input type="text" name="promo_code" class="form-control" placeholder="e.g. WELCOME10">
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-outline-secondary" data-prev><i class="bi bi-arrow-left"></i> Back</button>
                                <button type="button" class="btn btn-gold" data-next>Next <i class="bi bi-arrow-right"></i></button>
                            </div>
                        </div>
                        <!-- Step 4 -->
                        <div class="wizard-pane" data-step="4" style="display:none">
                            <h4 class="mb-4">Review & confirm</h4>
                            <div class="alert alert-info"><i class="bi bi-info-circle"></i> A 30% deposit is required to confirm your booking. Pay securely via Mobile Money or card.</div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Your Name</label>
                                    <input type="text" name="contact_name" class="form-control" required value="<?= e(isLoggedIn() ? currentUser()['first_name'] . ' ' . currentUser()['last_name'] : '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="contact_email" class="form-control" required value="<?= e(isLoggedIn() ? currentUser()['email'] : '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" name="contact_phone" class="form-control" required placeholder="+233 XX XXX XXXX">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Payment Method</label>
                                    <select name="payment_method" class="form-select" required>
                                        <?php foreach ($config['payment_methods'] as $key => $label): ?>
                                        <option value="<?= e($key) ?>"><?= e($label) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-outline-secondary" data-prev><i class="bi bi-arrow-left"></i> Back</button>
                                <button type="submit" class="btn btn-gold btn-lg"><i class="bi bi-check-circle"></i> Confirm Booking & Pay Deposit</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="quote-calculator" id="quoteCalculator" data-aos="fade-left">
                    <h5><i class="bi bi-calculator"></i> Instant Quote Calculator</h5>
                    <p class="small opacity-75">Real-time pricing as you configure your event</p>
                    <div class="quote-result">
                        <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><span id="quoteSubtotal">₵0.00</span></div>
                        <div class="d-flex justify-content-between mb-2"><span>Tax (12.5%)</span><span id="quoteTax">₵0.00</span></div>
                        <hr style="border-color:rgba(255,255,255,0.2)">
                        <div class="d-flex justify-content-between mb-2"><strong>Total</strong><span class="quote-total" id="quoteTotal">₵0.00</span></div>
                        <div class="d-flex justify-content-between"><span>Deposit (30%)</span><span id="quoteDeposit">₵0.00</span></div>
                    </div>
                </div>

                <div class="mt-4 p-4 bg-white rounded-4 shadow" data-aos="fade-left" data-aos-delay="100">
                    <h6><i class="bi bi-calendar3"></i> Live Availability</h6>
                    <div id="availabilityCalendar" class="availability-calendar mt-3"></div>
                    <small class="text-muted d-block mt-2"><span class="cal-day available d-inline-block" style="width:20px;height:20px;"></span> Available &nbsp;
                    <span class="cal-day limited d-inline-block" style="width:20px;height:20px;"></span> Limited</small>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    // Package selection highlight
    document.querySelectorAll('.package-radio').forEach(radio => {
        radio.addEventListener('change', () => {
            document.querySelectorAll('.package-select-card').forEach(c => c.classList.remove('border', 'border-warning'));
            if (radio.checked) radio.closest('label').querySelector('.package-select-card').classList.add('border', 'border-warning');
        });
        if (radio.checked) radio.dispatchEvent(new Event('change'));
    });

    // Load availability
    try {
        const res = await fetch('/api/availability');
        const data = await res.json();
        const cal = document.getElementById('availabilityCalendar');
        if (cal && data.days) {
            data.days.slice(0, 28).forEach(d => {
                const el = document.createElement('div');
                el.className = 'cal-day ' + d.status;
                el.textContent = new Date(d.date).getDate();
                el.title = d.date;
                cal.appendChild(el);
            });
        }
    } catch(e) {}
});
</script>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
