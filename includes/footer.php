<?php
$isAuthPage = $isAuthPage ?? str_contains($bodyClass ?? '', 'auth-page');
if (!$isDashboard ?? false):
?>
</main>

<?php if ($isAuthPage): ?>
<footer class="auth-footer">
    <div class="auth-footer__inner">
        <a href="<?= url() ?>" class="auth-footer__brand">
            <span class="brand-icon"><i class="bi bi-cup-hot-fill"></i></span>
            <span>Splendid Catering</span>
        </a>
        <p class="auth-footer__copy">&copy; <?= date('Y') ?> Splendid Catering</p>
    </div>
</footer>
<?php else: ?>

<footer class="site-footer site-footer-premium footer-reveal">

    <div class="container">

        <div class="footer-cta-strip text-center" data-aos="fade-up">

            <h3 class="display-font mb-2" style="color:#fff;">Ready to taste the difference?</h3>

            <p class="text-muted-light mb-3 mx-auto" style="max-width:520px;">From weddings to corporate galas, Splendid Catering brings chef-crafted menus and flawless service to every table.</p>

            <div class="d-flex flex-wrap justify-content-center gap-2">

                <a href="<?= url('book') ?>" class="btn btn-gold btn-interactive"><i class="bi bi-calendar-check"></i> Book Catering</a>

                <a href="<?= url('menu') ?>" class="btn btn-outline-light btn-interactive">Explore Menu</a>

            </div>

        </div>



        <div class="row g-4">

            <div class="col-lg-4">

                <div class="footer-brand mb-3">

                    <span class="brand-icon"><i class="bi bi-cup-hot-fill"></i></span>

                    <strong>Splendid Catering</strong>

                </div>

                <p class="text-muted-light">Ghana's premier smart catering platform. From intimate gatherings to grand celebrations, we deliver world-class cuisine with flawless execution.</p>

                <div class="social-links">

                    <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>

                    <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>

                    <a href="#" aria-label="X"><i class="bi bi-twitter-x"></i></a>

                    <a href="#" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>

                </div>

            </div>

            <div class="col-6 col-lg-2">

                <h6>Quick Links</h6>

                <ul class="footer-links">

                    <li><a href="<?= url('about') ?>">About Us</a></li>

                    <li><a href="<?= url('services') ?>">Services</a></li>

                    <li><a href="<?= url('packages') ?>">Packages</a></li>

                    <li><a href="<?= url('menu') ?>">Our Menu</a></li>

                    <li><a href="<?= url('gallery') ?>">Gallery</a></li>

                </ul>

            </div>

            <div class="col-6 col-lg-2">

                <h6>Services</h6>

                <ul class="footer-links">

                    <li><a href="<?= url('services') ?>">Weddings</a></li>

                    <li><a href="<?= url('services') ?>">Corporate</a></li>

                    <li><a href="<?= url('services') ?>">Private Chef</a></li>

                    <li><a href="<?= url('book') ?>">Get Quote</a></li>

                    <li><a href="<?= url('contact') ?>">Contact</a></li>

                </ul>

            </div>

            <div class="col-lg-4">

                <h6>Contact Us</h6>

                <ul class="footer-contact">

                    <?php $business = getBusinessLocation(); ?>
                    <li><i class="bi bi-geo-alt"></i> <?= e($business['address']) ?></li>

                    <li><i class="bi bi-telephone"></i> <?= e($business['phone']) ?></li>

                    <li><i class="bi bi-envelope"></i> <?= e($business['email']) ?></li>

                    <li><i class="bi bi-clock"></i> <?= e($business['hours']) ?></li>

                </ul>

            </div>

        </div>

        <hr class="footer-divider">

        <div class="d-flex flex-wrap justify-content-between align-items-center">

            <p class="mb-0 text-muted-light small">&copy; <?= date('Y') ?> Splendid Catering. All rights reserved.</p>

            <p class="mb-0 text-muted-light small">Crafted with care for unforgettable events</p>

        </div>

    </div>

</footer>



<!-- Gallery Lightbox -->
<div class="lightbox" id="galleryLightbox" role="dialog" aria-modal="true" aria-label="Image preview" hidden>
    <button type="button" class="lightbox__close" id="lightboxClose" aria-label="Close preview"><i class="bi bi-x-lg"></i></button>
    <button type="button" class="lightbox__nav lightbox__nav--prev" id="lightboxPrev" aria-label="Previous image"><i class="bi bi-chevron-left"></i></button>
    <button type="button" class="lightbox__nav lightbox__nav--next" id="lightboxNext" aria-label="Next image"><i class="bi bi-chevron-right"></i></button>
    <div class="lightbox__dialog">
        <div class="lightbox__counter" id="lightboxCounter"></div>
        <div class="lightbox__image-wrap">
            <img src="" alt="" id="lightboxImage">
        </div>
        <div class="lightbox__caption" id="lightboxCaption"></div>
    </div>
</div>



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
<?php endif; ?>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<script src="<?= asset('js/app.js') ?>"></script>

</body>

</html>

