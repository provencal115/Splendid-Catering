<?php if (!$isDashboard ?? false): ?>
<footer class="site-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="footer-brand mb-3">
                    <span class="brand-icon"><i class="bi bi-cup-hot-fill"></i></span>
                    <strong>Splendid Catering</strong>
                </div>
                <p class="text-muted-light">Ghana's premier smart catering platform. From intimate gatherings to grand celebrations, we deliver world-class cuisine with flawless execution.</p>
                <div class="social-links">
                    <a href="#"><i class="bi bi-facebook"></i></a>
                    <a href="#"><i class="bi bi-instagram"></i></a>
                    <a href="#"><i class="bi bi-twitter-x"></i></a>
                    <a href="#"><i class="bi bi-linkedin"></i></a>
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <h6>Quick Links</h6>
                <ul class="footer-links">
                    <li><a href="<?= url('about') ?>">About Us</a></li>
                    <li><a href="<?= url('services') ?>">Services</a></li>
                    <li><a href="<?= url('packages') ?>">Packages</a></li>
                    <li><a href="<?= url('menu') ?>">Our Menu</a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-2">
                <h6>Services</h6>
                <ul class="footer-links">
                    <li><a href="<?= url('services') ?>">Weddings</a></li>
                    <li><a href="<?= url('services') ?>">Corporate</a></li>
                    <li><a href="<?= url('services') ?>">Private Chef</a></li>
                    <li><a href="<?= url('book') ?>">Get Quote</a></li>
                </ul>
            </div>
            <div class="col-lg-4">
                <h6>Contact Us</h6>
                <ul class="footer-contact">
                    <li><i class="bi bi-geo-alt"></i> 14 Independence Avenue, Accra, Ghana</li>
                    <li><i class="bi bi-telephone"></i> <?= e($config['support_phone']) ?></li>
                    <li><i class="bi bi-envelope"></i> <?= e($config['support_email']) ?></li>
                    <li><i class="bi bi-clock"></i> Mon–Sat: 8AM – 8PM</li>
                </ul>
            </div>
        </div>
        <hr class="footer-divider">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <p class="mb-0 text-muted-light small">&copy; <?= date('Y') ?> Splendid Catering. All rights reserved.</p>
            <p class="mb-0 text-muted-light small">Powered by Smart Catering Technology</p>
        </div>
    </div>
</footer>

<!-- AI Chat Widget -->
<div id="aiChatWidget" class="ai-chat-widget">
    <button class="chat-toggle" id="chatToggle" aria-label="Open chat">
        <i class="bi bi-chat-dots-fill"></i>
    </button>
    <div class="chat-panel" id="chatPanel">
        <div class="chat-header">
            <div>
                <strong><i class="bi bi-robot"></i> Splendid AI Assistant</strong>
                <small>Ask about menus, packages & bookings</small>
            </div>
            <button class="btn-close btn-close-white" id="chatClose"></button>
        </div>
        <div class="chat-messages" id="chatMessages">
            <div class="chat-msg bot">
                <p>Hello! 👋 I'm your Splendid Catering assistant. How can I help you plan your event today?</p>
            </div>
        </div>
        <div class="chat-input">
            <input type="text" id="chatInput" placeholder="Ask about packages, menus, pricing..." autocomplete="off">
            <button id="chatSend"><i class="bi bi-send-fill"></i></button>
        </div>
    </div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
